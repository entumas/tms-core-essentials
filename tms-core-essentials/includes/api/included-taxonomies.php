<?php
/**
 * Includes -> API -> Included taxonomies
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Taxonomies useful for theme/plugin logic, excluding system and common plugin types
 */
function tcres_taxonomies_get_included(): array {
	$exclude = array(
		'nav_menu',
		'link_category',
		'post_format',
		'wp_theme',
		'wp_template_part_area',
		'wp_pattern_category',
		'product_type',
		'product_visibility',
		'product_shipping_class',
		'translation_priority',
	);

	/**
	 * Filter taxonomies excluded from tcres_taxonomies_get_included()
	 */
	$exclude = apply_filters( 'tcres_taxonomies_get_included_exclude', $exclude );
	$exclude = array_fill_keys( $exclude, true );

	$include = array();
	foreach ( get_taxonomies( array(), 'names' ) as $taxonomy ) :
		if ( ! isset( $exclude[ $taxonomy ] ) )
			$include[] = $taxonomy;
	endforeach;

	/**
	 * Filter the taxonomies returned by tcres_taxonomies_get_included()
	 */
	return apply_filters( 'tcres_taxonomies_get_included', $include );
}
