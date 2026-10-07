<?php
/**
 * Includes -> API -> SVG icon
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Default sprite URL for the current context (admin vs frontend settings).
 * Empty setting → plugin sprite.
 */
function tcres_svg_icon_get_default_file_url(): string {
	$key = is_admin()
		? 'admin_file'
		: 'frontend_file';
	$url = trim( (string) tcres_option_get( 'svg_icons', $key ) );

	return $url !== ''
		? $url
		: TCRES_PLUGIN_URL . 'assets/images/icons.svg';
}


if ( ! function_exists( 'tcres_svg_icon_get' ) ) :
	/**
	 * @param array{
	 *   icon?: string,
	 *   file?: string,
	 *   inline?: bool
	 * } $args
	 */
	function tcres_svg_icon_get( array $args = array() ): string {
		$icon = isset( $args['icon'] )
			? trim( (string) $args['icon'] )
			: '';
		if ( $icon === '' ) return '';

		$file = isset( $args['file'] )
			? trim( (string) $args['file'] )
			: '';
		$url  = $file !== ''
			? $file
			: tcres_svg_icon_get_default_file_url();

		$inline = array_key_exists( 'inline', $args )
			? (bool) $args['inline']
			: false;

		$icon_class = sanitize_html_class( $icon );
		$classes    = 'tcres-svg-icon svg-icon';
		if ( $inline ) :
			$classes .= ' svg-icon-inline';
		endif;
		if ( $icon_class !== '' ) :
			$classes .= ' svg-icon-' . $icon_class;
		endif;

		return sprintf(
			'<svg class="%1$s" aria-hidden="true" focusable="false"><use href="%2$s#%3$s"/></svg>',
			esc_attr( $classes ),
			esc_url( $url ),
			esc_attr( $icon )
		);
	}
endif;
