<?php
/**
 * Includes -> Storage
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get WordPress option
 */
function tcres_storage_option_get( string $option_name, mixed $default = null ): mixed {
	if ( '' === trim( $option_name ) ) return $default;

	$value = get_option( $option_name, $default );

	return $value;
}


/**
 * Update WordPress option
 */
function tcres_storage_option_update( string $option_name, mixed $value ): bool {
	if ( '' === trim( $option_name ) ) return false;

	return update_option( $option_name, $value );
}


/**
 * Get post meta
 */
function tcres_storage_post_meta_get( int $post_id, string $meta_key, mixed $default = null ): mixed {
	if ( $post_id <= 0 || '' === trim( $meta_key ) ) return $default;

	$value = get_post_meta( $post_id, $meta_key, true );
	if ( '' === $value && null !== $default ) return $default;

	return $value;
}


/**
 * Update post meta
 */
function tcres_storage_post_meta_update( int $post_id, string $meta_key, mixed $value ): bool {
	if ( $post_id <= 0 || '' === trim( $meta_key ) ) return false;

	$result = update_post_meta( $post_id, $meta_key, $value );

	return false !== $result;
}
