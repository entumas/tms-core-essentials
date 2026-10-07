<?php
/**
 * Includes -> Modules -> Admin -> Terms list
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Term meta key used to store custom term order.
 */
define( 'TCRES_TERM_ORDER_META_KEY', '_tcres_term_order' );


function tcres_terms_list_setting_is_enabled( string $key ): bool {
	return (bool) tcres_option_get( 'terms_list', $key );
}


/**
 * Taxonomies eligible for drag-and-drop order in the admin list.
 */
function tcres_terms_list_get_drag_order_taxonomies(): array {
	$exclude = array(
		'product_cat',   // WooCommerce categories (own order meta).
		'product_brand', // WooCommerce brands (own order).
	);

	$taxonomies = array();

	foreach ( tcres_taxonomies_get_included() as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		if ( in_array( $taxonomy, $exclude, true ) ) continue;

		// WooCommerce product attributes already have their own ordering UI.
		if ( function_exists( 'taxonomy_is_product_attribute' ) && taxonomy_is_product_attribute( $taxonomy ) ) continue;

		$taxonomies[] = $taxonomy;
	endforeach;

	/**
	 * Filter taxonomies available for terms list drag order.
	 */
	return apply_filters( 'tcres_terms_list_get_drag_order_taxonomies', $taxonomies );
}


function tcres_terms_list_drag_order_is_enabled_for_taxonomy( string $taxonomy ): bool {
	if ( ! tcres_terms_list_setting_is_enabled( 'drag_order' ) ) return false;

	$types = tcres_option_get( 'terms_list', 'drag_order_taxonomies' );
	if ( ! is_array( $types ) ) return false;

	return ! empty( $types[ $taxonomy ] );
}


function tcres_terms_list_get_term_order( int $term_id ): int {
	return (int) get_term_meta( $term_id, TCRES_TERM_ORDER_META_KEY, true );
}


/**
 * Insert Order column after the checkbox column.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function tcres_terms_list_drag_order_columns_filter( array $columns ): array {
	$columns['tcres_order'] = __( 'Order', 'tms-core-essentials' );
	return $columns;
}


/**
 * @param string $content Unused by WP for custom columns that echo.
 * @param string $column
 * @param int    $term_id
 */
function tcres_terms_list_drag_order_column_render( $content, string $column, $term_id ): string {
	if ( $column !== 'tcres_order' ) return (string) $content;

	$order = tcres_terms_list_get_term_order( (int) $term_id );

	return sprintf(
		'<span class="tcres-drag-handle" title="%1$s"><span class="dashicons dashicons-menu"></span><span class="tcres-order-num">%2$s</span></span>',
		esc_attr__( 'Drag to reorder', 'tms-core-essentials' ),
		esc_html( (string) $order )
	);
}


/**
 * @param array<string, string> $sortable
 * @return array<string, string>
 */
function tcres_terms_list_drag_order_sortable_columns_filter( array $sortable ): array {
	$sortable['tcres_order'] = 'tcres_order';
	return $sortable;
}


/**
 * Default / column sort for admin terms list only.
 *
 * @param array<string, mixed> $args
 * @param array<int, string>   $taxonomies
 * @return array<string, mixed>
 */
function tcres_terms_list_drag_order_get_terms_args( array $args, array $taxonomies ): array {
	if ( ! is_admin() ) return $args;

	// Never hijack object→term lookups (post list tax columns, etc.).
	if ( ! empty( $args['object_ids'] ) ) return $args;

	$taxonomy = '';
	if ( ! empty( $taxonomies[0] ) ) :
		$taxonomy = (string) $taxonomies[0];
	elseif ( ! empty( $args['taxonomy'] ) ) :
		$taxonomy = is_array( $args['taxonomy'] )
			? (string) ( $args['taxonomy'][0] ?? '' )
			: (string) $args['taxonomy'];
	endif;
	if ( $taxonomy === '' || ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) return $args;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Reading sort params for query modification, not form processing.
	$request_orderby = isset( $_REQUEST['orderby'] )
		? sanitize_key( wp_unslash( (string) $_REQUEST['orderby'] ) )
		: '';

	$screen        = function_exists( 'get_current_screen' )
		? get_current_screen()
		: null;
	$on_terms_list = $screen
		&& $screen->base === 'edit-tags'
		&& ! empty( $screen->taxonomy )
		&& (string) $screen->taxonomy === $taxonomy;

	// Only on the taxonomy list screen (or explicit Order column sort).
	if ( ! $on_terms_list && $request_orderby !== 'tcres_order' ) return $args;
	if ( $request_orderby !== '' && $request_orderby !== 'tcres_order' ) return $args;

	$order = isset( $_REQUEST['order'] )
		? strtoupper( sanitize_key( wp_unslash( (string) $_REQUEST['order'] ) ) )
		: 'ASC';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	if ( $order !== 'DESC' ) $order = 'ASC';

	$args['tcres_term_order_sort'] = true;
	$args['orderby']               = 'name'; // keep a valid key for WP_Term_Query
	$args['order']                 = $order;
	unset( $args['meta_key'] );

	return $args;
}


/**
 * LEFT JOIN term order meta so terms without a value still appear (as 0).
 *
 * @param array<string, string> $clauses
 * @param array<int, string>    $taxonomies
 * @param array<string, mixed>  $args
 * @return array<string, string>
 */
function tcres_terms_list_drag_order_terms_clauses( array $clauses, array $taxonomies, array $args ): array {
	if ( empty( $args['tcres_term_order_sort'] ) ) return $clauses;

	global $wpdb;

	$meta_key = TCRES_TERM_ORDER_META_KEY;
	$order    = isset( $args['order'] ) && strtoupper( (string) $args['order'] ) === 'DESC'
		? 'DESC'
		: 'ASC';

	$clauses['join'] .= $wpdb->prepare(
		" LEFT JOIN {$wpdb->termmeta} AS tcres_tm_order ON (t.term_id = tcres_tm_order.term_id AND tcres_tm_order.meta_key = %s)",
		$meta_key
	);

	// WP appends "$orderby $order" after terms_clauses — leave order empty to avoid "ASC ASC".
	$clauses['orderby'] = "ORDER BY CAST(COALESCE(tcres_tm_order.meta_value, '0') AS SIGNED) {$order}, t.name ASC";
	$clauses['order']   = '';

	return $clauses;
}


function tcres_terms_list_drag_order_render_add_field( string $taxonomy ): void {
	if ( ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) return;

	wp_nonce_field( '_tcres_term_order_nonce', 'tcres_term_order_nonce' );
	?>
	<div class="form-field term-tcres-order-wrap">
		<label for="tcres-term-order"><?php esc_html_e( 'Order', 'tms-core-essentials' ); ?></label>
		<input type="number" name="tcres_term_order" id="tcres-term-order" value="0" step="1" />
		<p><?php esc_html_e( 'Custom sort order for this term. Lower numbers appear first.', 'tms-core-essentials' ); ?></p>
	</div>
	<?php
}


function tcres_terms_list_drag_order_render_edit_field( WP_Term $term, string $taxonomy ): void {
	if ( ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) return;

	$order = tcres_terms_list_get_term_order( (int) $term->term_id );

	wp_nonce_field( '_tcres_term_order_nonce', 'tcres_term_order_nonce' );
	?>
	<tr class="form-field term-tcres-order-wrap">
		<th scope="row">
			<label for="tcres-term-order"><?php esc_html_e( 'Order', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<input type="number" name="tcres_term_order" id="tcres-term-order" value="<?php echo esc_attr( (string) $order ); ?>" step="1" />
			<p class="description"><?php esc_html_e( 'Custom sort order for this term. Lower numbers appear first.', 'tms-core-essentials' ); ?></p>
		</td>
	</tr>
	<?php
}


function tcres_terms_list_drag_order_save_term( int $term_id, int $tt_id = 0 ): void {
	unset( $tt_id );

	$term = get_term( $term_id );
	if ( ! $term || is_wp_error( $term ) ) return;

	$taxonomy = (string) $term->taxonomy;
	if ( ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) return;

	if (
		! isset( $_POST['tcres_term_order_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_term_order_nonce'] ) ),
			'_tcres_term_order_nonce'
		)
		|| ! current_user_can( 'edit_term', $term_id )
	) :
		return;
	endif;

	if ( ! isset( $_POST['tcres_term_order'] ) ) return;

	$order = (int) wp_unslash( $_POST['tcres_term_order'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	update_term_meta( $term_id, TCRES_TERM_ORDER_META_KEY, $order );
}


add_action( 'wp_ajax_tcres_admin_update_term_order', function(): void {
	check_ajax_referer( 'tcres_drag_order', 'nonce' );

	$taxonomy = isset( $_POST['taxonomy'] )
		? sanitize_key( wp_unslash( (string) $_POST['taxonomy'] ) )
		: '';
	if ( ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) :
		wp_send_json_error( 'Invalid taxonomy' );
	endif;

	$tax_object = get_taxonomy( $taxonomy );
	if ( ! $tax_object || ! current_user_can( $tax_object->cap->edit_terms ) ) :
		wp_send_json_error( 'Forbidden' );
	endif;

	$order = array();
	if ( isset( $_POST['order'] ) && is_array( $_POST['order'] ) ) :
		$order = array_map( 'absint', wp_unslash( $_POST['order'] ) );
	endif;
	if ( empty( $order ) ) :
		wp_send_json_error( 'Invalid request' );
	endif;

	foreach ( $order as $position => $term_id ) :
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) continue;

		$term = get_term( $term_id, $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) continue;

		update_term_meta( $term_id, TCRES_TERM_ORDER_META_KEY, (int) $position );
	endforeach;

	wp_send_json_success();
} );


add_action( 'admin_init', function(): void {
	if ( ! tcres_terms_list_setting_is_enabled( 'drag_order' ) ) return;

	foreach ( tcres_terms_list_get_drag_order_taxonomies() as $taxonomy ) :
		if ( ! tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) continue;

		add_filter( "manage_edit-{$taxonomy}_columns", 'tcres_terms_list_drag_order_columns_filter' );
		add_filter( "manage_{$taxonomy}_custom_column", 'tcres_terms_list_drag_order_column_render', 10, 3 );
		add_filter( "manage_edit-{$taxonomy}_sortable_columns", 'tcres_terms_list_drag_order_sortable_columns_filter' );

		add_action( "{$taxonomy}_add_form_fields", 'tcres_terms_list_drag_order_render_add_field', 9 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_terms_list_drag_order_render_edit_field', 9, 2 );
		add_action( "created_{$taxonomy}", 'tcres_terms_list_drag_order_save_term', 10, 2 );
		add_action( "edited_{$taxonomy}", 'tcres_terms_list_drag_order_save_term', 10, 2 );
	endforeach;

	add_filter( 'get_terms_args', 'tcres_terms_list_drag_order_get_terms_args', 10, 2 );
	add_filter( 'terms_clauses', 'tcres_terms_list_drag_order_terms_clauses', 10, 3 );
} );
