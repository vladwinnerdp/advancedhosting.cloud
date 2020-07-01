<?php
namespace Itgalaxy\Cf7\SendPulse\Integration\Admin;

use Itgalaxy\Cf7\SendPulse\Integration\Includes\Sendpulse;
use Itgalaxy\Cf7\SendPulse\Integration\Includes\Bootstrap;

class Cf7 extends \WPCF7_Service
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
        add_action('wpcf7_init', [$this, 'registerService']);

        if ($this->is_active()) {
            add_filter('wpcf7_editor_panels', [$this, 'settingsPanels']);
            add_action('save_post_' . \WPCF7_ContactForm::post_type, [$this, 'saveSettings']);

            if (isset($_GET['page']) && $_GET['page'] === 'wpcf7' && !empty($_GET['post'])) {
                add_action('admin_enqueue_scripts', function () {
                    wp_enqueue_style(
                        'cf7-sendpulse-admin-css',
                        CF7_SENDPULSE_INTEGRATION_PLUGIN_URL . 'admin/css/admin.css',
                        false,
                        CF7_SENDPULSE_INTEGRATION_PLUGIN_VERSION
                    );

                    wp_enqueue_script(
                        'cf7-sendpulse-admin-js',
                        CF7_SENDPULSE_INTEGRATION_PLUGIN_URL . 'admin/js/admin.js',
                        ['jquery'],
                        CF7_SENDPULSE_INTEGRATION_PLUGIN_VERSION,
                        true
                    );
                });
            }

            add_action('wp_ajax_cf7SendPulseGetFields', [$this, 'cf7SendPulseGetFields']);
        }
    }

    public function registerService()
    {
        $integration = \WPCF7_Integration::get_instance();
        $categories = ['crm' => $this->get_title()];

        foreach ($categories as $name => $category) {
            $integration->add_category($name, $category);
        }

        $services = ['cf7-sendpulse-integration' => self::getInstance()];

        foreach ($services as $name => $service) {
            $integration->add_service($name, $service);
        }
    }

    // @codingStandardsIgnoreStart
    public function is_active()
    {
        // @codingStandardsIgnoreEnd
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if api id or api secret is not specified
        if (empty($settings['api_id']) || empty($settings['api_secret'])) {
            return false;
        }

        return true;
    }

    // @codingStandardsIgnoreStart
    public function get_title()
    {
        // @codingStandardsIgnoreEnd
        return esc_html__('Integration with SendPulse', 'cf7-sendpulse-integration');
    }

    // @codingStandardsIgnoreStart
    public function get_categories()
    {
        // @codingStandardsIgnoreEnd
        return ['crm'];
    }

    public function icon()
    {
        // Nothing
    }

    public function link()
    {
        echo '<a href="https://codecanyon.net/user/itgalaxycompany">itgalaxycompany</a>';
    }

    public function load($action = '')
    {
        if ('setup' == $action) {
            if (isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD']) {
                if (isset($_POST['purchase-code'])) {
                    check_admin_referer('wpcf7-sendpulse-integration-setup-license');
                    $code = trim(wp_unslash($_POST['purchase-code']));

                    $response = \wp_remote_post(
                        'https://wordpress-plugins.xyz/envato/license.php',
                        [
                            'body' => [
                                'purchaseCode' => $code,
                                'itemID' => '24840931',
                                'action' => isset($_POST['verify']) ? 'activate' : 'deactivate',
                                'domain' => site_url()
                            ],
                            'timeout' => 20
                        ]
                    );

                    if (is_wp_error($response)) {
                        $messageContent = '(Code - '
                            . $response->get_error_code()
                            . ') '
                            . $response->get_error_message();

                        $message = 'failedCheck';
                    } else {
                        $response = json_decode(wp_remote_retrieve_body($response));

                        if ($response->status == 'successCheck') {
                            if (isset($_POST['verify'])) {
                                update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, $code);
                            } else {
                                update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
                            }
                        } elseif (!isset($_POST['verify']) && $response->status == 'alreadyInactive') {
                            update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
                        }

                        $messageContent = $response->message;
                        $message = $response->status;
                    }

                    wp_safe_redirect(
                        $this->menuPageUrl(
                            [
                                'action' => 'setup',
                                'message' => $message,
                                'messageContent' => rawurlencode($messageContent)
                            ]
                        )
                    );
                    exit();
                }

                check_admin_referer('wpcf7-sendpulse-integration-setup');
                $apiID = isset($_POST['api_id']) ? trim(wp_unslash($_POST['api_id'])) : '';
                $apiSecret = isset($_POST['api_secret']) ? trim(wp_unslash($_POST['api_secret'])) : '';

                if (empty($apiID) || empty($apiSecret)) {
                    wp_safe_redirect($this->menuPageUrl(['message' => 'invalid', 'action' => 'setup']));
                    exit();
                }

                update_option(
                    Bootstrap::OPTIONS_KEY,
                    [
                        'api_id' => $apiID,
                        'api_secret' => $apiSecret,
                    ]
                );

                Sendpulse::checkConnection();
                Sendpulse::updateInformation();

                wp_safe_redirect($this->menuPageUrl(['action' => 'setup', 'message' => 'success']));

                exit();
            }
        }
    }

    // @codingStandardsIgnoreStart
    public function admin_notice($message = '')
    {
        // @codingStandardsIgnoreEnd
        if ($message) {
            if ('invalid' === $message) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
                    esc_html__('ERROR', 'cf7-sendpulse-integration'),
                    esc_html__('To integrate with SendPulse, your must fill API ID and API Secret.', 'cf7-sendpulse-integration')
                );
            } elseif ('success' === $message) {
                echo sprintf(
                    '<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
                    esc_html__('Settings successfully updated.', 'cf7-sendpulse-integration')
                );
            } elseif ($message == 'successCheck') {
                echo sprintf(
                    '<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
                    esc_html(isset($_GET['messageContent']) ? $_GET['messageContent'] : '')
                );
            } elseif (isset($_GET['messageContent'])) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p>%s</p></div>',
                    esc_html(isset($_GET['messageContent']) ? $_GET['messageContent'] : '')
                );
            }
        }
    }

    public function display($action = '')
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        ?>
        <p>
            <?php
            esc_html_e(
                'Formation of contacts in SendPulse from the hits that users send on your site, '
                . 'using the Contact Form 7 plugin.',
                'cf7-sendpulse-integration'
            );
            ?>
        </p>
        <?php
        if ('setup' == $action) {
            ?>
            <form method="post" action="<?php echo esc_url($this->menuPageUrl('action=setup')); ?>">
                <?php wp_nonce_field('wpcf7-sendpulse-integration-setup'); ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_id">
                                    <?php esc_html_e('API ID', 'cf7-sendpulse-integration'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                    aria-required="true"
                                    value="<?php
                                    echo isset($settings['api_id'])
                                        ? esc_attr($settings['api_id'])
                                        : '';
                                    ?>"
                                    id="api_id"
                                    placeholder="<?php esc_html_e('Your API ID', 'cf7-sendpulse-integration'); ?>"
                                    name="api_id"
                                    class="large-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_secret">
                                    <?php esc_html_e('API Secret', 'cf7-sendpulse-integration'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                    aria-required="true"
                                    value="<?php
                                    echo isset($settings['api_secret'])
                                        ? esc_attr($settings['api_secret'])
                                        : '';
                                    ?>"
                                    id="api_secret"
                                    placeholder="<?php esc_html_e('Your API Secret', 'cf7-sendpulse-integration'); ?>"
                                    name="api_secret"
                                    class="large-text">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <?php
                    echo sprintf(
                        '%1$s <a href="%2$s" target="_blank">%3$s</a>. %4$s.',
                        esc_html__('Plugin documentation: ', 'cf7-sendpulse-integration'),
                        esc_url(CF7_SENDPULSE_INTEGRATION_PLUGIN_URL . 'documentation/index.html#step-1'),
                        esc_html__('open', 'cf7-sendpulse-integration'),
                        esc_html__('Or open the folder `documentation` in the plugin and open index.html', 'cf7-sendpulse-integration')
                    );
                    ?>
                </p>
                <p class="submit">
                    <input type="submit"
                        class="button button-primary"
                        value="<?php esc_attr_e('Save settings', 'cf7-sendpulse-integration'); ?>"
                        name="submit">
                </p>
            </form>
            <hr>
            <?php $code = get_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY); ?>
            <h1>
                <?php esc_html_e('License verification', 'cf7-sendpulse-integration'); ?>
                <?php if ($code) { ?>
                    - <small style="color: green;">verified</small>
                <?php } else { ?>
                    - <small style="color: red;">please verify your purchase code</small>
                <?php } ?>
            </h1>
            <form method="post" action="<?php echo esc_url($this->menuPageUrl('action=setup')); ?>">
                <?php wp_nonce_field('wpcf7-sendpulse-integration-setup-license'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="purchase-code">
                                <?php esc_html_e('Purchase code', 'cf7-sendpulse-integration'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text"
                                aria-required="true"
                                required
                                value="<?php
                                echo !empty($code)
                                    ? esc_attr($code)
                                    : '';
                                ?>"
                                id="purchase-code"
                                name="purchase-code"
                                class="large-text">
                            <small>
                                <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
                                    target="_blank">
                                    <?php esc_html_e('Where Is My Purchase Code?', 'cf7-sendpulse-integration'); ?>
                                </a>
                            </small>
                        </td>
                    </tr>
                </table>
                <p>
                    <input type="submit"
                        class="button button-primary"
                        value="<?php esc_attr_e('Verify', 'cf7-sendpulse-integration'); ?>"
                        name="verify">
                    <?php if ($code) { ?>
                        <input type="submit"
                            class="button button-primary"
                            value="<?php esc_attr_e('Unverify', 'cf7-sendpulse-integration'); ?>"
                            name="unverify">
                    <?php } ?>
                </p>
            </form>
            <?php
        } else {
            if ($this->is_active()) {
                ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Your API ID', 'cf7-sendpulse-integration'); ?></th>
                            <td class="code"><?php echo esc_html($settings['api_id']); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('API Secret', 'cf7-sendpulse-integration'); ?></th>
                            <td class="code"><?php echo esc_html($settings['api_secret']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <a href="<?php echo esc_url($this->menuPageUrl('action=setup')); ?>"
                        class="button">
                        <?php esc_html_e('Change settings', 'cf7-sendpulse-integration'); ?>
                    </a>
                </p>
                <?php
            } else {
                ?>
                <p>
                    <?php
                    esc_html_e(
                        'To work with the plugin, you must configure integration with SendPulse.',
                        'cf7-sendpulse-integration'
                    );
                    ?>
                </p>
                <p>
                    <a href="<?php echo esc_url($this->menuPageUrl('action=setup')); ?>"
                        class="button">
                        <?php esc_html_e('Go to setup', 'cf7-sendpulse-integration'); ?>
                    </a>
                </p>
                <p class="description">
                    <?php
                    esc_html_e(
                        'The fields sent to SendPulse are configured on the form editing page, on the "SendPulse" tab.',
                        'cf7-sendpulse-integration'
                    );
                    ?>
                </p>
                <?php
            }
        }
    }

    public function settingsPanels($panels)
    {
        $panels['cf7-sendpulse-panel'] = [
            'title' => esc_html__('SendPulse', 'cf7-sendpulse-integration'),
            'callback' => [$this, 'panel']
        ];

        return $panels;
    }

    public function panel(\WPCF7_ContactForm $post)
    {
        $meta = get_post_meta($post->id(), Bootstrap::META_KEY, true);
        $bookList = get_option(Bootstrap::MAIL_LIST_DATA_KEY);

        $enabled = isset($meta['ENABLED']) ? $meta['ENABLED'] : false;
        ?>
        <input type="hidden" name="cf7Sendpulse[ENABLED]" value="0">
        <input
            type="checkbox"
            name="cf7Sendpulse[ENABLED]" value="1"
            <?php checked($enabled, true); ?>
            title="<?php
            esc_attr_e('Enable send', 'cf7-sendpulse-integration');
            ?>">
        <strong>
            <?php
            esc_html_e(
                'Enable send',
                'cf7-sendpulse-integration'
            );
            ?>
        </strong>
        <br><br>
        <?php
        echo esc_html(__(
            'In the following fields, you can use these mail-tags:',
            'contact-form-7'
        ));
        ?>
        <br>
        <?php
        $post->suggest_mail_tags();
        ?>
        <br><br>
        <?php esc_html_e('Utm-fields', 'cf7-sendpulse-integration'); ?>:<br>
        <span class="mailtag code">[utm_source]</span>
        <span class="mailtag code">[utm_medium]</span>
        <span class="mailtag code">[utm_campaign]</span>
        <span class="mailtag code">[utm_term]</span>
        <span class="mailtag code">[utm_content]</span>
        <br><br>
        <?php esc_html_e('Roistat-fields', 'cf7-sendpulse-integration'); ?>:<br>
        <span class="mailtag code">[roistat_visit]</span>
        <br><br>
        <?php esc_html_e('GA fields', 'cf7-sendpulse-integration'); ?>:<br>
        <span class="mailtag code">[gaClientID]</span>
        <hr>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="email">
                        <?php esc_html_e('Email', 'cf7-sendpulse-integration'); ?>
                        <span class="cf7-sendpulse-red-mark"> * </span>
                    </label>
                </th>
                <td>
                    <input type="text"
                        aria-required="true"
                        value="<?php
                        echo isset($meta['email'])
                            ? esc_attr($meta['email'])
                            : '';
                        ?>"
                        id="email"
                        placeholder="Email"
                        name="cf7Sendpulse[email]"
                        class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="phone">
                        <?php esc_html_e('Phone', 'cf7-sendpulse-integration'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                        aria-required="false"
                        value="<?php
                        echo isset($meta['phone'])
                            ? esc_attr($meta['phone'])
                            : '';
                        ?>"
                        id="phone"
                        placeholder="Phone"
                        name="cf7Sendpulse[phone]"
                        class="large-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="confirmation">
                        <?php esc_html_e('Double Opt-In', 'cf7-sendpulse-integration'); ?>
                    </label>
                </th>
                <td>
                    <input type="hidden" value="0" name="cf7Sendpulse[confirmation]">
                    <input type="checkbox"
                        value="1"
                        <?php echo isset($meta['confirmation']) && $meta['confirmation'] == '1' ? 'checked' : ''; ?>
                        id="confirmation"
                        name="cf7Sendpulse[confirmation]">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="confirmation_email">
                        <?php esc_html_e('Sender Double Opt-In email', 'cf7-sendpulse-integration'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                        aria-required="false"
                        value="<?php
                        echo isset($meta['confirmation_email'])
                            ? esc_attr($meta['confirmation_email'])
                            : '';
                        ?>"
                        id="confirmation_email"
                        placeholder="Sender Double Opt-In email"
                        name="cf7Sendpulse[confirmation_email]"
                        class="large-text">
                    <br>
                    <small>
                        <?php
                        esc_html_e(
                            'Sender address from which confirmation email will be sent. Required for Double Opt-In. '
                            . 'Be careful, not all senders are allowed, please read DMARC (otherwise the contact will '
                            . 'not be added) - https://sendpulse.com/knowledge-base/email-service/general/dmarc-policy',
                            'cf7-sendpulse-integration'
                        )
                        ?>
                    </small>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mailing_list">
                        <?php esc_html_e('Mailing list', 'cf7-sendpulse-integration'); ?>
                        <span class="cf7-sendpulse-red-mark"> * </span>
                    </label>
                </th>
                <td>
                    <select name="cf7Sendpulse[mailing_list]"
                        id="mailing_list"
                        data-ui-component="cf7-sendpulse-mailing-list">
                        <?php
                        foreach ($bookList as $book) {
                            echo '<option value="'
                                . esc_attr($book->id)
                                . '"'
                                . (isset($meta['mailing_list']) && (int) $meta['mailing_list'] === (int) $book->id
                                    ? ' selected'
                                    : '')
                                . '>'
                                . esc_html($book->name)
                                . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <strong>
            <?php esc_html_e('Additional variables', 'cf7-sendpulse-integration'); ?>:
        </strong>
        <br>
        <div data-ui-component="cf7-sendpulse-additional-variables" data-form-id="<?php echo (int) $post->id(); ?>"></div>
        <div class="cf7-sendpulse-lds-dual-ring" data-ui-component="cf7-sendpulse-additional-variables-loader"></div>
        <?php
    }

    public function saveSettings($postID)
    {
        if (isset($_POST['cf7Sendpulse'])) {
            update_post_meta($postID, Bootstrap::META_KEY, wp_unslash($_POST['cf7Sendpulse']));
        }
    }

    public function cf7SendPulseGetFields()
    {
        if (empty($_POST['bookID']) || empty($_POST['formID'])) {
            esc_html_e('There are no additional variables for the selected list.', 'cf7-sendpulse-integration');

            exit();
        }

        $variables = get_option(Bootstrap::MAIL_LIST_FIELDS_DATA_KEY);
        $meta = get_post_meta($_POST['formID'], Bootstrap::META_KEY, true);
        $meta = isset($meta['variables']) ? $meta['variables'] : [];

        if (!empty($variables[$_POST['bookID']])) {
            ?>
            <table class="form-table">
                <tbody>
                    <?php
                    foreach ($variables[$_POST['bookID']] as $field) {
                        ?>
                        <tr>
                            <th scope="row">
                                <label for="api_id">
                                    <?php echo esc_html($field->name); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text"
                                    aria-required="false"
                                    value="<?php
                                    echo isset($meta[$field->name])
                                        ? esc_attr($meta[$field->name])
                                        : '';
                                    ?>"
                                    id="additional-variable-<?php echo esc_attr($field->name); ?>"
                                    placeholder="<?php echo esc_attr($field->name); ?>"
                                    name="cf7Sendpulse[variables][<?php echo esc_attr($field->name); ?>]"
                                    class="large-text">
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        } else {
            esc_html_e('There are no additional variables for the selected list.', 'cf7-sendpulse-integration');
        }

        exit();
    }

    protected function __clone()
    {
        // Nothing
    }

    private function menuPageUrl($args = '')
    {
        $args = wp_parse_args($args, []);
        $url = menu_page_url('wpcf7-integration', false);
        $url = add_query_arg(['service' => 'cf7-sendpulse-integration'], $url);

        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }

        return $url;
    }
}
