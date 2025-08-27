<?php
add_filter( 'single_template',  'gtmi_vcard_load_my_single_templates');

/**
 * Load templates from this module
 *
 * @param string $wp_template path of default WP template
 * @return string the new template to use
 */
function gtmi_vcard_load_my_single_templates($wp_template): string
{
  $templates = [
    'virtual_card' => 'single-virtual_card.php',
    'lead' => 'single-lead.php'
  ];

  foreach ($templates as $label => $filename) {
    if (is_singular( $label)) {
      $plugin_template = plugin_dir_path( __DIR__) . '../templates/' . $filename;
      if (file_exists( $plugin_template)) {
        return $plugin_template;
      }
    }
  }

  return $wp_template;
}

function gtmi_vcard_redirection_to_custom_url($post_id): void
{
    // Vérifier le mode de redirection ET l'URL
    $redirect_mode = get_post_meta($post_id, 'redirect_mode', true);
    $custom_url = get_field('custom_url', $post_id);
    
    // Redirection seulement si mode = 'custom' ET URL non vide
    if ($redirect_mode === 'custom' && strlen($custom_url) > 0) {
        
        // Validation de l'URL
        if (filter_var($custom_url, FILTER_VALIDATE_URL)) {
            error_log("🔗 Redirection vCard $post_id vers : $custom_url");
            header('Location: ' . $custom_url, true, 301);
            exit();
        } else {
            error_log("❌ URL custom invalide pour vCard $post_id : $custom_url");
        }
    }
    
    // Pas de redirection = affichage vCard normal
}