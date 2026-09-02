<?php
/**
 * Includes -> Modules -> Admin -> Disable features
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_disable_features_get_disabled_post_types(): array {
	if ( ! (bool) tcres_option_get( 'disable_features', 'disable_post_types' ) ) return array();

	$selected = tcres_option_get( 'disable_features', 'post_types' );
	if ( ! is_array( $selected ) ) return array();

	$out = array();
	foreach ( $selected as $post_type => $enabled ) :
		if ( empty( $enabled ) ) continue;
		$post_type = sanitize_key( (string) $post_type );
		if ( $post_type === '' ) continue;
		$out[] = $post_type;
	endforeach;

	return array_values( array_unique( $out ) );
}


function tcres_disable_features_get_disabled_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'disable_features', 'disable_taxonomies' ) ) return array();

	$selected = tcres_option_get( 'disable_features', 'taxonomies' );
	if ( ! is_array( $selected ) ) return array();

	$out = array();
	foreach ( $selected as $taxonomy => $enabled ) :
		if ( empty( $enabled ) ) continue;
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( $taxonomy === '' ) continue;
		$out[] = $taxonomy;
	endforeach;

	return array_values( array_unique( $out ) );
}


function tcres_disable_features_comments_are_disabled(): bool {
	return (bool) tcres_option_get( 'disable_features', 'disable_comments' );
}


function tcres_disable_features_clean_admin_bar_is_enabled(): bool {
	return (bool) tcres_option_get( 'disable_features', 'clean_admin_bar' );
}


function tcres_disable_features_get_admin_bar_nodes_for_current_user(): array {
	if ( ! tcres_disable_features_clean_admin_bar_is_enabled() ) return array();
	if ( ! is_user_logged_in() ) return array();

	$user = wp_get_current_user();
	if ( ! $user instanceof WP_User || empty( $user->roles ) || ! is_array( $user->roles ) ) return array();

	$stored_nodes  = tcres_option_get( 'disable_features', 'admin_bar_nodes' );
	$stored_custom = tcres_option_get( 'disable_features', 'admin_bar_custom' );
	$stored_nodes  = is_array( $stored_nodes )
		? $stored_nodes
		: array();
	$stored_custom = is_array( $stored_custom )
		? $stored_custom
		: array();

	$nodes = array();
	foreach ( $user->roles as $role_key ) :
		$role_key = sanitize_key( (string) $role_key );
		if ( $role_key === '' ) continue;

		$selected_nodes = isset( $stored_nodes[ $role_key ] ) && is_array( $stored_nodes[ $role_key ] )
			? $stored_nodes[ $role_key ]
			: array();
		foreach ( $selected_nodes as $node_id => $enabled ) :
			if ( empty( $enabled ) ) continue;
			$node_id = sanitize_key( (string) $node_id );
			if ( $node_id === '' ) continue;
			$nodes[] = $node_id;
		endforeach;

		$custom_raw = isset( $stored_custom[ $role_key ] )
			? (string) $stored_custom[ $role_key ]
			: '';
		if ( $custom_raw === '' ) continue;
		$custom_ids = preg_split( '/[\s,]+/', $custom_raw, -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $custom_ids ) ) continue;
		foreach ( $custom_ids as $custom_id ) :
			$custom_id = sanitize_key( (string) $custom_id );
			if ( $custom_id === '' ) continue;
			$nodes[] = $custom_id;
		endforeach;
	endforeach;

	return array_values( array_unique( $nodes ) );
}


add_action( 'admin_menu', function(): void {
	foreach ( tcres_disable_features_get_disabled_post_types() as $post_type ) :
		if ( $post_type === 'post' ) :
			remove_menu_page( 'edit.php' );
			continue;
		endif;

		remove_menu_page( 'edit.php?post_type=' . $post_type );
	endforeach;
}, 100 );


add_action( 'admin_menu', function(): void {
	if ( ! tcres_disable_features_comments_are_disabled() ) return;

	remove_menu_page( 'edit-comments.php' );
}, 100 );


function tcres_disable_features_remove_comments_admin_bar(): void {
	if ( ! tcres_disable_features_comments_are_disabled() ) return;

	global $wp_admin_bar;
	if ( ! $wp_admin_bar ) return;

	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'tcres_disable_features_remove_comments_admin_bar', 1000 );
add_action( 'wp_before_admin_bar_render', 'tcres_disable_features_remove_comments_admin_bar', 1000 );


function tcres_disable_features_clean_admin_bar_nodes(): void {
	$nodes = tcres_disable_features_get_admin_bar_nodes_for_current_user();
	if ( empty( $nodes ) ) return;

	global $wp_admin_bar;
	if ( ! $wp_admin_bar ) return;

	foreach ( $nodes as $node_id ) :
		$wp_admin_bar->remove_node( $node_id );
	endforeach;
}
add_action( 'admin_bar_menu', 'tcres_disable_features_clean_admin_bar_nodes', 999 );
add_action( 'wp_before_admin_bar_render', 'tcres_disable_features_clean_admin_bar_nodes', 999 );


add_action( 'admin_init', function(): void {
	if ( ! tcres_disable_features_comments_are_disabled() ) return;

	foreach ( get_post_types( array(), 'names' ) as $post_type ) :
		if ( post_type_supports( $post_type, 'comments' ) ) :
			remove_post_type_support( $post_type, 'comments' );
		endif;
		if ( post_type_supports( $post_type, 'trackbacks' ) ) :
			remove_post_type_support( $post_type, 'trackbacks' );
		endif;
	endforeach;
} );


add_filter( 'comments_open', function( bool $open ): bool {
	if ( tcres_disable_features_comments_are_disabled() ) return false;
	return $open;
}, 10, 1 );


add_filter( 'pings_open', function( bool $open ): bool {
	if ( tcres_disable_features_comments_are_disabled() ) return false;
	return $open;
}, 10, 1 );


add_filter( 'comments_array', function( array $comments ): array {
	if ( tcres_disable_features_comments_are_disabled() ) return array();
	return $comments;
}, 10, 1 );

add_action( 'admin_menu', function(): void {
	if ( ! tcres_disable_features_comments_are_disabled() ) return;
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}, 100 );


add_action( 'admin_init', function(): void {
	if ( ! tcres_disable_features_comments_are_disabled() ) return;
	if ( ! is_admin() ) return;

	global $pagenow;
	if ( ! is_string( $pagenow ) ) return;

	if ( $pagenow !== 'options-discussion.php' ) return;

	wp_safe_redirect( admin_url( 'options-general.php' ) );
	exit;
}, 1 );


add_action( 'init', function(): void {
	foreach ( tcres_disable_features_get_disabled_taxonomies() as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->object_type ) || ! is_array( $object->object_type ) ) continue;

		foreach ( $object->object_type as $post_type ) :
			unregister_taxonomy_for_object_type( $taxonomy, (string) $post_type );
		endforeach;
	endforeach;
}, 100 );


add_action( 'admin_init', function(): void {
	if ( ! is_admin() || wp_doing_ajax() ) return;

	$disabled_post_types = tcres_disable_features_get_disabled_post_types();
	$disabled_taxonomies = tcres_disable_features_get_disabled_taxonomies();

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only request guards for admin screens.
	$requested_post_type = isset( $_GET['post_type'] )
		? sanitize_key( wp_unslash( (string) $_GET['post_type'] ) )
		: '';
	$requested_taxonomy  = isset( $_GET['taxonomy'] )
		? sanitize_key( wp_unslash( (string) $_GET['taxonomy'] ) )
		: '';
	$post_id             = isset( $_GET['post'] )
		? absint( wp_unslash( $_GET['post'] ) )
		: 0;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ( $requested_post_type !== '' && in_array( $requested_post_type, $disabled_post_types, true ) ) :
		wp_safe_redirect( admin_url() );
		exit;
	endif;

	if ( $post_id > 0 ) :
		$post_type = get_post_type( $post_id );
		if ( is_string( $post_type ) && in_array( $post_type, $disabled_post_types, true ) ) :
			wp_safe_redirect( admin_url() );
			exit;
		endif;
	endif;

	if ( $requested_taxonomy !== '' && in_array( $requested_taxonomy, $disabled_taxonomies, true ) ) :
		wp_safe_redirect( admin_url() );
		exit;
	endif;

	if ( tcres_disable_features_comments_are_disabled() ) :
		global $pagenow;
		if ( $pagenow === 'edit-comments.php' || $pagenow === 'comment.php' ) :
			wp_safe_redirect( admin_url() );
			exit;
		endif;
	endif;
}, 1 );


function tcres_disable_features_is_disabled_blog_context(): bool {
	$disabled_post_types = tcres_disable_features_get_disabled_post_types();
	if ( ! in_array( 'post', $disabled_post_types, true ) ) return false;

	return is_home() || is_category() || is_tag() || is_date() || is_feed();
}


add_action( 'template_redirect', function(): void {
	if ( is_admin() ) return;

	$disabled_post_types = tcres_disable_features_get_disabled_post_types();
	if ( ! empty( $disabled_post_types ) ) :
		foreach ( $disabled_post_types as $post_type ) :
			if ( is_singular( $post_type ) || is_post_type_archive( $post_type ) ) :
				wp_safe_redirect( home_url( '/' ), 301 );
				exit;
			endif;
		endforeach;

		if ( tcres_disable_features_is_disabled_blog_context() ) :
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		endif;
	endif;

	foreach ( tcres_disable_features_get_disabled_taxonomies() as $taxonomy ) :
		if ( is_tax( $taxonomy ) ) :
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		endif;
		if ( $taxonomy === 'category' && is_category() ) :
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		endif;
		if ( $taxonomy === 'post_tag' && is_tag() ) :
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		endif;
	endforeach;
}, 1 );
