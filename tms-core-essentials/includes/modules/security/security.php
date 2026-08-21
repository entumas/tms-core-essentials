<?php
/**
 * Includes -> Modules -> Security -> Security
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_security_is_enabled(): bool {
	return (bool) tcres_option_get( 'security', 'enable' );
}


function tcres_security_setting_is_enabled( string $key ): bool {
	if ( ! tcres_security_is_enabled() ) return false;

	return (bool) tcres_option_get( 'security', $key );
}


function tcres_security_login_errors_filter( mixed $error ): string {
	if ( ! tcres_security_setting_is_enabled( 'hide_login_errors' ) ) :
		return is_string( $error )
			? $error
			: '';
	endif;

	return __( 'Login failed. Check your credentials and try again.', 'tms-core-essentials' );
}


function tcres_security_proxy_visit_detect_is_proxy(): bool {
	$headers = array(
		'HTTP_VIA',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED',
		'HTTP_PROXY_CONNECTION',
	);

	foreach ( $headers as $header ) :
		if ( empty( $_SERVER[ $header ] ) ) continue;

		$value = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( $value !== '' ) return true;
	endforeach;

	return false;
}


function tcres_security_proxy_visit_block(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_proxy_visits' ) ) return;
	if ( is_user_logged_in() ) return;
	if ( ! tcres_security_proxy_visit_detect_is_proxy() ) return;

	wp_die(
		esc_html__( 'Proxy access is not allowed.', 'tms-core-essentials' ),
		esc_html__( 'Forbidden', 'tms-core-essentials' ),
		array( 'response' => 403 )
	);
}


function tcres_security_user_enumeration_block_request(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return;
	if ( is_admin() || is_user_logged_in() ) return;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Security redirect, not form processing.
	if ( isset( $_GET['author'] ) && absint( wp_unslash( $_GET['author'] ) ) > 0 ) :
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	endif;

	$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
	if ( $query_string !== '' && preg_match( '/(?:^|&)author=\d+/i', $query_string ) ) :
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	endif;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}


function tcres_security_user_enumeration_filter_canonical( $redirect, $request ) {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return $redirect;
	if ( is_admin() || is_user_logged_in() ) return $redirect;

	if ( is_string( $request ) && preg_match( '/\?author=\d+/i', $request ) ) :
		return home_url( '/' );
	endif;

	return $redirect;
}


function tcres_security_user_enumeration_filter_rest_endpoints( array $endpoints ): array {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return $endpoints;
	if ( is_user_logged_in() ) return $endpoints;

	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}


function tcres_security_author_archive_block_request(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_author_archives' ) ) return;
	if ( is_admin() || is_user_logged_in() ) return;
	if ( ! is_author() ) return;

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}


function tcres_security_xmlrpc_filter_is_enabled( $enabled ) {
	if ( ! tcres_security_setting_is_enabled( 'disable_xmlrpc' ) ) return $enabled;

	return false;
}


add_filter( 'login_errors', 'tcres_security_login_errors_filter' );
add_action( 'after_setup_theme', 'tcres_security_proxy_visit_block' );
add_action( 'template_redirect', 'tcres_security_user_enumeration_block_request', 1 );
add_action( 'template_redirect', 'tcres_security_author_archive_block_request', 1 );
add_filter( 'redirect_canonical', 'tcres_security_user_enumeration_filter_canonical', 10, 2 );
add_filter( 'rest_endpoints', 'tcres_security_user_enumeration_filter_rest_endpoints' );
add_filter( 'xmlrpc_enabled', 'tcres_security_xmlrpc_filter_is_enabled' );
