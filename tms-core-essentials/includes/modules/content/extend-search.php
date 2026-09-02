<?php
/**
 * Includes -> Modules -> Content -> Extend search
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_extend_search_is_enabled(): bool {
	return (bool) tcres_option_get( 'extend_search', 'enable' );
}


/**
 * @return array<int, string>
 */
function tcres_extend_search_get_post_types(): array {
	if ( ! tcres_extend_search_is_enabled() ) return array();

	$types = tcres_option_get( 'extend_search', 'post_types' );
	if ( ! is_array( $types ) ) return array();

	$out = array();
	foreach ( $types as $post_type => $enabled ) :
		if ( empty( $enabled ) ) continue;
		$post_type = sanitize_key( (string) $post_type );

		if ( $post_type === '' ) continue;
		$out[] = $post_type;
	endforeach;

	return $out;
}


/**
 * @return array<int, string>
 */
function tcres_extend_search_get_meta_keys(): array {
	$raw = (string) tcres_option_get( 'extend_search', 'meta_keys' );
	if ( $raw === '' ) return array();

	$keys = preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! is_array( $keys ) ) return array();

	$out = array();
	foreach ( $keys as $key ) :
		$key = sanitize_text_field( (string) $key );
		if ( $key === '' ) :
			continue;
		endif;
		$out[] = $key;
	endforeach;

	return array_values( array_unique( $out ) );
}


function tcres_extend_search_get_meta_mode(): string {
	$mode = sanitize_key( (string) tcres_option_get( 'extend_search', 'meta_mode' ) );
	return in_array( $mode, array( 'public', 'keys', 'none' ), true )
		? $mode
		: 'public';
}


function tcres_extend_search_searches_meta(): bool {
	$mode = tcres_extend_search_get_meta_mode();
	if ( $mode === 'none' ) return false;
	if ( $mode === 'keys' ) return ! empty( tcres_extend_search_get_meta_keys() );

	return true;
}


function tcres_extend_search_searches_taxonomies(): bool {
	return (bool) tcres_option_get( 'extend_search', 'search_taxonomies' );
}


function tcres_extend_search_has_extensions(): bool {
	if ( empty( tcres_extend_search_get_post_types() ) ) return false;

	return tcres_extend_search_searches_meta() || tcres_extend_search_searches_taxonomies();
}


function tcres_extend_search_query_should_apply( WP_Query $query ): bool {
	if ( ! tcres_extend_search_is_enabled() ) return false;
	if ( ! tcres_extend_search_has_extensions() ) return false;
	if ( is_admin() ) return false;
	if ( ! $query->is_main_query() ) return false;
	if ( ! $query->is_search() ) return false;

	$search = $query->get( 's' );

	return is_string( $search ) && $search !== '';
}


add_filter( 'posts_join', function( string $join, WP_Query $query ): string {
	if ( ! tcres_extend_search_query_should_apply( $query ) ) return $join;

	global $wpdb;
	if ( tcres_extend_search_searches_meta() && strpos( $join, 'tcres_es_meta' ) === false ) :
		$join .= " LEFT JOIN {$wpdb->postmeta} AS tcres_es_meta ON ({$wpdb->posts}.ID = tcres_es_meta.post_id) ";
	endif;

	if ( tcres_extend_search_searches_taxonomies() && strpos( $join, 'tcres_es_tr' ) === false ) :
		$join .= " LEFT JOIN {$wpdb->term_relationships} AS tcres_es_tr ON ({$wpdb->posts}.ID = tcres_es_tr.object_id) ";
		$join .= " LEFT JOIN {$wpdb->term_taxonomy} AS tcres_es_tt ON (tcres_es_tr.term_taxonomy_id = tcres_es_tt.term_taxonomy_id) ";
		$join .= " LEFT JOIN {$wpdb->terms} AS tcres_es_t ON (tcres_es_tt.term_id = tcres_es_t.term_id) ";
	endif;

	return $join;
}, 10, 2 );


/**
 * @param array<int, string> $post_types
 */
function tcres_extend_search_sql_post_type_in( array $post_types ): string {
	global $wpdb;

	$escaped = array();
	foreach ( $post_types as $post_type ) :
		$escaped[] = $wpdb->prepare( '%s', $post_type );
	endforeach;

	return implode( ', ', $escaped );
}


add_filter( 'posts_where', function( string $where, WP_Query $query ): string {
	if ( ! tcres_extend_search_query_should_apply( $query ) ) return $where;

	global $wpdb;

	$search = $query->get( 's' );
	if ( ! is_string( $search ) || $search === '' ) return $where;

	$post_types = tcres_extend_search_get_post_types();
	if ( empty( $post_types ) ) return $where;

	$like  = '%' . $wpdb->esc_like( $search ) . '%';
	$parts = array();

	if ( tcres_extend_search_searches_meta() ) :
		$mode = tcres_extend_search_get_meta_mode();
		if ( $mode === 'keys' ) :
			$keys = tcres_extend_search_get_meta_keys();
			if ( ! empty( $keys ) ) :
				$placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- $placeholders is safe string of %s, $keys sanitized in prepare.
				$parts[] = $wpdb->prepare(
					'(tcres_es_meta.meta_key IN (' . $placeholders . ') AND tcres_es_meta.meta_value LIKE %s)',
					array_merge( $keys, array( $like ) )
				);
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			endif;
		else :
			$parts[] = $wpdb->prepare(
				'(tcres_es_meta.meta_key NOT LIKE %s AND tcres_es_meta.meta_value LIKE %s)',
				$wpdb->esc_like( '_' ) . '%',
				$like
			);
		endif;
	endif;

	if ( tcres_extend_search_searches_taxonomies() ) :
		$parts[] = $wpdb->prepare(
			'(tcres_es_t.name LIKE %s OR tcres_es_tt.description LIKE %s)',
			$like,
			$like
		);
	endif;

	if ( empty( $parts ) ) return $where;

	$type_in = tcres_extend_search_sql_post_type_in( $post_types );
	$where  .= " OR ({$wpdb->posts}.post_type IN ({$type_in}) AND (" . implode( ' OR ', $parts ) . '))';

	return $where;
}, 10, 2 );


add_filter( 'posts_distinct', function( string $distinct, WP_Query $query ): string {
	if ( ! tcres_extend_search_query_should_apply( $query ) ) return $distinct;

	return 'DISTINCT';
}, 10, 2 );


add_action( 'pre_get_posts', function( WP_Query $query ): void {
	if ( ! tcres_extend_search_is_enabled() ) return;
	if ( ! (bool) tcres_option_get( 'extend_search', 'prevent_empty_search' ) ) return;
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) return;

	$search = $query->get( 's' );
	if ( ! is_string( $search ) || trim( $search ) !== '' ) return;

	$query->set( 'post__in', array( 0 ) );
} );
