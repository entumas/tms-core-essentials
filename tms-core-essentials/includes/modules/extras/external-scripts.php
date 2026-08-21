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
	return array(
		'swiper' => array(
			'version'    => '11.2.10',
			'handle_js'  => 'tcres-swiper',
			'handle_css' => 'tcres-swiper',
			'js'         => 'https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.js',
			'css'        => 'https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.css',
		),
		'glightbox' => array(
			'version'    => '3.3.1',
			'handle_js'  => 'tcres-glightbox',
			'handle_css' => 'tcres-glightbox',
			'js'         => 'https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/js/glightbox.min.js',
			'css'        => 'https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/css/glightbox.min.css',
		),
		'choices' => array(
			'version'    => '11.1.0',
			'handle_js'  => 'tcres-choices',
			'handle_css' => 'tcres-choices',
			'js'         => 'https://cdn.jsdelivr.net/npm/choices.js@11.1.0/public/assets/scripts/choices.min.js',
			'css'        => 'https://cdn.jsdelivr.net/npm/choices.js@11.1.0/public/assets/styles/choices.min.css',
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
 * Enqueue enabled vendor assets on the frontend (CDN, pinned versions)
 */
function tcres_external_scripts_enqueue(): void {
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
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is pinned in the CDN URL.
			true
		);
	endforeach;
}

add_action( 'wp_enqueue_scripts', 'tcres_external_scripts_enqueue', 5 );
