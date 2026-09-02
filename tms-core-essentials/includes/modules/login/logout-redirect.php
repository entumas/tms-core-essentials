<?php
/**
 * Includes -> Modules -> Login -> Logout redirect
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_logout_redirect_is_enabled(): bool {
	return (bool) tcres_option_get( 'logout_redirect', 'enable' );
}


/**
 * Resolve the configured logout destination URL.
 */
function tcres_logout_redirect_get_url(): string {
	$destination = (string) tcres_option_get( 'logout_redirect', 'destination' );
	$fallback    = home_url( '/' );

	switch ( $destination ) :
		case 'login':
			return wp_login_url();

		case 'custom':
			$custom = trim( (string) tcres_option_get( 'logout_redirect', 'custom_url' ) );
			return $custom !== ''
				? $custom
				: $fallback;

		case 'wc_shop':
		case 'wc_myaccount':
		case 'wc_cart':
			if ( ! function_exists( 'wc_get_page_permalink' ) ) return $fallback;

			$page = match ( $destination ) {
				'wc_shop'      => 'shop',
				'wc_myaccount' => 'myaccount',
				'wc_cart'      => 'cart',
			};

			$url = (string) wc_get_page_permalink( $page );
			return $url !== ''
				? $url
				: $fallback;

		case 'home':
		default:
			return $fallback;
	endswitch;
}


/**
 * Force logout redirect when the module is enabled.
 *
 * @param string           $redirect_to           Default redirect.
 * @param string           $requested_redirect_to Requested redirect from the logout URL.
 * @param WP_User|WP_Error $user                  Logged-out user.
 */
add_filter( 'logout_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
	unset( $requested_redirect_to, $user );

	if ( ! tcres_logout_redirect_is_enabled() ) return $redirect_to;

	return tcres_logout_redirect_get_url();
}, 10, 3 );


/**
 * Allow the custom logout host for wp_safe_redirect().
 *
 * @param array<int, string> $hosts
 * @return array<int, string>
 */
add_filter( 'allowed_redirect_hosts', function( array $hosts ): array {
	if ( ! tcres_logout_redirect_is_enabled() ) return $hosts;

	$destination = (string) tcres_option_get( 'logout_redirect', 'destination' );
	if ( $destination !== 'custom' ) return $hosts;

	$custom = trim( (string) tcres_option_get( 'logout_redirect', 'custom_url' ) );
	if ( $custom === '' ) return $hosts;

	$host = wp_parse_url( $custom, PHP_URL_HOST );
	if ( ! is_string( $host ) || $host === '' ) return $hosts;

	$hosts[] = $host;
	return array_values( array_unique( $hosts ) );
} );
