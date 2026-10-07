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


/**
 * @return array<int, string>
 */
function tcres_disable_gutenberg_get_keep_block_editor_templates( string $post_type ): array {
	$post_type = sanitize_key( $post_type );
	if ( $post_type === '' ) return array();

	$group = tcres_option_get( 'disable_gutenberg', 'keep_block_editor_on_templates' );
	if ( ! is_array( $group ) ) return array();

	$selected = isset( $group[ $post_type ] ) && is_array( $group[ $post_type ] )
		? $group[ $post_type ]
		: array();

	return array_values(
		array_filter(
			array_map( 'sanitize_key', $selected ),
			static function ( string $slug ): bool {
				return $slug !== '';
			}
		)
	);
}


function tcres_disable_gutenberg_post_keeps_block_editor( WP_Post $post ): bool {
	$selected = tcres_disable_gutenberg_get_keep_block_editor_templates( $post->post_type );
	if ( empty( $selected ) ) return false;

	return in_array( tcres_template_get_post_slug( $post ), $selected, true );
}


add_filter( 'use_block_editor_for_post_type', function( $is_enabled, $post_type ): bool {
	if ( ! is_string( $post_type ) || ! tcres_disable_gutenberg_post_type_is_disabled( $post_type ) ) :
		return (bool) $is_enabled;
	endif;

	return false;
}, 10, 2 );


add_filter( 'use_block_editor_for_post', function( $use_block_editor, $post ): bool {
	if ( ! $post instanceof WP_Post ) return (bool) $use_block_editor;

	if ( ! tcres_disable_gutenberg_post_type_is_disabled( $post->post_type ) ) :
		return (bool) $use_block_editor;
	endif;

	if ( tcres_disable_gutenberg_post_keeps_block_editor( $post ) ) :
		return true;
	endif;

	return false;
}, 10, 2 );
