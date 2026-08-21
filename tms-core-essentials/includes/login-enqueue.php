<?php
/**
 * Includes -> Login enqueue
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'login_enqueue_scripts', function (): void {
	$disable_css = (bool) tcres_option_get( 'assets', 'disable_frontend_css' );
	$disable_js  = (bool) tcres_option_get( 'assets', 'disable_frontend_js' );

	if ( ! $disable_css ) :
		$style = 'assets/css/login-styles.css';
		$path  = TCRES_PLUGIN_PATH . $style;
		if ( file_exists( $path ) && filesize( $path ) > 0 ) :
			wp_enqueue_style(
				'tcres-login-styles',
				TCRES_PLUGIN_URL . $style,
				array(),
				tcres_plugin_get_asset_file_mtime( $style ),
				'all'
			);
		endif;
	endif;

	if ( ! $disable_js ) :
		$script = 'assets/js/login-scripts.js';
		$path   = TCRES_PLUGIN_PATH . $script;
		if ( file_exists( $path ) && filesize( $path ) > 0 ) :
			wp_enqueue_script(
				'tcres-login-scripts',
				TCRES_PLUGIN_URL . $script,
				array(),
				tcres_plugin_get_asset_file_mtime( $script ),
				true
			);
		endif;
	endif;
} );
