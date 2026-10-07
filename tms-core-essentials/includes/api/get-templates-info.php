<?php
/**
 * Includes -> API -> Get templates info
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Page templates registered by the active theme
 *
 * @return array<int, array{slug: string, name: string, file: string}>
 */
function tcres_template_get_info( string $post_type = 'page' ): array {
	$templates = array();
	$post_type = sanitize_key( $post_type );
	if ( $post_type === '' ) $post_type = 'page';

	foreach ( wp_get_theme()->get_page_templates( null, $post_type ) as $file => $name ) :
		$templates[] = array(
			'slug' => tcres_template_get_slug_from_file( $file ),
			'name' => $name,
			'file' => $file,
		);
	endforeach;

	return $templates;
}


/**
 * Whether the theme registers custom templates for a post type
 */
function tcres_template_post_type_has_custom_templates( string $post_type ): bool {
	return ! empty( tcres_template_get_info( $post_type ) );
}


/**
 * Slug used in plugin options for the default page template
 */
function tcres_template_get_default_slug(): string {
	return 'default';
}


/**
 * Map a post to the template slug used in plugin options
 */
function tcres_template_get_post_slug( WP_Post $post ): string {
	$file = get_page_template_slug( $post );
	if ( ! is_string( $file ) || $file === '' ) :
		return tcres_template_get_default_slug();
	endif;

	return tcres_template_get_slug_from_file( $file );
}


/**
 * Build the template slug used in plugin options (Shotgun-compatible)
 */
function tcres_template_get_slug_from_file( string $file ): string {
	$slug = str_replace( '.php', '', str_replace( 'templates/template', '', $file ) );

	return str_replace( '-', '', $slug );
}
