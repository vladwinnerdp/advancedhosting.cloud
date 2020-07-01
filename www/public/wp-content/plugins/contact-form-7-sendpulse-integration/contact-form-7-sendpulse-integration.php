<?php
/**
 * Plugin Name: Contact Form 7 - SendPulse - Integration
 * Description: Allows you to integrate your forms and SendPulse
 * Version: 1.0.0
 * Author: itgalaxycompany
 * Author URI: https://codecanyon.net/user/itgalaxycompany
 * License: GPLv2
 * Text Domain: cf7-sendpulse-integration
 * Domain Path: /languages/
 */

use Itgalaxy\Cf7\SendPulse\Integration\Admin\Cf7 as Cf7Admin;
use Itgalaxy\Cf7\SendPulse\Integration\Includes\Bootstrap;
use Itgalaxy\Cf7\SendPulse\Integration\Includes\Cf7 as Cf7Includes;
use Itgalaxy\Cf7\SendPulse\Integration\Includes\Cron;

if (!defined('ABSPATH')) {
    exit();
}

/*
 * Require for `is_plugin_active` function.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

define('CF7_SENDPULSE_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CF7_SENDPULSE_INTEGRATION_PLUGIN_VERSION', '1.0.0');
define('CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));

load_theme_textdomain('cf7-sendpulse-integration', CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'languages');

require CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'vendor/autoload.php';

require CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'includes/Bootstrap.php';
require CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'includes/Sendpulse.php';
require CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'includes/Cf7.php';

Bootstrap::getInstance(__FILE__);
Cf7Includes::getInstance();

if (defined('DOING_CRON') && DOING_CRON) {
    include CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'includes/Cron.php';

    Cron::getInstance();
}

if (is_admin()
    && is_plugin_active('contact-form-7/wp-contact-form-7.php')
) {
    add_action('plugins_loaded', function () {
        include CF7_SENDPULSE_INTEGRATION_PLUGIN_DIR . 'admin/Cf7.php';

        Cf7Admin::getInstance();
    });
}
