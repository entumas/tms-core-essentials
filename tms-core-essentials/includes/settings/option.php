<?php
/**
 * Includes -> Settings -> Option
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Post types listed in Disable Gutenberg settings
 *
 * @return array<int, string>
 */
function tcres_settings_disable_gutenberg_get_post_types(): array {
	$post_types = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		if ( ! post_type_supports( $post_type, 'editor' ) ) continue;

		$post_types[] = $post_type;
	endforeach;

	/**
	 * Filter post types listed in the Disable Gutenberg settings
	 */
	return apply_filters( 'tcres_disable_gutenberg_get_post_types', $post_types );
}


/**
 * Default Disable Gutenberg post type option keys
 */
function tcres_settings_disable_gutenberg_get_post_type_defaults(): array {
	$defaults = array();

	foreach ( tcres_settings_disable_gutenberg_get_post_types() as $post_type ) :
		$defaults[ 'disable_' . $post_type ] = false;
	endforeach;

	return $defaults;
}


/**
 * Default thumbnail post type toggles for Posts list
 */
function tcres_settings_posts_list_thumbnail_post_type_defaults(): array {
	$defaults = array();

	foreach ( tcres_posts_list_get_thumbnail_post_types() as $post_type ) :
		$defaults[ $post_type ] = false;
	endforeach;

	return $defaults;
}


/**
 * Default drag-order post type toggles for Posts list
 */
function tcres_settings_posts_list_drag_order_post_type_defaults(): array {
	$defaults = array();

	foreach ( tcres_posts_list_get_drag_order_post_types() as $post_type ) :
		$defaults[ $post_type ] = false;
	endforeach;

	return $defaults;
}


/**
 * Default post type toggles for Duplicate posts
 */
function tcres_settings_duplicate_posts_post_type_defaults(): array {
	$defaults = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$defaults[ $post_type ] = false;
	endforeach;

	return $defaults;
}


/**
 * Default drag-order taxonomy toggles for Terms list
 */
function tcres_settings_terms_list_drag_order_taxonomy_defaults(): array {
	$defaults = array();

	foreach ( tcres_terms_list_get_drag_order_taxonomies() as $taxonomy ) :
		$defaults[ $taxonomy ] = false;
	endforeach;

	return $defaults;
}


/**
 * Default role toggles for SVG uploads (roles that can upload files).
 *
 * @return array<string, bool>
 */
function tcres_settings_svg_uploads_role_defaults(): array {
	$defaults = array();
	$wp_roles = wp_roles();
	if ( ! $wp_roles ) return $defaults;

	foreach ( $wp_roles->role_objects as $role_key => $role ) :
		if ( ! $role instanceof WP_Role || ! $role->has_cap( 'upload_files' ) ) continue;

		$defaults[ $role_key ] = ( $role_key === 'administrator' );
	endforeach;

	return $defaults;
}


/**
 * Core admin bar nodes available in Disable features -> Clean admin bar.
 *
 * @return array<string, string>
 */
function tcres_settings_admin_bar_node_choices(): array {
	return array(
		'wp-logo'     => __( 'WordPress logo', 'tms-core-essentials' ),
		'comments'    => __( 'Comments', 'tms-core-essentials' ),
		'new-content' => __( 'New content', 'tms-core-essentials' ),
		'updates'     => __( 'Updates', 'tms-core-essentials' ),
		'customize'   => __( 'Customize', 'tms-core-essentials' ),
	);
}


/**
 * Role -> admin bar node -> bool defaults.
 *
 * @return array<string, array<string, bool>>
 */
function tcres_settings_admin_bar_role_node_defaults(): array {
	$defaults = array();
	$wp_roles = wp_roles();
	if ( ! $wp_roles ) return $defaults;

	foreach ( $wp_roles->role_names as $role_key => $role_name ) :
		$defaults[ $role_key ] = array();
		foreach ( array_keys( tcres_settings_admin_bar_node_choices() ) as $node_id ) :
			$defaults[ $role_key ][ $node_id ] = false;
		endforeach;
	endforeach;

	return $defaults;
}


/**
 * Role -> custom admin bar node IDs defaults.
 *
 * @return array<string, string>
 */
function tcres_settings_admin_bar_role_custom_defaults(): array {
	$defaults = array();
	$wp_roles = wp_roles();
	if ( ! $wp_roles ) return $defaults;

	foreach ( array_keys( $wp_roles->role_names ) as $role_key ) :
		$defaults[ $role_key ] = '';
	endforeach;

	return $defaults;
}


/**
 * Default sortable columns config (post type → column key → bool)
 */
function tcres_settings_posts_list_sortable_columns_config_defaults(): array {
	$defaults = array();

	foreach ( tcres_posts_list_get_sortable_post_types() as $post_type ) :
		$defaults[ $post_type ] = array();
		foreach ( array_keys( tcres_posts_list_get_sortable_column_defs( $post_type ) ) as $column_key ) :
			$defaults[ $post_type ][ $column_key ] = false;
		endforeach;
	endforeach;

	return $defaults;
}


/**
 * Default post type toggles for Extend search (publicly searchable types, on by default).
 *
 * @return array<string, bool>
 */
function tcres_settings_extend_search_post_type_defaults(): array {
	$defaults = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		if ( ! empty( $object->exclude_from_search ) ) continue;

		$defaults[ $post_type ] = true;
	endforeach;

	return $defaults;
}


/**
 * Default post-type toggles for a content module (keys = post type slug)
 *
 * @return array<string, bool>
 */
function tcres_settings_module_post_type_toggles_defaults(): array {
	$defaults = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$defaults[ $post_type ] = false;
	endforeach;

	return $defaults;
}


/**
 * Post types configurable in Breadcrumbs CPT section (excludes post and page)
 *
 * @return array<int, string>
 */
function tcres_settings_breadcrumbs_get_cpt_slugs(): array {
	$slugs = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		if ( $post_type === 'post' || $post_type === 'page' ) continue;

		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$slugs[] = $post_type;
	endforeach;

	return $slugs;
}


/**
 * Default CPT trail config for Breadcrumbs
 *
 * @return array<string, array{page: int, taxonomy: string}>
 */
function tcres_settings_breadcrumbs_cpt_defaults(): array {
	$defaults = array();

	foreach ( tcres_settings_breadcrumbs_get_cpt_slugs() as $post_type ) :
		$defaults[ $post_type ] = array(
			'page'     => 0,
			'taxonomy' => '',
		);
	endforeach;

	return $defaults;
}


/**
 * First show_ui taxonomy slug for a post type, or empty string
 */
function tcres_settings_sitemap_default_taxonomy_for_post_type( string $post_type ): string {
	foreach ( get_object_taxonomies( $post_type, 'names' ) as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		return $taxonomy;
	endforeach;

	return '';
}


/**
 * Default CPT section config for Sitemap (teppll-style)
 *
 * @return array<string, array{enable: bool, parent_page_id: int, taxonomy: string, show_taxonomy: bool, show_posts: bool, max_depth: string}>
 */
function tcres_settings_sitemap_cpt_defaults(): array {
	$defaults = array();

	foreach ( tcres_settings_breadcrumbs_get_cpt_slugs() as $post_type ) :
		$taxonomy = tcres_settings_sitemap_default_taxonomy_for_post_type( $post_type );
		$defaults[ $post_type ] = array(
			'enable'         => false,
			'parent_page_id' => 0,
			'taxonomy'       => $taxonomy,
			'show_taxonomy'  => $taxonomy !== '',
			'show_posts'     => true,
			'max_depth'      => '',
		);
	endforeach;

	return $defaults;
}


/**
 * Default taxonomy toggles for a content module
 *
 * @param string $key_prefix Prefix for taxonomy keys (e.g. "tax_" or "")
 * @return array<string, bool>
 */
function tcres_settings_module_taxonomy_toggles_defaults( string $key_prefix = 'tax_' ): array {
	$defaults = array();

	foreach ( tcres_taxonomies_get_included() as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$defaults[ $key_prefix . $taxonomy ] = false;
	endforeach;

	return $defaults;
}


/**
 * Whether an array is a sequential list (0..n-1), including empty.
 *
 * @param array<mixed> $arr
 */
function tcres_settings_is_list_array( array $arr ): bool {
	if ( array() === $arr ) return true;

	return array_keys( $arr ) === range( 0, count( $arr ) - 1 );
}


/**
 * Merge stored settings into defaults, keeping only keys that exist in defaults.
 *
 * Indexed lists (e.g. selected form IDs with default `array()`) are kept as-is;
 * recursive merge would wipe them because an empty default schema has no keys.
 *
 * @param array<string, mixed> $defaults
 * @param array<string, mixed> $stored
 * @return array<string, mixed>
 */
function tcres_settings_merge_defaults( array $defaults, array $stored ): array {
	$out = array();

	foreach ( $defaults as $key => $default_value ) :
		if ( ! array_key_exists( $key, $stored ) ) :
			$out[ $key ] = $default_value;
			continue;
		endif;

		if ( is_array( $default_value ) ) :
			$child = is_array( $stored[ $key ] )
				? $stored[ $key ]
				: array();

			if (
				array() === $default_value
				|| (
					tcres_settings_is_list_array( $default_value )
					&& tcres_settings_is_list_array( $child )
				)
			) :
				$out[ $key ] = array_values( $child );
				continue;
			endif;

			$out[ $key ] = tcres_settings_merge_defaults( $default_value, $child );
			continue;
		endif;

		$out[ $key ] = $stored[ $key ];
	endforeach;

	return $out;
}


/**
 * Default settings structure
 */
function tcres_settings_get_defaults(): array {
	return array(
		'assets' => array(
			'disable_frontend_css' => false,
			'disable_frontend_js'  => false,
		),
		'external_scripts' => array(
			'swiper'    => false,
			'glightbox' => false,
			'choices'   => false,
		),
		'smooth_scroll' => array(
			'enable'            => false,
			'lerp'              => 0.05,
			'smooth_wheel'      => true,
			'sync_touch'        => true,
			'anchors'           => true,
			'exclude_selectors' => '.swiper',
		),
		'svg_icons' => array(
			'frontend_file' => '',
			'admin_file'    => '',
		),
		'shortcodes' => array(
			'enable' => false,
		),
		'disable_gutenberg' => tcres_settings_disable_gutenberg_get_post_type_defaults(),
		'disable_features' => array(
			'disable_post_types' => false,
			'post_types'         => tcres_settings_module_post_type_toggles_defaults(),
			'disable_taxonomies' => false,
			'taxonomies'         => tcres_settings_module_taxonomy_toggles_defaults( '' ),
			'disable_comments'   => false,
			'clean_admin_bar'    => false,
			'admin_bar_nodes'    => tcres_settings_admin_bar_role_node_defaults(),
			'admin_bar_custom'   => tcres_settings_admin_bar_role_custom_defaults(),
		),
		'posts_list' => array(
			'add_thumbnail_column'    => false,
			'thumbnail_post_types'    => tcres_settings_posts_list_thumbnail_post_type_defaults(),
			'sortable_columns'        => false,
			'sortable_columns_config' => tcres_settings_posts_list_sortable_columns_config_defaults(),
			'drag_order'              => false,
			'drag_order_post_types'   => tcres_settings_posts_list_drag_order_post_type_defaults(),
		),
		'duplicate_posts' => array(
			'enable'               => false,
			'post_types'           => tcres_settings_duplicate_posts_post_type_defaults(),
			'status'               => 'draft',
			'copy_taxonomies'      => true,
			'copy_meta'            => true,
			'copy_featured_image'  => true,
		),
		'terms_list' => array(
			'drag_order'            => false,
			'drag_order_taxonomies' => tcres_settings_terms_list_drag_order_taxonomy_defaults(),
		),
		'svg_uploads' => array(
			'enable' => false,
			'roles'  => tcres_settings_svg_uploads_role_defaults(),
		),
		'logout_redirect' => array(
			'enable'      => false,
			'destination' => 'home',
			'custom_url'  => '',
		),
		'login_customization' => array(
			'enable' => false,
		),
		'extend_search' => array(
			'enable'               => false,
			'post_types'           => tcres_settings_extend_search_post_type_defaults(),
			'search_taxonomies'    => false,
			'meta_mode'            => 'public',
			'meta_keys'            => '',
			'prevent_empty_search' => false,
		),
		'social_menu' => array(
			'enable'            => false,
			'show_network_name' => false,
		),
		'share_content' => array(
			'enable'                     => false,
			'output_location'             => 'manual',
			'output_target'               => '',
			'show_on_front_page'          => true,
			'show_on_home'                => true,
			'show_on_search'              => true,
			'show_on_author'              => true,
			'show_on_date'                => true,
			'show_on_templates'           => array(),
			'show_on_post_type_archives'  => array(),
			'show_on_taxonomies'          => array(),
			'show_on_post_types'          => array(),
			'title'                       => '',
			'show_title'                  => true,
			'network_facebook'            => true,
			'network_x'                   => true,
			'network_linkedin'            => true,
			'network_pinterest'           => true,
			'network_whatsapp'            => true,
			'network_telegram'            => true,
			'network_email'               => true,
			'network_copy_link'           => true,
			'button_show_icon'            => true,
			'button_show_share_text'      => false,
			'button_show_name'            => false,
			'buttons_vertical'            => false,
		),
		'chats' => array(
			'enable'              => false,
			'general_title'       => '',
			'whatsapp_enable'     => false,
			'whatsapp_number'     => '',
			'whatsapp_message'    => '',
			'telegram_enable'     => false,
			'telegram_username'   => '',
			'telegram_message'    => '',
			'messenger_enable'    => false,
			'messenger_fbpageid'  => '',
		),
		'scroll_to_top' => array(
			'enable'               => false,
			'threshold_viewports'  => 1,
			'avoid_footer'         => false,
			'footer_selector'      => '',
			'footer_gap'           => 16,
		),
		'privacy_notice' => array(
			'enable'                => false,
			'trigger'               => '',
			'contact'               => '',
			'subscribe'             => '',
			'comments'              => '',
			'register'              => '',
			'checkout'              => '',
			'wpforms_contact_ids'   => array(),
			'wpforms_subscribe_ids' => array(),
		),
		'privacy_consent' => array(
			'enable'   => false,
			'comments' => false,
			'register' => false,
			'checkout' => false,
			'label'    => '',
		),
		'google_consent_mode' => array(
			'enable' => false,
		),
		'breadcrumbs' => array(
			'enable'           => false,
			'output_location'  => 'manual',
			'output_target'    => '',
			'separator'        => '/',
			'show_home'        => true,
			'home_label'       => '',
			'home_display'     => 'icon_label',
			'blog_page'        => 0,
			'blog_taxonomy'    => 'category',
			'cpt'              => tcres_settings_breadcrumbs_cpt_defaults(),
		),
		'sitemap' => array(
			'enable'              => false,
			'hide_empty'          => true,
			'show_list_bullets'   => true,
			'max_depth'           => 3,
			'page_sort'           => 'menu_order',
			'blog'                => true,
			'blog_parent_page_id' => 0,
			'blog_taxonomy'       => 'category',
			'blog_show_taxonomy'  => true,
			'blog_show_posts'     => true,
			'blog_max_depth'      => '',
			'cpt'                 => tcres_settings_sitemap_cpt_defaults(),
		),
		'related_content' => array_merge(
			array(
				'enable'               => false,
				'output_location'      => 'manual',
				'output_target'        => '',
				'post_type_list'       => 'current',
				'posts_per_page'       => 3,
				'image_size'           => 'medium',
				'title_tag'            => 'h3',
				'show_excerpt'         => false,
				'show_button'          => false,
				'button_label'         => '',
				'show_taxonomies'      => false,
				'taxonomies_display'   => 'grouped',
				'show_taxonomy_labels' => false,
			),
			tcres_settings_module_post_type_toggles_defaults(),
			tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
		),
		'bodyclass' => array_merge(
			array( 'enable' => false ),
			tcres_settings_module_post_type_toggles_defaults(),
			tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
		),
		'term_image' => array_merge(
			array( 'enable' => false ),
			tcres_settings_module_taxonomy_toggles_defaults( '' )
		),
		'featured_video' => array_merge(
			array( 'enable' => false ),
			tcres_settings_module_post_type_toggles_defaults(),
			tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
		),
		'subtitle' => array_merge(
			array( 'enable' => false ),
			tcres_settings_module_post_type_toggles_defaults(),
			tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
		),
		'hero' => array_merge(
			array( 'enable' => false ),
			tcres_settings_module_post_type_toggles_defaults(),
			tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
		),
		'performance' => array(
			'enable'                  => false,
			'remove_wp_version'       => false,
			'remove_resource_hints'   => false,
			'remove_rsd'              => false,
			'remove_wlwmanifest'      => false,
			'remove_shortlink'        => false,
			'remove_emoji'            => false,
			'remove_rest_api_link'    => false,
			'remove_oembed_discovery' => false,
			'disable_wp_embed'        => false,
		),
		'security' => array(
			'enable'                 => false,
			'hide_login_errors'      => false,
			'block_proxy_visits'     => false,
			'block_user_enumeration' => false,
			'block_author_archives'  => false,
			'disable_xmlrpc'         => false,
		),
	);
}


/**
 * Get merged settings from the database
 */
function tcres_settings_get(): array {
	$defaults = tcres_settings_get_defaults();
	$stored   = tcres_storage_option_get( TCRES_OPTION_NAME, array() );
	if ( ! is_array( $stored ) ) $stored = array();

	$out = tcres_settings_merge_defaults( $defaults, $stored );

	return tcres_multilingual_merge_settings( $out );
}
