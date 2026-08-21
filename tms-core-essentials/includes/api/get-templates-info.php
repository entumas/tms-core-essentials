<?php
/**
 * Includes -> API -> Get templates info
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Page templates registered by the active theme
 */
function tcres_template_get_info(): array {
	$templates = array();

	foreach ( wp_get_theme()->get_page_templates() as $file => $name ) :
		$templates[] = array(
			'slug' => tcres_template_get_slug_from_file( $file ),
			'name' => $name,
			'file' => $file,
		);
	endforeach;

	return $templates;
}


/**
 * Build the template slug used in plugin options (Shotgun-compatible)
 */
function tcres_template_get_slug_from_file( string $file ): string {
	$slug = str_replace( '.php', '', str_replace( 'templates/template', '', $file ) );

	return str_replace( '-', '', $slug );
}
