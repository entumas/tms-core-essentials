<?php
/**
 * Includes -> API -> Convert slug to ID
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_slug_convert_to_id( string|array $value, string $type = 'post', string $taxonomy = '' ): int|array|null {
	$is_array_input = is_array( $value );
	$values = $is_array_input
		? $value
		: array( $value );
	$ids = array();
	$post_slugs = array();

	foreach ( $values as $index => $single_value ) :
		if ( is_int( $single_value ) || ( is_string( $single_value ) && ctype_digit( $single_value ) ) ) :
			$ids[ $index ] = (int) $single_value;
			continue;
		endif;

		if ( ! is_string( $single_value ) || $single_value === '' ) :
			$ids[ $index ] = null;
			continue;
		endif;

		if ( $type === 'post' ) :
			$post_slugs[ $index ] = $single_value;
			continue;
		endif;

		if ( $type === 'term' && $taxonomy !== '' ) :
			$term          = get_term_by( 'slug', $single_value, $taxonomy );
			$ids[ $index ] = $term ? (int) $term->term_id : null;
			continue;
		endif;

		$ids[ $index ] = null;
	endforeach;

	if ( $type === 'post' && ! empty( $post_slugs ) ) :
		$slug_map = tcres_slug_convert_post_to_ids( array_values( $post_slugs ) );

		foreach ( $post_slugs as $index => $slug ) :
			$ids[ $index ] = $slug_map[ $slug ] ?? null;
		endforeach;
	endif;

	ksort( $ids );
	$ids = array_values( $ids );

	return $is_array_input
		? $ids
		: $ids[0];
}


function tcres_slug_convert_post_to_ids( array $slugs ): array {
	$slugs = array_values( array_unique( array_filter( $slugs, 'is_string' ) ) );
	if ( empty( $slugs ) ) return array();

	$query = new WP_Query(
		array(
			'post_type'              => tcres_post_types_get_included(),
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'post_name__in'          => $slugs,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$map = array();
	foreach ( $query->posts as $post ) :
		if ( ! $post instanceof WP_Post ) continue;
		if ( ! isset( $map[ $post->post_name ] ) )
			$map[ $post->post_name ] = (int) $post->ID;
	endforeach;

	return $map;
}
