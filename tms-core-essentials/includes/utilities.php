<?php
/**
 * Includes -> Utilities
 * Utilities and helpers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! defined( 'TCRES_REQUIRES_WP' ) ) define( 'TCRES_REQUIRES_WP', '6.0' );
if ( ! defined( 'TCRES_REQUIRES_PHP' ) ) define( 'TCRES_REQUIRES_PHP', '8.0' );


/**
 * Get plugin name
 */
function tcres_plugin_get_name(): string {
	static $tcres_plugin_name = null;

	if ( null !== $tcres_plugin_name ) return $tcres_plugin_name;

	$data = get_plugin_data( TCRES_PLUGIN_FILE, false, false );
	$tcres_plugin_name = $data['Name'] ?? __( 'Plugin', 'tms-core-essentials' );

	return $tcres_plugin_name;
}


/**
 * File modification time for a path under the plugin directory, or plugin version if missing
 */
function tcres_plugin_get_asset_file_mtime( string $relative ): int {
	$path = TCRES_PLUGIN_PATH . $relative;
	return file_exists( $path )
		? (int) filemtime( $path )
		: (int) TCRES_PLUGIN_VERSION;
}


/**
 * Minimum WordPress and PHP version
 */
function tcres_plugin_requirements_are_met(): bool {
	global $wp_version;

	$wp = is_string( $wp_version )
		? $wp_version
		: '';
	if ( $wp === '' ) return false;

	return version_compare( $wp, TCRES_REQUIRES_WP, '>=' )
		&& version_compare( PHP_VERSION, TCRES_REQUIRES_PHP, '>=' );
}


/**
 * Register admin notice when requirements are not met (plugin boot stops in main file)
 */
function tcres_plugin_requirements_register_admin_notice(): void {
	add_action( 'admin_notices', function(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) return;

		global $wp_version;

		$wp_current = is_string( $wp_version )
			? $wp_version
			: __( 'unknown', 'tms-core-essentials' );
		$name = tcres_plugin_get_name();

		$message = sprintf(
			/* translators: 1: plugin name, 2: required WP version, 3: current WP version, 4: required PHP version, 5: current PHP version */
			__( '%1$s requires WordPress %2$s or higher (you are running %3$s) and PHP %4$s or higher (this server reports %5$s). Please update WordPress or PHP, or contact your host.', 'tms-core-essentials' ),
			$name,
			TCRES_REQUIRES_WP,
			$wp_current,
			TCRES_REQUIRES_PHP,
			PHP_VERSION
		);

		echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
	}, 0 );
}


function tcres_plugin_log_error( string $message ): void {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging when WP_DEBUG is enabled.
	error_log( $message );
}


/**
 * Unique ID for markup (collapse pairs, etc.)
 */
function tcres_unique_id( string $prefix = 'tcres-' ): string {
	return wp_unique_id( $prefix );
}


/**
 * Render WYSIWYG / settings-editor HTML for the frontend (autop, shortcodes, kses).
 */
function tcres_format_editor_content( string $html ): string {
	$html = trim( $html );
	if ( $html === '' ) return '';

	return wp_kses_post( apply_filters( 'the_content', $html ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core hook.
}


/**
 * Safely require a file from /includes relative path
 */
function tcres_include_file( string $relative ): void {
	$tcres_includes_file = TCRES_PLUGIN_PATH . 'includes/' . ltrim( $relative, '/' );

	if ( file_exists( $tcres_includes_file ) ) require_once $tcres_includes_file;
}


/**
 * Attach CSS classes to a meta box postbox for the given post types.
 *
 * @param array<int, string> $post_types
 * @param string             $metabox_id Meta box id (same as add_meta_box $id).
 * @param array<int, string> $classes
 */
function tcres_admin_register_metabox_classes( array $post_types, string $metabox_id, array $classes ): void {
	$metabox_id = sanitize_key( $metabox_id );
	$classes    = array_values(
		array_filter(
			array_map(
				static function ( $class ): string {
					return sanitize_html_class( (string) $class );
				},
				$classes
			)
		)
	);
	if ( $metabox_id === '' || empty( $post_types ) || empty( $classes ) ) return;

	foreach ( $post_types as $post_type ) :
		$post_type = sanitize_key( (string) $post_type );
		if ( $post_type === '' ) continue;

		add_filter(
			"postbox_classes_{$post_type}_{$metabox_id}",
			static function ( array $existing ) use ( $classes ): array {
				foreach ( $classes as $class ) :
					if ( ! in_array( $class, $existing, true ) ) :
						$existing[] = $class;
					endif;
				endforeach;
				return $existing;
			}
		);
	endforeach;
}
