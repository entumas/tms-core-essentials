<?php
/**
 * Includes -> Modules -> Admin -> Duplicate posts
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_duplicate_posts_is_enabled(): bool {
	return (bool) tcres_option_get( 'duplicate_posts', 'enable' );
}


function tcres_duplicate_posts_is_enabled_for_post_type( string $post_type ): bool {
	if ( ! tcres_duplicate_posts_is_enabled() ) return false;

	$types = tcres_option_get( 'duplicate_posts', 'post_types' );
	if ( ! is_array( $types ) ) return false;

	return ! empty( $types[ $post_type ] );
}


/**
 * Meta keys that must never be copied.
 *
 * @return array<int, string>
 */
function tcres_duplicate_posts_get_excluded_meta_keys(): array {
	$keys = array(
		'_edit_lock',
		'_edit_last',
		'_wp_old_slug',
		'_thumbnail_id', // handled separately via copy_featured_image
	);

	/**
	 * Filter meta keys excluded when duplicating a post.
	 */
	return apply_filters( 'tcres_duplicate_posts_excluded_meta_keys', $keys );
}


/**
 * @param array<string, string> $actions
 * @return array<string, string>
 */
function tcres_duplicate_posts_row_actions( array $actions, WP_Post $post ): array {
	if ( ! tcres_duplicate_posts_is_enabled_for_post_type( $post->post_type ) ) :
		return $actions;
	endif;

	if ( ! current_user_can( 'edit_post', $post->ID ) ) :
		return $actions;
	endif;

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) :
		return $actions;
	endif;

	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'tcres_duplicate_post',
				'post'   => $post->ID,
			),
			admin_url( 'admin.php' )
		),
		'tcres_duplicate_post_' . $post->ID
	);

	$actions['tcres_duplicate'] = sprintf(
		'<a href="%1$s" aria-label="%2$s">%3$s</a>',
		esc_url( $url ),
		esc_attr(
			sprintf(
				/* translators: %s: Post title. */
				__( 'Duplicate “%s”', 'tms-core-essentials' ),
				get_the_title( $post )
			)
		),
		esc_html__( 'Duplicate', 'tms-core-essentials' )
	);

	return $actions;
}


/**
 * Duplicate a post according to plugin settings.
 *
 * @return int|WP_Error New post ID or error.
 */
function tcres_duplicate_posts_duplicate( int $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) :
		return new WP_Error( 'tcres_duplicate_missing', __( 'Original post not found.', 'tms-core-essentials' ) );
	endif;

	if ( ! tcres_duplicate_posts_is_enabled_for_post_type( $post->post_type ) ) :
		return new WP_Error( 'tcres_duplicate_disabled', __( 'Duplication is not enabled for this post type.', 'tms-core-essentials' ) );
	endif;

	if ( ! current_user_can( 'edit_post', $post_id ) ) :
		return new WP_Error( 'tcres_duplicate_forbidden', __( 'You are not allowed to duplicate this post.', 'tms-core-essentials' ) );
	endif;

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) :
		return new WP_Error( 'tcres_duplicate_forbidden', __( 'You are not allowed to create posts of this type.', 'tms-core-essentials' ) );
	endif;

	$status = (string) tcres_option_get( 'duplicate_posts', 'status' );
	if ( ! in_array( $status, array( 'draft', 'pending', 'private', 'publish' ), true ) ) :
		$status = 'draft';
	endif;

	$title = $post->post_title !== ''
		? sprintf(
			/* translators: %s: Original post title. */
			__( '%s (Copy)', 'tms-core-essentials' ),
			$post->post_title
		)
		: __( '(Copy)', 'tms-core-essentials' );

	$new_post_id = wp_insert_post(
		array(
			'post_author'           => get_current_user_id() ?: (int) $post->post_author,
			'post_content'          => $post->post_content,
			'post_content_filtered' => $post->post_content_filtered,
			'post_title'            => $title,
			'post_excerpt'          => $post->post_excerpt,
			'post_status'           => $status,
			'post_type'             => $post->post_type,
			'comment_status'        => $post->comment_status,
			'ping_status'           => $post->ping_status,
			'post_password'         => $post->post_password,
			'post_parent'           => (int) $post->post_parent,
			'menu_order'            => (int) $post->menu_order,
			'to_ping'               => $post->to_ping,
			'pinged'                => $post->pinged,
			'post_mime_type'        => $post->post_mime_type,
		),
		true
	);

	if ( is_wp_error( $new_post_id ) ) :
		return $new_post_id;
	endif;

	$new_post_id = (int) $new_post_id;

	if ( (bool) tcres_option_get( 'duplicate_posts', 'copy_taxonomies' ) ) :
		$taxonomies = get_object_taxonomies( $post->post_type );
		foreach ( $taxonomies as $taxonomy ) :
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $terms ) || empty( $terms ) ) continue;
			wp_set_object_terms( $new_post_id, $terms, $taxonomy, false );
		endforeach;
	endif;

	if ( (bool) tcres_option_get( 'duplicate_posts', 'copy_meta' ) ) :
		$excluded = array_fill_keys( tcres_duplicate_posts_get_excluded_meta_keys(), true );
		$all_meta = get_post_meta( $post_id );

		if ( is_array( $all_meta ) ) :
			foreach ( $all_meta as $meta_key => $meta_values ) :
				if ( isset( $excluded[ $meta_key ] ) ) continue;
				if ( ! is_array( $meta_values ) ) continue;

				foreach ( $meta_values as $meta_value ) :
					add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
				endforeach;
			endforeach;
		endif;
	endif;

	if ( (bool) tcres_option_get( 'duplicate_posts', 'copy_featured_image' ) ) :
		$thumbnail_id = (int) get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id > 0 ) :
			set_post_thumbnail( $new_post_id, $thumbnail_id );
		endif;
	endif;

	/**
	 * Fires after a post has been duplicated.
	 *
	 * @param int     $new_post_id New post ID.
	 * @param WP_Post $post        Original post.
	 */
	do_action( 'tcres_duplicate_posts_after_duplicate', $new_post_id, $post );

	return $new_post_id;
}


function tcres_duplicate_posts_handle_admin_action(): void {
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id <= 0 ) :
		wp_die( esc_html__( 'Invalid post.', 'tms-core-essentials' ) );
	endif;

	check_admin_referer( 'tcres_duplicate_post_' . $post_id );

	$result = tcres_duplicate_posts_duplicate( $post_id );
	if ( is_wp_error( $result ) ) :
		wp_die( esc_html( $result->get_error_message() ) );
	endif;

	$edit_url = get_edit_post_link( $result, 'raw' );
	if ( ! is_string( $edit_url ) || $edit_url === '' ) :
		$edit_url = admin_url( 'post.php?action=edit&post=' . $result );
	endif;

	wp_safe_redirect( $edit_url );
	exit;
}


add_filter( 'post_row_actions', 'tcres_duplicate_posts_row_actions', 10, 2 );
add_filter( 'page_row_actions', 'tcres_duplicate_posts_row_actions', 10, 2 );
add_action( 'admin_action_tcres_duplicate_post', 'tcres_duplicate_posts_handle_admin_action' );
