<?php
/**
 * Includes -> API -> Get option for post types diff
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Post types present in $from but not in $against
 */
function tcres_option_get_for_post_types_diff( string $from_option_group, string $from_option_prefix, string $against_option_group, string $against_option_prefix ): array {
	$from    = tcres_option_get_for_post_types( $from_option_group, $from_option_prefix );
	$against = tcres_option_get_for_post_types( $against_option_group, $against_option_prefix );

	if ( ! is_array( $from ) ) return array();
	if ( ! is_array( $against ) ) return $from;

	return array_values( array_diff( $from, $against ) );
}
