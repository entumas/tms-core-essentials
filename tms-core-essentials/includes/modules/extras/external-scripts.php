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
 * Enqueue enabled bundled vendor assets on the frontend
 */
add_action( 'wp_enqueue_scripts', function(): void {
	if ( is_admin() ) return;

	foreach ( tcres_external_scripts_get_libraries() as $key => $lib ) :
		if ( ! tcres_external_scripts_is_enabled( $key ) ) continue;

		wp_enqueue_style(
			$lib['handle_css'],
			$lib['css'],
			array(),
			$lib['version'],
			'all'
		);

		wp_enqueue_script(
			$lib['handle_js'],
			$lib['js'],
			array(),
			$lib['version'],
			true
		);
	endforeach;
}, 5 );
