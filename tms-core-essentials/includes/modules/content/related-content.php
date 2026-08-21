<?php
/**
 * Includes -> Modules -> Content -> Related content
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_related_content_is_enabled(): bool {
	return (bool) tcres_option_get( 'related_content', 'enable' )
		&& ! empty( tcres_related_content_get_post_types() );
}


/**
 * @return array<int, string>
 */
function tcres_related_content_get_post_types(): array {
	$post_types = tcres_option_get_for_post_types( 'related_content', '' );
	if ( ! is_array( $post_types ) ) return array();

	return $post_types;
}


function tcres_related_content_get_posts_per_page(): int {
	$n = (int) tcres_option_get( 'related_content', 'posts_per_page' );
	if ( $n < 1 ) return 3;
	if ( $n > 12 ) return 12;

	return $n;
}


function tcres_related_content_get_post_type_list(): string {
	$value = (string) tcres_option_get( 'related_content', 'post_type_list' );
	$allowed = array( 'all', 'current', 'others' );

	return in_array( $value, $allowed, true ) ? $value : 'current';
}


function tcres_related_content_get_image_size(): string {
	$size = sanitize_key( (string) tcres_option_get( 'related_content', 'image_size' ) );
	if ( $size === '' ) return 'medium';

	return $size;
}


function tcres_related_content_get_title_tag(): string {
	$tag = strtolower( sanitize_key( (string) tcres_option_get( 'related_content', 'title_tag' ) ) );
	$allowed = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' );

	return in_array( $tag, $allowed, true ) ? $tag : 'h3';
}


function tcres_related_content_get_button_label(): string {
	$label = trim( (string) tcres_option_get( 'related_content', 'button_label' ) );
	if ( $label === '' ) :
		return __( 'Read more', 'tms-core-essentials' );
	endif;

	return $label;
}


function tcres_related_content_show_taxonomies(): bool {
	return (bool) tcres_option_get( 'related_content', 'show_taxonomies' );
}


function tcres_related_content_get_taxonomies_display(): string {
	$value = sanitize_key( (string) tcres_option_get( 'related_content', 'taxonomies_display' ) );

	return in_array( $value, array( 'grouped', 'mixed' ), true ) ? $value : 'grouped';
}


function tcres_related_content_show_taxonomy_labels(): bool {
	return (bool) tcres_option_get( 'related_content', 'show_taxonomy_labels' );
}


/**
 * @return array<int, string>
 */
function tcres_related_content_get_item_taxonomies(): array {
	$taxonomies = tcres_option_get_for_taxonomies( 'related_content', 'tax_' );
	if ( ! is_array( $taxonomies ) ) return array();

	return $taxonomies;
}


/**
 * @return array<int, WP_Term>
 */
function tcres_related_content_get_ordered_terms( int $post_id, string $taxonomy ): array {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $terms ) || is_wp_error( $terms ) || empty( $terms ) ) return array();

	$terms = array_values( $terms );
	$primary_id = tcres_related_content_get_primary_term_id( $post_id, $taxonomy );
	if ( $primary_id <= 0 ) return $terms;

	$primary = null;
	$rest    = array();
	foreach ( $terms as $term ) :
		if ( ! ( $term instanceof WP_Term ) ) continue;
		if ( (int) $term->term_id === $primary_id ) :
			$primary = $term;
			continue;
		endif;
		$rest[] = $term;
	endforeach;

	if ( $primary instanceof WP_Term ) :
		array_unshift( $rest, $primary );
	endif;

	return $rest;
}


/**
 * @param array<int, string> $taxonomies
 */
function tcres_related_content_get_item_taxonomies_html(
	int $post_id,
	array $taxonomies,
	string $display,
	bool $show_labels = true
): string {
	if ( $post_id <= 0 || empty( $taxonomies ) ) return '';

	$display = in_array( $display, array( 'grouped', 'mixed' ), true ) ? $display : 'grouped';
	$groups  = array();

	foreach ( $taxonomies as $taxonomy ) :
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( $taxonomy === '' || ! taxonomy_exists( $taxonomy ) ) continue;

		$terms = tcres_related_content_get_ordered_terms( $post_id, $taxonomy );
		if ( empty( $terms ) ) continue;

		$term_links = array();
		foreach ( $terms as $term ) :
			if ( ! ( $term instanceof WP_Term ) ) continue;
			$url = get_term_link( $term );
			if ( is_wp_error( $url ) ) continue;
			$term_links[] = '<a class="tcres-related-content-term" data-taxonomy="' . esc_attr( $taxonomy ) . '" href="' . esc_url( $url ) . '">'
				. esc_html( $term->name )
				. '</a>';
		endforeach;

		if ( empty( $term_links ) ) continue;

		$tax_obj = get_taxonomy( $taxonomy );
		$label   = $tax_obj && isset( $tax_obj->labels->name )
			? (string) $tax_obj->labels->name
			: $taxonomy;

		$groups[] = array(
			'taxonomy' => $taxonomy,
			'label'    => $label,
			'links'    => $term_links,
		);
	endforeach;

	if ( empty( $groups ) ) return '';

	$html = '<div class="tcres-related-content-taxonomies tcres-related-content-taxonomies-' . esc_attr( $display ) . '">';

	if ( $display === 'mixed' ) :
		$all_links = array();
		foreach ( $groups as $group ) :
			foreach ( $group['links'] as $link ) :
				$all_links[] = $link;
			endforeach;
		endforeach;
		$html .= implode( ' <span class="tcres-related-content-term-separator" aria-hidden="true">·</span> ', $all_links );
	else :
		foreach ( $groups as $group ) :
			$html .= '<div class="tcres-related-content-taxonomy" data-taxonomy="' . esc_attr( $group['taxonomy'] ) . '">';
			if ( $show_labels ) :
				$html .= '<span class="tcres-related-content-taxonomy-label">' . esc_html( $group['label'] ) . '</span>';
			endif;
			$html .= '<span class="tcres-related-content-taxonomy-terms">'
				. implode( ' <span class="tcres-related-content-term-separator" aria-hidden="true">·</span> ', $group['links'] )
				. '</span>';
			$html .= '</div>';
		endforeach;
	endif;

	$html .= '</div>';

	return $html;
}


/**
 * Primary term ID from SEOPress, Yoast or Rank Math.
 */
function tcres_related_content_get_primary_term_id( int $post_id, string $taxonomy = 'category' ): int {
	if ( $taxonomy === 'category' ) :
		$seopress = get_post_meta( $post_id, '_seopress_robots_primary_cat', true );
		if ( is_numeric( $seopress ) && (int) $seopress > 0 ) :
			return (int) $seopress;
		endif;
	endif;

	$yoast = get_post_meta( $post_id, '_yoast_wpseo_primary_' . $taxonomy, true );
	if ( is_numeric( $yoast ) && (int) $yoast > 0 ) :
		return (int) $yoast;
	endif;

	$rankmath = get_post_meta( $post_id, 'rank_math_primary_' . $taxonomy, true );
	if ( is_numeric( $rankmath ) && (int) $rankmath > 0 ) :
		return (int) $rankmath;
	endif;

	return 0;
}


/**
 * @param array<int, int>              $final_ids
 * @param array<int, int>              $exclude_ids
 * @param array<string, mixed>         $query_args
 */
function tcres_related_content_fetch_step( array &$final_ids, array &$exclude_ids, int $needed, array $query_args ): void {
	if ( $needed <= 0 ) return;

	$args = array_merge(
		array(
			'post_status'         => 'publish',
			'posts_per_page'      => $needed,
			'ignore_sticky_posts' => true,
			'post__not_in'        => $exclude_ids, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Necessary to exclude current post and already fetched posts.
			'fields'              => 'ids',
			'no_found_rows'       => true,
		),
		$query_args
	);

	$query = new WP_Query( $args );
	if ( empty( $query->posts ) ) return;

	foreach ( $query->posts as $id ) :
		$id = (int) $id;
		if ( $id <= 0 || in_array( $id, $exclude_ids, true ) ) continue;
		$final_ids[]   = $id;
		$exclude_ids[] = $id;
	endforeach;
}


/**
 * Fill related IDs from a taxonomy (primary term → first term → others)
 *
 * @param array<int, int> $final_ids
 * @param array<int, int> $exclude_ids
 * @param array<int, WP_Term> $terms
 */
function tcres_related_content_fill_from_taxonomy(
	array &$final_ids,
	array &$exclude_ids,
	int $limit,
	int $post_id,
	string $post_type,
	string $taxonomy,
	array $terms
): void {
	if ( count( $final_ids ) >= $limit || empty( $terms ) ) return;

	$terms = array_values( $terms );
	$primary_id = tcres_related_content_get_primary_term_id( $post_id, $taxonomy );
	$first_id   = (int) $terms[0]->term_id;
	$other_ids  = array();

	if ( $primary_id > 0 && count( $final_ids ) < $limit ) :
		tcres_related_content_fetch_step(
			$final_ids,
			$exclude_ids,
			$limit - count( $final_ids ),
			array(
				'post_type' => $post_type,
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Optimized with fields=ids and no_found_rows=true.
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $primary_id,
					),
				),
			)
		);
	endif;

	if ( count( $final_ids ) < $limit && ( $primary_id <= 0 || $primary_id !== $first_id ) ) :
		tcres_related_content_fetch_step(
			$final_ids,
			$exclude_ids,
			$limit - count( $final_ids ),
			array(
				'post_type' => $post_type,
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Optimized with fields=ids and no_found_rows=true.
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $first_id,
					),
				),
			)
		);
	endif;

	foreach ( $terms as $term ) :
		$tid = (int) $term->term_id;
		if ( $tid === $first_id ) continue;
		if ( $primary_id > 0 && $tid === $primary_id ) continue;
		$other_ids[] = $tid;
	endforeach;

	if ( ! empty( $other_ids ) && count( $final_ids ) < $limit ) :
		tcres_related_content_fetch_step(
			$final_ids,
			$exclude_ids,
			$limit - count( $final_ids ),
			array(
				'post_type' => $post_type,
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Optimized with fields=ids and no_found_rows=true.
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $other_ids,
					),
				),
			)
		);
	endif;
}


/**
 * @return array<int, int>
 */
function tcres_related_content_collect_ids( int $post_id, int $limit, string $taxonomy, string $fallback_taxonomy ): array {
	$post = get_post( $post_id );
	if ( ! ( $post instanceof WP_Post ) ) return array();

	$post_type   = $post->post_type;
	$exclude_ids = array( $post_id );
	$final_ids   = array();

	// 1. Manual selection
	$selected = get_post_meta( $post_id, 'tcres_related_content_posts', true );
	if ( is_array( $selected ) ) :
		foreach ( $selected as $raw_id ) :
			if ( count( $final_ids ) >= $limit ) break;
			$id = absint( $raw_id );
			if ( $id <= 0 || in_array( $id, $exclude_ids, true ) ) continue;
			if ( get_post_status( $id ) !== 'publish' ) continue;
			$final_ids[]   = $id;
			$exclude_ids[] = $id;
		endforeach;
	endif;

	// 2. Primary taxonomy
	if ( count( $final_ids ) < $limit && taxonomy_exists( $taxonomy ) && is_object_in_taxonomy( $post_type, $taxonomy ) ) :
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( is_array( $terms ) && ! is_wp_error( $terms ) ) :
			tcres_related_content_fill_from_taxonomy(
				$final_ids,
				$exclude_ids,
				$limit,
				$post_id,
				$post_type,
				$taxonomy,
				$terms
			);
		endif;
	endif;

	// 3. Fallback taxonomy
	if ( count( $final_ids ) < $limit && taxonomy_exists( $fallback_taxonomy ) && is_object_in_taxonomy( $post_type, $fallback_taxonomy ) ) :
		$terms = get_the_terms( $post_id, $fallback_taxonomy );
		if ( is_array( $terms ) && ! is_wp_error( $terms ) ) :
			tcres_related_content_fill_from_taxonomy(
				$final_ids,
				$exclude_ids,
				$limit,
				$post_id,
				$post_type,
				$fallback_taxonomy,
				$terms
			);
		endif;
	endif;

	// 4. Other public taxonomies on this post type
	if ( count( $final_ids ) < $limit ) :
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		foreach ( $taxonomies as $tax_slug => $tax_obj ) :
			if ( count( $final_ids ) >= $limit ) break;
			if ( ! ( $tax_obj instanceof WP_Taxonomy ) || empty( $tax_obj->public ) ) continue;
			if ( in_array( $tax_slug, array( $taxonomy, $fallback_taxonomy, 'post_format' ), true ) ) continue;

			$terms = get_the_terms( $post_id, $tax_slug );
			if ( ! is_array( $terms ) || is_wp_error( $terms ) || empty( $terms ) ) continue;

			tcres_related_content_fill_from_taxonomy(
				$final_ids,
				$exclude_ids,
				$limit,
				$post_id,
				$post_type,
				$tax_slug,
				$terms
			);
			break;
		endforeach;
	endif;

	// 5. Random fill
	if ( count( $final_ids ) < $limit ) :
		tcres_related_content_fetch_step(
			$final_ids,
			$exclude_ids,
			$limit - count( $final_ids ),
			array(
				'post_type' => $post_type,
				'orderby'   => 'rand',
			)
		);
	endif;

	return $final_ids;
}


/**
 * Get related content HTML
 *
 * @param array{
 *   class?: string,
 *   post_id?: int,
 *   taxonomy?: string,
 *   fallback_taxonomy?: string,
 *   posts_per_page?: int,
 *   image_size?: string,
 *   title_tag?: string,
 *   show_excerpt?: bool,
 *   show_button?: bool,
 *   button_label?: string,
 *   show_taxonomies?: bool,
 *   taxonomies?: array<int, string>,
 *   taxonomies_display?: string,
 *   show_taxonomy_labels?: bool
 * } $args
 */
function tcres_related_content_get( array $args = array() ): string {
	if ( ! tcres_related_content_is_enabled() ) return '';

	$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
	if ( $post_id <= 0 ) :
		$post_id = (int) get_the_ID();
	endif;
	if ( $post_id <= 0 ) return '';

	$limit = isset( $args['posts_per_page'] )
		? max( 1, min( 12, (int) $args['posts_per_page'] ) )
		: tcres_related_content_get_posts_per_page();

	$taxonomy = isset( $args['taxonomy'] )
		? sanitize_key( (string) $args['taxonomy'] )
		: 'category';
	if ( $taxonomy === '' ) $taxonomy = 'category';

	$fallback = isset( $args['fallback_taxonomy'] )
		? sanitize_key( (string) $args['fallback_taxonomy'] )
		: 'post_tag';
	if ( $fallback === '' ) $fallback = 'post_tag';

	$ids = tcres_related_content_collect_ids( $post_id, $limit, $taxonomy, $fallback );
	if ( empty( $ids ) ) return '';

	$image_size = isset( $args['image_size'] )
		? sanitize_key( (string) $args['image_size'] )
		: tcres_related_content_get_image_size();
	if ( $image_size === '' ) $image_size = 'medium';

	$title_tag = isset( $args['title_tag'] )
		? strtolower( sanitize_key( (string) $args['title_tag'] ) )
		: tcres_related_content_get_title_tag();
	$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' );
	if ( ! in_array( $title_tag, $allowed_tags, true ) ) :
		$title_tag = 'h3';
	endif;

	$show_excerpt = array_key_exists( 'show_excerpt', $args )
		? ! empty( $args['show_excerpt'] )
		: (bool) tcres_option_get( 'related_content', 'show_excerpt' );

	$show_button = array_key_exists( 'show_button', $args )
		? ! empty( $args['show_button'] )
		: (bool) tcres_option_get( 'related_content', 'show_button' );

	$button_label = isset( $args['button_label'] )
		? sanitize_text_field( (string) $args['button_label'] )
		: tcres_related_content_get_button_label();
	if ( $button_label === '' ) :
		$button_label = __( 'Read more', 'tms-core-essentials' );
	endif;

	$show_taxonomies = array_key_exists( 'show_taxonomies', $args )
		? ! empty( $args['show_taxonomies'] )
		: tcres_related_content_show_taxonomies();

	$item_taxonomies = array();
	if ( $show_taxonomies ) :
		if ( isset( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) :
			foreach ( $args['taxonomies'] as $tax_slug ) :
				$tax_slug = sanitize_key( (string) $tax_slug );
				if ( $tax_slug !== '' && taxonomy_exists( $tax_slug ) ) :
					$item_taxonomies[] = $tax_slug;
				endif;
			endforeach;
			$item_taxonomies = array_values( array_unique( $item_taxonomies ) );
		else :
			$item_taxonomies = tcres_related_content_get_item_taxonomies();
		endif;
	endif;

	$taxonomies_display = isset( $args['taxonomies_display'] )
		? sanitize_key( (string) $args['taxonomies_display'] )
		: tcres_related_content_get_taxonomies_display();
	if ( ! in_array( $taxonomies_display, array( 'grouped', 'mixed' ), true ) ) :
		$taxonomies_display = 'grouped';
	endif;

	$show_taxonomy_labels = array_key_exists( 'show_taxonomy_labels', $args )
		? ! empty( $args['show_taxonomy_labels'] )
		: tcres_related_content_show_taxonomy_labels();

	$class = 'tcres-related-content';
	$count = count( $ids );
	if ( $count > 1 ) :
		$class .= ' tcres-related-content-grid-' . $count;
	endif;
	if ( isset( $args['class'] ) ) :
		$extra = trim( preg_replace( '/\s+/', ' ', (string) $args['class'] ) ?? '' );
		if ( $extra !== '' ) :
			$class .= ' ' . $extra;
		endif;
	endif;

	$query = new WP_Query(
		array(
			'post_type'           => 'any',
			'posts_per_page'      => $count,
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $query->have_posts() ) :
		wp_reset_postdata();
		return '';
	endif;

	$html = '<div class="' . esc_attr( $class ) . '">';

	while ( $query->have_posts() ) :
		$query->the_post();
		$item_id = (int) get_the_ID();

		$html .= '<div class="tcres-related-content-item">';
		if ( $image_size !== 'none' && has_post_thumbnail( $item_id ) ) :
			$html .= '<div class="tcres-related-content-image">'
				. get_the_post_thumbnail( $item_id, $image_size )
				. '</div>';
		endif;

		$html .= '<div class="tcres-related-content-content">';

		$html .= '<' . $title_tag . ' class="tcres-related-content-title">'
			. '<a href="' . esc_url( get_permalink( $item_id ) ) . '">' . esc_html( get_the_title( $item_id ) ) . '</a>'
			. '</' . $title_tag . '>';

		if ( $show_taxonomies && ! empty( $item_taxonomies ) ) :
			$html .= tcres_related_content_get_item_taxonomies_html(
				$item_id,
				$item_taxonomies,
				$taxonomies_display,
				$show_taxonomy_labels
			);
		endif;

		if ( $show_excerpt ) :
			$excerpt = get_the_excerpt( $item_id );
			if ( is_string( $excerpt ) && $excerpt !== '' ) :
				$html .= '<p class="tcres-related-content-excerpt">' . esc_html( $excerpt ) . '</p>';
			endif;
		endif;

		if ( $show_button ) :
			$html .= '<a class="tcres-related-content-button" href="' . esc_url( get_permalink( $item_id ) ) . '">'
				. esc_html( $button_label )
				. '</a>';
		endif;

		$html .= '</div>';
		$html .= '</div>';
	endwhile;

	$html .= '</div>';
	wp_reset_postdata();

	return $html;
}


/**
 * Echo related content HTML
 *
 * @param array{
 *   class?: string,
 *   post_id?: int,
 *   taxonomy?: string,
 *   fallback_taxonomy?: string,
 *   posts_per_page?: int,
 *   image_size?: string,
 *   title_tag?: string,
 *   show_excerpt?: bool,
 *   show_button?: bool,
 *   button_label?: string,
 *   show_taxonomies?: bool,
 *   taxonomies?: array<int, string>,
 *   taxonomies_display?: string,
 *   show_taxonomy_labels?: bool
 * } $args
 */
function tcres_related_content( array $args = array() ): void {
	echo tcres_related_content_get( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaping helpers.
}


function tcres_related_content_shortcode(): string {
	return tcres_related_content_get();
}


function tcres_related_content_register_shortcode(): void {
	add_shortcode( 'tcres-related-content', 'tcres_related_content_shortcode' );
}

add_action( 'init', 'tcres_related_content_register_shortcode' );


/**
 * @return array<int, string>|string
 */
function tcres_related_content_get_metabox_query_post_types( string $current_post_type ) {
	$list = tcres_related_content_get_post_type_list();

	if ( $list === 'current' ) :
		return $current_post_type;
	endif;

	$public = get_post_types( array( 'public' => true ), 'names' );
	if ( ! is_array( $public ) ) $public = array();

	if ( $list === 'others' ) :
		$others = array_values( array_diff( $public, array( $current_post_type ) ) );
		return ! empty( $others )
			? $others
			: $current_post_type;
	endif;

	return ! empty( $public )
		? array_values( $public )
		: 'any';
}


function tcres_related_content_register_meta_box(): void {
	$post_types = tcres_related_content_get_post_types();
	if ( empty( $post_types ) ) return;

	add_meta_box(
		'tcres_related_content_metabox',
		__( 'Related content', 'tms-core-essentials' ),
		'tcres_related_content_render_meta_box',
		$post_types,
		'side',
		'low'
	);

	tcres_admin_register_metabox_classes(
		$post_types,
		'tcres_related_content_metabox',
		array( 'tcres-metabox', 'tcres-related-content-field' )
	);
}


/**
 * @param WP_Post $post
 */
function tcres_related_content_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( '_tcres_related_content_nonce', 'tcres_related_content_nonce' );

	$limit = tcres_related_content_get_posts_per_page();
	$selected = get_post_meta( (int) $post->ID, 'tcres_related_content_posts', true );
	if ( ! is_array( $selected ) ) $selected = array();

	$query_types = tcres_related_content_get_metabox_query_post_types( $post->post_type );
	$candidates  = get_posts(
		array(
			'post_type'              => $query_types,
			'post_status'            => 'publish',
			'post__not_in'           => array( (int) $post->ID ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Necessary to exclude current post from metabox selection.
			'posts_per_page'         => 200,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	?>
	<div class="tcres-related-content-metabox">
		<?php for ( $i = 0; $i < $limit; $i++ ) : ?>
			<?php
			$selected_id = isset( $selected[ $i ] )
				? absint( $selected[ $i ] )
				: 0;
			$field_id    = 'tcres_related_content_post_' . $i;
			?>
			<div class="tcres-metabox-field">
				<label class="tcres-metabox-field-label<?php echo $i > 0 ? ' screen-reader-text' : ''; ?>" for="<?php echo esc_attr( $field_id ); ?>">
					<?php esc_html_e( 'Select posts to display:', 'tms-core-essentials' ); ?>
				</label>
				<select
					class="widefat tcres-metabox-field-input"
					name="tcres_related_content_post[<?php echo esc_attr( (string) $i ); ?>]"
					id="<?php echo esc_attr( $field_id ); ?>">
					<option value=""><?php esc_html_e( 'Select a post', 'tms-core-essentials' ); ?></option>
					<?php foreach ( $candidates as $candidate ) : ?>
						<?php if ( ! ( $candidate instanceof WP_Post ) ) continue; ?>
						<option
							value="<?php echo esc_attr( (string) $candidate->ID ); ?>"
							<?php selected( $selected_id, (int) $candidate->ID ); ?>>
							<?php echo esc_html( get_the_title( $candidate ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endfor; ?>
		<p class="tcres-metabox-field-help">
			<?php
			printf(
				/* translators: %d: Maximum number of related posts */
				esc_html__( 'Select up to %d related posts. If none are selected, related posts are chosen automatically. All fields are optional.', 'tms-core-essentials' ),
				absint( $limit )
			);
			?>
		</p>
	</div>
	<?php
}


function tcres_related_content_save_post( int $post_id ): void {
	if (
		! isset( $_POST['tcres_related_content_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_related_content_nonce'] ) ),
			'_tcres_related_content_nonce'
		)
	) :
		return;
	endif;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$limit = tcres_related_content_get_posts_per_page();
	$raw   = isset( $_POST['tcres_related_content_post'] ) && is_array( $_POST['tcres_related_content_post'] )
		? array_map( 'absint', wp_unslash( $_POST['tcres_related_content_post'] ) )
		: array();

	$ids = array();
	for ( $i = 0; $i < $limit; $i++ ) :
		$id = isset( $raw[ $i ] ) ? $raw[ $i ] : 0;
		if ( $id > 0 && $id !== $post_id && get_post_status( $id ) === 'publish' ) :
			$ids[] = $id;
		else :
			$ids[] = 0;
		endif;
	endfor;

	$ids = array_values( array_filter( $ids ) );
	if ( empty( $ids ) ) :
		delete_post_meta( $post_id, 'tcres_related_content_posts' );
		return;
	endif;

	update_post_meta( $post_id, 'tcres_related_content_posts', $ids );
}


add_action( 'init', function (): void {
	if ( ! (bool) tcres_option_get( 'related_content', 'enable' ) ) return;
	if ( empty( tcres_related_content_get_post_types() ) ) return;

	add_action( 'add_meta_boxes', 'tcres_related_content_register_meta_box', 22 );
	add_action( 'save_post', 'tcres_related_content_save_post' );
}, 20 );
