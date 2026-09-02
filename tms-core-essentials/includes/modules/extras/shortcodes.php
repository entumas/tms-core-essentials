<?php
/**
 * Modules -> Extras -> Shortcodes
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_shortcodes_is_enabled(): bool {
	return (bool) tcres_option_get( 'shortcodes', 'enable' );
}


/**
 * @param array<string, mixed> $atts
 * @return array<string, string>
 */
function tcres_shortcodes_string_atts( array $atts, array $defaults ): array {
	$merged = shortcode_atts( $defaults, $atts );
	$out    = array();
	foreach ( $merged as $key => $value ) :
		$out[ $key ] = is_scalar( $value )
			? trim( (string) $value )
			: '';
	endforeach;
	return $out;
}


function tcres_shortcodes_sanitize_embed_id( string $id ): string {
	return preg_replace( '/[^a-zA-Z0-9_-]/', '', $id ) ?? '';
}


// Data / API ========================================

function tcres_shortcode_field( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'field'         => '',
			'field_num'     => '',
			'group'         => '',
			'group_num'     => '',
			'before'        => '',
			'after'         => '',
			'before_repeat' => '',
			'after_repeat'  => '',
			'format'        => '',
			'post_id'       => '',
		)
	);

	if ( $atts['field'] === '' ) return '';

	$args = array_filter( $atts, static fn( $value ) => $value !== '' );
	return tcres_field_get( $args );
}


function tcres_shortcode_tax_field( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'tax'           => '',
			'post_id'       => '',
			'field'         => '',
			'field_num'     => '',
			'group'         => '',
			'group_num'     => '',
			'before'        => '',
			'after'         => '',
			'before_repeat' => '',
			'after_repeat'  => '',
			'format'        => '',
		)
	);

	if ( $atts['tax'] === '' || $atts['field'] === '' ) return '';

	$args = array_filter( $atts, static fn( $value ) => $value !== '' );
	return tcres_tax_field_get( $args );
}


function tcres_shortcode_option( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'name'   => '',
			'value'  => '',
			'format' => '',
		)
	);
	if ( $atts['name'] === '' || $atts['value'] === '' ) return '';

	$name = sanitize_key( $atts['name'] );
	if ( ! tcres_option_is_plugin_group( $name ) ) return '';

	$format = $atts['format'] !== ''
		? sanitize_key( $atts['format'] )
		: 'esc_html';
	if ( ! in_array( $format, array( 'esc_html', 'html', 'wpautop' ), true ) ) :
		$format = 'esc_html';
	endif;

	$result = tcres_option_get( $name, $atts['value'], $format );
	if ( null === $result || false === $result ) return '';
	if ( is_scalar( $result ) ) return (string) $result;

	return '';
}


// Markup / UI ========================================

function tcres_shortcode_button( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'link'  => '',
			'label' => '',
			'class' => '',
			'title' => '',
			'blank' => '',
		)
	);
	if ( $atts['label'] === '' || $atts['link'] === '' ) return '';

	$classes = trim( 'tcres-button ' . $atts['class'] );

	if ( $atts['title'] !== '' ) :
		$title = $atts['title'] !== 'none'
			? $atts['title']
			: '';
	elseif ( stripos( $atts['link'], 'tel:' ) !== false ) :
		$title = __( 'Call to', 'tms-core-essentials' ) . ' ' . str_replace( 'tel:', '', $atts['link'] );
	elseif ( stripos( $atts['link'], 'mailto:' ) !== false ) :
		$title = __( 'Send mail to', 'tms-core-essentials' ) . ' ' . str_replace( 'mailto:', '', $atts['link'] );
	else :
		$title = '';
	endif;

	$title_attr = $title !== ''
		? ' title="' . esc_attr( $title ) . '"'
		: '';
	$blank_attr = $atts['blank'] === 'yes'
		? ' target="_blank" rel="noopener noreferrer"'
		: '';

	return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $atts['link'] ) . '"' . $title_attr . $blank_attr . ' role="button">'
		. esc_html( $atts['label'] )
		. '</a>';
}


/**
 * @param array<string, mixed>|string $atts
 * @param string|null                 $content
 */
function tcres_shortcode_highlighted( $atts = array(), $content = null ): string {
	$content = null !== $content
		? trim( (string) $content )
		: '';
	if ( $content === '' ) return '';

	return '<span class="tcres-highlighted">' . wp_kses_post( $content ) . '</span>';
}


function tcres_shortcode_svgicon( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'icon'  => '',
			'file'  => '',
			'class' => '',
		)
	);
	if ( $atts['icon'] === '' ) return '';

	$args = array(
		'icon' => $atts['icon'],
	);
	if ( $atts['file'] !== '' ) :
		$args['file'] = $atts['file'];
	endif;

	$html = tcres_svg_icon_get( $args );
	if ( $html === '' || $atts['class'] === '' ) return $html;

	return preg_replace(
		'/class="/',
		'class="' . esc_attr( $atts['class'] ) . ' ',
		$html,
		1
	) ?: $html;
}


// Embeds ========================================

function tcres_shortcode_youtube( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'id' => '',
		)
	);

	$id = tcres_shortcodes_sanitize_embed_id( $atts['id'] );
	if ( $id === '' ) return '';

	return '<div class="tcres-embed tcres-embed-youtube">'
		. '<iframe src="https://www.youtube.com/embed/' . esc_attr( $id ) . '" title="' . esc_attr__( 'YouTube video', 'tms-core-essentials' ) . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen loading="lazy"></iframe>'
		. '</div>';
}


function tcres_shortcode_vimeo( $atts ): string {
	$atts = tcres_shortcodes_string_atts(
		is_array( $atts ) ? $atts : array(),
		array(
			'id' => '',
		)
	);

	$id = tcres_shortcodes_sanitize_embed_id( $atts['id'] );
	if ( $id === '' ) return '';

	return '<div class="tcres-embed tcres-embed-vimeo">'
		. '<iframe src="https://player.vimeo.com/video/' . esc_attr( $id ) . '" title="' . esc_attr__( 'Vimeo video', 'tms-core-essentials' ) . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>'
		. '</div>';
}


add_action( 'init', function(): void {
	if ( ! tcres_shortcodes_is_enabled() ) return;

	add_shortcode( 'tcres-field', 'tcres_shortcode_field' );
	add_shortcode( 'tcres-tax-field', 'tcres_shortcode_tax_field' );
	add_shortcode( 'tcres-option', 'tcres_shortcode_option' );
	add_shortcode( 'tcres-button', 'tcres_shortcode_button' );
	add_shortcode( 'tcres-highlighted', 'tcres_shortcode_highlighted' );
	add_shortcode( 'tcres-svgicon', 'tcres_shortcode_svgicon' );
	add_shortcode( 'tcres-youtube', 'tcres_shortcode_youtube' );
	add_shortcode( 'tcres-vimeo', 'tcres_shortcode_vimeo' );
} );
