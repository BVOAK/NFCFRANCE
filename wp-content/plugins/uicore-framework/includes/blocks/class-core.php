<?php
namespace UiCore\Blocks;

class Core {
    public function __construct() {
       add_action('uicore_block_styles_updated', [$this, 'write_styles_to_file'],10,2);
       add_action('uicore_block_styles_deleted', [$this, 'delete_styles_file']);
       add_action('init', [$this, 'register_uicore_blocks'], 51);
    }

        /**
     * Register
     *
     * @return void
     */
    public function register_uicore_blocks()
    {
        $path = \UICORE_PATH;
        // if(defined('UICORE_LOCAL')){
        if(defined('UICORE_BLOCKS_PATH')){
            $path = \UICORE_BLOCKS_PATH;
        }
        $path = apply_filters('uicore_blocks_path', $path);

        foreach (self::get_blocks() as $block => $data) {
            register_block_type($path . '/assets/blocks/' . $block);
        }
    }

        static function get_blocks()
    {
        return [
            //Dynamic blocks (rendered via php)
            'dynamic/post-grid' => [],


            //advanced blocks
            
            'advanced/tabs' => [
                'frontend_styles' => true,
                'frontend_scripts' => true,
            ],
            'advanced/tabs/content' => [],
            'advanced/tabs/content/item' => [],
            'advanced/tabs/nav' => [],
            'advanced/tabs/nav/item' => [],
            
            'advanced/accordion' => [
                'frontend_styles' => true,
                'frontend_scripts' => true,
            ],

            'advanced/form' => [
                'frontend_styles' => true,
            ],
            
            'advanced/icon-text' => [],
            'advanced/icon-card' => [],
            'advanced/testimonial-card' => [],
            
            'advanced/grid-icon-card' => [],
            'advanced/grid-icon-text' => [],
            'advanced/grid-testimonial-card' => [],

            //'advanced/grid' => ['frontend_styles' => true,],
            'advanced/carousel' => [],


        ];
    }

    public static function write_styles_to_file($post_id, $styles) {

        if( !is_array($styles) || empty($styles)) {
            return;
        }
        
        \UiCore\Helper::ensure_assets_manager_exists();

        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'].'/uicore-blocks';
        if (!file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
        }
        foreach ($styles as $name => $css) {
            $minifier = new \MatthiasMullie\Minify\CustomCSS();
            $minifier->add($css);
            $content = $minifier->minify();
            $file = $base_dir."/uicore-".$post_id."-".$name.'.css';
            $fp = fopen($file, 'w');
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    public function delete_styles_file($post_id) {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'].'/uicore-blocks';
        if (!file_exists($base_dir)) {
            return;
        }
        $files = glob($base_dir.'/uicore-'.$post_id.'-*.css');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
new Core();
