<?php
/**
 * Includes -> API -> Update term meta
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'tcres_term_meta_update' ) ) :

	/**
	 * @param string $sanitize text|html
	 */
	function tcres_term_meta_update( int $term_id, array $meta_keys, string $prefix = '', string $sanitize = 'text' ): void {
		if ( empty( $meta_keys ) ) return;

		$nonce_name = str_replace( $prefix, '', (string) $meta_keys[0] );
		$length     = strpos( $nonce_name, '_' );
		$nonce_name = false !== $length
			? substr( $nonce_name, 0, $length )
			: $nonce_name;
		$nonce_name = $prefix . $nonce_name . '_nonce';

		if (
			! isset( $_POST[ $nonce_name ] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST[ $nonce_name ] ) ), '_' . $nonce_name )
			|| ! current_user_can( 'edit_term', $term_id )
		) :
			return;
		endif;

		foreach ( $meta_keys as $meta_key ) :
			if ( isset( $_POST[ $meta_key ] ) && $_POST[ $meta_key ] !== '' ) :
				$raw   = wp_unslash( $_POST[ $meta_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$value = $sanitize === 'html'
					? wp_kses_post( (string) $raw )
					: tcres_post_meta_sanitize_value( $raw );

				if ( $sanitize === 'html' ) :
					$value = (string) apply_filters( 'tcres_post_meta_sanitize_html_value', $value, (string) $meta_key );
				endif;

				if ( $value === '' ) :
					delete_term_meta( $term_id, $meta_key );
				else :
					update_term_meta( $term_id, $meta_key, $value );
				endif;
			else :
				delete_term_meta( $term_id, $meta_key );
			endif;
		endforeach;
	}

endif;
