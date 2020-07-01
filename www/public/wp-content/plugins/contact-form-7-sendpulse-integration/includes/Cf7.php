<?php
namespace Itgalaxy\Cf7\SendPulse\Integration\Includes;

class Cf7
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        if ($this->isActive()) {
            add_action('wpcf7_mail_sent', [$this, 'onFormSubmit'], 10, 1);
        }
    }

    public function onFormSubmit(\WPCF7_ContactForm $contactForm)
    {
        if (!class_exists('\\WPCF7_Submission')) {
            return;
        }

        $submission = \WPCF7_Submission::get_instance();

        if (!$submission) {
            return;
        }

        $postedData = $submission->get_posted_data();

        if (!$postedData) {
            return;
        }

        $meta = get_post_meta($contactForm->id(), Bootstrap::META_KEY, true);

        // If empty form setting or not enabled send lead
        if (empty($meta) || !$meta['ENABLED']) {
            return;
        }

        if (empty($meta['email'])
            || empty($meta['mailing_list'])
        ) {
            return;
        }

        $utmFields = $this->parseUtmCookie();

        if (!isset($postedData['utm_source'])) {
            $postedData['utm_source'] = isset($utmFields['utm_source'])
                ? rawurldecode(wp_unslash($utmFields['utm_source']))
                : '';
        }

        if (!isset($postedData['utm_medium'])) {
            $postedData['utm_medium'] = isset($utmFields['utm_medium'])
                ? rawurldecode(wp_unslash($utmFields['utm_medium']))
                : '';
        }

        if (!isset($postedData['utm_campaign'])) {
            $postedData['utm_campaign'] = isset($utmFields['utm_campaign'])
                ? rawurldecode(wp_unslash($utmFields['utm_campaign']))
                : '';
        }

        if (!isset($postedData['utm_term'])) {
            $postedData['utm_term'] = isset($utmFields['utm_term'])
                ? rawurldecode(wp_unslash($utmFields['utm_term']))
                : '';
        }

        if (!isset($postedData['utm_content'])) {
            $postedData['utm_content'] = isset($utmFields['utm_content'])
                ? rawurldecode(wp_unslash($utmFields['utm_content']))
                : '';
        }

        // Set roistat client id
        $postedData['roistat_visit'] = isset($_COOKIE['roistat_visit'])
            ? $_COOKIE['roistat_visit']
            : '';

        // Set ga client id
        $postedData['gaClientID'] = '';

        if (!empty($_COOKIE['_ga'])) {
            $clientId = explode('.', wp_unslash($_COOKIE['_ga']));
            $postedData['gaClientID'] = $clientId[2] . '.' . $clientId[3];
        }

        $keys = array_map(function ($key) {
            return '[' . $key . ']';
        }, array_keys($postedData));
        $values = array_values($postedData);
        array_walk($values, function (&$value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
        });

        $data = [];
        $data['keys'] = $keys;
        $data['values'] = $values;

        $bookID = $meta['mailing_list'];
        $phone = !empty($meta['phone'])
            ? $this->prepareSendField($meta['phone'], $data)
            : '';
        $variables = [];

        if (!empty($meta['variables'])) {
            foreach ($meta['variables'] as $name => $variable) {
                $variable = $this->prepareSendField($variable, $data);

                if (!empty($variable)) {
                    $variables[$name] = $variable;
                }
            }
        }

        $variables['email'] = $this->prepareSendField($meta['email'], $data);

        if ($phone) {
            $variables['Phone'] = $phone;
        }

        $additionalParams = [];

        // double opt-in
        if (isset($meta['confirmation'])
            && $meta['confirmation'] === '1'
            && !empty($meta['confirmation_email'])
        ) {
            $additionalParams['confirmation'] = 'force';
            $additionalParams['sender_email'] = $meta['confirmation_email'];
        }

        Sendpulse::send($bookID, $variables, $additionalParams);
    }

    public function prepareSendField($value, $postedValues)
    {
        $value = trim(
            str_replace($postedValues['keys'], $postedValues['values'], $value)
        );

        if (function_exists('wpcf7_mail_replace_tags')) {
            $value = \wpcf7_mail_replace_tags($value);
        }

        return $value;
    }

    public function parseUtmCookie()
    {
        if (!empty($_COOKIE[Bootstrap::UTM_COOKIES])) {
            return json_decode(wp_unslash($_COOKIE[Bootstrap::UTM_COOKIES]), true);
        }

        return [];
    }

    public function isActive()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if api id or api secret is not specified
        if (empty($settings['api_id']) || empty($settings['api_secret'])) {
            return false;
        }

        return true;
    }

    protected function __clone()
    {
        // Nothing
    }
}
