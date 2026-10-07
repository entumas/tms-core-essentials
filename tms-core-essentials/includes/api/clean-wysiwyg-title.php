<?php
/**
 * Includes -> API -> Clean WYSIWYG title
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Prepare WYSIWYG title HTML for frontend output
 *
 * Converts paragraph markup to line breaks and allows only safe inline tags
 * Use on output only; save with wp_kses_post via tcres_post_meta_update( ..., 'html' )
 */
function tcres_clean_wysiwyg_title( string $title ): string {
	$title = trim( $title );
	if ( $title === '' ) return '';

	$title = str_replace( array( '&nbsp;', '&#160;', '&#xA0;', "\xC2\xA0" ), ' ', $title );

	$title = str_replace( array( '</p>', '<br>', '<br/>', '<br />' ), '<br>', $title );
	$title = preg_replace( '#</p>\s*<p(?:\s[^>]*)?>#i', '<br>', $title );
	$title = is_string( $title )
		? $title
		: '';
	$title = preg_replace( '#<p(?:\s[^>]*)?>#i', '', $title );
	$title = is_string( $title )
		? $title
		: '';
	$title = preg_replace( '#</p>#i', '', $title );
	$title = is_string( $title )
		? $title
		: '';
	$title = preg_replace( '#^(?:<br\s*/?>\s*)+#i', '', $title );
	$title = is_string( $title )
		? $title
		: '';
	$title = preg_replace( '#(?:\s*<br\s*/?>)+$#i', '', $title );
	$title = is_string( $title )
		? $title
		: '';

	$title = trim( $title );
	if ( $title === '' ) return '';

	$allowed_tags = array(
		'a'      => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
			'rel'    => array(),
		),
		'br'     => array(),
		'strong' => array(),
		'em'     => array(),
		'span'   => array(),
		'u'      => array(),
		's'      => array(),
	);

	return wp_kses( $title, $allowed_tags );
}
