<?php
namespace Itgalaxy\Cf7\SendPulse\Integration\Includes;

class Cron
{
    private static $instance = false;

    protected function __construct()
    {
        add_action('init', [$this, 'createCron']);
        add_action(Bootstrap::CRON_TASK, [$this, 'cronAction']);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function createCron()
    {
        if (!wp_next_scheduled(Bootstrap::CRON_TASK)) {
            wp_schedule_event(time(), 'hourly', Bootstrap::CRON_TASK);
        }
    }

    public function cronAction()
    {
        $last = get_option('cf7-sendpulse-integration-last-cron');

        if (!empty($last) && date_i18n('Y-m-d') == $last) {
            return;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['api_id']) || empty($settings['api_secret'])) {
            update_option('cf7-sendpulse-integration-last-cron', date_i18n('Y-m-d'));

            SendPulse::updateInformation();
        }
    }

    private function __clone()
    {
        // Nothing
    }
}
