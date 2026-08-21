<?php
/**
 * Includes -> Modules -> Social -> Social menu
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_social_menu_is_enabled(): bool {
	return (bool) tcres_option_get( 'social_menu', 'enable' );
}


function tcres_social_menu_get_location(): string {
	return 'social';
}


function tcres_social_menu_show_network_name(): bool {
	return (bool) tcres_option_get( 'social_menu', 'show_network_name' );
}


/**
 * Build social menu markup for the registered `social` location.
 */
function tcres_social_menu_get(): string {
	if ( ! tcres_social_menu_is_enabled() ) return '';

	$menu_name = tcres_social_menu_get_location();

	if ( ! has_nav_menu( $menu_name ) ) return '';

	$locations = get_nav_menu_locations();
	if ( ! isset( $locations[ $menu_name ] ) ) return '';

	$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
	if ( ! $menu ) return '';

	$menu_items = wp_get_nav_menu_items( $menu->term_id );
	if ( ! is_array( $menu_items ) || empty( $menu_items ) ) return '';

	$show_name  = tcres_social_menu_show_network_name();
	$items_html = '';

	foreach ( $menu_items as $menu_item ) :
		if ( ! $menu_item instanceof WP_Post ) continue;

		$id    = (int) $menu_item->ID;
		$title = (string) $menu_item->title;
		$url   = (string) $menu_item->url;
		$attr_title = isset( $menu_item->attr_title ) && $menu_item->attr_title !== ''
			? (string) $menu_item->attr_title
			: '';

		$icon = tcres_svg_icon_get(
			array(
				'icon' => strtolower( $title ),
			)
		);

		// Always print the network name; hide visually with .screen-reader-text when disabled.
		$label = $show_name
			? '<span>' . esc_html( $title ) . '</span>'
			: '<span class="screen-reader-text">' . esc_html( $title ) . '</span>';

		$items_html .= sprintf(
			'<li class="tcres-menu-item menu-item-%1$d"><a href="%2$s"%3$s>%4$s%5$s</a></li>',
			$id,
			esc_url( $url ),
			$attr_title !== '' ? ' title="' . esc_attr( $attr_title ) . '"' : '',
			$icon,
			$label
		);
	endforeach;

	if ( $items_html === '' ) return '';

	$nav_class = 'tcres-social-navigation';
	if ( $show_name ) :
		$nav_class .= ' tcres-social-navigation-with-names';
	endif;

	return sprintf(
		'<nav class="%1$s" role="navigation" aria-label="%2$s"><ul id="menu-%3$s" class="%3$s-menu">%4$s</ul></nav>',
		esc_attr( $nav_class ),
		esc_attr__( 'Social menu', 'tms-core-essentials' ),
		esc_attr( $menu_name ),
		$items_html
	);
}


function tcres_social_menu_register_location(): void {
	if ( ! tcres_social_menu_is_enabled() ) return;

	register_nav_menu(
		tcres_social_menu_get_location(),
		__( 'Social menu', 'tms-core-essentials' )
	);
}

add_action( 'after_setup_theme', 'tcres_social_menu_register_location', 20 );


function tcres_social_menu_shortcode(): string {
	return tcres_social_menu_get();
}


function tcres_social_menu_register_shortcode(): void {
	add_shortcode( 'tcres-social-menu', 'tcres_social_menu_shortcode' );
}

add_action( 'init', 'tcres_social_menu_register_shortcode' );
