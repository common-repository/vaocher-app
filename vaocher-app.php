<?php

/**
 * The plugin bootstrap file
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://vaocherapp.com
 * @since             1.0.0
 * @package           Vaocher_App
 * @wordpress-plugin
 * Plugin Name:       VaocherApp
 * Plugin URI:        vaocher-app
 * Description:       Sell your own gift cards, gift vouchers and gift certificates from your WordPress website (WooCommerce compatible) easily in just a few minutes
 * Version:           1.1.0
 * Author:            VaocherApp Team
 * Author URI:        https://vaocherapp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.2.0
 * WC tested up to: 9.2.2
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('VAOCHER_APP_VERSION', '1.1.0');
// This is the absolute path to this vaocher-app.php file
define('VAOCHER_APP_PLUGIN_REGISTER_PATH', __FILE__);
// This is the absolute path to the "plugins/vaocher-app/" folder
define('VAOCHER_APP_BASE_PATH', plugin_dir_path(__FILE__));
// Something like "vaocher-app/vaocher-app.php"
define('VAOCHER_APP_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('VAOCHER_APP_ENV', $_SERVER['HTTP_HOST'] === 'vaocherapp-local.local' ? 'development' : 'production');

require_once VAOCHER_APP_BASE_PATH.'includes/hook-functions.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherApp.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppLoader.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppView.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppData.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppApi.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppResponse.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppUrl.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppAssets.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppHelpers.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppWoocommerce.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppShortcode.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppHandlers.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppDiagnostics.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppStorage.php';
require_once VAOCHER_APP_BASE_PATH.'includes/VaocherAppLogger.php';

require_once VAOCHER_APP_BASE_PATH.'models/VaocherAppModelAbstract.php';
require_once VAOCHER_APP_BASE_PATH.'models/VaocherAppAccount.php';
require_once VAOCHER_APP_BASE_PATH.'models/VaocherAppVoucher.php';
require_once VAOCHER_APP_BASE_PATH.'models/VaocherAppRedeemed.php';

VaocherApp::getInstance()->boot();
