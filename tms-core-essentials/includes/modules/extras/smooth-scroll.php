<?php
/**
 * Modules -> Extras -> Smooth scroll
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Bundled Lenis engine version and asset URLs
 *
 * @return array{version: string, js: string, css: string}
 */
function tcres_smooth_scroll_get_vendor_assets(): array {
	$vendor = TCRES_PLUGIN_URL . 'assets/vendor/lenis/';

	return array(
		'version' => '1.3.26',
		'js'      => $vendor . 'lenis.min.js',
		'css'     => $vendor . 'lenis.css',
	);
}


function tcres_smooth_scroll_is_enabled(): bool {
	return (bool) tcres_option_get( 'smooth_scroll', 'enable' );
}


/**
 * @return array<string, mixed>
 */
function tcres_smooth_scroll_get_group(): array {
	$settings = tcres_settings_get();
	$group    = isset( $settings['smooth_scroll'] ) && is_array( $settings['smooth_scroll'] )
		? $settings['smooth_scroll']
		: array();

	return $group;
}


/**
 * Normalize exclude selectors: one CSS selector per line.
 * Also accepts comma-separated input for convenience.
 */
function tcres_smooth_scroll_normalize_exclude_selectors( string $raw ): string {
	$raw   = str_replace( array( "\r\n", "\r" ), "\n", $raw );
	$parts = preg_split( '/[\n,]+/', $raw );
	if ( ! is_array( $parts ) ) return '';

	$lines = array();
	foreach ( $parts as $part ) :
		$part = trim( (string) $part );
		if ( $part !== '' ) $lines[] = $part;
	endforeach;

	return implode( "\n", $lines );
}


/**
 * Options passed to the frontend smooth-scroll init
 *
 * @return array{
 *   lerp: float,
 *   smoothWheel: bool,
 *   syncTouch: bool,
 *   anchors: bool,
 *   excludeSelectors: string
 * }
 */
function tcres_smooth_scroll_get_frontend_config(): array {
	$group = tcres_smooth_scroll_get_group();

	$lerp = isset( $group['lerp'] )
		? (float) $group['lerp']
		: 0.05;
	if ( $lerp < 0.01 ) $lerp = 0.01;
	if ( $lerp > 1 ) $lerp = 1.0;

	$config = array(
		'lerp'             => $lerp,
		'smoothWheel'      => ! empty( $group['smooth_wheel'] ),
		'syncTouch'        => ! empty( $group['sync_touch'] ),
		'anchors'          => ! empty( $group['anchors'] ),
		'excludeSelectors' => isset( $group['exclude_selectors'] )
			? tcres_smooth_scroll_normalize_exclude_selectors( (string) $group['exclude_selectors'] )
			: '',
	);

	/**
	 * Filter smooth scroll frontend config before localize/init.
	 *
	 * @param array<string, mixed> $config
	 * @param array<string, mixed> $group
	 */
	$filtered = apply_filters( 'tcres_smooth_scroll_config', $config, $group );
	return is_array( $filtered )
		? $filtered
		: $config;
}


/**
 * Inline init script (runs after the bundled Lenis file)
 */
function tcres_smooth_scroll_get_init_script(): string {
	return <<<'JS'
(function () {
	'use strict';
	if (typeof Lenis === 'undefined') return;

	var cfg = window.tcresSmoothScroll || {};
	var selectors = String(cfg.excludeSelectors || '')
		.split(/[\n,]+/)
		.map(function (s) { return s.trim(); })
		.filter(Boolean);

	selectors.forEach(function (sel) {
		try {
			document.querySelectorAll(sel).forEach(function (el) {
				if (el instanceof Element) el.setAttribute('data-lenis-prevent', '');
			});
		} catch (e) {}
	});

	window.tcresSmoothScrollInstance = new Lenis({
		autoRaf: true,
		lerp: typeof cfg.lerp === 'number' ? cfg.lerp : 0.05,
		smoothWheel: cfg.smoothWheel !== false,
		syncTouch: !!cfg.syncTouch,
		anchors: !!cfg.anchors
	});
})();
JS;
}


add_action( 'wp_enqueue_scripts', function(): void {
	if ( is_admin() || ! tcres_smooth_scroll_is_enabled() ) return;

	$assets = tcres_smooth_scroll_get_vendor_assets();

	wp_enqueue_style(
		'tcres-smooth-scroll',
		$assets['css'],
		array(),
		$assets['version'],
		'all'
	);

	wp_enqueue_script(
		'tcres-smooth-scroll',
		$assets['js'],
		array(),
		$assets['version'],
		true
	);

	wp_localize_script( 'tcres-smooth-scroll', 'tcresSmoothScroll', tcres_smooth_scroll_get_frontend_config() );
	wp_add_inline_script( 'tcres-smooth-scroll', tcres_smooth_scroll_get_init_script(), 'after' );
}, 6 );
