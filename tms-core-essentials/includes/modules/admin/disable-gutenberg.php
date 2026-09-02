<?php
/**
 * Includes -> Modules -> Admin -> Disable Gutenberg
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_disable_gutenberg_setting_is_enabled( string $key ): bool {
	return (bool) tcres_option_get( 'disable_gutenberg', $key );
}


function tcres_disable_gutenberg_post_type_is_disabled( string $post_type ): bool {
	$post_type = sanitize_key( $post_type );
	if ( $post_type === '' ) return false;

	return tcres_disable_gutenberg_setting_is_enabled( 'disable_' . $post_type );
}


add_filter( 'use_block_editor_for_post_type', function( $is_enabled, $post_type ): bool {
	if ( ! is_string( $post_type ) || ! tcres_disable_gutenberg_post_type_is_disabled( $post_type ) ) :
		return (bool) $is_enabled;
	endif;

	return false;
}, 10, 2 );
