<?php
/**
 * Includes -> API -> Field helpers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Apply shortcode parsing only when the stored value may contain shortcodes
 */
function tcres_field_apply_value_format( string $value ): string {
	if ( $value === '' || ! str_contains( $value, '[' ) ) return $value;

	return do_shortcode( $value );
}


/**
 * Apply optional output formatting to a resolved field string
 */
function tcres_field_apply_output_format( string $value, string $format = '' ): string {
	if ( 'wpautop' === $format ) return wpautop( $value );
	if ( 'esc_html' === $format ) return esc_html( $value );

	return $value;
}


/**
 * Sanitize a single post meta value from admin POST data
 */
function tcres_post_meta_sanitize_value( mixed $raw ): string {
	$value = sanitize_text_field( wp_unslash( (string) $raw ) );

	return (string) apply_filters( 'tcres_post_meta_sanitize_value', $value, $raw );
}
