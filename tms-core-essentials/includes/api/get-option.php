<?php
/**
 * Includes -> API -> Get option
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'tcres_option_get' ) ) :

	function tcres_option_get( string $name, string $key = '', string $format = '' ): mixed {
		$resolved = tcres_option_resolve_value( $name, $key );
		return tcres_option_format_value( $resolved, $format );
	}


	/**
	 * Whether $name is a plugin settings group stored in tcres_settings.
	 */
	function tcres_option_is_plugin_group( string $name ): bool {
		$name = sanitize_key( $name );
		if ( $name === '' ) return false;

		$settings = tcres_settings_get();
		return array_key_exists( $name, $settings );
	}


	function tcres_option_resolve_value( string $name, string $key = '' ): mixed {
		$settings = tcres_settings_get();
		if ( ! array_key_exists( $name, $settings ) ) return null;

		if ( $key !== '' ) :
			if ( ! is_array( $settings[ $name ] ) ) return null;
			return $settings[ $name ][ $key ] ?? null;
		endif;

		return $settings[ $name ];
	}


	function tcres_option_format_value( mixed $value, string $format = '' ): mixed {
		if ( null === $value ) return null;

		if ( 'esc_html' === $format ) return esc_html( (string) $value );
		if ( 'html' === $format ) return wp_kses_post( (string) $value );
		if ( 'wpautop' === $format ) return wpautop( (string) $value );

		return $value;
	}

endif;
