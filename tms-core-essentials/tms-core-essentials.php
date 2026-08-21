<?php
/**
 * Plugin Name:       TMS Core Essentials
 * Plugin URI:        https://github.com/entumas/tms-core-essentials
 * Description:       Core utilities and shared essentials for TMS WordPress projects.
 * Version:           1.0.0
 * Author:            Tumàs Muntané
 * Author URI:        https://tumasmuntane.com/
 * Text Domain:       tms-core-essentials
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Constants
 */
define( 'TCRES_PLUGIN_FILE', __FILE__ );
define( 'TCRES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'TCRES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TCRES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'TCRES_OPTION_NAME', 'tcres_settings' );
define( 'TCRES_SETTINGS_PAGE_SLUG', 'tcres-settings' );
define( 'TCRES_SETTINGS_TAB_QUERY_VAR', 'tcres_tab' );
define( 'TCRES_SETTINGS_GROUP', 'tcres_settings_group' );
define( 'TCRES_SETTINGS_SUBMIT_TAB_FIELD', 'tcres_settings_submit_tab' );

if ( ! function_exists( 'get_plugin_data' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
$tcres_plugin_header = get_plugin_data( TCRES_PLUGIN_FILE, false, false );
define( 'TCRES_PLUGIN_VERSION', $tcres_plugin_header['Version'] ?? '1.0.0' );


/**
 * Requirements, then rest of includes
 */

// Utilities: version guard + shared helpers (must load before other includes).
require_once TCRES_PLUGIN_PATH . 'includes/utilities.php';
if ( ! tcres_plugin_requirements_are_met() ) :
	tcres_plugin_requirements_register_admin_notice();
	return;
endif;

// Storage
require_once TCRES_PLUGIN_PATH . 'includes/storage.php';

// i18n
require_once TCRES_PLUGIN_PATH . 'includes/i18n.php';

// Multilingual (WPML and Polylang)
require_once TCRES_PLUGIN_PATH . 'includes/multilingual.php';

// Admin/editor component strings
require_once TCRES_PLUGIN_PATH . 'includes/admin-components-i18n.php';

// Enqueues
require_once TCRES_PLUGIN_PATH . 'includes/login-enqueue.php';
require_once TCRES_PLUGIN_PATH . 'includes/admin-enqueue.php';
require_once TCRES_PLUGIN_PATH . 'includes/enqueue.php';

// Bootstrap (module loader)
require_once TCRES_PLUGIN_PATH . 'includes/bootstrap.php';
