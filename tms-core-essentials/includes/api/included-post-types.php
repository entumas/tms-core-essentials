<?php
/**
 * Includes -> API -> Included post types
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Post types useful for theme/plugin logic, excluding system and common plugin types
 */
function tcres_post_types_get_included(): array {
	$exclude = array(
		'attachment',
		'custom_css',
		'customize_changeset',
		'nav_menu_item',
		'oembed_cache',
		'patterns_ai_data',
		'revision',
		'scheduled-action',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_global_styles',
		'wp_navigation',
		'wp_font_family',
		'wp_font_face',
		'acf-taxonomy',
		'acf-post-type',
		'acf-field-group',
		'acf-ui-options-page',
		'acf-field',
		'wpcf7_contact_form',
		'e-landing-page',
		'elementor_font',
		'elementor-hf',
		'elementor_icons',
		'elementor_library',
		'es_template',
		'feedback',
		'jp_mem_plan',
		'jp_pay_order',
		'jp_pay_product',
		'product_attribute',
		'product_variation',
		'shop_coupon',
		'shop_order',
		'shop_order_placehold',
		'shop_order_refund',
		'shop_subscription',
		'shop_webhook',
		'tms-overlay',
	);

	/**
	 * Filter post types excluded from tcres_post_types_get_included()
	 */
	$exclude = apply_filters( 'tcres_post_types_get_included_exclude', $exclude );
	$exclude = array_fill_keys( $exclude, true );

	$include = array();
	foreach ( get_post_types( array(), 'names' ) as $post_type ) :
		if ( ! isset( $exclude[ $post_type ] ) )
			$include[] = $post_type;
	endforeach;

	/**
	 * Filter the post types returned by tcres_post_types_get_included()
	 */
	return apply_filters( 'tcres_post_types_get_included', $include );
}
