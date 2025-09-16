<?php

/**
 * Generate new slug UUID to have unique URL and not easy to know
 *
 * @param array $data post data(custom post type here virutal_card)
 * @param array $postarr post data not clean
 * @return array post data clean.
 */
function gtmi_vcard_random_slug($data, $postarr)
{
    if ('virtual_card' === $data['post_type']) {
        // Check is a new not a update
        if (empty($postarr['ID']) || empty($data['post_name'])) {
            if (function_exists('wp_generate_uuid4')) { // WP 5.9+ && PHP > 7.0
                $uuid = wp_generate_uuid4();// stronger
            } else {
                $uuid = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                );
            }

            // new URL
            $data['post_name'] = str_replace('-', '', $uuid);

            /**
             * TODO Empêcher l'utilisateur de modifier le slug dans l'éditeur WordPress
             *  Cela peut être fait avec JavaScript côté admin ou en désactivant le champ slug.
             *  Pour le moment, nous laissons la possibilité à WP de le modifier si l'utilisateur y touche,
             *  mais ce hook écrasera le slug à chaque création si la zone est vide.
             *  Pour vraiment l'empêcher, il faudrait masquer le champ permalien dans l'admin.
             */
        }
    }
    error_log('GTMI_VCARD: Post name update and is unique ' .$data['post_name']);

    return $data;
}
add_filter('wp_insert_post_data', 'gtmi_vcard_random_slug', 10, 2);
