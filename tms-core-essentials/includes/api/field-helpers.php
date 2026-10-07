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
 * Allowed output formats for field/tax-field shortcodes and APIs.
 *
 * @return array<int, string>
 */
function tcres_field_get_allowed_formats(): array {
	return array( 'esc_html', 'html', 'wpautop' );
}


/**
 * Normalize and validate a field output format key.
 */
function tcres_field_resolve_format( string $format, string $default = '' ): string {
	$format = $format !== ''
		? sanitize_key( $format )
		: $default;

	if ( ! in_array( $format, tcres_field_get_allowed_formats(), true ) ) :
		return $default !== ''
			? $default
			: '';
	endif;

	return $format;
}


/**
 * Apply optional output formatting to a resolved field string
 */
function tcres_field_apply_output_format( string $value, string $format = '' ): string {
	if ( $format === '' ) return $value;

	if ( 'esc_html' === $format ) return esc_html( $value );
	if ( 'html' === $format ) return wp_kses_post( $value );
	if ( 'wpautop' === $format ) return wp_kses_post( wpautop( $value ) );

	return $value;
}


/**
 * Format a stored meta value for output (shortcodes + optional format).
 */
function tcres_field_format_field_value( string $value, string $format = '' ): string {
	return tcres_field_apply_output_format( tcres_field_apply_value_format( $value ), $format );
}


/**
 * Sanitize shortcode wrapper attributes (before, after, before_repeat, after_repeat).
 *
 * @return array{0: string, 1: string, 2: string, 3: string}
 */
function tcres_field_format_wrapper_parts(
	string $before,
	string $after,
	string $before_repeat,
	string $after_repeat,
	string $format = ''
): array {
	if ( $format === '' ) :
		return array( $before, $after, $before_repeat, $after_repeat );
	endif;

	return array(
		tcres_field_apply_output_format( $before, $format ),
		tcres_field_apply_output_format( $after, $format ),
		tcres_field_apply_output_format( $before_repeat, $format ),
		tcres_field_apply_output_format( $after_repeat, $format ),
	);
}


/**
 * Build one field segment with escaped wrappers and value.
 */
function tcres_field_build_segment( string $before, string $value, string $after, string $format = '' ): string {
	return $before . tcres_field_format_field_value( $value, $format ) . $after;
}


/**
 * Sanitize a single post meta value from admin POST data
 */
function tcres_post_meta_sanitize_value( mixed $raw ): string {
	$value = sanitize_text_field( wp_unslash( (string) $raw ) );

	return (string) apply_filters( 'tcres_post_meta_sanitize_value', $value, $raw );
}
