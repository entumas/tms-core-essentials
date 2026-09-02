<?php
/**
 * Includes -> Modules -> Security -> Performance
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_performance_is_enabled(): bool {
	return (bool) tcres_option_get( 'performance', 'enable' );
}


function tcres_performance_setting_is_enabled( string $key ): bool {
	if ( ! tcres_performance_is_enabled() ) return false;

	return (bool) tcres_option_get( 'performance', $key );
}


function tcres_performance_asset_version_filter( $src ) {
	if ( ! is_string( $src ) || $src === '' ) return $src;
	if ( ! tcres_performance_setting_is_enabled( 'remove_wp_version' ) ) return $src;

	$wp_version = get_bloginfo( 'version' );
	if ( $wp_version === '' ) return $src;

	$query = wp_parse_url( $src, PHP_URL_QUERY );
	if ( ! is_string( $query ) || $query === '' ) return $src;

	parse_str( $query, $args );
	if ( ! isset( $args['ver'] ) ) return $src;
	if ( (string) $args['ver'] !== (string) $wp_version ) return $src;

	return remove_query_arg( 'ver', $src );
}
add_filter( 'style_loader_src', 'tcres_performance_asset_version_filter', 15 );
add_filter( 'script_loader_src', 'tcres_performance_asset_version_filter', 15 );


add_filter( 'wp_resource_hints', function( array $urls, string $relation_type ): array {
	if ( ! tcres_performance_setting_is_enabled( 'remove_resource_hints' ) ) return $urls;
	if ( $relation_type !== 'dns-prefetch' ) return $urls;

	return array();
}, 10, 2 );


add_filter( 'tiny_mce_plugins', function( array $plugins ): array {
	if ( ! tcres_performance_setting_is_enabled( 'remove_emoji' ) ) return $plugins;

	return array_diff( $plugins, array( 'wpemoji' ) );
} );


add_filter( 'emoji_svg_url', function( $url ) {
	if ( ! tcres_performance_setting_is_enabled( 'remove_emoji' ) ) return $url;

	return false;
} );


add_action( 'after_setup_theme', function(): void {
	if ( ! tcres_performance_is_enabled() || is_admin() ) return;

	if ( tcres_performance_setting_is_enabled( 'remove_wp_version' ) ) :
		remove_action( 'wp_head', 'wp_generator' );
	endif;

	if ( tcres_performance_setting_is_enabled( 'remove_rsd' ) ) :
		remove_action( 'wp_head', 'rsd_link' );
	endif;

	if ( tcres_performance_setting_is_enabled( 'remove_wlwmanifest' ) ) :
		remove_action( 'wp_head', 'wlwmanifest_link' );
	endif;

	if ( tcres_performance_setting_is_enabled( 'remove_shortlink' ) ) :
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	endif;

	if ( tcres_performance_setting_is_enabled( 'remove_rest_api_link' ) ) :
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	endif;

	if ( tcres_performance_setting_is_enabled( 'remove_oembed_discovery' ) ) :
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	endif;
} );


add_action( 'wp_enqueue_scripts', function(): void {
	if ( ! tcres_performance_setting_is_enabled( 'disable_wp_embed' ) ) return;

	wp_deregister_script( 'wp-embed' );
}, 100 );


add_action( 'init', function(): void {
	if ( ! tcres_performance_setting_is_enabled( 'remove_emoji' ) ) return;

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}, 1 );
