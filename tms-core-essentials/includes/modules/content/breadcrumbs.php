<?php
/**
 * Includes -> Modules -> Content -> Breadcrumbs
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_breadcrumb_is_enabled(): bool {
	return (bool) tcres_option_get( 'breadcrumbs', 'enable' );
}


/**
 * Runtime config: panel settings as defaults, $args override.
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function tcres_breadcrumb_get_config( array $args = array() ): array {
	$group = tcres_option_get( 'breadcrumbs' );
	if ( ! is_array( $group ) ) $group = array();

	$defaults = array(
		'separator'     => isset( $group['separator'] ) ? (string) $group['separator'] : '/',
		'show_home'     => ! empty( $group['show_home'] ),
		'home_label'    => isset( $group['home_label'] ) ? (string) $group['home_label'] : '',
		'home_display'  => isset( $group['home_display'] ) ? (string) $group['home_display'] : 'icon_label',
		'blog_page'     => isset( $group['blog_page'] ) ? (int) $group['blog_page'] : 0,
		'blog_taxonomy' => isset( $group['blog_taxonomy'] ) ? (string) $group['blog_taxonomy'] : 'category',
		'cpt'           => isset( $group['cpt'] ) && is_array( $group['cpt'] ) ? $group['cpt'] : array(),
		'class'         => '',
	);

	$config = array_replace_recursive( $defaults, $args );
	$display = isset( $config['home_display'] )
		? (string) $config['home_display']
		: 'icon_label';
	if ( ! in_array( $display, array( 'icon_label', 'label', 'icon' ), true ) ) :
		$display = 'icon_label';
	endif;
	$config['home_display'] = $display;

	return $config;
}


/**
 * Resolve blog landing page ID (0 = none).
 * Config: 0 = automatic Posts page, -1 = none, >0 = page ID.
 *
 * @param array<string, mixed> $config
 */
function tcres_breadcrumb_resolve_blog_page_id( array $config ): int {
	$page_id = isset( $config['blog_page'] )
		? (int) $config['blog_page']
		: 0;
	if ( $page_id === -1 ) return 0;

	if ( $page_id <= 0 ) :
		$page_id = (int) get_option( 'page_for_posts' );
	endif;

	return $page_id > 0
		? tcres_multilingual_translate_post_id( $page_id )
		: 0;
}


/**
 * @param array<string, mixed> $config
 * @return array{page: int, taxonomy: string}
 */
function tcres_breadcrumb_get_cpt_config( array $config, string $post_type ): array {
	$rows = isset( $config['cpt'] ) && is_array( $config['cpt'] )
		? $config['cpt']
		: array();
	$row  = isset( $rows[ $post_type ] ) && is_array( $rows[ $post_type ] )
		? $rows[ $post_type ]
		: array();

	return array(
		'page'     => isset( $row['page'] ) ? (int) $row['page'] : 0,
		'taxonomy' => isset( $row['taxonomy'] ) ? (string) $row['taxonomy'] : '',
	);
}


/**
 * @return array{label: string, url: string, icon?: string, class?: string, label_sr_only?: bool}
 */
function tcres_breadcrumb_make_item(
	string $label,
	string $url = '',
	string $icon = '',
	string $class = '',
	bool $label_sr_only = false
): array {
	$item = array(
		'label' => $label,
		'url'   => $url,
	);
	if ( $icon !== '' ) $item['icon'] = $icon;
	if ( $class !== '' ) $item['class'] = $class;
	if ( $label_sr_only ) $item['label_sr_only'] = true;

	return $item;
}


/**
 * @param array<string, mixed> $config
 * @return array{label: string, url: string, icon?: string, class?: string, label_sr_only?: bool}|null
 */
function tcres_breadcrumb_home_item( array $config, bool $is_current ): ?array {
	if ( empty( $config['show_home'] ) ) return null;

	$label = isset( $config['home_label'] )
		? trim( (string) $config['home_label'] )
		: '';
	if ( $label === '' ) :
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id > 0 ) :
			$front_id = tcres_multilingual_translate_post_id( $front_id );
			$label    = get_the_title( $front_id );
		endif;
	endif;
	if ( $label === '' ) $label = get_bloginfo( 'name' );

	$display = isset( $config['home_display'] )
		? (string) $config['home_display']
		: 'icon_label';
	$icon    = ( $display === 'icon_label' || $display === 'icon' )
		? 'home'
		: '';
	$sr_only = ( $display === 'icon' );

	return tcres_breadcrumb_make_item(
		$label,
		$is_current ? '' : home_url( '/' ),
		$icon,
		'tcres-breadcrumbs-home',
		$sr_only
	);
}


/**
 * Append page ancestor crumbs (translated).
 *
 * @param array<int, array{label: string, url: string, icon?: string}> $items
 */
function tcres_breadcrumb_append_page_ancestors( array &$items, int $post_id ): void {
	$ancestors = array_reverse( get_post_ancestors( $post_id ) );
	foreach ( $ancestors as $ancestor_id ) :
		$ancestor_id = tcres_multilingual_translate_post_id( (int) $ancestor_id );
		if ( $ancestor_id <= 0 ) continue;
		$items[] = tcres_breadcrumb_make_item(
			get_the_title( $ancestor_id ),
			(string) get_permalink( $ancestor_id )
		);
	endforeach;
}


/**
 * Append hierarchical term trail (ancestors + term). Current term uses empty URL when $as_current.
 *
 * @param array<int, array{label: string, url: string, icon?: string}> $items
 */
function tcres_breadcrumb_append_term_trail( array &$items, WP_Term $term, bool $as_current = false ): void {
	$taxonomy  = $term->taxonomy;
	$term_id   = tcres_multilingual_translate_term_id( (int) $term->term_id, $taxonomy );
	$term      = get_term( $term_id, $taxonomy );
	if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) return;

	$ancestors = get_ancestors( $term->term_id, $taxonomy );
	foreach ( array_reverse( $ancestors ) as $ancestor_id ) :
		$ancestor_id = tcres_multilingual_translate_term_id( (int) $ancestor_id, $taxonomy );
		$ancestor    = get_term( $ancestor_id, $taxonomy );
		if ( ! $ancestor instanceof WP_Term || is_wp_error( $ancestor ) ) continue;

		$link = get_term_link( $ancestor );
		if ( is_wp_error( $link ) ) continue;

		$items[] = tcres_breadcrumb_make_item( $ancestor->name, (string) $link );
	endforeach;

	$link = '';
	if ( ! $as_current ) :
		$term_link = get_term_link( $term );
		$link      = is_wp_error( $term_link )
			? ''
			: (string) $term_link;
	endif;

	$items[] = tcres_breadcrumb_make_item( $term->name, $link );
}


/**
 * Primary or first term for a post/taxonomy pair.
 */
function tcres_breadcrumb_get_primary_term( int $post_id, string $taxonomy ): ?WP_Term {
	if ( $post_id <= 0 || $taxonomy === '' || ! taxonomy_exists( $taxonomy ) ) return null;

	$terms = get_the_terms( $post_id, $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) return null;

	$primary_id = function_exists( 'tcres_related_content_get_primary_term_id' )
		? tcres_related_content_get_primary_term_id( $post_id, $taxonomy )
		: 0;

	if ( $primary_id > 0 ) :
		foreach ( $terms as $term ) :
			if ( $term instanceof WP_Term && (int) $term->term_id === $primary_id ) :
				return $term;
			endif;
		endforeach;
		$primary = get_term( $primary_id, $taxonomy );
		if ( $primary instanceof WP_Term && ! is_wp_error( $primary ) ) :
			return $primary;
		endif;
	endif;

	$first = reset( $terms );
	return $first instanceof WP_Term
		? $first
		: null;
}


/**
 * Find CPT config whose taxonomy matches.
 *
 * @param array<string, mixed> $config
 * @return array{page: int, taxonomy: string}|null
 */
function tcres_breadcrumb_find_cpt_by_taxonomy( array $config, string $taxonomy ): ?array {
	if ( $taxonomy === '' ) return null;

	$rows = isset( $config['cpt'] ) && is_array( $config['cpt'] )
		? $config['cpt']
		: array();
	foreach ( $rows as $post_type => $row ) :
		if ( ! is_array( $row ) ) continue;
		if ( (string) ( $row['taxonomy'] ?? '' ) !== $taxonomy ) continue;

		return tcres_breadcrumb_get_cpt_config( $config, (string) $post_type );
	endforeach;

	return null;
}


/**
 * @param array<string, mixed> $config
 * @return array<int, array{label: string, url: string, icon?: string}>
 */
function tcres_breadcrumb_get_items( array $config ): array {
	$items = array();

	$home = tcres_breadcrumb_home_item( $config, is_front_page() );
	if ( $home !== null ) :
		$items[] = $home;
	endif;

	if ( is_front_page() ) :
		return $items;
	endif;

	// Blog posts index
	if ( is_home() ) :
		$blog_id = tcres_breadcrumb_resolve_blog_page_id( $config );
		$label   = $blog_id > 0
			? get_the_title( $blog_id )
			: __( 'Blog', 'tms-core-essentials' );
		$items[] = tcres_breadcrumb_make_item( $label !== '' ? $label : __( 'Blog', 'tms-core-essentials' ), '' );
		return $items;
	endif;

	// Pages (hierarchy)
	if ( is_page() ) :
		$post_id = tcres_multilingual_translate_post_id( (int) get_the_ID() );
		tcres_breadcrumb_append_page_ancestors( $items, $post_id );
		$items[] = tcres_breadcrumb_make_item( get_the_title( $post_id ), '' );
		return $items;
	endif;

	// Singular posts / CPTs
	if ( is_singular() ) :
		$post_id   = tcres_multilingual_translate_post_id( (int) get_the_ID() );
		$post_type = get_post_type( $post_id );
		if ( ! is_string( $post_type ) || $post_type === '' ) :
			$post_type = get_post_type();
		endif;

		if ( $post_type === 'post' ) :
			$blog_id = tcres_breadcrumb_resolve_blog_page_id( $config );
			if ( $blog_id > 0 ) :
				$items[] = tcres_breadcrumb_make_item(
					get_the_title( $blog_id ),
					(string) get_permalink( $blog_id )
				);
			endif;

			$taxonomy = isset( $config['blog_taxonomy'] )
				? (string) $config['blog_taxonomy']
				: '';
			if ( $taxonomy !== '' ) :
				$term = tcres_breadcrumb_get_primary_term( $post_id, $taxonomy );
				if ( $term instanceof WP_Term ) :
					tcres_breadcrumb_append_term_trail( $items, $term, false );
				endif;
			endif;
		else :
			$cpt = tcres_breadcrumb_get_cpt_config( $config, (string) $post_type );
			if ( $cpt['page'] > 0 ) :
				$page_id = tcres_multilingual_translate_post_id( $cpt['page'] );
				if ( $page_id > 0 ) :
					$items[] = tcres_breadcrumb_make_item(
						get_the_title( $page_id ),
						(string) get_permalink( $page_id )
					);
				endif;
			endif;

			if ( $cpt['taxonomy'] !== '' ) :
				$term = tcres_breadcrumb_get_primary_term( $post_id, $cpt['taxonomy'] );
				if ( $term instanceof WP_Term ) :
					tcres_breadcrumb_append_term_trail( $items, $term, false );
				endif;
			elseif ( is_post_type_hierarchical( (string) $post_type ) ) :
				tcres_breadcrumb_append_page_ancestors( $items, $post_id );
			endif;
		endif;

		$items[] = tcres_breadcrumb_make_item( get_the_title( $post_id ), '' );
		return $items;
	endif;

	// Taxonomy archives
	if ( is_category() || is_tag() || is_tax() ) :
		$term = get_queried_object();
		if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) :
			$taxonomy = $term->taxonomy;

			$blog_tax = isset( $config['blog_taxonomy'] )
				? (string) $config['blog_taxonomy']
				: '';
			if ( $blog_tax !== '' && $taxonomy === $blog_tax ) :
				$blog_id = tcres_breadcrumb_resolve_blog_page_id( $config );
				if ( $blog_id > 0 ) :
					$items[] = tcres_breadcrumb_make_item(
						get_the_title( $blog_id ),
						(string) get_permalink( $blog_id )
					);
				endif;
			else :
				$cpt = tcres_breadcrumb_find_cpt_by_taxonomy( $config, $taxonomy );
				if ( $cpt !== null && $cpt['page'] > 0 ) :
					$page_id = tcres_multilingual_translate_post_id( $cpt['page'] );
					if ( $page_id > 0 ) :
						$items[] = tcres_breadcrumb_make_item(
							get_the_title( $page_id ),
							(string) get_permalink( $page_id )
						);
					endif;
				endif;
			endif;

			tcres_breadcrumb_append_term_trail( $items, $term, true );
		endif;
		return $items;
	endif;

	// Post type archives
	if ( is_post_type_archive() ) :
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) :
			$post_type = reset( $post_type );
		endif;
		$post_type = is_string( $post_type )
			? $post_type
			: '';
		$cpt       = $post_type !== ''
			? tcres_breadcrumb_get_cpt_config( $config, $post_type )
			: array( 'page' => 0, 'taxonomy' => '' );

		if ( $cpt['page'] > 0 ) :
			$page_id = tcres_multilingual_translate_post_id( $cpt['page'] );
			$label   = $page_id > 0
				? get_the_title( $page_id )
				: post_type_archive_title( '', false );
			$items[] = tcres_breadcrumb_make_item( $label !== ''
				? $label
				: (string) $post_type, '' );
		else :
			$title = post_type_archive_title( '', false );
			if ( $title !== '' ) :
				$items[] = tcres_breadcrumb_make_item( $title, '' );
			endif;
		endif;
		return $items;
	endif;

	// Search
	if ( is_search() ) :
		$items[] = tcres_breadcrumb_make_item(
			sprintf(
				/* translators: %s: search query */
				__( 'Search results for “%s”', 'tms-core-essentials' ),
				get_search_query()
			),
			''
		);
		return $items;
	endif;

	// 404
	if ( is_404() ) :
		$items[] = tcres_breadcrumb_make_item( __( 'Page not found', 'tms-core-essentials' ), '' );
		return $items;
	endif;

	return $items;
}


/**
 * @param array<int, array{label: string, url: string, icon?: string}> $items
 * @param array<string, mixed>                                         $config
 */
function tcres_breadcrumb_render_html( array $items, array $config ): string {
	if ( empty( $items ) ) return '';

	$separator = isset( $config['separator'] )
		? (string) $config['separator']
		: '/';
	if ( $separator === '' ) $separator = '/';

	$class = 'tcres-breadcrumbs';
	$extra = isset( $config['class'] )
		? trim( (string) $config['class'] )
		: '';
	if ( $extra !== '' ) $class .= ' ' . $extra;

	$last_index = count( $items ) - 1;
	$list_html  = '';
	$position   = 0;

	foreach ( $items as $i => $item ) :
		$label = isset( $item['label'] )
			? (string) $item['label']
			: '';
		$url   = isset( $item['url'] )
			? (string) $item['url']
			: '';
		$icon  = isset( $item['icon'] )
			? (string) $item['icon']
			: '';
		$item_class = isset( $item['class'] )
			? trim( (string) $item['class'] )
			: '';
		$label_sr_only = ! empty( $item['label_sr_only'] );
		$is_last = ( $i === $last_index );
		$position++;

		$icon_html = '';
		if ( $icon !== '' ) :
			$icon_html = tcres_svg_icon_get(
				array(
					'icon' => $icon,
				)
			);
		endif;

		$label_class = $label_sr_only
			? ' class="screen-reader-text"'
			: '';
		$name_html   = $icon_html . '<span' . $label_class . ' itemprop="name">' . esc_html( $label ) . '</span>';
		$class_attr = $item_class !== ''
			? ' class="' . esc_attr( $item_class ) . '"'
			: '';
		if ( $url !== '' && ! $is_last ) :
			$inner = '<a href="' . esc_url( $url ) . '"' . $class_attr . ' itemprop="item">' . $name_html . '</a>';
		else :
			$inner = '<span' . $class_attr . ' itemprop="item">' . $name_html . '</span>';
		endif;

		$li_attrs = ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
		if ( $is_last ) :
			$li_attrs .= ' aria-current="page"';
		endif;

		$list_html .= '<li' . $li_attrs . '>' . $inner;
		$list_html .= '<meta itemprop="position" content="' . esc_attr( (string) $position ) . '" />';
		if ( ! $is_last ) :
			$list_html .= '<span class="tcres-breadcrumbs-separator" aria-hidden="true">' . esc_html( $separator ) . '</span>';
		endif;
		$list_html .= '</li>';
	endforeach;

	return sprintf(
		'<nav class="%1$s" role="navigation" aria-label="%2$s" itemscope itemtype="https://schema.org/BreadcrumbList"><ol>%3$s</ol></nav>',
		esc_attr( $class ),
		esc_attr__( 'Breadcrumb', 'tms-core-essentials' ),
		$list_html
	);
}


/**
 * Build breadcrumb HTML for the current request.
 *
 * @param array<string, mixed> $args Overrides panel settings (same keys) plus optional `class`.
 */
function tcres_breadcrumb_get( array $args = array() ): string {
	if ( ! tcres_breadcrumb_is_enabled() ) return '';

	$config = tcres_breadcrumb_get_config( $args );
	$items  = tcres_breadcrumb_get_items( $config );

	/**
	 * Filter breadcrumb items before HTML render.
	 *
	 * @param array<int, array{label: string, url: string, icon?: string}> $items
	 * @param array<string, mixed>                                         $config
	 */
	$items = apply_filters( 'tcres_breadcrumb_items', $items, $config );
	if ( ! is_array( $items ) || empty( $items ) ) return '';

	return tcres_breadcrumb_render_html( $items, $config );
}


function tcres_breadcrumb_shortcode(): string {
	return tcres_kses_html( tcres_breadcrumb_get() );
}


add_action( 'init', function(): void {
	add_shortcode( 'tcres-breadcrumb', 'tcres_breadcrumb_shortcode' );
} );
