<?php
/**
 * Includes -> Settings -> Hooks
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'admin_init', function (): void {
	register_setting(
		TCRES_SETTINGS_GROUP,
		TCRES_OPTION_NAME,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'tcres_settings_sanitize',
			'default'           => tcres_settings_get_defaults(),
		)
	);
} );


add_action( 'admin_menu', function (): void {
	add_options_page(
		tcres_plugin_get_name(),
		tcres_plugin_get_name(),
		'manage_options',
		TCRES_SETTINGS_PAGE_SLUG,
		'tcres_settings_render_admin_page'
	);
}, 15 );


add_filter( 'plugin_action_links_' . TCRES_PLUGIN_BASENAME, function ( array $links ): array {
	if ( ! current_user_can( 'manage_options' ) ) return $links;

	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( tcres_settings_get_admin_url_base() ),
		esc_html__( 'Settings', 'tms-core-essentials' )
	);
	array_unshift( $links, $settings_link );

	return $links;
}, 10, 1 );
