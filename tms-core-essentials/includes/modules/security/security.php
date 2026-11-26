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


function tcres_security_login_failure_message(): string {
	return __( 'Login failed. Check your credentials and try again.', 'tms-core-essentials' );
}


function tcres_security_replace_account_errors( WP_Error $errors, array $codes ): WP_Error {
	$replaced = false;

	foreach ( $codes as $code ) :
		if ( ! in_array( $code, $errors->get_error_codes(), true ) ) continue;

		$errors->remove( $code );
		$replaced = true;
	endforeach;

	if ( ! $replaced ) return $errors;

	$errors->add( 'tcres_login_failed', tcres_security_login_failure_message() );

	return $errors;
}


add_filter( 'authenticate', function( mixed $user ): mixed {
	if ( ! tcres_security_setting_is_enabled( 'hide_login_errors' ) ) return $user;
	if ( ! $user instanceof WP_Error ) return $user;

	return tcres_security_replace_account_errors(
		$user,
		array( 'invalid_username', 'invalid_email', 'incorrect_password' )
	);
}, 100 );


add_filter( 'lostpassword_errors', function( mixed $errors, mixed $user_data ): mixed {
	if ( ! tcres_security_setting_is_enabled( 'hide_login_errors' ) ) return $errors;
	if ( ! $errors instanceof WP_Error ) return $errors;

	$errors = tcres_security_replace_account_errors(
		$errors,
		array( 'invalidcombo', 'invalid_email' )
	);

	// retrieve_password() adds invalidcombo after this filter when the account
	// does not exist and no errors remain (username without "@").
	if ( ! $user_data instanceof WP_User && ! $errors->has_errors() ) :
		$errors->add( 'tcres_login_failed', tcres_security_login_failure_message() );
	endif;

	return $errors;
}, 10, 2 );


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


add_action( 'after_setup_theme', function(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_proxy_visits' ) ) return;
	if ( is_user_logged_in() ) return;
	if ( ! tcres_security_proxy_visit_detect_is_proxy() ) return;

	wp_die(
		esc_html__( 'Proxy access is not allowed.', 'tms-core-essentials' ),
		esc_html__( 'Forbidden', 'tms-core-essentials' ),
		array( 'response' => 403 )
	);
} );


add_action( 'template_redirect', function(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return;
	if ( is_admin() || is_user_logged_in() ) return;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Security redirect, not form processing.
	if ( isset( $_GET['author'] ) && absint( wp_unslash( $_GET['author'] ) ) > 0 ) :
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	endif;

	$query_string = isset( $_SERVER['QUERY_STRING'] )
		? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) )
		: '';
	if ( $query_string !== '' && preg_match( '/(?:^|&)author=\d+/i', $query_string ) ) :
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	endif;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}, 1 );


add_filter( 'redirect_canonical', function( $redirect, $request ) {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return $redirect;
	if ( is_admin() || is_user_logged_in() ) return $redirect;

	if ( is_string( $request ) && preg_match( '/\?author=\d+/i', $request ) ) return home_url( '/' );

	return $redirect;
}, 10, 2 );


add_filter( 'rest_endpoints', function( array $endpoints ): array {
	if ( ! tcres_security_setting_is_enabled( 'block_user_enumeration' ) ) return $endpoints;
	if ( is_user_logged_in() ) return $endpoints;

	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
} );


add_action( 'template_redirect', function(): void {
	if ( ! tcres_security_setting_is_enabled( 'block_author_archives' ) ) return;
	if ( is_admin() || is_user_logged_in() ) return;
	if ( ! is_author() ) return;

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}, 1 );


add_filter( 'xmlrpc_enabled', function( $enabled ) {
	if ( ! tcres_security_setting_is_enabled( 'disable_xmlrpc' ) ) return $enabled;

	return false;
} );
