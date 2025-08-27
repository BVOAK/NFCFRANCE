<?php
namespace UiCore;

class Frontend {
  public static function init() {
    if (isset($_GET['uistudio']) && $_GET['uistudio'] === 'true') {
      add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
      add_action('wp_head', [__CLASS__, 'inject_preview']);
      add_filter('template_include', [__CLASS__, 'render_studio_page']);
    }
  }

  public static function enqueue_scripts() {
    wp_enqueue_script('uicore-studio', UICORE_URL . 'assets/js/studio.js', ['jquery'], UICORE_VERSION, true);
    wp_enqueue_style('uicore-studio', UICORE_URL . 'assets/css/studio.css', [], UICORE_VERSION);
  }

  public static function inject_preview() {
    if (isset($_GET['uistudio_preview']) && $_GET['uistudio_preview'] === 'true') {
      echo '<style>#wpadminbar{display:none!important;}</style>';
      echo '<script>var uiCoreStudioPreview = true;</script>';
    }
  }

  public static function render_studio_page($template) {
    if (isset($_GET['uistudio']) && $_GET['uistudio'] === 'true') {
      $template = UICORE_INCLUDES . 'templates/studio.php';
    }
    return $template;
  }
}
