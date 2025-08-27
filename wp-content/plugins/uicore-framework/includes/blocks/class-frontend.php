<?php
namespace UiCore\Blocks;

class Frontend {
    public function __construct() {
        add_filter('uicore_bl_should_add_inline_styles', '__return_false', 11);
        add_action('uicore_bl_enqueue_styles', [$this, 'enqueue_styles']);
    }

    public static function enqueue_styles($post_id = null) {
        $post_id = $post_id ?: get_the_ID();
        $file_was_not_created = false;
        if (!$post_id) {
            return;
        }


        $version = \UiCoreBlocks\Frontend::get_post_style_version($post_id);
        if( !$version ) {
            return;
        }
        $device_assets = [];
        $devices_mapping = [
            'mobile' => '(max-width: 767px)',
            'tablet' => '(max-width: 1024px) and (min-width: 768px)',
            'desktop' => '(min-width: 1024px)',
        ];

        //check for what devices we have styles and enque them + regenerate if file was removed
        $saved_assets = get_post_meta($post_id, '_uicore_block_assets', true);
        if (!is_array($saved_assets) && !isset($saved_assets['devices'])) {
            $device_assets = [ 
                'mobile' => '(max-width: 767px)',
                'tablet' => '(max-width: 1024px) and (min-width: 768px)',
                'desktop' => '(min-width: 1024px)'
            ];
        }elseif( is_array($saved_assets) && isset($saved_assets['devices']) && is_array($saved_assets['devices']) ) {
            foreach ($saved_assets['devices'] as $device) {
                $device_assets[$device] = $devices_mapping[$device] ?? '';
            }
        }

        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'].'/uicore-blocks';
        foreach ($device_assets as $device => $media_query) {
            $file = $base_dir."/uicore-".$post_id."-".$device.".css";
            if (!file_exists($file)) {
                $file_was_not_created = true;
            }
            wp_enqueue_style(
                'uicore-bl-p-'.$post_id.'-'.$device,
                $upload_dir['baseurl'].'/uicore-blocks/uicore-'.$post_id.'-'.$device.'.css',
                [],
                $version,
                $media_query
            );
        }

        //fallback for when the file was not created
        if($file_was_not_created) {
            $styles = \UiCoreBlocks\Frontend::get_post_styles($post_id);
            Core::write_styles_to_file($post_id, $styles);
        }
    }
}
new Frontend();