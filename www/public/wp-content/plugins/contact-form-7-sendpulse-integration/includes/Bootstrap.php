<?php
namespace Itgalaxy\Cf7\SendPulse\Integration\Includes;

class Bootstrap
{
    const OPTIONS_KEY = 'cf7-sendpulse-integration-settings';
    const PURCHASE_CODE_OPTIONS_KEY = 'cf7-sendpulse-purchase-code';

    const META_KEY = '_cf7-sendpulse-integration';
    const MAIL_LIST_DATA_KEY = '_cf7-sendpulse-mail-list-data';
    const MAIL_LIST_FIELDS_DATA_KEY = '_cf7-sendpulse-mail-list-fields-data';

    const UTM_COOKIES = 'cf7-sendpulse-utm-cookie';

    const CRON_TASK = 'cf7-sendpulse-cron-task';

    public static $plugin = '';

    private static $instance = false;

    protected function __construct($file)
    {
        self::$plugin = $file;

        register_activation_hook(
            self::$plugin,
            ['Itgalaxy\Cf7\SendPulse\Integration\Includes\Bootstrap', 'pluginActivation']
        );
        register_deactivation_hook(
            self::$plugin,
            ['Itgalaxy\Cf7\SendPulse\Integration\Includes\Bootstrap', 'pluginDeactivation']
        );
        register_uninstall_hook(
            self::$plugin,
            ['Itgalaxy\Cf7\SendPulse\Integration\Includes\Bootstrap', 'pluginUninstall']
        );

        add_action('init', [$this, 'utmCookies']);
    }

    public static function getInstance($file)
    {
        if (!self::$instance) {
            self::$instance = new self($file);
        }

        return self::$instance;
    }

    public static function pluginActivation()
    {
        if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
            wp_die(
                esc_html__(
                    'To run the plug-in, you must first install and activate the Contact Form 7 plugin.',
                    'cf7-sendpulse-integration'
                ),
                esc_html__(
                    'Error while activating the Contact Form 7 - SendPulse - Integration',
                    'cf7-sendpulse-integration'
                ),
                [
                    'back_link' => true
                ]
            );
            // Escape ok
        }

        $roles = new \WP_Roles();

        foreach (self::capabilities() as $capGroup) {
            foreach ($capGroup as $cap) {
                $roles->add_cap('administrator', $cap);

                if (is_multisite()) {
                    $roles->add_cap('super_admin', $cap);
                }
            }
        }
    }

    public static function pluginDeactivation()
    {
        wp_clear_scheduled_hook(self::CRON_TASK);
    }

    public static function pluginUninstall()
    {
        // Nothing
    }

    public static function capabilities()
    {
        $capabilities = [];
        $capabilities['core'] = ['manage_' . self::OPTIONS_KEY];
        flush_rewrite_rules(true);

        return $capabilities;
    }

    public function utmCookies()
    {
        if (isset($_GET['utm_source'])) {
            setcookie(
                self::UTM_COOKIES,
                wp_json_encode([
                    'utm_source' => isset($_GET['utm_source']) ? wp_unslash($_GET['utm_source']) : '',
                    'utm_medium' => isset($_GET['utm_medium']) ? wp_unslash($_GET['utm_medium']) : '',
                    'utm_campaign' => isset($_GET['utm_campaign']) ? wp_unslash($_GET['utm_campaign']) : '',
                    'utm_term' => isset($_GET['utm_term']) ? wp_unslash($_GET['utm_term']) : '',
                    'utm_content' => isset($_GET['utm_content']) ? wp_unslash($_GET['utm_content']) : ''
                ]),
                time() + 86400,
                '/'
            );
        }
    }

    private function __clone()
    {
        // Nothing
    }
}
