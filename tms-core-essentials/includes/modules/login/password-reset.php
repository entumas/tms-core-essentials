<?php
/**
 * Includes -> Modules -> Login -> Password reset
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Lost-password submission. The new-password form is a different request.
 */
function tcres_login_password_reset_is_recovery_request(): bool {
	// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Detecting the lost-password request, not processing the form.
	$method = isset( $_SERVER['REQUEST_METHOD'] )
		? strtoupper( (string) $_SERVER['REQUEST_METHOD'] )
		: '';
	if ( $method !== 'POST' ) return false;

	if ( isset( $_POST['pass1'] ) || isset( $_POST['pass2'] ) || isset( $_POST['rp_key'] ) ) return false;

	$pagenow = isset( $GLOBALS['pagenow'] )
		? (string) $GLOBALS['pagenow']
		: '';
	$action = '';
	if ( isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ) :
		$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
	endif;

	$is_wp_login = $pagenow === 'wp-login.php'
		&& $action === 'lostpassword'
		&& isset( $_POST['user_login'] );
	$is_woocommerce = isset( $_POST['wc_reset_password'] ) && isset( $_POST['user_login'] );
	// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return $is_wp_login || $is_woocommerce;
}


/**
 * Keep the first allow_password_reset result for this user.
 *
 * WooCommerce checks the filter, then get_password_reset_key() checks it again.
 * A captcha that deletes its answer on the first call would fail the second.
 */
function tcres_login_password_reset_reuse_first_result( mixed $allow, $user_id ): mixed {
	if ( ! tcres_login_password_reset_is_recovery_request() ) return $allow;

	static $results = array();
	$user_id = (int) $user_id;

	if ( array_key_exists( $user_id, $results ) ) return $results[ $user_id ];

	$results[ $user_id ] = $allow;

	return $allow;
}
add_filter( 'allow_password_reset', 'tcres_login_password_reset_reuse_first_result', PHP_INT_MAX, 2 );
