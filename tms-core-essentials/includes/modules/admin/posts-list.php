<?php
/**
 * Includes -> Modules -> Admin -> Posts list
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_posts_list_setting_is_enabled( string $key ): bool {
	return (bool) tcres_option_get( 'posts_list', $key );
}


/**
 * Post types eligible for the featured image column (UI + thumbnail support).
 */
function tcres_posts_list_get_thumbnail_post_types(): array {
	$post_types = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		if ( ! post_type_supports( $post_type, 'thumbnail' ) ) continue;

		$post_types[] = $post_type;
	endforeach;

	/**
	 * Filter post types available for the posts list featured image column.
	 */
	return apply_filters( 'tcres_posts_list_get_thumbnail_post_types', $post_types );
}


function tcres_posts_list_thumbnail_is_enabled_for_post_type( string $post_type ): bool {
	if ( ! tcres_posts_list_setting_is_enabled( 'add_thumbnail_column' ) ) return false;

	$types = tcres_option_get( 'posts_list', 'thumbnail_post_types' );
	if ( ! is_array( $types ) ) return false;

	return ! empty( $types[ $post_type ] );
}


/**
 * Post types eligible for drag-and-drop menu_order in the admin list.
 */
function tcres_posts_list_get_drag_order_post_types(): array {
	$post_types = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$post_types[] = $post_type;
	endforeach;

	/**
	 * Filter post types available for posts list drag order.
	 */
	return apply_filters( 'tcres_posts_list_get_drag_order_post_types', $post_types );
}


function tcres_posts_list_drag_order_is_enabled_for_post_type( string $post_type ): bool {
	if ( ! tcres_posts_list_setting_is_enabled( 'drag_order' ) ) return false;

	$types = tcres_option_get( 'posts_list', 'drag_order_post_types' );
	if ( ! is_array( $types ) ) return false;

	return ! empty( $types[ $post_type ] );
}


/**
 * Insert Order column after the checkbox column.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function tcres_posts_list_drag_order_columns_filter( array $columns ): array {
	$columns['menu_order'] = __( 'Order', 'tms-core-essentials' );
	return $columns;
}


function tcres_posts_list_drag_order_column_render( $column, $post_id ): void {
	if ( $column !== 'menu_order' ) return;

	$order = (int) get_post_field( 'menu_order', (int) $post_id );
	printf(
		'<span class="tcres-drag-handle" title="%1$s"><span class="dashicons dashicons-menu"></span><span class="tcres-order-num">%2$s</span></span>',
		esc_attr__( 'Drag to reorder', 'tms-core-essentials' ),
		esc_html( (string) $order )
	);
}


/**
 * @param array<string, string> $sortable
 * @return array<string, string>
 */
function tcres_posts_list_drag_order_sortable_columns_filter( array $sortable ): array {
	$sortable['menu_order'] = 'menu_order';
	return $sortable;
}


function tcres_posts_list_drag_order_pre_get_posts( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) return;

	$post_type = $query->get( 'post_type' );
	if ( is_array( $post_type ) ) :
		$post_type = isset( $post_type[0] ) ? (string) $post_type[0] : '';
	else :
		$post_type = (string) $post_type;
	endif;
	if ( $post_type === '' ) :
		$post_type = 'post';
	endif;

	if ( ! tcres_posts_list_drag_order_is_enabled_for_post_type( $post_type ) ) return;

	$orderby = $query->get( 'orderby' );
	if ( $orderby === '' || $orderby === false || $orderby === null ) :
		$query->set( 'orderby', 'menu_order' );
		if ( ! $query->get( 'order' ) ) :
			$query->set( 'order', 'ASC' );
		endif;
		return;
	endif;

	if ( $orderby === 'menu_order' && ! $query->get( 'order' ) ) :
		$query->set( 'order', 'ASC' );
	endif;
}


function tcres_posts_list_drag_order_ajax_update(): void {
	check_ajax_referer( 'tcres_drag_order', 'nonce' );

	$post_type = isset( $_POST['post_type'] )
		? sanitize_key( wp_unslash( (string) $_POST['post_type'] ) )
		: '';
	if ( ! tcres_posts_list_drag_order_is_enabled_for_post_type( $post_type ) ) :
		wp_send_json_error( 'Invalid post type' );
	endif;

	$post_type_object = get_post_type_object( $post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) :
		wp_send_json_error( 'Forbidden' );
	endif;

	$order = array();
	if ( isset( $_POST['order'] ) && is_array( $_POST['order'] ) ) :
		$order = array_map( 'absint', wp_unslash( $_POST['order'] ) );
	endif;
	if ( empty( $order ) ) :
		wp_send_json_error( 'Invalid request' );
	endif;

	foreach ( $order as $position => $post_id ) :
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) continue;
		if ( get_post_type( $post_id ) !== $post_type ) continue;
		if ( ! current_user_can( 'edit_post', $post_id ) ) continue;

		wp_update_post(
			array(
				'ID'         => $post_id,
				'menu_order' => (int) $position,
			)
		);
	endforeach;

	wp_send_json_success();
}


/**
 * Post types eligible for sortable list columns.
 */
function tcres_posts_list_get_sortable_post_types(): array {
	$post_types = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$post_types[] = $post_type;
	endforeach;

	/**
	 * Filter post types available for sortable posts list columns.
	 */
	return apply_filters( 'tcres_posts_list_get_sortable_post_types', $post_types );
}


/**
 * List-table column key for a taxonomy.
 */
function tcres_posts_list_get_taxonomy_column_key( string $taxonomy ): string {
	if ( $taxonomy === 'category' ) return 'categories';
	if ( $taxonomy === 'post_tag' ) return 'tags';

	return 'taxonomy-' . $taxonomy;
}


/**
 * Sortable column definitions for a post type (author + list-table taxonomies).
 *
 * @return array<string, array{label: string, type: string, column: string, taxonomy?: string}>
 */
function tcres_posts_list_get_sortable_column_defs( string $post_type ): array {
	$defs = array();

	if ( post_type_supports( $post_type, 'author' ) ) :
		$defs['author'] = array(
			'label'  => __( 'Author', 'tms-core-essentials' ),
			'type'   => 'author',
			'column' => 'author',
		);
	endif;

	$taxonomies = get_object_taxonomies( $post_type, 'objects' );
	if ( is_array( $taxonomies ) ) :
		foreach ( $taxonomies as $taxonomy => $object ) :
			if ( ! $object instanceof WP_Taxonomy ) continue;
			if ( empty( $object->show_ui ) ) continue;

			$defs[ $taxonomy ] = array(
				'label'    => $object->labels->name,
				'type'     => 'taxonomy',
				'column'   => tcres_posts_list_get_taxonomy_column_key( $taxonomy ),
				'taxonomy' => $taxonomy,
			);
		endforeach;
	endif;

	/**
	 * Filter sortable column definitions for a post type.
	 */
	return apply_filters( 'tcres_posts_list_get_sortable_column_defs', $defs, $post_type );
}


function tcres_posts_list_sortable_column_is_enabled( string $post_type, string $column_key ): bool {
	if ( ! tcres_posts_list_setting_is_enabled( 'sortable_columns' ) ) return false;

	$config = tcres_option_get( 'posts_list', 'sortable_columns_config' );
	if ( ! is_array( $config ) ) return false;
	if ( empty( $config[ $post_type ] ) || ! is_array( $config[ $post_type ] ) ) return false;

	return ! empty( $config[ $post_type ][ $column_key ] );
}


function tcres_posts_list_get_placeholder_image_url(): string {
	return TCRES_PLUGIN_URL . 'assets/images/no-featured-image.svg';
}


function tcres_posts_list_columns_filter( array $columns ): array {
	return array_slice( $columns, 0, 1, true ) + array(
		'thumbnail' => __( 'Image', 'tms-core-essentials' ),
	) + array_slice( $columns, 1, null, true );
}


function tcres_posts_list_thumbnail_column_render( $column_name, $post_id ): void {
	if ( (string) $column_name !== 'thumbnail' ) return;

	$post_id = (int) $post_id;
	if ( has_post_thumbnail( $post_id ) ) :
		echo get_the_post_thumbnail( $post_id, array( 80, 80 ) );
		return;
	endif;

	printf(
		'<img width="80" height="80" src="%s" class="attachment-80x80 size-80x80 wp-post-image" alt="" />',
		esc_url( tcres_posts_list_get_placeholder_image_url() )
	);
}


/**
 * Resolve the post type for an admin list query.
 */
function tcres_posts_list_get_query_post_type( WP_Query $query ): string {
	$post_type = $query->get( 'post_type' );

	if ( is_array( $post_type ) ) :
		$post_type = reset( $post_type );
	endif;

	if ( ! is_string( $post_type ) || $post_type === '' || $post_type === 'any' ) :
		return 'post';
	endif;

	return sanitize_key( $post_type );
}


/**
 * Normalize orderby query value to a string key.
 */
function tcres_posts_list_get_query_orderby( WP_Query $query ): string {
	$orderby = $query->get( 'orderby' );

	if ( is_array( $orderby ) ) :
		$keys = array_keys( $orderby );
		$first = reset( $keys );
		return is_string( $first ) ? $first : ( is_string( reset( $orderby ) ) ? (string) reset( $orderby ) : '' );
	endif;

	return is_string( $orderby ) ? $orderby : '';
}


function tcres_posts_list_sortable_columns_filter( array $columns ): array {
	$filter   = current_filter();
	$post_type = '';

	if ( preg_match( '/^manage_edit-(.+)_sortable_columns$/', $filter, $matches ) ) :
		$post_type = sanitize_key( $matches[1] );
	endif;

	if ( $post_type === '' ) return $columns;

	foreach ( tcres_posts_list_get_sortable_column_defs( $post_type ) as $key => $def ) :
		if ( ! tcres_posts_list_sortable_column_is_enabled( $post_type, $key ) ) continue;

		if ( $def['type'] === 'author' ) :
			$columns[ $def['column'] ] = 'author';
			continue;
		endif;

		if ( $def['type'] === 'taxonomy' && ! empty( $def['taxonomy'] ) ) :
			$columns[ $def['column'] ] = array( 'tcres_tax_' . $def['taxonomy'], false );
		endif;
	endforeach;

	return $columns;
}


function tcres_posts_list_get_active_taxonomy_orderby( WP_Query $query ): string {
	if ( ! is_admin() || ! $query->is_main_query() ) return '';
	if ( ! tcres_posts_list_setting_is_enabled( 'sortable_columns' ) ) return '';

	$orderby = tcres_posts_list_get_query_orderby( $query );
	if ( $orderby === '' || strpos( $orderby, 'tcres_tax_' ) !== 0 ) return '';

	$taxonomy  = sanitize_key( substr( $orderby, strlen( 'tcres_tax_' ) ) );
	$post_type = tcres_posts_list_get_query_post_type( $query );

	if ( $taxonomy === '' ) return '';
	if ( ! tcres_posts_list_sortable_column_is_enabled( $post_type, $taxonomy ) ) return '';

	return $taxonomy;
}


function tcres_posts_list_taxonomy_order_clauses_filter( array $clauses, WP_Query $query ): array {
	$taxonomy = tcres_posts_list_get_active_taxonomy_orderby( $query );
	if ( $taxonomy === '' ) return $clauses;

	global $wpdb;

	$alias_tr = preg_replace( '/[^a-z0-9_]/', '', 'tcres_tr_' . $taxonomy );
	$alias_tt = preg_replace( '/[^a-z0-9_]/', '', 'tcres_tt_' . $taxonomy );
	$alias_t  = preg_replace( '/[^a-z0-9_]/', '', 'tcres_t_' . $taxonomy );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Aliases sanitized with preg_replace above, safe for interpolation.
	$clauses['join'] .= $wpdb->prepare(
		" LEFT JOIN {$wpdb->term_relationships} AS {$alias_tr} ON ({$wpdb->posts}.ID = {$alias_tr}.object_id)"
		. " LEFT JOIN {$wpdb->term_taxonomy} AS {$alias_tt} ON ({$alias_tr}.term_taxonomy_id = {$alias_tt}.term_taxonomy_id AND {$alias_tt}.taxonomy = %s)"
		. " LEFT JOIN {$wpdb->terms} AS {$alias_t} ON ({$alias_tt}.term_id = {$alias_t}.term_id)",
		$taxonomy
	);
	$clauses['groupby'] = "{$wpdb->posts}.ID";

	$order = strtoupper( (string) $query->get( 'order' ) ) === 'ASC' ? 'ASC' : 'DESC';
	$clauses['orderby'] = "GROUP_CONCAT({$alias_t}.name ORDER BY {$alias_t}.name ASC) {$order}";
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	return $clauses;
}


function tcres_posts_list_admin_init_register(): void {
	if ( tcres_posts_list_setting_is_enabled( 'add_thumbnail_column' ) ) :
		foreach ( tcres_posts_list_get_thumbnail_post_types() as $post_type ) :
			if ( ! tcres_posts_list_thumbnail_is_enabled_for_post_type( $post_type ) ) continue;

			add_filter( "manage_{$post_type}_posts_columns", 'tcres_posts_list_columns_filter' );
			add_action( "manage_{$post_type}_posts_custom_column", 'tcres_posts_list_thumbnail_column_render', 10, 2 );
		endforeach;
	endif;

	if ( tcres_posts_list_setting_is_enabled( 'sortable_columns' ) ) :
		foreach ( tcres_posts_list_get_sortable_post_types() as $post_type ) :
			$has_enabled = false;
			foreach ( array_keys( tcres_posts_list_get_sortable_column_defs( $post_type ) ) as $column_key ) :
				if ( tcres_posts_list_sortable_column_is_enabled( $post_type, $column_key ) ) :
					$has_enabled = true;
					break;
				endif;
			endforeach;

			if ( ! $has_enabled ) continue;

			add_filter( "manage_edit-{$post_type}_sortable_columns", 'tcres_posts_list_sortable_columns_filter' );
		endforeach;

		add_filter( 'posts_clauses', 'tcres_posts_list_taxonomy_order_clauses_filter', 10, 2 );
	endif;

	if ( tcres_posts_list_setting_is_enabled( 'drag_order' ) ) :
		foreach ( tcres_posts_list_get_drag_order_post_types() as $post_type ) :
			if ( ! tcres_posts_list_drag_order_is_enabled_for_post_type( $post_type ) ) continue;

			add_filter( "manage_{$post_type}_posts_columns", 'tcres_posts_list_drag_order_columns_filter' );
			add_action( "manage_{$post_type}_posts_custom_column", 'tcres_posts_list_drag_order_column_render', 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", 'tcres_posts_list_drag_order_sortable_columns_filter' );
		endforeach;

		add_action( 'pre_get_posts', 'tcres_posts_list_drag_order_pre_get_posts' );
	endif;
}


add_action( 'admin_init', 'tcres_posts_list_admin_init_register' );
add_action( 'wp_ajax_tcres_admin_update_post_menu_order', 'tcres_posts_list_drag_order_ajax_update' );
