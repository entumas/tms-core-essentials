<?php
/**
 * Includes -> Modules -> Content -> Scroll to top
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_scroll_to_top_is_enabled(): bool {
	return (bool) tcres_option_get( 'scroll_to_top', 'enable' );
}


/**
 * @return array<string, mixed>
 */
function tcres_scroll_to_top_get_group(): array {
	$settings = tcres_settings_get();
	$group    = isset( $settings['scroll_to_top'] ) && is_array( $settings['scroll_to_top'] )
		? $settings['scroll_to_top']
		: array();

	return $group;
}


function tcres_scroll_to_top_should_display(): bool {
	return tcres_scroll_to_top_is_enabled() && ! is_admin();
}


/**
 * Build scroll-to-top button markup
 */
function tcres_scroll_to_top_get(): string {
	if ( ! tcres_scroll_to_top_should_display() ) return '';

	$group = tcres_scroll_to_top_get_group();

	$threshold = isset( $group['threshold_viewports'] )
		? (float) $group['threshold_viewports']
		: 1.0;
	if ( $threshold < 0.1 ) $threshold = 0.1;

	$avoid_footer    = ! empty( $group['avoid_footer'] );
	$footer_selector = isset( $group['footer_selector'] )
		? trim( (string) $group['footer_selector'] )
		: '';
	$footer_gap      = isset( $group['footer_gap'] )
		? (int) $group['footer_gap']
		: 16;
	if ( $footer_gap < 0 ) $footer_gap = 0;

	if ( ! $avoid_footer || $footer_selector === '' ) :
		$avoid_footer    = false;
		$footer_selector = '';
	endif;

	$icon = tcres_svg_icon_get(
		array(
			'icon' => 'arrow-up',
		)
	);

	$attrs = array(
		'type'                           => 'button',
		'id'                             => 'tcres-scroll-to-top',
		'class'                          => 'tcres-scroll-to-top',
		'aria-label'                     => __( 'Scroll to top', 'tms-core-essentials' ),
		'hidden'                         => true,
		'data-tcres-scroll-threshold'     => (string) $threshold,
		'data-tcres-scroll-avoid-footer'  => $avoid_footer ? '1' : '0',
		'data-tcres-scroll-footer-gap'    => (string) $footer_gap,
	);

	if ( $footer_selector !== '' ) :
		$attrs['data-tcres-scroll-footer-selector'] = $footer_selector;
	endif;

	$attr_html = '';
	foreach ( $attrs as $key => $value ) :
		if ( $key === 'hidden' ) :
			$attr_html .= ' hidden';
			continue;
		endif;
		$attr_html .= ' ' . esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
	endforeach;

	return '<button' . $attr_html . '>' . $icon . '</button>';
}


function tcres_scroll_to_top_render(): void {
	echo tcres_scroll_to_top_get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaping.
}


/**
 * Print chat + scroll-to-top back-to-back in the footer so
 * `.tcres-chat + .tcres-scroll-to-top` can offset the button when both exist.
 */
add_action( 'wp_footer', function(): void {
	if ( function_exists( 'tcres_chats_render' ) ) tcres_chats_render();
	tcres_scroll_to_top_render();
}, 20 );
