<?php
/**
 * Includes -> Admin enqueue
 * Backend scripts & styles
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_admin_style_deps( string ...$deps ): array {
	if ( empty( $deps ) ) return array( 'wp-base-styles' );
	return $deps;
}


function tcres_admin_enqueue_style( string $handle, string $relative_path ): void {
	wp_enqueue_style(
		$handle,
		TCRES_PLUGIN_URL . $relative_path,
		tcres_admin_style_deps(),
		tcres_plugin_get_asset_file_mtime( $relative_path ),
		'all'
	);
}


/**
 * Settings screen
 */
add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ): void {
	if ( $hook_suffix !== 'settings_page_' . TCRES_SETTINGS_PAGE_SLUG ) return;

	tcres_admin_enqueue_style( 'tcres-admin-settings', 'assets/css/admin-settings.css' );

	$deps = tcres_settings_editor_enqueue_assets();

	$admin_settings_js = 'assets/js/admin-settings.js';
	wp_enqueue_script(
		'tcres-admin-settings',
		TCRES_PLUGIN_URL . $admin_settings_js,
		$deps,
		tcres_plugin_get_asset_file_mtime( $admin_settings_js ),
		true
	);
}, 5 );


/**
 * Post screens — classic / block editor
 */
add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ): void {
	if ( $hook_suffix !== 'post.php' && $hook_suffix !== 'post-new.php' ) return;

	$screen    = function_exists( 'get_current_screen' )
		? get_current_screen()
		: null;
	$post_type = ( $screen && ! empty( $screen->post_type ) )
		? (string) $screen->post_type
		: '';
	if ( $post_type === '' ) return;

	$needs_subtitle = tcres_subtitle_is_enabled()
		&& in_array( $post_type, tcres_subtitle_get_post_types(), true );

	$needs_hero = tcres_hero_is_enabled()
		&& in_array( $post_type, tcres_hero_get_post_types(), true );

	$needs_bodyclass = tcres_body_classes_is_enabled()
		&& in_array( $post_type, tcres_body_classes_get_post_types(), true );

	$needs_featured_video = tcres_featured_video_is_enabled()
		&& in_array( $post_type, tcres_featured_video_get_post_types(), true );

	$needs_related_content = tcres_related_content_is_enabled()
		&& in_array( $post_type, tcres_related_content_get_post_types(), true );

	if ( ! $needs_subtitle && ! $needs_hero && ! $needs_bodyclass && ! $needs_featured_video && ! $needs_related_content ) return;

	$post       = get_post();
	$uses_block = $post instanceof WP_Post
		? use_block_editor_for_post( $post )
		: use_block_editor_for_post_type( $post_type );

	if ( $uses_block ) :
		tcres_admin_enqueue_style( 'tcres-editor-post', 'assets/css/editor-post.css' );
	else :
		tcres_admin_enqueue_style( 'tcres-admin-post', 'assets/css/admin-post.css' );
	endif;

	if ( ! $needs_subtitle && ! $needs_hero && ! $needs_featured_video ) return;

	if ( $needs_subtitle || $needs_hero ) :
		wp_enqueue_editor();
		if ( $needs_hero ) :
			wp_enqueue_script( 'wplink' );
			wp_enqueue_style( 'editor-buttons' );
		endif;
	endif;

	if ( $needs_featured_video ) :
		wp_enqueue_media();
	endif;

	$script = $uses_block
		? 'assets/js/editor-post.js'
		: 'assets/js/admin-post.js';
	$script_handle = $uses_block
		? 'tcres-editor-post'
		: 'tcres-admin-post';
	$deps = array( 'jquery' );

	if ( $needs_subtitle || $needs_hero ) :
		$deps[] = 'editor';
		if ( $uses_block ) :
			$deps[] = 'wp-data';
			$deps[] = 'wp-edit-post';
		endif;
	endif;
	if ( $needs_hero ) :
		$deps[] = 'wplink';
	endif;
	if ( $needs_featured_video ) :
		$deps[] = 'media-editor';
	endif;

	wp_enqueue_script(
		$script_handle,
		TCRES_PLUGIN_URL . $script,
		$deps,
		tcres_plugin_get_asset_file_mtime( $script ),
		true
	);

	if ( $needs_featured_video ) :
		wp_localize_script(
			$script_handle,
			'tcresI18n',
			tcres_admin_components_get_i18n_strings()
		);
	endif;
} );


/**
 * Term screens
 */
add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ): void {
	if ( $hook_suffix !== 'edit-tags.php' && $hook_suffix !== 'term.php' ) return;

	$needs_subtitle = tcres_subtitle_is_enabled()
		&& ! empty( tcres_subtitle_get_taxonomies() );

	$needs_hero = tcres_hero_is_enabled()
		&& ! empty( tcres_hero_get_taxonomies() );

	$needs_termimage = tcres_term_image_is_enabled()
		&& ! empty( tcres_term_image_get_taxonomies() );

	$needs_featured_video = tcres_featured_video_is_enabled()
		&& ! empty( tcres_featured_video_get_taxonomies() );

	if ( ! $needs_subtitle && ! $needs_hero && ! $needs_termimage && ! $needs_featured_video ) return;

	tcres_admin_enqueue_style( 'tcres-admin-term', 'assets/css/admin-term.css' );

	if ( $needs_subtitle || $needs_hero ) :
		wp_enqueue_editor();
	endif;

	if ( $needs_hero ) :
		wp_enqueue_script( 'wplink' );
		wp_enqueue_style( 'editor-buttons' );
	endif;

	if ( $needs_termimage || $needs_featured_video ) :
		wp_enqueue_media();
	endif;

	$script = 'assets/js/admin-term.js';
	$deps   = array( 'jquery' );
	if ( $needs_subtitle || $needs_hero ) :
		$deps[] = 'editor';
	endif;
	if ( $needs_hero ) :
		$deps[] = 'wplink';
	endif;
	if ( $needs_termimage || $needs_featured_video ) :
		$deps[] = 'media-editor';
	endif;

	wp_enqueue_script(
		'tcres-admin-term',
		TCRES_PLUGIN_URL . $script,
		$deps,
		tcres_plugin_get_asset_file_mtime( $script ),
		true
	);

	wp_localize_script(
		'tcres-admin-term',
		'tcresI18n',
		tcres_admin_components_get_i18n_strings()
	);
} );


/**
 * Posts / terms list screens
 */
add_action( 'admin_enqueue_scripts', function ( string $hook_suffix ): void {
	$screen = function_exists( 'get_current_screen' )
		? get_current_screen()
		: null;
	if ( ! $screen ) return;

	$needs_list_style = false;
	$drag_config      = null;

	if ( $hook_suffix === 'edit.php' ) :
		$post_type = ! empty( $screen->post_type )
			? (string) $screen->post_type
			: 'post';

		if ( tcres_posts_list_thumbnail_is_enabled_for_post_type( $post_type ) ) :
			$needs_list_style = true;
		endif;

		if ( tcres_posts_list_drag_order_is_enabled_for_post_type( $post_type ) ) :
			$needs_list_style = true;
			$drag_config      = array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'tcres_drag_order' ),
				'action'     => 'tcres_admin_update_post_menu_order',
				'idPrefix'   => 'post-',
				'post_type'  => $post_type,
			);
		endif;
	elseif ( $hook_suffix === 'edit-tags.php' ) :
		$taxonomy = ! empty( $screen->taxonomy )
			? (string) $screen->taxonomy
			: '';

		if ( $taxonomy !== '' && tcres_terms_list_drag_order_is_enabled_for_taxonomy( $taxonomy ) ) :
			$needs_list_style = true;
			$drag_config      = array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'tcres_drag_order' ),
				'action'    => 'tcres_admin_update_term_order',
				'idPrefix'  => 'tag-',
				'taxonomy'  => $taxonomy,
			);
		endif;
	endif;

	if ( ! $needs_list_style && ! $drag_config ) return;

	if ( $needs_list_style ) :
		tcres_admin_enqueue_style( 'tcres-admin-list', 'assets/css/admin-list.css' );
	endif;

	if ( ! $drag_config ) return;

	$script = 'assets/js/admin-drag-order.js';
	wp_enqueue_script(
		'tcres-admin-drag-order',
		TCRES_PLUGIN_URL . $script,
		array(),
		tcres_plugin_get_asset_file_mtime( $script ),
		true
	);
	wp_localize_script( 'tcres-admin-drag-order', 'tcresDragOrderConfig', $drag_config );
} );
