<?php
/**
 * Includes -> i18n
 * Internationalization loader
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'init', function() {
	$domain = 'tms-core-essentials';
	$locale = function_exists( 'get_user_locale' )
		? get_user_locale()
		: get_locale();
	$mofile = TCRES_PLUGIN_PATH . 'languages/' . $domain . '-' . $locale . '.mo';

	if ( file_exists( $mofile ) ) load_textdomain( $domain, $mofile );
}, 5 );
