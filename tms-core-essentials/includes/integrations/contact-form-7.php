<?php
/**
 * Includes -> Integrations -> Contact Form 7
 * Expand plugin shortcodes (tcres-*) inside CF7 form markup by default.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Run do_shortcode limited to registered tags that start with tcres-.
 */
function tcres_cf7_do_tcres_shortcodes( string $content ): string {
	if ( $content === '' || false === strpos( $content, '[tcres-' ) ) :
		return $content;
	endif;

	global $shortcode_tags;

	if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) :
		return $content;
	endif;

	$tcres_tags = array();
	foreach ( $shortcode_tags as $tag => $callback ) :
		if ( is_string( $tag ) && str_starts_with( $tag, 'tcres-' ) ) :
			$tcres_tags[ $tag ] = $callback;
		endif;
	endforeach;

	if ( empty( $tcres_tags ) ) :
		return $content;
	endif;

	$previous       = $shortcode_tags;
	$shortcode_tags = $tcres_tags;
	$content        = do_shortcode( $content );
	$shortcode_tags = $previous;

	return $content;
}


/**
 * @param string $form
 * @return string
 */
add_filter( 'wpcf7_form_elements', function( $form ) {
	if ( ! is_string( $form ) || $form === '' ) return $form;

	return tcres_cf7_do_tcres_shortcodes( $form );
} );
