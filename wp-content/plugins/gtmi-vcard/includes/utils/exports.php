<?php

$vcard_args = [
    'post_type' => 'virtual_card',
    'page_title' => 'Export Virtual cards',
    'menu_title' => 'Export to CSV',
    'menu_slug' => 'export_virtual_cards_csv',
    'headers' => ['Firtsname', 'Lastname', 'email', 'society', 'Order', 'Card_status', 'URL'],
    'acf_fields' => ['firstname', 'lastname', 'email', 'society', 'order', 'card_status', 'url'],
    'action' => 'export_virtual_cards_csv',
    'nonce' => 'export_virtual_cards_nonce',
    'filename' => 'virtual_card_export_'
];

$leads_args = [
    'post_type' => 'lead',
    'page_title' => 'Export leads',
    'menu_title' => 'Export to CSV',
    'menu_slug' => 'export_leads_csv',
    'headers' => ['Firtsname', 'Lastname', 'email', 'society', 'post', 'Linked virtual card ID', 'Contact datetime'],
    'acf_fields' => ['firstname', 'lastname', 'email', 'society', 'post', 'linked_virtual_card', 'contact_datetime'],
    'action' => 'export_leads_csv',
    'nonce' => 'export_leads_nonce',
    'filename' => 'leads_export_'
];

add_action(
    hook_name: 'admin_menu',
    callback: function () use ($vcard_args): void {
        gtmi_vcard_export(args: $vcard_args);
    },
    priority: 10,
    accepted_args: 1
);
add_action(
    hook_name: 'admin_menu',
    callback: function () use ($leads_args): void {
        gtmi_vcard_export(args: $leads_args);
    },
    priority: 10,
    accepted_args: 1
);
add_action(
    hook_name: 'admin_init',
    callback: function () use ($vcard_args): void {
        gtmi_vcard_check_permissions(args: $vcard_args);
    },
    priority: 10,
    accepted_args: 1
);

add_action(
    hook_name: 'admin_init',
    callback: function () use ($leads_args): void {
        gtmi_vcard_check_permissions(args: $leads_args);
    },
    priority: 10,
    accepted_args: 1
);
add_action(
    hook_name: 'admin_notices',
    callback: function (): void {
        virtual_card_admin_notices(label: 'Virtual Card');
    },
    priority: 10,
    accepted_args: 1
);
add_action(
    hook_name: 'admin_notices',
    callback: function (): void {
        virtual_card_admin_notices(label: 'Lead');
    },
    priority: 10,
    accepted_args: 1
);

function gtmi_vcard_export($args): void
{
    add_submenu_page(
        parent_slug: 'edit.php?post_type=' . $args['post_type'],
        page_title: $args['page_title'],
        menu_title: $args['menu_title'],
        capability: 'manage_options',
        menu_slug: $args['menu_slug'],
        callback: function () use ($args): void {
            gtmi_vcard_export_callback(args: $args);
        }
    );
}

function gtmi_vcard_export_callback($args)
{
    // URL d'exportation : nous allons déclencher la fonction d'exportation via ce paramètre d'URL
    $export_url = add_query_arg([
        'action' => $args['action'],
        'nonce' => wp_create_nonce(action: $args['nonce'])
    ], admin_url(path: 'admin.php'));

    ?>
    <div class="wrap">
        <h1><?= __(text: $args['page_title'], domain: 'gtmi_vcard'); ?></h1>
        <p><?= __(text: 'Click down button to download', domain: 'gtmi_vcard'); ?></p>
        <p>
            <a href="<?php echo esc_url(url: $export_url); ?>"
                class="button button-primary"><?= __(text: $args['page_title'], domain: 'gtmi_vcard'); ?></a>
        </p>
    </div>
    <?php
}

function gtmi_vcard_check_permissions($args): void
{
    if (
        isset($_GET['action']) && $_GET['action'] === $args['action'] &&
        current_user_can(capability: 'manage_options') &&
        isset($_GET['nonce']) && wp_verify_nonce(nonce: $_GET['nonce'], action: $args['nonce'])
    ) {
        gtmi_vcard_export_to_csv(args: $args);
        exit;
    }
}

function gtmi_vcard_export_to_csv($args): never
{
    $params = [
        'post_type' => $args['post_type'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    $virtual_cards = new WP_Query(query: $params);

    if ($virtual_cards->have_posts()) {
        $csv_data = [];
        $csv_data[] = $args['headers'];
        while ($virtual_cards->have_posts()) {
            $virtual_cards->the_post();
            $post_id = get_the_ID();
            $row = [];
            foreach ($args['acf_fields'] as $field_name) {
                $field_value = get_field(selector: $field_name, post_id: $post_id);
                if (is_object(value: $field_value) && method_exists(object_or_class: $field_value, method: 'post_title')) {
                    $row[$field_name] = $field_value->post_title;
                } elseif (is_array(value: $field_value)) {
                    $sub_values = [];
                    foreach ($field_value as $item) {
                        if (is_array(value: $item) && isset($item['sub_text'])) {
                            $sub_values[] = $item['sub_text'];
                        } elseif (!is_array(value: $item)) {
                            $sub_values[] = $item;
                        }
                    }
                    $row[$field_name] = implode(separator: '; ', array: $sub_values);
                } else {
                    $row[$field_name] = $field_value;
                }
            }
            if ($args['post_type'] === 'lead' || strtolower(string: $row['card_status']) === 'processing' && $args['post_type'] === 'virtual_card') {
                $csv_data[] = $row;
            }
        }
        wp_reset_postdata();
        // Generate csv file
        header(header: 'Content-Type: text/csv; charset=utf-8');
        header(header: 'Content-Disposition: attachment; filename="' . $args['filename'] . date(format: 'Y-m-d-H-i-s') . '.csv"');
        header(header: 'Pragma: no-cache');
        header(header: 'Expires: 0');

        $output = fopen(filename: 'php://output', mode: 'w');
        fprintf(stream: $output, format: chr(codepoint: 0xEF) . chr(codepoint: 0xBB) . chr(codepoint: 0xBF));
        foreach ($csv_data as $data_row) {
            fputcsv(stream: $output, fields: $data_row, separator: ';');
        }

        fclose(stream: $output);
        exit;
    } else {
        wp_redirect(location: admin_url(path: 'edit.php?post_type=' . $args['post_type'] . '&message=not_found'));
        exit;
    }
}

function virtual_card_admin_notices($label): void
{
    if (isset($_GET['message']) && $_GET['message'] === 'not_found') {
        echo '<div class="notice notice-warning is-dismissible"><p>' . __(text: 'Not found', domain: 'gtmi_vcard') . ' ' . $label . '</p></div>';
    }
}
