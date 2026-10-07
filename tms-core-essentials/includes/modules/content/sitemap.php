<?php
/**
 * Includes -> Modules -> Content -> Sitemap
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_sitemap_is_enabled(): bool {
	return (bool) tcres_option_get( 'sitemap', 'enable' );
}


/**
 * Language slug for Polylang-aware queries, or empty string.
 */
function tcres_sitemap_get_query_lang(): string {
	$lang = tcres_multilingual_get_current_language();
	return is_string( $lang )
		? $lang
		: '';
}


/**
 * Whether taxonomy is registered for the post type.
 */
function tcres_sitemap_taxonomy_is_valid_for_post_type( string $taxonomy, string $post_type ): bool {
	if ( $taxonomy === '' ) return false;

	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax || ! is_array( $tax->object_type ) ) return false;

	return in_array( $post_type, $tax->object_type, true );
}


/**
 * Parse section max depth from settings (empty = null = use global).
 *
 * @param mixed $value
 */
function tcres_sitemap_parse_section_max_depth( $value ): ?int {
	if ( $value === null ) return null;
	if ( is_string( $value ) && trim( $value ) === '' ) return null;

	$n = absint( $value );

	return $n >= 1
		? $n
		: null;
}


/**
 * Normalize one post-type branch config.
 *
 * @param array<string, mixed> $cfg
 * @return array{parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: int|null}
 */
function tcres_sitemap_normalize_post_type_cfg( string $post_type, array $cfg ): array {
	$taxonomy = isset( $cfg['taxonomy'] )
		? sanitize_key( (string) $cfg['taxonomy'] )
		: ( $post_type === 'post' ? 'category' : '' );

	$out = array(
		'parent_page_id' => isset( $cfg['parent_page_id'] ) ? (int) $cfg['parent_page_id'] : 0,
		'taxonomy'       => $taxonomy,
		'show_taxonomy'  => ! empty( $cfg['show_taxonomy'] ),
		'show_posts'     => ! empty( $cfg['show_posts'] ),
		'max_depth'      => array_key_exists( 'max_depth', $cfg )
			? tcres_sitemap_parse_section_max_depth( $cfg['max_depth'] )
			: null,
	);

	if ( ! tcres_sitemap_taxonomy_is_valid_for_post_type( $out['taxonomy'], $post_type ) ) :
		$out['taxonomy'] = $post_type === 'post'
			? 'category'
			: '';
		if ( $out['taxonomy'] === '' || ! tcres_sitemap_taxonomy_is_valid_for_post_type( $out['taxonomy'], $post_type ) ) :
			$out['show_taxonomy'] = false;
		endif;
	endif;

	return $out;
}


/**
 * Build post_types map from panel-shaped blog + cpt settings.
 *
 * @param array<string, mixed> $config
 * @return array<string, array{parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: int|null}>
 */
function tcres_sitemap_build_post_types_config( array $config ): array {
	$post_types = array();

	if ( ! empty( $config['blog'] ) ) :
		$post_types['post'] = tcres_sitemap_normalize_post_type_cfg(
			'post',
			array(
				'parent_page_id' => isset( $config['blog_parent_page_id'] ) ? (int) $config['blog_parent_page_id'] : 0,
				'taxonomy'       => isset( $config['blog_taxonomy'] ) ? (string) $config['blog_taxonomy'] : 'category',
				'show_taxonomy'  => ! empty( $config['blog_show_taxonomy'] ),
				'show_posts'     => ! empty( $config['blog_show_posts'] ),
				'max_depth'      => $config['blog_max_depth'] ?? '',
			)
		);
	endif;

	$cpt_rows = isset( $config['cpt'] ) && is_array( $config['cpt'] )
		? $config['cpt']
		: array();
	foreach ( $cpt_rows as $post_type => $row ) :
		if ( ! is_string( $post_type ) || ! post_type_exists( $post_type ) ) continue;
		if ( $post_type === 'post' || $post_type === 'page' ) continue;
		if ( ! is_array( $row ) || empty( $row['enable'] ) ) continue;

		$post_types[ $post_type ] = tcres_sitemap_normalize_post_type_cfg( $post_type, $row );
	endforeach;

	return $post_types;
}


/**
 * Runtime config: panel settings as defaults, $args override.
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function tcres_sitemap_get_config( array $args = array() ): array {
	$group = tcres_option_get( 'sitemap' );
	if ( ! is_array( $group ) ) $group = array();

	$defaults = array(
		'hide_empty'          => ! empty( $group['hide_empty'] ),
		'show_list_bullets'   => array_key_exists( 'show_list_bullets', $group ) ? ! empty( $group['show_list_bullets'] ) : true,
		'max_depth'           => isset( $group['max_depth'] ) ? max( 1, absint( $group['max_depth'] ) ) : 3,
		'page_sort'           => isset( $group['page_sort'] ) ? (string) $group['page_sort'] : 'menu_order',
		'blog'                => ! empty( $group['blog'] ),
		'blog_parent_page_id' => isset( $group['blog_parent_page_id'] ) ? absint( $group['blog_parent_page_id'] ) : 0,
		'blog_taxonomy'       => isset( $group['blog_taxonomy'] ) ? (string) $group['blog_taxonomy'] : 'category',
		'blog_show_taxonomy'  => ! empty( $group['blog_show_taxonomy'] ),
		'blog_show_posts'     => ! empty( $group['blog_show_posts'] ),
		'blog_max_depth'      => isset( $group['blog_max_depth'] ) ? (string) $group['blog_max_depth'] : '',
		'cpt'                 => isset( $group['cpt'] ) && is_array( $group['cpt'] ) ? $group['cpt'] : array(),
		'class'               => '',
	);

	$config = array_replace_recursive( $defaults, $args );

	$page_sort = isset( $config['page_sort'] )
		? (string) $config['page_sort']
		: 'menu_order';
	$config['page_sort'] = $page_sort === 'alphabetical'
		? 'alphabetical'
		: 'menu_order';
	$config['max_depth'] = max( 1, absint( $config['max_depth'] ?? 3 ) );
	$config['hide_empty'] = ! empty( $config['hide_empty'] );
	$config['show_list_bullets'] = ! empty( $config['show_list_bullets'] );

	if ( array_key_exists( 'post_types', $args ) && is_array( $args['post_types'] ) ) :
		$clean = array();
		foreach ( $args['post_types'] as $post_type => $cfg ) :
			if ( ! is_string( $post_type ) || ! post_type_exists( $post_type ) ) continue;
			if ( ! is_array( $cfg ) ) continue;
			$clean[ $post_type ] = tcres_sitemap_normalize_post_type_cfg( $post_type, $cfg );
		endforeach;
		$config['post_types'] = $clean;
	else :
		$config['post_types'] = tcres_sitemap_build_post_types_config( $config );
	endif;

	return $config;
}


/**
 * @return array{sort_column: string, sort_order: string}
 */
function tcres_sitemap_get_page_query_sort( string $page_sort ): array {
	if ( $page_sort === 'alphabetical' ) :
		return array(
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
		);
	endif;

	return array(
		'sort_column' => 'menu_order,post_title',
		'sort_order'  => 'ASC',
	);
}


/**
 * @return array<int, WP_Post>
 */
function tcres_sitemap_get_pages_by_parent( int $parent_id, string $lang, string $page_sort ): array {
	$sort  = tcres_sitemap_get_page_query_sort( $page_sort );
	$query = array(
		'parent'      => $parent_id,
		'post_status' => 'publish',
		'sort_column' => $sort['sort_column'],
		'sort_order'  => $sort['sort_order'],
	);
	if ( $lang !== '' ) :
		$query['lang'] = $lang;
	endif;

	$pages = get_pages( $query );
	return is_array( $pages )
		? $pages
		: array();
}


/**
 * Resolve anchor page ID for a post-type branch (Posts page fallback for post).
 *
 * @param array{parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: int|null} $cfg
 */
function tcres_sitemap_resolve_parent_page_id( string $post_type, array $cfg ): int {
	if ( $post_type === 'post' ) :
		$raw = (int) ( $cfg['parent_page_id'] ?? 0 );
		if ( $raw > 0 ) :
			return tcres_multilingual_translate_post_id( $raw );
		endif;
		$blog = (int) get_option( 'page_for_posts' );
		return $blog > 0
			? tcres_multilingual_translate_post_id( $blog )
			: 0;
	endif;

	$parent = (int) ( $cfg['parent_page_id'] ?? 0 );
	return $parent > 0
		? tcres_multilingual_translate_post_id( $parent )
		: 0;
}


/**
 * @return array<int, WP_Term>
 */
function tcres_sitemap_get_terms_for_taxonomy( string $taxonomy, bool $hide_empty, string $lang ): array {
	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => $hide_empty,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	if ( $lang !== '' ) $args['lang'] = $lang;

	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) return array();

	return $terms;
}


/**
 * @return array<int, WP_Post>
 */
function tcres_sitemap_get_posts_for_term( string $post_type, string $taxonomy, int $term_id, string $lang ): array {
	$cache_key = 'tcres_sitemap_posts_term_' . md5( wp_json_encode( array( $post_type, $taxonomy, $term_id, $lang ) ) );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) return $cached;

	$term_id = tcres_multilingual_translate_term_id( $term_id, $taxonomy );
	if ( $term_id <= 0 ) return array();

	$q = array(
		'post_type'              => $post_type,
		'posts_per_page'         => -1,
		'post_status'            => 'publish',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'fields'                 => 'ids',
		'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached via transient.
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	);
	if ( $lang !== '' ) $q['lang'] = $lang;

	$post_ids = get_posts( $q );
	$post_ids = is_array( $post_ids )
		? $post_ids
		: array();
	$posts    = array();
	foreach ( $post_ids as $post_id ) :
		$post = get_post( (int) $post_id );
		if ( $post instanceof WP_Post ) $posts[] = $post;
	endforeach;

	set_transient( $cache_key, $posts, HOUR_IN_SECONDS );

	return $posts;
}


/**
 * @return array<int, WP_Post>
 */
function tcres_sitemap_get_posts_for_post_type( string $post_type, string $lang ): array {
	$q = array(
		'post_type'              => $post_type,
		'posts_per_page'         => -1,
		'post_status'            => 'publish',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);
	if ( $lang !== '' ) $q['lang'] = $lang;

	$posts = get_posts( $q );
	return is_array( $posts )
		? $posts
		: array();
}


/**
 * @param array<int, WP_Post> $posts
 */
function tcres_sitemap_render_post_links( array $posts ): string {
	if ( empty( $posts ) ) return '';

	$html = '<ul class="tcres-sitemap-posts">';
	foreach ( $posts as $post ) :
		if ( ! $post instanceof WP_Post ) continue;
		$html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
	endforeach;
	$html .= '</ul>';

	return $html;
}


/**
 * @param array{parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: int|null} $cfg
 * @param array<string, mixed>                                                                                    $args
 */
function tcres_sitemap_get_section_max_depth( array $cfg, array $args ): int {
	return isset( $cfg['max_depth'] ) && $cfg['max_depth'] !== null
		? (int) $cfg['max_depth']
		: (int) $args['max_depth'];
}


/**
 * @param array{parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: int|null} $cfg
 * @param array<string, mixed>                                                                                    $args
 */
function tcres_sitemap_render_post_type_section( string $post_type, array $cfg, array $args, string $lang ): string {
	if ( empty( $cfg['show_taxonomy'] ) && empty( $cfg['show_posts'] ) ) return '';

	$section_max = tcres_sitemap_get_section_max_depth( $cfg, $args );
	if ( $section_max < 2 ) return '';

	$taxonomy = $cfg['taxonomy'];
	$show_tax = ! empty( $cfg['show_taxonomy'] )
		&& $taxonomy !== ''
		&& tcres_sitemap_taxonomy_is_valid_for_post_type( $taxonomy, $post_type );
	$show_pts = ! empty( $cfg['show_posts'] );

	if ( $show_tax && $show_pts && $section_max < 3 ) $show_pts = false;

	$inner = '';

	if ( $show_tax ) :
		$terms = tcres_sitemap_get_terms_for_taxonomy( $taxonomy, ! empty( $args['hide_empty'] ), $lang );
		foreach ( $terms as $term ) :
			if ( ! $term instanceof WP_Term ) continue;
			$tlink = get_term_link( $term );
			if ( is_wp_error( $tlink ) ) continue;
			$inner .= '<li><a href="' . esc_url( $tlink ) . '">' . esc_html( $term->name ) . '</a>';
			if ( $show_pts ) :
				$posts  = tcres_sitemap_get_posts_for_term( $post_type, $taxonomy, (int) $term->term_id, $lang );
				$inner .= tcres_sitemap_render_post_links( $posts );
			endif;
			$inner .= '</li>';
		endforeach;
	elseif ( $show_pts ) :
		$posts  = tcres_sitemap_get_posts_for_post_type( $post_type, $lang );
		$inner .= tcres_sitemap_render_post_links( $posts );
	endif;

	if ( $inner === '' ) return '';

	if ( $show_tax ) :
		$ul_class = $taxonomy === 'category'
			? 'tcres-sitemap-categories tcres-sitemap-post-type-' . sanitize_html_class( $post_type )
			: 'tcres-sitemap-terms tcres-sitemap-terms-' . sanitize_html_class( $taxonomy ) . ' tcres-sitemap-post-type-' . sanitize_html_class( $post_type );
		return '<ul class="' . esc_attr( $ul_class ) . '">' . $inner . '</ul>';
	endif;

	return $inner;
}


/**
 * @param array<string, mixed> $args
 */
function tcres_sitemap_build_page_list_item( WP_Post $page, int $depth, array $args, string $lang ): string {
	$html  = '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $page->post_title ) . '</a>';
	$extra = '';

	$post_types = isset( $args['post_types'] ) && is_array( $args['post_types'] )
		? $args['post_types']
		: array();
	foreach ( $post_types as $pt => $cfg ) :
		if ( ! is_array( $cfg ) ) continue;
		$parent_id = tcres_sitemap_resolve_parent_page_id( (string) $pt, $cfg );
		if ( $parent_id !== (int) $page->ID ) continue;
		$extra .= tcres_sitemap_render_post_type_section( (string) $pt, $cfg, $args, $lang );
	endforeach;

	if ( $depth < (int) $args['max_depth'] ) :
		$children = tcres_sitemap_get_pages_by_parent( (int) $page->ID, $lang, (string) $args['page_sort'] );
		if ( ! empty( $children ) ) :
			$sub = '';
			foreach ( $children as $child ) :
				if ( ! $child instanceof WP_Post ) continue;
				$sub .= tcres_sitemap_build_page_list_item( $child, $depth + 1, $args, $lang );
			endforeach;
			if ( $sub !== '' ) :
				$extra .= '<ul class="tcres-sitemap-pages">' . $sub . '</ul>';
			endif;
		endif;
	endif;

	$html .= $extra . '</li>';
	return $html;
}


/**
 * Build sitemap HTML for the current request.
 *
 * @param array<string, mixed> $args Overrides panel settings (same keys) plus optional `class` / `post_types`.
 */
function tcres_sitemap_get( array $args = array() ): string {
	if ( ! tcres_sitemap_is_enabled() ) return '';

	$config = tcres_sitemap_get_config( $args );
	$lang   = tcres_sitemap_get_query_lang();
	$roots  = tcres_sitemap_get_pages_by_parent( 0, $lang, (string) $config['page_sort'] );

	$list = '';
	foreach ( $roots as $page ) :
		if ( ! $page instanceof WP_Post ) continue;
		$list .= tcres_sitemap_build_page_list_item( $page, 1, $config, $lang );
	endforeach;

	/**
	 * Filter sitemap inner list markup (li items) before wrap.
	 *
	 * @param string               $list
	 * @param array<string, mixed> $config
	 */
	$list = apply_filters( 'tcres_sitemap_list_html', $list, $config );
	if ( ! is_string( $list ) ) $list = '';

	$inner = '<ul class="tcres-sitemap-root">' . $list . '</ul>';

	$class = 'tcres-sitemap';
	if ( empty( $config['show_list_bullets'] ) ) $class .= ' is-no-bullets';

	$extra = isset( $config['class'] )
		? trim( (string) $config['class'] )
		: '';
	if ( $extra !== '' ) $class .= ' ' . $extra;

	$html = '<div class="' . esc_attr( $class ) . '">' . $inner . '</div>';

	/**
	 * Filter full sitemap HTML.
	 *
	 * @param string               $html
	 * @param array<string, mixed> $config
	 */
	$html = apply_filters( 'tcres_sitemap_html', $html, $config );
	return is_string( $html )
		? $html
		: '';
}


function tcres_sitemap_shortcode(): string {
	return tcres_kses_html( tcres_sitemap_get() );
}


add_action( 'init', function(): void {
	add_shortcode( 'tcres-sitemap', 'tcres_sitemap_shortcode' );
} );
