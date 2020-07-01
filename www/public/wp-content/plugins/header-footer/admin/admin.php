<?php
defined('ABSPATH') || exit;

add_action('admin_init', 'hefo_admin_init');

function hefo_admin_init() {
    global $hefo_options;
    if (isset($_GET['page']) && strpos($_GET['page'], 'header-footer/') === 0) {
        header('X-XSS-Protection: 0');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('header-footer', plugins_url('header-footer') . '/admin/admin.css', array(), time());
        wp_enqueue_style('header-footer-tabs', plugins_url('header-footer') . '/lib/easytabs/tabs.css', array(), time());
        wp_enqueue_script('header-footer-tabs', plugins_url('header-footer') . '/lib/easytabs/jquery.easytabs.min.js', array(), time());
    }

    if (isset($hefo_options['page_add_tags'])) {
        register_taxonomy_for_object_type('post_tag', 'page');
    }

    if (isset($hefo_options['page_add_categories'])) {
        register_taxonomy_for_object_type('category', 'page');
    }
}

add_action('admin_menu', function() {
    add_options_page('Header and Footer', 'Header and Footer', 'manage_options', 'header-footer/admin/options.php');
});
