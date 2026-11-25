<?php
/**
 * Includes -> API -> Query listing
 * AJAX filters, order, and pagination for a theme-rendered post grid.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<string, array<string, mixed>>
 */
function &tcres_query_listing_registry(): array {
	static $registry = array();
	return $registry;
}


/**
 * @return array<int, string>
 */
function tcres_query_listing_orderby_whitelist(): array {
	$allowed = array( 'date', 'title', 'menu_order' );

	/**
	 * Filter orderby values accepted by Query listing.
	 *
	 * @param array<int, string> $allowed
	 */
	$filtered = apply_filters( 'tcres_query_listing_orderby', $allowed );
	if ( ! is_array( $filtered ) ) return $allowed;

	$out = array();
	foreach ( $filtered as $key ) :
		$key = sanitize_key( (string) $key );
		if ( $key !== '' ) $out[] = $key;
	endforeach;

	$out = array_values( array_unique( $out ) );
	return ! empty( $out )
		? $out
		: $allowed;
}


/**
 * @param mixed $raw
 * @return array<int, string>
 */
function tcres_query_listing_sanitize_post_types( $raw ): array {
	if ( is_string( $raw ) ) $raw = array( $raw );
	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $post_type ) :
		$post_type = sanitize_key( (string) $post_type );
		if ( $post_type === '' || ! post_type_exists( $post_type ) ) continue;
		if ( ! is_post_type_viewable( $post_type ) ) continue;
		$out[] = $post_type;
	endforeach;

	$out = array_values( array_unique( $out ) );
	sort( $out );
	return $out;
}


/**
 * @param mixed              $raw
 * @param array<int, string> $post_types
 * @return array<int, string>
 */
function tcres_query_listing_sanitize_taxonomies( $raw, array $post_types ): array {
	if ( is_string( $raw ) ) $raw = array( $raw );
	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $taxonomy ) :
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( $taxonomy === '' || ! taxonomy_exists( $taxonomy ) ) continue;
		if ( ! is_taxonomy_viewable( $taxonomy ) ) continue;

		$object = get_taxonomy( $taxonomy );
		if ( ! $object || ! is_array( $object->object_type ) ) continue;
		if ( empty( array_intersect( $post_types, $object->object_type ) ) ) continue;
		$out[] = $taxonomy;
	endforeach;

	$out = array_values( array_unique( $out ) );
	sort( $out );
	return $out;
}


/**
 * @param array<int, string> $post_types
 * @return array<int, string>
 */
function tcres_query_listing_attached_taxonomies( array $post_types ): array {
	$names = array();
	foreach ( $post_types as $post_type ) :
		$found = get_object_taxonomies( $post_type, 'names' );
		if ( is_array( $found ) ) $names = array_merge( $names, $found );
	endforeach;

	return tcres_query_listing_sanitize_taxonomies( $names, $post_types );
}


/**
 * @param mixed              $raw
 * @param array<int, string> $post_types
 * @return array{relation: string, filters: array<int, array{type: string, taxonomy: string, operator: string, values: array<int, string>}>}
 */
function tcres_query_listing_sanitize_scope( $raw, array $post_types ): array {
	$empty = array(
		'relation' => 'AND',
		'filters'  => array(),
	);
	if ( ! is_array( $raw ) ) return $empty;

	$relation = isset( $raw['relation'] )
		? strtoupper( sanitize_text_field( (string) $raw['relation'] ) )
		: 'AND';
	if ( ! in_array( $relation, array( 'AND', 'OR' ), true ) ) $relation = 'AND';

	$allowed = tcres_query_listing_attached_taxonomies( $post_types );
	$raw_filters = isset( $raw['filters'] ) && is_array( $raw['filters'] )
		? $raw['filters']
		: array();
	$filters = array();

	foreach ( $raw_filters as $filter ) :
		if ( count( $filters ) >= 30 || ! is_array( $filter ) ) continue;

		$type = sanitize_key( (string) ( $filter['type'] ?? '' ) );
		if ( ! in_array( $type, array( 'taxonomy', 'term' ), true ) ) continue;

		$operator = sanitize_key( (string) ( $filter['operator'] ?? 'in' ) );
		if ( ! in_array( $operator, array( 'in', 'and', 'not_in' ), true ) ) $operator = 'in';

		if ( $type === 'taxonomy' ) :
			$values = array_values( array_intersect(
				$allowed,
				tcres_query_listing_key_list( $filter['values'] ?? array(), 50 )
			) );
			sort( $values );
			if ( empty( $values ) ) continue;
			$filters[] = array(
				'type'     => 'taxonomy',
				'taxonomy' => '',
				'operator' => $operator,
				'values'   => $values,
			);
			continue;
		endif;

		$taxonomy = sanitize_key( (string) ( $filter['taxonomy'] ?? '' ) );
		if ( ! in_array( $taxonomy, $allowed, true ) ) continue;

		$values = tcres_query_listing_slug_list( $filter['values'] ?? array(), 50 );
		sort( $values );
		if ( empty( $values ) ) continue;

		$filters[] = array(
			'type'     => 'term',
			'taxonomy' => $taxonomy,
			'operator' => $operator,
			'values'   => $values,
		);
	endforeach;

	if ( empty( $filters ) ) return $empty;

	return array(
		'relation' => $relation,
		'filters'  => $filters,
	);
}


/**
 * @param array<string, mixed> $args
 */
function tcres_query_listing_register( string $id, array $args ): void {
	$id = sanitize_key( $id );
	if ( $id === '' ) return;

	$post_types = tcres_query_listing_sanitize_post_types( $args['post_type'] ?? array() );
	if ( empty( $post_types ) ) return;

	$per_page = -1;
	if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] !== '' && $args['posts_per_page'] !== null ) :
		$per_page = (int) $args['posts_per_page'];
		if ( $per_page < 1 ) :
			$per_page = -1;
		elseif ( $per_page > 100 ) :
			$per_page = 100;
		endif;
	endif;

	$orderby = isset( $args['orderby'] )
		? sanitize_key( (string) $args['orderby'] )
		: 'date';
	if ( ! in_array( $orderby, tcres_query_listing_orderby_whitelist(), true ) ) $orderby = 'date';

	$order = isset( $args['order'] )
		? strtoupper( sanitize_text_field( (string) $args['order'] ) )
		: 'DESC';
	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) $order = 'DESC';

	$render_item = tcres_query_listing_sanitize_render_item( $args['render_item'] ?? '' );
	if ( $render_item === '' ) return;

	$registry = &tcres_query_listing_registry();
	$registry[ $id ] = array(
		'post_type'      => $post_types,
		'taxonomies'     => tcres_query_listing_sanitize_taxonomies( $args['taxonomies'] ?? array(), $post_types ),
		'scope'          => tcres_query_listing_sanitize_scope( $args['scope'] ?? array(), $post_types ),
		'posts_per_page' => $per_page,
		'orderby'        => $orderby,
		'order'          => $order,
		'render_item'    => $render_item,
		'empty'          => isset( $args['empty'] ) ? (string) $args['empty'] : '',
		'error'          => isset( $args['error'] ) ? (string) $args['error'] : '',
	);
}


/**
 * @return array<string, mixed>|null
 */
function tcres_query_listing_get( string $id ): ?array {
	$id = sanitize_key( $id );
	if ( $id === '' ) return null;

	$registry = &tcres_query_listing_registry();
	return isset( $registry[ $id ] ) && is_array( $registry[ $id ] )
		? $registry[ $id ]
		: null;
}


function tcres_query_listing_switch_language( string $lang ): void {
	$lang = sanitize_key( $lang );
	if ( $lang === '' ) return;

	do_action( 'wpml_switch_language', $lang ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.

	if ( ! function_exists( 'PLL' ) ) return;

	$pll = PLL();
	if ( ! is_object( $pll ) || ! isset( $pll->model ) || ! is_object( $pll->model ) ) return;
	if ( ! method_exists( $pll->model, 'get_language' ) ) return;

	$language = $pll->model->get_language( $lang );
	if ( $language ) $pll->curlang = $language;
}


/**
 * @param array<string, mixed> $listing
 * @return array<string, mixed>
 */
function tcres_query_listing_payload( string $id, array $listing, int $total ): array {
	$lang = tcres_multilingual_get_current_language();

	return array(
		'id'             => $id,
		'post_type'      => $listing['post_type'],
		'taxonomies'     => $listing['taxonomies'],
		'posts_per_page' => (int) $listing['posts_per_page'],
		'scope'          => $listing['scope'],
		'orderby'        => $listing['orderby'],
		'order'          => $listing['order'],
		'render_item'    => $listing['render_item'],
		'lang'           => is_string( $lang ) ? $lang : '',
		'total'          => max( 0, $total ),
	);
}


/**
 * @param array<string, mixed> $payload
 */
function tcres_query_listing_encode_config( array $payload ): string {
	$json = wp_json_encode( $payload );
	if ( ! is_string( $json ) ) $json = '{}';

	$token = wp_json_encode(
		array(
			'json' => $json,
			'sig'  => hash_hmac( 'sha256', $json, wp_salt( 'auth' ) ),
		)
	);

	return base64_encode( is_string( $token ) ? $token : '' );
}


/**
 * @return array<string, mixed>|null
 */
function tcres_query_listing_decode_config( string $id, string $config ): ?array {
	$decoded = base64_decode( $config, true );
	if ( ! is_string( $decoded ) || $decoded === '' ) return null;

	$token = json_decode( $decoded, true );
	if ( ! is_array( $token ) || ! isset( $token['json'], $token['sig'] ) ) return null;

	$json = (string) $token['json'];
	$sig  = (string) $token['sig'];
	$expected = hash_hmac( 'sha256', $json, wp_salt( 'auth' ) );
	if ( ! hash_equals( $expected, $sig ) ) return null;

	$payload = json_decode( $json, true );
	if ( ! is_array( $payload ) ) return null;
	if ( sanitize_key( (string) ( $payload['id'] ?? '' ) ) !== $id ) return null;

	$listing = tcres_query_listing_listing_from_payload( $payload );
	if ( $listing === null ) return null;

	return array(
		'listing' => $listing,
		'lang'    => sanitize_key( (string) ( $payload['lang'] ?? '' ) ),
		'total'   => max( 0, (int) ( $payload['total'] ?? 0 ) ),
	);
}


/**
 * Rebuild the listing from a signed payload. The values are sanitized again
 * and must match the payload, so AJAX cannot widen the query or swap the card.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function tcres_query_listing_listing_from_payload( array $payload ): ?array {
	foreach ( array( 'post_type', 'taxonomies', 'posts_per_page', 'scope', 'orderby', 'order', 'render_item' ) as $key ) :
		if ( ! array_key_exists( $key, $payload ) ) return null;
	endforeach;

	$post_types = tcres_query_listing_sanitize_post_types( $payload['post_type'] );
	$signed_types = isset( $payload['post_type'] ) && is_array( $payload['post_type'] )
		? array_values( array_map( 'strval', $payload['post_type'] ) )
		: array();
	sort( $signed_types );
	if ( $post_types === array() || $post_types !== $signed_types ) return null;

	$taxonomies = tcres_query_listing_sanitize_taxonomies( $payload['taxonomies'], $post_types );
	$signed_taxes = isset( $payload['taxonomies'] ) && is_array( $payload['taxonomies'] )
		? array_values( array_map( 'strval', $payload['taxonomies'] ) )
		: array();
	sort( $signed_taxes );
	if ( $taxonomies !== $signed_taxes ) return null;

	$per_page = (int) $payload['posts_per_page'];
	if ( $per_page < 1 ) :
		$per_page = -1;
	elseif ( $per_page > 100 ) :
		$per_page = 100;
	endif;
	if ( $per_page !== (int) $payload['posts_per_page'] ) return null;

	if ( ! is_array( $payload['scope'] ) ) return null;
	$scope = tcres_query_listing_sanitize_scope( $payload['scope'], $post_types );
	if ( $scope !== $payload['scope'] ) return null;

	$orderby = sanitize_key( (string) $payload['orderby'] );
	if ( $orderby === '' || $orderby !== (string) $payload['orderby'] ) return null;
	if ( ! in_array( $orderby, tcres_query_listing_orderby_whitelist(), true ) ) return null;

	$order = strtoupper( sanitize_text_field( (string) $payload['order'] ) );
	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) || $order !== (string) $payload['order'] ) return null;

	if ( ! is_string( $payload['render_item'] ) ) return null;
	$render_item = tcres_query_listing_sanitize_render_item( $payload['render_item'] );
	if ( $render_item === '' || $render_item !== $payload['render_item'] ) return null;

	return array(
		'post_type'      => $post_types,
		'taxonomies'     => $taxonomies,
		'scope'          => $scope,
		'posts_per_page' => $per_page,
		'orderby'        => $orderby,
		'order'          => $order,
		'render_item'    => $render_item,
		'empty'          => '',
		'error'          => '',
	);
}


/**
 * Template slug, or an existing PHP file under wp-content.
 *
 * @param mixed $raw
 */
function tcres_query_listing_sanitize_render_item( $raw ): string {
	if ( ! is_string( $raw ) ) return '';

	$raw = trim( $raw );
	if ( $raw === '' ) return '';

	$looks_like_path = str_ends_with( strtolower( $raw ), '.php' )
		|| preg_match( '/^(?:\/|[A-Za-z]:[\\\\\\/])/', $raw );

	if ( $looks_like_path ) :
		if ( ! is_file( $raw ) ) return '';
		$path = wp_normalize_path( $raw );
		$content = wp_normalize_path( trailingslashit( WP_CONTENT_DIR ) );
		if ( stripos( $path, $content ) !== 0 || str_contains( $path, '..' ) ) return '';
		return $path;
	endif;

	$slug = ltrim( str_replace( '\\', '/', $raw ), '/' );
	$slug = preg_replace( '/\.php$/', '', $slug );
	if ( ! is_string( $slug ) || $slug === '' || str_contains( $slug, '..' ) ) return '';
	if ( ! preg_match( '/^[A-Za-z0-9_\/-]+$/', $slug ) ) return '';

	return $slug;
}


/**
 * @param mixed $raw
 * @return array<int, string>
 */
function tcres_query_listing_key_list( $raw, int $limit ): array {
	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $value ) :
		if ( count( $out ) >= $limit ) break;
		$key = sanitize_key( (string) $value );
		if ( $key === '' ) continue;
		$out[] = $key;
	endforeach;

	return array_values( array_unique( $out ) );
}


/**
 * @param mixed $raw
 * @return array<int, string>
 */
function tcres_query_listing_slug_list( $raw, int $limit ): array {
	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $value ) :
		if ( count( $out ) >= $limit ) break;
		$slug = sanitize_title( (string) $value );
		if ( $slug === '' ) continue;
		$out[] = $slug;
	endforeach;

	return array_values( array_unique( $out ) );
}


/**
 * @param array<string, mixed> $listing
 * @return array<string, mixed>
 */
function tcres_query_listing_sanitize_request( string $json, array $listing ): array {
	$decoded = json_decode( $json, true );
	if ( ! is_array( $decoded ) ) $decoded = array();

	$page = isset( $decoded['page'] )
		? (int) $decoded['page']
		: 1;
	if ( $page < 1 ) $page = 1;
	if ( $page > 10000 ) $page = 10000;

	$orderby = isset( $decoded['orderby'] )
		? sanitize_key( (string) $decoded['orderby'] )
		: (string) $listing['orderby'];
	if ( ! in_array( $orderby, tcres_query_listing_orderby_whitelist(), true ) ) :
		$orderby = (string) $listing['orderby'];
	endif;

	$order = isset( $decoded['order'] )
		? strtoupper( sanitize_text_field( (string) $decoded['order'] ) )
		: (string) $listing['order'];
	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) $order = (string) $listing['order'];

	$relation = isset( $decoded['relation'] )
		? strtoupper( sanitize_text_field( (string) $decoded['relation'] ) )
		: 'AND';
	if ( ! in_array( $relation, array( 'AND', 'OR' ), true ) ) $relation = 'AND';

	$terms_relation = isset( $decoded['terms_relation'] )
		? strtoupper( sanitize_text_field( (string) $decoded['terms_relation'] ) )
		: 'AND';
	if ( ! in_array( $terms_relation, array( 'AND', 'OR' ), true ) ) $terms_relation = 'AND';

	$search = isset( $decoded['search'] )
		? sanitize_text_field( (string) $decoded['search'] )
		: '';
	$search = trim( $search );
	if ( function_exists( 'mb_substr' ) ) :
		$search = mb_substr( $search, 0, 200 );
	elseif ( strlen( $search ) > 200 ) :
		$search = substr( $search, 0, 200 );
	endif;

	$filters = array();
	$raw_filters = isset( $decoded['filters'] ) && is_array( $decoded['filters'] )
		? $decoded['filters']
		: array();
	foreach ( $raw_filters as $filter ) :
		if ( count( $filters ) >= 30 || ! is_array( $filter ) ) continue;

		$type = sanitize_key( (string) ( $filter['type'] ?? '' ) );
		if ( ! in_array( $type, array( 'post_type', 'taxonomy', 'term' ), true ) ) continue;

		$operator = sanitize_key( (string) ( $filter['operator'] ?? 'in' ) );
		if ( ! in_array( $operator, array( 'in', 'and', 'not_in' ), true ) ) $operator = 'in';
		if ( $type === 'post_type' && $operator === 'and' ) $operator = 'in';

		$taxonomy = sanitize_key( (string) ( $filter['taxonomy'] ?? '' ) );
		if ( $type === 'term' && ! in_array( $taxonomy, $listing['taxonomies'], true ) ) continue;

		$list = $type === 'term'
			? 'tcres_query_listing_slug_list'
			: 'tcres_query_listing_key_list';

		$filters[] = array(
			'type'     => $type,
			'taxonomy' => $taxonomy,
			'operator' => $operator,
			'values'   => $list( $filter['values'] ?? array(), 50 ),
			'options'  => $list( $filter['options'] ?? array(), 50 ),
		);
	endforeach;

	return array(
		'page'           => $page,
		'orderby'        => $orderby,
		'order'          => $order,
		'search'         => $search,
		'relation'       => $relation,
		'terms_relation' => $terms_relation,
		'filters'        => $filters,
	);
}


/**
 * @param array<int, array<string, mixed>> $filters
 * @return array<int, array<string, mixed>>
 */
function tcres_query_listing_filters_of_type( array $filters, string $type ): array {
	$out = array();
	foreach ( $filters as $filter ) :
		if ( ( $filter['type'] ?? '' ) === $type ) $out[] = $filter;
	endforeach;
	return $out;
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @return array<int, string>
 */
function tcres_query_listing_resolve_post_types( array $listing, array $request ): array {
	$post_types = $listing['post_type'];
	$filters = tcres_query_listing_filters_of_type( $request['filters'], 'post_type' );

	foreach ( $filters as $filter ) :
		$values = array_values( array_intersect( $listing['post_type'], $filter['values'] ) );
		if ( empty( $values ) ) continue;

		if ( $filter['operator'] === 'not_in' ) :
			$post_types = array_values( array_diff( $post_types, $values ) );
		else :
			$post_types = array_values( array_intersect( $post_types, $values ) );
		endif;
	endforeach;

	return array_values( $post_types );
}


/**
 * @param array<string, mixed> $filter
 * @return array<string, mixed>|null
 */
function tcres_query_listing_taxonomy_clause( array $filter, array $allowed ): ?array {
	$values = array_values( array_intersect( $allowed, $filter['values'] ) );
	if ( empty( $values ) ) return null;

	$relation = $filter['operator'] === 'and'
		? 'AND'
		: 'OR';
	$operator = $filter['operator'] === 'not_in'
		? 'NOT EXISTS'
		: 'EXISTS';
	if ( $operator === 'NOT EXISTS' ) $relation = 'AND';

	$group = array( 'relation' => $relation );
	foreach ( $values as $taxonomy ) :
		$group[] = array(
			'taxonomy' => $taxonomy,
			'operator' => $operator,
		);
	endforeach;

	return $group;
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @return array<int|string, mixed>
 */
function tcres_query_listing_tax_query( array $listing, array $request ): array {
	$tax_query = array(
		'relation' => 'AND',
	);

	foreach ( tcres_query_listing_filters_of_type( $request['filters'], 'taxonomy' ) as $filter ) :
		$clause = tcres_query_listing_taxonomy_clause( $filter, $listing['taxonomies'] );
		if ( $clause !== null ) $tax_query[] = $clause;
	endforeach;

	$term_group = array(
		'relation' => $request['terms_relation'] === 'OR' ? 'OR' : 'AND',
	);
	foreach ( tcres_query_listing_filters_of_type( $request['filters'], 'term' ) as $filter ) :
		if ( empty( $filter['values'] ) ) continue;
		$operator = 'IN';
		if ( $filter['operator'] === 'not_in' ) $operator = 'NOT IN';
		if ( $filter['operator'] === 'and' ) $operator = 'AND';

		$term_group[] = array(
			'taxonomy' => $filter['taxonomy'],
			'field'    => 'slug',
			'terms'    => $filter['values'],
			'operator' => $operator,
		);
	endforeach;

	if ( count( $term_group ) > 1 ) $tax_query[] = $term_group;

	return $tax_query;
}


/**
 * @param array<int|string, mixed> $tax_query
 */
function tcres_query_listing_tax_query_has_clauses( array $tax_query ): bool {
	foreach ( $tax_query as $key => $clause ) :
		if ( $key === 'relation' ) continue;
		if ( is_array( $clause ) ) return true;
	endforeach;
	return false;
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @param array<int, string>   $post_types
 * @param array<int|string, mixed> $tax_query
 * @param array<int, int>      $post_in
 * @return array<string, mixed>
 */
function tcres_query_listing_base_args( array $listing, array $request, string $lang, array $post_types, array $tax_query, array $post_in ): array {
	$args = array(
		'post_type'           => $post_types,
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $listing['posts_per_page'],
		'paged'               => (int) $request['page'],
		'orderby'             => (string) $request['orderby'],
		'order'               => (string) $request['order'],
		'ignore_sticky_posts' => true,
		'suppress_filters'    => false,
	);

	if ( $lang !== '' ) $args['lang'] = $lang;
	$search = isset( $request['search'] ) ? trim( (string) $request['search'] ) : '';
	if ( $search !== '' ) $args['s'] = $search;
	if ( tcres_query_listing_tax_query_has_clauses( $tax_query ) ) :
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Filtered public listing.
	endif;
	if ( ! empty( $post_in ) ) :
		$args['post__in'] = $post_in;
	endif;

	return $args;
}


/**
 * @param array<string, mixed> $args
 * @return array<int, int>
 */
function tcres_query_listing_collect_ids( array $args ): array {
	$args['fields']                 = 'ids';
	$args['posts_per_page']         = -1;
	$args['paged']                  = 1;
	$args['no_found_rows']          = true;
	$args['update_post_meta_cache'] = false;
	$args['update_post_term_cache'] = false;

	$query = new WP_Query( $args );
	$ids = array();
	foreach ( $query->posts as $post_id ) :
		$ids[] = (int) $post_id;
	endforeach;
	wp_reset_postdata();

	return $ids;
}


/**
 * @param array<string, mixed> $listing
 * @return array<int|string, mixed>
 */
function tcres_query_listing_scope_tax_query( array $listing ): array {
	$scope = $listing['scope'];
	$relation = $scope['relation'] === 'OR'
		? 'OR'
		: 'AND';
	$tax_query = array( 'relation' => $relation );
	$allowed = tcres_query_listing_attached_taxonomies( $listing['post_type'] );

	foreach ( $scope['filters'] as $filter ) :
		if ( $filter['type'] === 'taxonomy' ) :
			$clause = tcres_query_listing_taxonomy_clause( $filter, $allowed );
			if ( $clause !== null ) $tax_query[] = $clause;
			continue;
		endif;

		if ( $filter['type'] !== 'term' || empty( $filter['values'] ) ) continue;
		if ( ! in_array( $filter['taxonomy'], $allowed, true ) ) continue;

		$operator = 'IN';
		if ( $filter['operator'] === 'not_in' ) $operator = 'NOT IN';
		if ( $filter['operator'] === 'and' ) $operator = 'AND';
		$tax_query[] = array(
			'taxonomy' => $filter['taxonomy'],
			'field'    => 'slug',
			'terms'    => $filter['values'],
			'operator' => $operator,
		);
	endforeach;

	return $tax_query;
}


/**
 * Visitor clauses stay in their own group. Scope is ANDed outside that group,
 * so a toolbar OR cannot leave the registered scope.
 *
 * @param array<int|string, mixed> $tax_query
 * @param array<string, mixed>     $listing
 * @return array<int|string, mixed>
 */
function tcres_query_listing_with_scope( array $tax_query, array $listing ): array {
	$scope_query = tcres_query_listing_scope_tax_query( $listing );
	if ( ! tcres_query_listing_tax_query_has_clauses( $scope_query ) ) return $tax_query;
	if ( ! tcres_query_listing_tax_query_has_clauses( $tax_query ) ) return $scope_query;

	return array(
		'relation' => 'AND',
		$tax_query,
		$scope_query,
	);
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @return array<string, mixed>|null
 */
function tcres_query_listing_query_args( array $listing, array $request, string $lang ): ?array {
	$post_types = tcres_query_listing_resolve_post_types( $listing, $request );
	$tax_query = tcres_query_listing_with_scope(
		tcres_query_listing_tax_query( $listing, $request ),
		$listing
	);
	$relation = $request['relation'] === 'OR'
		? 'OR'
		: 'AND';

	if ( $relation === 'OR' ) :
		$branches = array();
		$post_type_filters = tcres_query_listing_filters_of_type( $request['filters'], 'post_type' );
		$post_type_active = false;
		foreach ( $post_type_filters as $filter ) :
			if ( ! empty( $filter['values'] ) ) $post_type_active = true;
		endforeach;
		if ( $post_type_active && ! empty( $post_types ) ) :
			$branches[] = tcres_query_listing_base_args(
				$listing,
				$request,
				$lang,
				$post_types,
				tcres_query_listing_with_scope( array(), $listing ),
				array()
			);
		endif;

		$taxonomy_only = array( 'relation' => 'AND' );
		foreach ( tcres_query_listing_filters_of_type( $request['filters'], 'taxonomy' ) as $filter ) :
			$clause = tcres_query_listing_taxonomy_clause( $filter, $listing['taxonomies'] );
			if ( $clause !== null ) $taxonomy_only[] = $clause;
		endforeach;
		if ( tcres_query_listing_tax_query_has_clauses( $taxonomy_only ) ) :
			$branches[] = tcres_query_listing_base_args(
				$listing,
				$request,
				$lang,
				$listing['post_type'],
				tcres_query_listing_with_scope( $taxonomy_only, $listing ),
				array()
			);
		endif;

		$terms_relation = $request['terms_relation'] === 'OR'
			? 'OR'
			: 'AND';
		$terms_only = array( 'relation' => $terms_relation );
		foreach ( tcres_query_listing_filters_of_type( $request['filters'], 'term' ) as $filter ) :
			if ( empty( $filter['values'] ) ) continue;
			$operator = 'IN';
			if ( $filter['operator'] === 'not_in' ) $operator = 'NOT IN';
			if ( $filter['operator'] === 'and' ) $operator = 'AND';
			$terms_only[] = array(
				'taxonomy' => $filter['taxonomy'],
				'field'    => 'slug',
				'terms'    => $filter['values'],
				'operator' => $operator,
			);
		endforeach;
		if ( tcres_query_listing_tax_query_has_clauses( $terms_only ) ) :
			$branches[] = tcres_query_listing_base_args(
				$listing,
				$request,
				$lang,
				$listing['post_type'],
				tcres_query_listing_with_scope( $terms_only, $listing ),
				array()
			);
		endif;

		if ( count( $branches ) === 1 ) return $branches[0];

		if ( count( $branches ) > 1 ) :
			$ids = array();
			foreach ( $branches as $branch ) :
				$ids = array_merge( $ids, tcres_query_listing_collect_ids( $branch ) );
			endforeach;
			$ids = array_values( array_unique( array_filter( $ids ) ) );
			if ( empty( $ids ) ) return null;
			return tcres_query_listing_base_args(
				$listing,
				$request,
				$lang,
				$listing['post_type'],
				tcres_query_listing_with_scope( array(), $listing ),
				$ids
			);
		endif;
	endif;

	if ( empty( $post_types ) ) return null;
	return tcres_query_listing_base_args( $listing, $request, $lang, $post_types, $tax_query, array() );
}


function tcres_query_listing_render_one( string $render_item ): void {
	$render_item = tcres_query_listing_sanitize_render_item( $render_item );
	if ( $render_item === '' ) return;

	$content = wp_normalize_path( trailingslashit( WP_CONTENT_DIR ) );
	if ( stripos( $render_item, $content ) === 0 ) :
		load_template( $render_item, false );
		return;
	endif;

	get_template_part( $render_item );
}


/**
 * @param array<string, mixed> $listing
 */
function tcres_query_listing_render_items_html( array $listing, WP_Query $query ): string {
	ob_start();

	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) :
			$query->the_post();
			tcres_query_listing_render_one( $listing['render_item'] );
		endwhile;
	endif;

	wp_reset_postdata();
	return (string) ob_get_clean();
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @return array{html: string, found: int, page: int, max_pages: int}
 */
function tcres_query_listing_run( array $listing, array $request, string $lang ): array {
	$args = tcres_query_listing_query_args( $listing, $request, $lang );
	$page = (int) $request['page'];
	if ( $args === null ) :
		return array(
			'html'      => '',
			'found'     => 0,
			'page'      => $page,
			'max_pages' => 0,
		);
	endif;

	$query = new WP_Query( $args );
	$html = tcres_query_listing_render_items_html( $listing, $query );
	$found = (int) $query->found_posts;
	$max_pages = (int) $query->max_num_pages;
	wp_reset_postdata();

	return array(
		'html'      => $html,
		'found'     => $found,
		'page'      => $page,
		'max_pages' => $max_pages,
	);
}


/**
 * @param array<string, mixed> $args
 */
function tcres_query_listing_has_match( array $args ): bool {
	$args['fields']                 = 'ids';
	$args['posts_per_page']         = 1;
	$args['paged']                  = 1;
	$args['no_found_rows']          = true;
	$args['update_post_meta_cache'] = false;
	$args['update_post_term_cache'] = false;

	$query = new WP_Query( $args );
	$has = ! empty( $query->posts );
	wp_reset_postdata();
	return $has;
}


/**
 * @param array<string, mixed> $listing
 * @param array<string, mixed> $request
 * @return array<int, array{type: string, taxonomy: string, values: array<int, string>}>
 */
function tcres_query_listing_available( array $listing, array $request, string $lang ): array {
	$available = array();

	foreach ( $request['filters'] as $index => $filter ) :
		$options = $filter['options'];
		$checked = array_slice( $options, 0, 50 );
		$rest = array_slice( $options, 50 );
		$values = $rest;

		foreach ( $checked as $option ) :
			if ( $filter['type'] === 'post_type' && ! in_array( $option, $listing['post_type'], true ) ) continue;
			if ( $filter['type'] === 'taxonomy' && ! in_array( $option, $listing['taxonomies'], true ) ) continue;

			$probe = $request;
			$probe['page'] = 1;
			$probe['filters'][ $index ]['values'] = array( $option );
			$probe['filters'][ $index ]['operator'] = 'in';
			$args = tcres_query_listing_query_args( $listing, $probe, $lang );
			if ( is_array( $args ) && tcres_query_listing_has_match( $args ) ) $values[] = $option;
		endforeach;

		$available[] = array(
			'type'     => (string) $filter['type'],
			'taxonomy' => (string) $filter['taxonomy'],
			'values'   => array_values( array_unique( $values ) ),
		);
	endforeach;

	return $available;
}


/**
 * First page for a listing, queried once per request.
 *
 * @return array{id: string, listing: array<string, mixed>, result: array{html: string, found: int, page: int, max_pages: int}, config: string}|null
 */
function tcres_query_listing_first( string $id ): ?array {
	static $cache = array();

	$id = sanitize_key( $id );
	if ( $id === '' ) return null;
	if ( array_key_exists( $id, $cache ) ) return $cache[ $id ];

	$listing = tcres_query_listing_get( $id );
	if ( $listing === null ) :
		$cache[ $id ] = null;
		return null;
	endif;

	$lang = tcres_multilingual_get_current_language();
	$lang = is_string( $lang ) ? $lang : '';
	tcres_query_listing_switch_language( $lang );

	$request = array(
		'page'           => 1,
		'orderby'        => $listing['orderby'],
		'order'          => $listing['order'],
		'relation'       => 'AND',
		'terms_relation' => 'AND',
		'filters'        => array(),
	);
	$result = tcres_query_listing_run( $listing, $request, $lang );

	$cache[ $id ] = array(
		'id'      => $id,
		'listing' => $listing,
		'result'  => $result,
		'config'  => tcres_query_listing_encode_config(
			tcres_query_listing_payload( $id, $listing, $result['found'] )
		),
	);

	return $cache[ $id ];
}


function tcres_query_listing_attrs( string $id ): void {
	$first = tcres_query_listing_first( $id );
	if ( $first === null ) return;

	$result = $first['result'];
	$attrs = array(
		'data-tcres-query-listing-grid'   => true,
		'data-tcres-query-listing-id'     => $first['id'],
		'data-tcres-query-listing-ajax'   => admin_url( 'admin-ajax.php' ),
		'data-tcres-query-listing-config' => $first['config'],
		'data-tcres-query-listing-total'  => (string) $result['found'],
		'data-tcres-query-listing-pages'  => (string) $result['max_pages'],
	);

	$parts = array();
	foreach ( $attrs as $name => $value ) :
		if ( $value === true ) :
			$parts[] = esc_attr( $name );
			continue;
		endif;
		$escaped = $name === 'data-tcres-query-listing-ajax'
			? esc_url( (string) $value )
			: esc_attr( (string) $value );
		$parts[] = esc_attr( $name ) . '="' . $escaped . '"';
	endforeach;

	echo implode( ' ', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each attribute name and value is escaped above.
}


function tcres_query_listing_items( string $id ): void {
	$first = tcres_query_listing_first( $id );
	if ( $first === null ) return;

	$listing = $first['listing'];
	$result = $first['result'];
	$empty = tcres_kses_html( (string) $listing['empty'] );
	$error = tcres_kses_html( (string) $listing['error'] );
	?>
	<div data-tcres-query-listing-loading hidden></div>
	<?php if ( $empty !== '' ) : ?>
		<template data-tcres-query-listing-empty><?php tcres_echo_html( $empty ); ?></template>
	<?php endif; ?>
	<?php if ( $error !== '' ) : ?>
		<template data-tcres-query-listing-error><?php tcres_echo_html( $error ); ?></template>
	<?php endif; ?>
	<?php
	if ( $result['found'] > 0 ) :
		echo $result['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Card markup comes from the theme or plugin template registered in PHP.
	elseif ( $empty !== '' ) :
		echo '<div data-tcres-query-listing-empty-message>';
		tcres_echo_html( $empty );
		echo '</div>';
	endif;
}


function tcres_query_listing_ajax(): void {
	$id = isset( $_POST['id'] )
		? sanitize_key( wp_unslash( (string) $_POST['id'] ) )
		: '';

	$config = isset( $_POST['config'] )
		? (string) wp_unslash( $_POST['config'] )
		: '';
	$decoded = tcres_query_listing_decode_config( $id, $config );
	if ( $decoded === null ) :
		wp_send_json_error( array( 'message' => 'invalid' ), 403 );
	endif;

	$listing = $decoded['listing'];
	$request_json = isset( $_POST['request'] )
		? (string) wp_unslash( $_POST['request'] )
		: '';
	$request = tcres_query_listing_sanitize_request( $request_json, $listing );
	$lang = (string) $decoded['lang'];
	tcres_query_listing_switch_language( $lang );

	$result = tcres_query_listing_run( $listing, $request, $lang );
	$available = tcres_query_listing_available( $listing, $request, $lang );

	wp_send_json_success(
		array(
			'html'      => $result['html'],
			'found'     => $result['found'],
			'total'     => (int) $decoded['total'],
			'page'      => $result['page'],
			'max_pages' => $result['max_pages'],
			'available' => $available,
		)
	);
}


add_action( 'wp_ajax_tcres_query_listing', 'tcres_query_listing_ajax' );
add_action( 'wp_ajax_nopriv_tcres_query_listing', 'tcres_query_listing_ajax' );
