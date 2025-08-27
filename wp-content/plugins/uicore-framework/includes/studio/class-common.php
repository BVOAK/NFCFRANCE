<?php
namespace UiCore;

class Common {
  public static function init() {
    if (isset($_GET['uistudio_preview']) && $_GET['uistudio_preview'] === 'true') {
      add_action('wp_footer', [__CLASS__, 'add_scripts']);
    }
    add_action('admin_init', [__CLASS__, 'save_settings']);
  }

  public static function enqueue_scripts() {
    echo '<script>
    console.log("dada");
    </script>';
  }

  public static function save_settings() {
    if (isset($_POST['uicore_settings'])) {
      $new_settings = [];
      parse_str($_POST['uicore_settings'], $new_settings);
      Settings::update_settings($new_settings);
      if (isset($_GET['uistudio_preview']) && $_GET['uistudio_preview'] === 'true') {
        wp_redirect($_SERVER['HTTP_REFERER']);
        exit;
      }
    }
  }
}
