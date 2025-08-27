<?php

namespace UiCoreElements\Utils;

defined('ABSPATH') || exit();

/**
 * Email and Form service providers methods
 *
 * Fields expected structure:
 *      array(
 *          'email'     => 'thomas@gmail.com', // *required
 *          'name'      => 'Thomas',
 *          'last_name' => 'Sankara',
 *          'phone'     => '+55 21 9999-9999',
 *          'birthday'  => '1990-01-01',
 *      )
 *
 * Custom Fields expected structure:
 *      array(
 *          'country' => array(
 *              'value'  => 'Burkina Faso',
 *              'method' => 'sanitize_text_field', // `sanitize_phone` and `sanitize_birthday` are custom methods available within this class.
 *          ),
 *          'website' => array(
 *              'value'  => 'https://www.website.gov',
 *              'method' => 'sanitize_url'
 *          ),
 *      )
 *
 * List Expected structure:
 *      array( 'abjm194', 'kmlrmg9482903-30' )
 *
 *
 * @param string $api_key - API key for the service provider.
 * @param array $data - Data to be sent to the service provider.
 */

class Newsletter_Services
{

    protected $api_key, $fields, $list, $custom_fields, $headers;

    public function __construct(string $api_key, array $fields, array $list, array $custom_fields = [])
    {
        $this->api_key = $api_key;
        $this->fields = $fields;
        $this->list = $list;
        $this->custom_fields = $custom_fields;

        $this->headers = [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Handle the requested newsletter service.
     * You must pass the service api key and the data in the class constructor before calling this method.
     */
    public function handle(string $service)
    {
        if (empty($this->api_key)) {
            throw new Newsletter_Service_Exception(esc_html__('Your API key is missing.', 'uicore-elements'));
        }

        if (empty($this->list)) {
            throw new Newsletter_Service_Exception(esc_html__("You haven't set any list; group; form or audience ID.", 'uicore-elements'));
        }

        if (!method_exists($this, $service)) {
            throw new \Exception(esc_html__('Unsupported service provider.', 'uicore-elements'));
        }

        return call_user_func([$this, $service]);
    }

    /**
     * Returns the list of available services.
     *
     * @param mixed $filter - Let you return only the service keys, if set to 'keys',
     *                        or only the service names, if set to 'names'. Default is
     *                        false, which returns the full array.
     *
     * @return array - An associative array of service keys and values or a simple array of keys or values
     */
    public static function get_services_list($filter = false)
    {
        $services = [
            'brevo' => esc_html__('Brevo', 'uicore-elements'),
            'getresponse' => esc_html__('GetResponse', 'uicore-elements'),
            'kit' => esc_html__('Kit', 'uicore-elements'),
            'mailchimp' => esc_html__('MailChimp', 'uicore-elements'),
            'moosend' => esc_html__('Moosend', 'uicore-elements'),
            'mailerlite' => esc_html__('MailerLite', 'uicore-elements'),
        ];

        if ($filter === 'keys') {
            return array_keys($services);
        } else if ($filter === 'names') {
            return array_values($services);
        }

        return $services;
    }

    /**
     * Services
     */
    protected function mailchimp()
    {
        $server = explode('-', $this->api_key)[1]; // Server value can be found on API key after the dash

        $map = [
            'name'      => 'FNAME',
            'last_name' => 'LNAME',
            'phone'     => 'PHONE',
            'birthday'  => 'BIRTHDAY',
        ];

        $this->headers['Authorization'] = 'Basic ' . base64_encode('anystring:' . sanitize_text_field($this->api_key));

        $body = [
            "email_address" => sanitize_email($this->fields['email']),
            "status"        => 'subscribed',
            "merge_fields"  => $this->build_fields($this->fields, $map, $this->custom_fields), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ];

        $result = [];
        foreach ($this->list as $list) {
            $url = sanitize_url('https://' . $server . '.api.mailchimp.com/3.0/lists/' . $list . '/members/');
            $result[] = $this->submit($url, $this->headers, $body);
        }
        return $result;
    }
    protected function brevo()
    {
        $map = [
            'name'      => 'FIRSTNAME',
            'last_name' => 'LASTNAME',
            'phone'     => 'SMS',
            'birthday'  => 'BIRTHDAY'
        ];

        $this->headers['api-key'] = sanitize_text_field($this->api_key);

        $body = [
            'email'         => sanitize_email($this->fields['email']),
            'attributes'    => $this->build_fields($this->fields, $map, $this->custom_fields), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            'listIds'       => array_map('intval', $this->list),
            'updateEnabled' => true
        ];

        $result = $this->submit('https://api.brevo.com/v3/contacts', $this->headers, $body);
        return $result;
    }
    protected function kit()
    {
        $map = [
            'last_name' => 'last_name',
            'phone'     => 'phone',
            'birthday'  => 'birthday',
        ];

        $body = [
            'email'     => sanitize_email($this->fields['email']),
            'api_key'   => sanitize_text_field($this->api_key),
            'fields'    => $this->build_fields($this->fields, $map, $this->custom_fields), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ];

        if (isset($this->fields['name'])) {
            $body['first_name'] = sanitize_text_field($this->fields['name']);
        }

        $result = [];
        foreach ($this->list as $list) {
            $url = sanitize_url('https://api.convertkit.com/v3/forms/' . $list . '/subscribe');
            $result[] = $this->submit($url, $this->headers, $body);
        }
        return $result;
    }
    protected function moosend()
    {
        $body = [
            'Email'     => sanitize_email($this->fields['email']),
            'api_key'   => sanitize_text_field($this->api_key),
        ];

        if (isset($this->fields['name']) && !empty($this->fields['name'])) {
            $body['Name'] = sanitize_text_field($this->fields['name']);
        }

        // Moosend has a different custom fields structure, compared to the other services, so we built it in here
        foreach ($this->custom_fields as $key => $values) {
            if (!empty($values['value'] && !empty($values['method']))) {
                $body['CustomFields'][] = [
                    sanitize_text_field($key) . '=' . $this->get_sanitized_value($values['method'], $values['value']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                ];
            }
        }

        $result = [];
        foreach ($this->list as $list) {
            $url = sanitize_url('https://api.moosend.com/v3/subscribers/' . $list . '/subscribe.json?apikey=' . $this->api_key);
            $result[] = $this->submit($url, $this->headers, $body);
        }
        return $result;
    }
    protected function getresponse()
    {
        $body = ['email' => sanitize_email($this->fields['email'])];

        if (isset($fields['name']) && !empty($fields['name'])) {
            $body['name'] = sanitize_text_field($fields['name']);
        }

        // Getresponse has a different custom fields structure, compared to the other services, so we built it in here
        foreach ($this->custom_fields as $key => $values) {
            if (!empty($values['value']) && !empty($values['method'])) {
                $body['customFieldValues'][] = [
                    'customFieldId' => sanitize_text_field($key),
                    'value' => [$this->get_sanitized_value($values['method'], $values['value'])] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                ];
            }
        }

        $result = [];
        foreach ($this->list as $list) {
            $this->headers['X-Auth-Token'] = 'api-key ' . sanitize_text_field($this->api_key);
            $body['campaign'] = ['campaignId' => sanitize_text_field($list)];
            $result[] = $this->submit('https://api.getresponse.com/v3/contacts', $this->headers, $body);
        }
        return $result;
    }
    protected function mailerlite()
    {
        $map = [
            'name'      => 'name',
            'last_name' => 'last_name',
            'phone'     => 'phone',
        ];

        $this->headers['Authorization'] = 'Bearer ' . sanitize_text_field($this->api_key);

        $body = [
            'email'          => sanitize_email($this->fields['email']),
            'resubscribe'    => true,
            'autoresponders' => true,
            'type'           => 'active',
            'groups'         => $this->list, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            'fields'         => $this->build_fields($this->fields, $map, $this->custom_fields), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ];
        return $this->submit('https://connect.mailerlite.com/api/subscribers', $this->headers, $body);
    }

    /**
     * Send the post request to the service provider.
     *
     * @param string $url - The URL to send the request to.
     * @param array $headers - The post request header.
     * @param array $body - The post request body.
     * @param int $timeout - The request timeout in seconds. Default is 15 seconds.
     *
     * @return array - The response from the service provider.
     */
    protected function submit($url, $headers, $body, $timeout = 15)
    {
        $response = wp_remote_post($url, [
            'headers' => array_map('sanitize_text_field', $headers),
            'body' => json_encode($body),
            'timeout' => (int) $timeout,
        ]);

        if (is_wp_error($response)) {
            return [
                'status' => 'error',
                'message' => $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        return $result;
    }

    /**
     * Translate, sanitize and parse field values to be used in the request body.
     *
     * @param array $data - The array of default fields
     * @param array $map - The reference map with the provider correspondent key for each default field available.
     * @param array $custom_fields - An array of custom fields to merged to the fields stack. Default is empty.
     *
     * @return array - An array with each available data, sanitized and prepared to be added to the body request.
     */
    protected function build_fields(array $data, array $map, array $custom_fields = []): array
    {
        $fields = [];

        // Default fields
        foreach ($map as $reference => $key) {
            if (isset($data[$reference]) && !empty($data[$reference])) {

                $slug = sanitize_text_field($key);
                $value = $data[$reference];

                switch ($reference) {
                    case 'birthday':
                        $fields[$slug] = $this->sanitize_birthday($value);
                        break;
                    case 'phone':
                        $fields[$slug] = $this->sanitize_phone($value);
                        break;
                    default:
                        $fields[$slug] = sanitize_text_field($value);
                }
            }
        }

        // Merge custom fields using the provided sanitization method
        if (isset($custom_fields)) {
            foreach ($custom_fields as $key => $values) {
                if (!empty($values['value'] && !empty($values['method']))) {
                    $fields[sanitize_text_field($key)] = $this->get_sanitized_value($values['method'], $values['value']); //call_user_func([$this, $values['method']], $values['value']);
                }
            }
        }

        return $fields;
    }
    /**
     * Check if the provided sanitization method is available before sanitizing the value.
     *
     * @param @string $method - The sanitization method to call.
     * @param mixed $value - The value to sanitize.
     *
     *  @return mixed - The sanitized value or the original value if the method does not exist.
     */
    protected function get_sanitized_value(string $method, string $value)
    {
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $value); // methods within this class
        } else if (function_exists($method)) {
            return call_user_func($method, $value); // global functions
        }
        return $value;
    }

    /**
     * Sanitizers
     */
    protected function sanitize_birthday(string $date, string $format = 'd/m')
    {
        $date = trim($date);
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return '';
        }
        return date($format, $timestamp);
    }
    protected function sanitize_phone(string $number)
    {
        return preg_replace('/[^\d+]/', '', $number ?? '');
    }
}
