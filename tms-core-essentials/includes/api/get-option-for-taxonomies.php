<?php
/**
 * Includes -> API -> Get option for taxonomies
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Taxonomies where a prefixed option key is enabled/truthy
 */
function tcres_option_get_for_taxonomies( string $option_group, string $option_prefix ): array|false {
	$include = array();

	foreach ( tcres_taxonomies_get_included() as $taxonomy ) :
		if ( tcres_option_get( $option_group, $option_prefix . $taxonomy ) )
			$include[] = $taxonomy;
	endforeach;

	if ( empty( $include ) ) return false;

	return $include;
}
