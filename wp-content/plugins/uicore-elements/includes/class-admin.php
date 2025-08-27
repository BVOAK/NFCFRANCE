<?php

namespace UiCoreElements;

use UiCoreElements\Utils\Newsletter_Services as Services;

/**
 * Admin Pages Handler
 */
class Admin
{
    /**
     * Constructor function to initialize hooks
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'init_hooks']);

        // TODO: Remove this after, at least, 5 releases or 1 year from 1.3.3
        // Mailchimp to generic api key fields migration
        if (
            get_option('uicore_elements_mailchimp_secret_key')
            && !get_option('uicore_elements_newsletter_service_key')
        ) {
            $value = get_option('uicore_elements_mailchimp_secret_key');
            update_option('uicore_elements_newsletter_service_key', $value);
            delete_option('uicore_elements_mailchimp_secret_key');
        }
    }

    /**
     * Add admin menu page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function admin_menu()
    {
        // Settings page (only required if uicore framework is not active)
        // if (!\class_exists('\UiCore\Helper')) {
        $hook = add_submenu_page(
            'options-general.php',
            'UiCore Elements',
            'UiCore Elements',
            'manage_options',
            'uicore-elements',
            [$this, 'plugin_page']
        );

        // }

        // Connect handle
        // add_submenu_page(
        //     null,
        //     'UiCore Connect',
        //     'UiCore Connect',
        //     'manage_options',
        //     'uicore_connect_free',
        //     [$this, 'connect_page_callback']
        // );
    }

    /**
     * Initialize hooks for settings fields and sections
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function init_hooks()
    {
        // Register settings for reCAPTCHA
        register_setting('uicore_elements_recaptcha', 'uicore_elements_recaptcha_site_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('uicore_elements_recaptcha', 'uicore_elements_recaptcha_secret_key', ['sanitize_callback' => 'sanitize_text_field']);

        // Register settings for Mailchimp
        register_setting('uicore_elements_newsletter_service', 'uicore_elements_newsletter_service_key', ['sanitize_callback' => 'sanitize_text_field']);

        // Add settings sections
        add_settings_section('uicore_elements_recaptcha_section', 'reCAPTCHA Keys', [$this, 'recaptcha_section'], 'uicore_elements_recaptcha');
        add_settings_section('uicore_elements_newsletter_service_section', 'Newsletter Service Key', [$this, 'newsletter_service_section'], 'uicore_elements_newsletter_service');

        // Add settings fields
        add_settings_field('uicore_elements_recaptcha_site_key', 'Site Key', [$this, 'site_key'], 'uicore_elements_recaptcha', 'uicore_elements_recaptcha_section');
        add_settings_field('uicore_elements_recaptcha_secret_key', 'Secret Key', [$this, 'secret_key'], 'uicore_elements_recaptcha', 'uicore_elements_recaptcha_section');
        add_settings_field('uicore_elements_newsletter_service_key', 'API Key', [$this, 'newsletter_service_key'], 'uicore_elements_newsletter_service', 'uicore_elements_newsletter_service_section');
    }

    /**
     * Render plugin page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function plugin_page()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }


        // show error/update messages
        settings_errors('uicoreelements_messages');

        // display plugin page
?>
        <div class="wrap">
            <h1>UiCore Elements Settings</h1>

            <form method="post" action="options.php" style="margin-top:40px">
                <?php
                settings_fields('uicore_elements_recaptcha');
                do_settings_sections('uicore_elements_recaptcha');
                submit_button();
                ?>
            </form>

            <form method="post" action="options.php" style="margin-top:40px">
                <?php
                settings_fields('uicore_elements_newsletter_service');
                do_settings_sections('uicore_elements_newsletter_service');
                submit_button();
                ?>
            </form>

        </div>
<?php
    }

    /**
     * Newsletter Services sections
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     *
     * recaptcha @since 1.0.5
     * mailchimp @since 1.0.7
     * constant contact, brevo, a, c @since 1.3.3
     */

    public function recaptcha_section()
    {
        echo '<p class="description">Go to your Google <a href="https://www.google.com/recaptcha/admin/create" target="_blank">reCAPTCHA</a>, choose between V2 or V3 versions and create your API keys</p>';
    }
    public function newsletter_service_section()
    {
        $services = '<strong>' . implode('</strong>, <strong>', Services::get_services_list('names')) . '</strong>';
        printf(
            '<p class="description">%s %s.</p>',
            esc_html__('Set your newsletter service provider API key. We support the following services:', 'uicore-elements'),
            Helper::esc_string($services) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }

    /**
     * Render Uicore Elements settings fields
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     *
     * site key, secret key @since 1.0.5
     * mailchimp key @since 1.0.7
     * mailchim update for XX @since 1.3.3
     */

    public function site_key()
    {
        $site_key = get_option('uicore_elements_recaptcha_site_key');
        echo '<input type="text" name="uicore_elements_recaptcha_site_key" value="' . esc_attr($site_key) . '" class="regular-text" />';
    }
    public function secret_key()
    {
        $secret_key = get_option('uicore_elements_recaptcha_secret_key');
        echo '<input type="text" name="uicore_elements_recaptcha_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text" />';
    }
    public function newsletter_service_key()
    {
        $secret_key = get_option('uicore_elements_newsletter_service_key');
        echo '<input type="text" name="uicore_elements_newsletter_service_key" value="' . esc_attr($secret_key) . '" class="regular-text" />';
    }
}
