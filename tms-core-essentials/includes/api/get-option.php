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


	function tcres_option_resolve_value( string $name, string $key = '' ): mixed {
		$settings = tcres_settings_get();

		if ( $key !== '' ) :
			// Plugin settings group: never fall back to a WP option of the same name
			if ( array_key_exists( $name, $settings ) && is_array( $settings[ $name ] ) ) :
				return $settings[ $name ][ $key ] ?? null;
			endif;

			return tcres_option_read_wp_option_value( $name, $key );
		endif;

		if ( array_key_exists( $name, $settings ) ) return $settings[ $name ];

		return tcres_storage_option_get( $name, null );
	}


	/**
	 * Read a key from a standalone WordPress option (outside tcres_settings)
	 */
	function tcres_option_read_wp_option_value( string $option_name, string $key ): mixed {
		$options = tcres_storage_option_get( $option_name, array() );
		if ( ! is_array( $options ) ) return null;

		$lang = tcres_multilingual_get_current_language();
		if ( null !== $lang ) :
			$translated = tcres_storage_option_get( $option_name . '_' . $lang, array() );
			if ( is_array( $translated ) && ! empty( $translated ) ) :
				$options = array_replace_recursive( $options, $translated );
			endif;
		endif;

		return $options[ $key ] ?? null;
	}


	function tcres_option_format_value( mixed $value, string $format = '' ): mixed {
		if ( null === $value ) return null;

		if ( 'esc_html' === $format ) return esc_html( (string) $value );
		if ( 'wpautop' === $format ) return wpautop( (string) $value );

		return $value;
	}

endif;
