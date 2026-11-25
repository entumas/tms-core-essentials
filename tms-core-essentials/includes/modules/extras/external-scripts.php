<?php
/**
 * Modules -> Extras -> External scripts
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Library definitions (order matches Settings UI)
 *
 * @return array<string, array{
 *   version: string,
 *   handle_js: string,
 *   handle_css: string,
 *   js: string,
 *   css: string
 * }>
 */
function tcres_external_scripts_get_libraries(): array {
	$vendor = TCRES_PLUGIN_URL . 'assets/vendor/';

	return array(
		'swiper' => array(
			'version'    => '11.2.10',
			'handle_js'  => 'tcres-swiper',
			'handle_css' => 'tcres-swiper',
			'js'         => $vendor . 'swiper/swiper-bundle.min.js',
			'css'        => $vendor . 'swiper/swiper-bundle.min.css',
		),
		'glightbox' => array(
			'version'    => '3.3.1',
			'handle_js'  => 'tcres-glightbox',
			'handle_css' => 'tcres-glightbox',
			'js'         => $vendor . 'glightbox/glightbox.min.js',
			'css'        => $vendor . 'glightbox/glightbox.min.css',
		),
		'choices' => array(
			'version'    => '11.1.0',
			'handle_js'  => 'tcres-choices',
			'handle_css' => 'tcres-choices',
			'js'         => $vendor . 'choices/choices.min.js',
			'css'        => $vendor . 'choices/choices.min.css',
		),
	);
}


/**
 * Whether a library switch is enabled
 */
function tcres_external_scripts_is_enabled( string $key ): bool {
	return (bool) tcres_option_get( 'external_scripts', $key );
}


/**
 * Public site only. Admin and the login screen do not register these libraries.
 */
function tcres_external_scripts_is_public_request(): bool {
	if ( is_admin() ) return false;

	$pagenow = isset( $GLOBALS['pagenow'] )
		? (string) $GLOBALS['pagenow']
		: '';
	if ( in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) return false;

	return true;
}


/**
 * Register enabled bundled vendor assets on the public site.
 * They print when a script depends on the handle or enqueues it.
 */
add_action( 'wp_enqueue_scripts', function(): void {
	if ( ! tcres_external_scripts_is_public_request() ) return;

	foreach ( tcres_external_scripts_get_libraries() as $key => $lib ) :
		if ( ! tcres_external_scripts_is_enabled( $key ) ) continue;

		wp_register_style(
			$lib['handle_css'],
			$lib['css'],
			array(),
			$lib['version'],
			'all'
		);

		wp_register_script(
			$lib['handle_js'],
			$lib['js'],
			array(),
			$lib['version'],
			true
		);
	endforeach;
}, 5 );


/**
 * Print library CSS when its script handle is queued or required.
 *
 * Styles print before WordPress resolves script dependencies, so a handle
 * that only sits in deps is not in the queue yet.
 */
add_action( 'wp_print_styles', function(): void {
	if ( ! tcres_external_scripts_is_public_request() ) return;

	global $wp_scripts;
	if ( ! ( $wp_scripts instanceof WP_Scripts ) || ! is_array( $wp_scripts->queue ) ) return;

	$style_handles = array();
	foreach ( tcres_external_scripts_get_libraries() as $key => $lib ) :
		if ( ! tcres_external_scripts_is_enabled( $key ) ) continue;
		$style_handles[ $lib['handle_js'] ] = $lib['handle_css'];
	endforeach;
	if ( $style_handles === array() ) return;

	$seen  = array();
	$stack = array();
	foreach ( $wp_scripts->queue as $queued ) :
		$stack[] = (string) $queued;
	endforeach;

	while ( $stack !== array() ) :
		$handle = (string) array_pop( $stack );
		if ( isset( $seen[ $handle ] ) ) continue;
		$seen[ $handle ] = true;

		if ( isset( $style_handles[ $handle ] ) ) :
			wp_enqueue_style( $style_handles[ $handle ] );
		endif;

		$item = isset( $wp_scripts->registered[ $handle ] )
			? $wp_scripts->registered[ $handle ]
			: null;
		if ( ! $item instanceof _WP_Dependency ) continue;

		foreach ( $item->deps as $dep ) :
			$stack[] = (string) $dep;
		endforeach;
	endwhile;
} );
