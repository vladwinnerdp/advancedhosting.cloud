<?php
namespace Itgalaxy\Cf7\SendPulse\Integration\Includes;

use Sendpulse\RestApi\ApiClient as Client;

class Sendpulse
{
    public static function send($bookID, $fields, $additionalParams = [])
    {
        $preparedData = [
            'email' => '',
            'variables' => []
        ];

        foreach ($fields as $key => $value) {
            if ($key === 'email') {
                $preparedData['email'] = $value;
            } else {
                $preparedData['variables'][$key] = $value;
            }
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        try {
            $sendPulseClient = new Client($settings['api_id'], $settings['api_secret']);
        } catch (\Exception $error) {
            return false;
        }

        $sendPulseClient->addEmails(
            $bookID,
            [
                $preparedData
            ],
            $additionalParams
        );
    }

    public static function checkConnection()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        try {
            $sendPulseClient = new Client($settings['api_id'], $settings['api_secret']);
        } catch (\Exception $error) {
            // Clean failed information
            update_option(
                Bootstrap::OPTIONS_KEY,
                []
            );

            wp_die(
                sprintf(
                    esc_html__(
                        'Response SendPulse: Error code (%s): %s. Check the settings.',
                        'cf7-sendpulse-integration'
                    ),
                    $error->getCode(),
                    $error->getMessage()
                ),
                esc_html__(
                    'An error occurred while verifying the connection to the SendPulse.',
                    'cf7-sendpulse-integration'
                ),
                [
                    'back_link' => true
                ]
            );
            // Escape ok
        }
    }

    public static function updateInformation()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        try {
            $sendPulseClient = new Client($settings['api_id'], $settings['api_secret']);
        } catch (\Exception $error) {
            return false;
        }

        $bookListVariables = [];
        $bookList = $sendPulseClient->listAddressBooks();

        update_option(Bootstrap::MAIL_LIST_DATA_KEY, (array) $bookList);

        foreach ($bookList as $book) {
            $bookListVariables[$book->id] = (array) $sendPulseClient->getBookVariables($book->id);
        }

        update_option(Bootstrap::MAIL_LIST_FIELDS_DATA_KEY, $bookListVariables);
    }
}
