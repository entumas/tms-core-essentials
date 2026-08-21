<?php
/**
 * Includes -> Modules -> Login -> Login customization
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_login_customization_is_enabled(): bool {
	return (bool) tcres_option_get( 'login_customization', 'enable' );
}


function tcres_login_customization_get_site_icon_url(): string {
	$icon_id = (int) get_option( 'site_icon' );
	if ( $icon_id <= 0 ) return '';

	$url = wp_get_attachment_image_url( $icon_id, 'full' );

	return is_string( $url )
		? $url
		: '';
}


function tcres_login_customization_enqueue_inline_css(): void {
	if ( ! tcres_login_customization_is_enabled() ) return;

	$icon_url = tcres_login_customization_get_site_icon_url();
	if ( $icon_url === '' ) return;

	$css = sprintf(
		'#login h1 a{background-image:url(%1$s);width:1em;height:1em;font-size:96px;background-size:1em;background-position:center;}',
		esc_url_raw( $icon_url )
	);

	wp_register_style( 'tcres-login-customization-inline', false, array(), TCRES_PLUGIN_VERSION );
	wp_enqueue_style( 'tcres-login-customization-inline' );
	wp_add_inline_style( 'tcres-login-customization-inline', $css );
}


function tcres_login_customization_logo_url( string $url ): string {
	if ( ! tcres_login_customization_is_enabled() ) return $url;

	return home_url( '/' );
}


function tcres_login_customization_logo_title( string $title ): string {
	if ( ! tcres_login_customization_is_enabled() ) return $title;

	return get_bloginfo( 'name' );
}


add_action( 'login_enqueue_scripts', 'tcres_login_customization_enqueue_inline_css', 20 );
add_filter( 'login_headerurl', 'tcres_login_customization_logo_url' );
add_filter( 'login_headertext', 'tcres_login_customization_logo_title' );
