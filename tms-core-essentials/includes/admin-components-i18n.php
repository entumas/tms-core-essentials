<?php
/**
 * Includes -> Admin Components i18n
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Central catalog of translatable strings for JavaScript (admin, editor, frontend)
 */
function tcres_admin_components_get_i18n_strings(): array {
	return array(
		'replaceImage'        => __( 'Replace image', 'tms-core-essentials' ),
		'selectFeaturedImage' => __( 'Select featured image', 'tms-core-essentials' ),
		'selectImage'         => __( 'Select image', 'tms-core-essentials' ),
		'useThisImage'        => __( 'Use this image', 'tms-core-essentials' ),
		'replaceVideo'        => __( 'Replace video', 'tms-core-essentials' ),
		'removeVideo'         => __( 'Remove video', 'tms-core-essentials' ),
		'setFeaturedVideo'    => __( 'Set featured video', 'tms-core-essentials' ),
		'selectFeaturedVideo' => __( 'Select featured video', 'tms-core-essentials' ),
		'selectVideo'         => __( 'Select video', 'tms-core-essentials' ),
		'useThisVideo'        => __( 'Use this video', 'tms-core-essentials' ),
		'removeFeaturedVideo' => __( 'Remove video', 'tms-core-essentials' ),
	);
}
