<?php

namespace UiCoreElements\Utils;

use UiCoreELements\Utils\Newsletter_Services as Services;

class Email_Exception extends \Exception {}
class Redirect_Exception extends \Exception {}
class Submit_Exception extends \Exception {}
class Newsletter_Service_Exception extends \Exception {}

defined('ABSPATH') || exit();

/**
 * Handles the form submissions and responses
 */

class Contact_Form_Service
{

    protected $form_data,
        $settings,
        $files;

    public function __construct($form_data, $settings, $files)
    {
        $this->form_data = $form_data;
        $this->settings = $settings;
        $this->files = $files;
    }

    public function handle()
    {

        $data = [];
        $responses = [];

        // Checks for reCAPTCHA validation
        if (isset($this->form_data['grecaptcha_token']) && !empty($this->form_data['grecaptcha_token'])) {

            $recaptcha = $this->validate_recaptcha($this->form_data['grecaptcha_token'], $this->form_data['grecaptcha_version']);

            if (!$recaptcha['success']) {
                return [
                    'status' => 'error',
                    'data' => [
                        'message' => esc_html__('reCAPTCHA validation failed.', 'uicore-elements'),
                    ]
                ];
            }
        }

        // Check for honeypot spam
        if (!$this->validate_spam()) {
            return [
                'status' => 'success',
                'data' => [
                    'message' => $this->get_response_message('success') // Fakes a successfull submission
                ]
            ];
        }

        // Run all registered submit actions
        if (isset($this->settings['submit_actions']) && !empty($this->settings['submit_actions'])) {
            foreach ($this->settings['submit_actions'] as $action) {
                try {
                    switch ($action) {
                        case 'email':
                            $data = $this->send_mail($action);
                            $responses['email'] = $data['response'];
                            break;

                        case 'email_2':
                            $data = $this->send_mail($action, $data);
                            $responses['email'] = $data['response'];
                            break;

                        case 'redirect':
                            $responses['redirect'] = $this->redirect();
                            break;

                        case 'popup':
                            $responses['popup'] = $this->popup();
                            break;

                        case 'mailchimp':
                            $responses['mailchimp'] = $this->newsletter_service('mailchimp');
                            break;

                        case 'brevo':
                            $responses['brevo'] = $this->newsletter_service('brevo');
                            break;

                        case 'kit':
                            $responses['kit'] = $this->newsletter_service('kit');
                            break;

                        case 'moosend':
                            $responses['moosend'] = $this->newsletter_service('moosend');
                            break;

                        case 'getresponse':
                            $responses['getresponse'] = $this->newsletter_service('getresponse');
                            break;

                        case 'mailerlite':
                            $responses['mailerlite'] = $this->newsletter_service('mailerlite');
                            break;

                        default:
                            throw new Submit_Exception(esc_html__('Unknown submit action: ', 'uicore-elements') . $action . esc_html__('. Check your settings.', 'uicore-elements'));
                    }
                } catch (Email_Exception $e) {
                    $responses['email'] = [
                        'status' => false,
                        'message' => $e->getMessage()
                    ];
                } catch (Redirect_Exception $e) {
                    $responses['redirect'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                } catch (Newsletter_Service_Exception $e) {
                    $responses['newsletter_service'] = [
                        'status' => 'error',
                        'service' => $action,
                        'message' => $e->getMessage()
                    ];
                } catch (Submit_Exception $e) {
                    $responses['submit'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
                // We're avoiding throwing exception for mailchimp because would require a specific validation function, and is to much for now
            }

            // There's no need to continue without a submit action enabled
        } else {
            return [
                'status' => 'error',
                'data' => [
                    'message' => esc_html__('No submit action enabled.', 'uicore-elements')
                ]
            ];
        }

        // Consider `current_user_can( 'manage_options' )` as filter to return more specific messages on frontend (not tested)

        // Since attachments may be used up to two times (both emails), they need to be deleted only after processing submits
        if (isset($data['attachments']) && !empty($data['attachments']['files'])) {
            register_shutdown_function('unlink', $data['attachments']['files']);
        }

        $output = $this->build_frontend_responses($responses);

        return [
            'status' => $output['status'],
            'data' => $output['data'],
        ];
    }

    /**
     * Mail submition
     */
    protected function send_mail(string $action, array $data = [])
    {

        $attachments = isset($data['attachments']) ? $data['attachments'] : []; // Check if there's attachments from previous mail submit action

        $mail_data = $this->compose_mail_data($action, $attachments); // build mail data

        // Check if there's any attachment error before sending mail
        if (!empty($mail_data['attachments']['errors'])) {
            // throwing exceptions here will block proper data flow. Is best directly returning the error on email action
            return [
                'response' => [
                    'status' => false,
                    'message' => $mail_data['attachments']['errors']
                ],
            ];
        }

        $email = wp_mail(
            $mail_data['email']['to'],
            $mail_data['email']['subject'],
            $mail_data['email']['message'],
            $mail_data['email']['headers'],
            $mail_data['email']['attachments']
        );

        return [
            'response' => [
                'status' => $email ? 'success' : 'error',
                'message' => $email ? $this->get_response_message('success') : $this->get_response_message('mail_error')
            ],
            'attachments' => $mail_data['attachments'] // Return attachments for deletion and error handling
        ];
    }
    protected function compose_mail_data(string $action, array $attachments = [])
    {

        // Set short vars for the data
        $settings = $this->settings;
        $data = $this->form_data;
        $files = $this->files;

        $slug = $action == 'email_2' ? '_2' : ''; // Update controls slugs based on the mail submit type
        $line_break = $settings['email_content_type' . $slug] === 'html' ? '<br>' : "\n"; // Set line break type

        // Replace shortcodes by form data
        $content = $this->replace_content_shortcode($settings['email_content' . $slug], $line_break);

        // Adds the metadata to content
        $content = $this->compose_metadata($content, $settings['form_metadata' . $slug], $line_break);

        // Set empty attachments to avoid undefined errors for widgets without attachment options
        if ($data['widget_type'] !== 'contact-form') {
            $attachments = ['files' => '', 'errors' => ''];
        } else {
            $attachments = !empty($attachments) ? $attachments : $this->prepare_attachments($files); // If theres attachments from previous submit action, use it, otherwhise prepare it from $files,
        }

        // Validate and replace fields shortcodes
        $mail_to = $this->replace_content_shortcode($this->validate_field($settings['email_to' . $slug], 'Recipient (to)'));
        $mail_subject = $this->replace_content_shortcode($this->validate_field($settings['email_subject' . $slug], 'Subject'));
        $mail_name = $this->replace_content_shortcode($this->validate_field($settings['email_from_name' . $slug], 'From Name'));
        $mail_from = $this->replace_content_shortcode($this->validate_field($settings['email_from' . $slug], 'From'));
        $mail_reply = $this->replace_content_shortcode($this->validate_field($settings['email_reply_to' . $slug], 'Reply To'));

        // Build the data
        $mail_data = [
            'to' => $mail_to,
            'subject' => $mail_subject,
            'message' => $content,
            'headers' => [
                'Content-Type: text/' . $settings['email_content_type' . $slug] . '; charset=UTF-8',
                'From: ' . $mail_name . ' <' . $mail_from . '>',
                'Reply-To: ' . $mail_reply,
            ],
            'attachments' => $attachments['files']
        ];

        // Build optional data
        if (!empty($settings['email_to_cc' . $slug])) {
            $mail_data['headers'][] = 'Cc: ' . $settings['email_to_cc'];
        }
        if (!empty($settings['email_to_bcc' . $slug])) {
            $mail_data['headers'][] = 'Bcc: ' . $settings['email_to_bcc'];
        }

        return [
            'email' => $mail_data,
            'attachments' => $attachments
        ];
    }
    protected function replace_content_shortcode(string $content, string $line_break = '')
    {

        // Set short vars for the data
        $fields = $this->get_setting_fields();
        $form_data = $this->form_data;

        // [all-fieds] shortcode replacement
        if (false !== strpos($content, '[all-fields]')) {
            $text = '';
            // Return formated text as key: value
            foreach ($form_data['form_fields'] as $key => $field) {
                $field_value = is_array($field) ? implode(', ', $field) : $field;
                $text .= !empty($field_value) ? sprintf('%s: %s', $key, $field_value) . $line_break : '';
            }
            $content = str_replace('[all-fields]', $text, $content);
        }

        // Custom [field id="{id}"] shortcode replacement
        foreach ($fields as $field) {
            $shortcode = '[field id="' . $field['custom_id'] . '"]';
            $value = isset($form_data['form_fields'][$field['custom_id']]) ? $form_data['form_fields'][$field['custom_id']] : '';
            $value = is_array($value) ? implode(', ', $value) : $value;
            $content = str_replace($shortcode, $value, $content);
        }

        // Replaces all manual line breaks from content
        if (!empty($line_break)) {
            $content = str_replace(array("\r\n", "\r", "\n"), $line_break, $content);
        }

        return $content;
    }
    protected function prepare_attachments(array $files)
    {
        $attachments = [];
        $errors = '';

        if (!isset($files['form_fields']) || empty($files['form_fields'])) {
            return [
                'files' => '',
                'errors' => ''
            ];
        }

        // Requires wp_handle_upload() file if unavailable
        if (! function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        // Check if theres a valid file to upload
        foreach ($files['form_fields']['tmp_name'] as $input => $value) {
            if ($files['form_fields']['error'][$input] !== UPLOAD_ERR_NO_FILE) {
                $file = [
                    'name' => $files['form_fields']['name'][$input],
                    'type' => $files['form_fields']['type'][$input],
                    'tmp_name' => $files['form_fields']['tmp_name'][$input],
                    'error' => $files['form_fields']['error'][$input],
                    'size' => $files['form_fields']['size'][$input],
                ];

                // Handle the file upload
                $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($uploaded_file['error'])) {
                    $attachments = $uploaded_file['file'];
                } else {
                    // Since throwing exceptions here will block the proper data flow, we return the error and let send_mail() handle it
                    $errors = esc_html__('Failed to upload file: ', 'uicore-elements') . $uploaded_file['error'];
                }

                // Break after processing the first valid file
                break;
            }
        }

        return [
            'files' => $attachments,
            'errors' => $errors
        ];
    }
    protected function compose_metadata(string $content, array $metadada, string $line_break)
    {

        if (empty($metadada)) {
            return $content;
        }

        $content = $content . $line_break . $line_break . '--' . $line_break . $line_break; // Adds spacing between content and metadata

        foreach ($metadada as $meta) {
            switch ($meta) {
                case 'date':
                    $content .= sprintf('%s: %s', 'Date', gmdate('Y-m-d') . $line_break);
                    break;

                case 'time':
                    $content .= sprintf('%s: %s', 'Time', gmdate('H:i:s') . $line_break);
                    break;

                case 'remote_ip':
                    $content .= isset($_SERVER['REMOTE_ADDR'])
                        ? sprintf('%s: %s', 'IP', sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) . $line_break)
                        : '';
                    break;

                case 'user_agent':
                    $content .= isset($_SERVER['HTTP_USER_AGENT'])
                        ?  sprintf('%s: %s', 'User Agent', sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) . $line_break)
                        : '';
                    break;

                case 'page_url':
                    $content .= isset($_SERVER['HTTP_REFERER'])
                        ?  sprintf('%s: %s', 'Page URL', sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) . $line_break)
                        : '';
                    break;
            }
        }

        return $content;
    }

    /**
     * Extra submissions
     */
    protected function redirect()
    {

        $validation = $this->validate_url($this->settings['redirect_to']);

        // Above function exception blocks this execution
        return [
            'status' => 'success',
            'url' => esc_url($validation['url']),
            'delay' => 1500,
            'message' => esc_html($this->get_response_message('redirect'))
        ];
    }
    protected function popup()
    {
        $action = $this->settings['popup_action'];

        if ($action === 'open') {
            return [
                'status'  => 'success',
                'action'  => sanitize_text_field($action),
                'id'      => sanitize_text_field($this->settings['open_popup']),
                'message' => sanitize_text_field($this->get_response_message('popup'))
            ];
        }

        return [
            'status'  => 'success',
            'action'  => sanitize_text_field($action),
            'message' => sanitize_text_field($this->get_response_message('popup'))
        ];
    }
    protected function newsletter_service(string $service)
    {
        $settings = $this->settings;
        $data     = $this->get_submission_data_to_service_fields();

        $api_key = get_option('uicore_elements_newsletter_service_key');
        $fields  = $data['fields'];
        $list    = array_map('trim', explode(',', $settings['mailchimp_audience_id']));
        $custom_fields = $data['custom_fields'];

        $instance = new Services($api_key, $fields, $list, $custom_fields);
        $response = $instance->handle($service);

        return $response;
    }

    /**
     * Validations
     */
    protected function validate_recaptcha(string $token, string $version)
    {

        // Check if secret and site key are set
        if (!get_option('uicore_elements_recaptcha_secret_key') || !get_option('uicore_elements_recaptcha_site_key')) {
            return [
                'success' => false,
                'message' => esc_html__('reCAPTCHA API keys are not set.', 'uicore-elements')
            ];
        }

        $data = [
            'secret' => get_option('uicore_elements_recaptcha_secret_key'),
            'response' => sanitize_text_field($token)
        ];

        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", [
            'body' => $data,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $captcha = json_decode(wp_remote_retrieve_body($response));

        if ($version === 'V3') {
            return ['success' => ($captcha->success && $captcha->score >= 0.5) ? true : false];
        }

        // V2 default
        return ['success' => $captcha->success];
    }
    protected function validate_spam()
    {
        // `ui-e-h-p` is the key for the honeypot
        return (isset($this->form_data['ui-e-h-p']) && !empty($this->form_data['ui-e-h-p'])) ? false : true;
    }
    protected function validate_url(string $url)
    {
        if (empty($url)) {
            throw new Redirect_Exception(esc_html($this->get_response_message('redirect_no_url')));
        }

        return [
            'status' => true,
            'url' => $url,
        ];
    }
    protected function validate_field(string $field, string $label)
    {
        if (empty($field)) {
            throw new Submit_Exception(esc_html($this->get_response_message('empty_field', esc_html($label))));
        }
        return $field;
    }

    /**
     * Helpers
     */
    protected function get_setting_fields()
    {
        // Used to determine if the widget fields are repeaters with custom IDS or fixed fields, and if fixed fields
        // compose them into an array similar to repeaters, to simplify shortcode replacement function

        switch ($this->form_data['widget_type']) {

            case 'newsletter':
                return [
                    ['custom_id' => 'email'],
                    ['custom_id' => 'name'],
                ];
                break;

            // Dynamic fields values
            default:
                return $this->settings['form_fields'];
                break;
        }
    }
    protected function get_submission_data_to_service_fields(): array
    {
        $settings = $this->settings;
        $widget = $this->form_data['widget_type'];

        // Newsletter widget works only with email and perpahs name
        if ($widget === 'newsletter') {
            return [
                'fields' => [
                    'email' => $this->replace_content_shortcode('[field id="email"]'),
                    'name' => $this->replace_content_shortcode('[field id="name"]'),
                ],
                'custom_fields' => []
            ];
        }

        // Contact Form case has custom fields and allows fields ID change

        $repeater_fields = $settings['newsletter_service_custom_fields'];

        $fields = [
            'email'     => 'mailchimp_email_id',
            'name'      => 'mailchimp_fname_id',
            'last_name' => 'mailchimp_lname_id',
            'phone'     => 'mailchimp_phone_id',
            'birthday'  => 'mailchimp_birthday_id',
        ];

        $custom_fields = [];

        // Build custom field stack
        if (is_array($repeater_fields) && !empty($repeater_fields)) {
            foreach ($repeater_fields as $field) {
                $custom_fields[$field['field_name']] = [
                    'value' => $this->replace_content_shortcode($field['field_id']), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'method' => $this->get_custom_field_sanitization_method($field['field_type']), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ];
            }
        }

        // Remove not used fields from the stack, and extract the content of used fields, if they're using shortcodes
        foreach ($fields as $data => $field) {
            if (isset($settings[$field]) && !empty($settings[$field])) {
                $fields[$data] = $this->replace_content_shortcode($settings[$field]);
            } else if ($data !== 'email' && isset($field[$data])) {
                unset($field[$data]);
            }
        }

        return [
            'fields' => $fields,
            'custom_fields' => $custom_fields
        ];
    }
    protected function get_custom_field_sanitization_method(string $type): string
    {
        // Values are default wp or custom methods from our newsletter services class,
        // while $type should be field type options from form component class
        switch ($type) {
            case 'email':
                return 'sanitize_email';
            case 'phone':
                return 'sanitize_phone';
            case 'birthday':
                return 'sanitize_birthday';
            default:
                return 'sanitize_text_field';
        }
    }

    protected function all_submissions_succedded($responses)
    {
        foreach ($responses as $submission => $data) {
            // Redirect and popup actions failure shouldn't return `error` to main status because is not properly a submission action, so we skip it.
            if (in_array($submission, ['redirect', ' popup'])) {
                continue;
            }
            if (
                isset($data['status']) &&
                (
                    $data['status'] === 'error' ||
                    $data['status'] === false
                )
            ) {
                return false;
            }
        }
        return true;
    }
    public static function get_form_submit_options()
    {

        $options =  [
            'email'  => esc_html__('Email', 'uicore-elements'),
            'email_2' => esc_html__('Email 2', 'uicore-elements'),
            'redirect' => esc_html__('Redirect', 'uicore-elements')
        ];

        // Uicore Framework dependent actions
        if (defined('UICORE_ASSETS')) {
            $options['popup'] = esc_html('Popup', 'uicore-elements');
        }

        $services = Services::get_services_list();

        if (is_array($services) || empty($services)) {
            $options = array_merge($options, $services);
        }

        return $options;
    }

    /**
     * Responses
     */
    // Also used by form widget(s), therefore public and static
    public static function get_default_messages()
    {
        return [
            // main messages
            'success'    => esc_html__('Your submission was successful.', 'uicore-elements'),
            'error'      => esc_html__('Your submission failed because of an error.', 'uicore-elements'),
            'mail_error' => esc_html__('Failed to send email.', 'uicore-elements'),
            'required'   => esc_html__('Fill all required fields.', 'uicore-elements'),
            'redirect'   => esc_html__('Redirecting...', 'uicore-elements'),
        ];
    }
    protected function get_response_message(string $status, string $dinamic_data = '')
    {
        // non-customizable messages (for settings debugging only)
        $default_messages = [
            'invalid_status'    => esc_html__('Invalid status message.', 'uicore-elements'),
            'redirect_no_url'   => esc_html__('Redirection failed. No URL set.', 'uicore-elements'),
            'empty_field'       => esc_html__('The following field is empty: ', 'uicore-elements') . $dinamic_data,
        ];

        if ($this->settings['custom_messages'] === 'yes') {
            $messages = [
                'success' => esc_html($this->settings['success_message']),
                'error' => esc_html($this->settings['error_message']),
                'mail_error' => esc_html($this->settings['mail_error_message']),
                'redirect' => esc_html($this->settings['redirect_message']),
            ];
        } else {
            $messages = self::get_default_messages();
        }

        $messages = array_merge($default_messages, $messages);

        return isset($messages[$status]) ? $messages[$status] : $messages['invalid_status'];
    }
    protected function build_frontend_responses($responses)
    {
        $data = [];

        // Mail response
        if (isset($responses['email']) && $responses['email']['status'] !== 'success') {
            $data['email'] = $responses['email'];
        }

        // Mail attachment response - is always an error
        if (isset($responses['email']) && isset($responses['email']['attachment'])) {
            $data['attachment'] = $responses['attachment'];
        }

        // Submit Actions response - is always an error
        if (isset($responses['submit'])) {
            $data['submit'] = $responses['submit'];
        }

        // Newsletter Services responses
        $data = $this->build_services_responses($responses);

        // Main response
        $status = $this->all_submissions_succedded($responses) ? 'success' : 'error';
        $data['message'] = $this->get_response_message($status);

        // Below responses should work only if all previous submitions hasn't failed
        if (isset($responses['redirect']) && $status === 'success') {
            $data['redirect'] = $responses['redirect'];
        }
        if (isset($responses['popup']) && $status === 'success') {
            $data['popup'] = $responses['popup'];
        }

        // The only successfull response that should be sent is 'main' and 'redirect'
        return [
            'status' => $status,
            'data' => $data
        ];
    }

    protected function build_services_responses($responses)
    {
        $success = true;
        $service = '';
        $status = '';
        $detail = '';

        if (isset($responses['mailchimp'])) {
            // We know mailchimp failed if status is an integer
            // If one response fail, in multiple list cases, returns the failure
            foreach ($responses['mailchimp'] as $response) {
                if (is_int($response['status'])) {
                    $success = false;
                    $service = 'Mailchimp';
                    $status = 'HTTP: ' . $response['status'];
                    $detail = $response['detail'];
                    break;
                }
            }
        } else if (isset($responses['brevo'])) {
            // We know Brevo failed if 'code' is set in the response
            if (isset($responses['brevo']['code'])) {
                $success = false;
                $service = 'Brevo';
                $status = 'Error: ' . $responses['brevo']['code'];
                $detail = $responses['brevo']['message'] ?? '';
            }
        } else if (isset($responses['getresponse'])) {
            // We know GetResponse failed if 'httpStatus' is set in the response
            foreach ($responses['getresponse'] as $response) {
                if (isset($response['httpStatus'])) {
                    $success = false;
                    $service = 'GetResponse';
                    $status = 'HTTP: ' . $response['httpStatus'];
                    $detail = 'Error: ' . $response['code'] . ' - ' . $response['message'];
                    break;
                }
            }
        } else if (isset($responses['kit'])) {
            // We know Kit failed if 'error' is set in the response
            foreach ($responses['kit'] as $response) {
                if (isset($response['error'])) {
                    $success = false;
                    $service = 'Kit';
                    $status = 'Error: ' . $response['error'];
                    $detail = $response['message'];
                    break;
                }
            }
        } else if (isset($responses['moosend'])) {
            // We know Moosend failed if 'Error' is set in the response
            foreach ($responses['moosend'] as $response) {
                if (!empty($response['Error'])) {
                    $success = false;
                    $service = 'Moosend';
                    $status = 'HTTP: ' . $response['Code'];
                    $detail = $response['Error'];
                    break;
                }
            }
        } else if (isset($responses['mailerlite'])) {
            // We know MailerLite failed if 'message' is set in the response
            if (isset($responses['mailerlite']['message'])) {
                $success = false;
                $service = 'MailerLite';
                $status = 'Error';
                $detail = $responses['mailerlite']['message'] ?? '';
            }
        }

        // Return the error response if the service fails
        if ($success === false) {
            $data['newsletter_service'] = [
                'status' => 'error',
                /* translators: 1: Service Provider, 2: Service Error code, 3: Service Status Response */
                'message' => sprintf(esc_html__('[%1$s] %2$s - %3$s', 'uicore-elements'), $service, $status, $detail)
            ];
        }

        return [];
    }
}
