<?php
/**
 * Includes -> API -> Get registered image sizes
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Registered image sizes with dimensions, formatted for UI or API consumers
 */
function tcres_image_size_get_registered(): array {
	$sizes       = wp_get_registered_image_subsizes();
	$image_sizes = array();

	foreach ( $sizes as $size => $details ) :
		if ( empty( $details['width'] ) || empty( $details['height'] ) ) continue;

		$image_size = array(
			'label' => ucfirst( (string) $size ) . ' (' . (int) $details['width'] . 'x' . (int) $details['height'] . ')',
			'value' => $size,
		);

		if ( $size === 'medium' ) $image_size['selected'] = 'true';

		$image_sizes[] = $image_size;
	endforeach;

	return $image_sizes;
}
