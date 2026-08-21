<?php
/**
 * Includes -> API -> Get option for post types
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Post types where a prefixed option key is enabled/truthy
 */
function tcres_option_get_for_post_types( string $option_group, string $option_prefix ): array|false {
	$include = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		if ( tcres_option_get( $option_group, $option_prefix . $post_type ) )
			$include[] = $post_type;
	endforeach;

	if ( empty( $include ) ) return false;

	return $include;
}
