<?php
namespace UiCore;

defined('ABSPATH') || exit();

/**
 * General Functions
 */
class BlockEditorStyle
{

    public $settings;

    public $br;

    /**
     * Generate css from Theme Options for Block Editor
     *
     * @param $json_settings
     */
    public function __construct($json = null)
    {
        $json = $json ?? Settings::current_settings();
        $this->settings = $json;
        $this->generate_css($json);
        $this->generate_fonts_import($json);
    }

    public function fam($fam)
    {

        switch ($fam) {
            case "Primary":
                $font = $this->settings['pFont']['f'];
                break;
            case "Secondary":
                $font = $this->settings['sFont']['f'];
                break;
            case "Text":
                $font = $this->settings['tFont']['f'];
                break;
            case "Accent":
                $font = $this->settings['aFont']['f'];
                break;
            default :
                $font = $fam;
        }
        return $font;

    }

    function color($color)
    {
        //Color + Blur Migrate support
        if(!is_string($color) && isset($color['color'])){
            $color = $color['color'];
        }
        if ($color == 'Primary') {
            $color = $this->settings['pColor'];
        } else if ($color == 'Secondary') {
            $color = $this->settings['sColor'];
        } else if ($color == 'Accent') {
            $color = $this->settings['aColor'];
        } else if ($color == 'Headline') {
            $color = $this->settings['hColor'];
        } else if ($color == 'Body') {
            $color = $this->settings['bColor'];
        } else if ($color == 'Dark Neutral') {
            $color = $this->settings['dColor'];
        } else if ($color == 'Light Neutral') {
            $color = $this->settings['lColor'];
        } else if ($color == 'White') {
            $color = $this->settings['wColor'];
        }
        return $color;
    }

    /**
     * Generate css for Block Editor
     *
     * @param $json_settings
     * @return void
     */
    public function generate_css(array $json_settings)
    {
        if (class_exists('Elementor')) {
            $br_points = \Elementor\Core\Responsive\Responsive::get_breakpoints();
        } else {
            $br_points = [
                'sm' => '480',
                'md' => '767',
                'lg' => '1024',
            ];
        }
        $this->br = $br_points;


        //Backend Editor Style
        $blog_typo =
            '
        .post-type-post .editor-post-title__block .editor-post-title__input {
            font-family: ' .
            $this->fam($json_settings['blog_h1']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h1']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h1']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h1']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h1']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h1']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h1']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h1']['s']['d'], 'font-size') . ' !important;
        }


        .post-type-post .editor-styles-wrapper h1{
            font-family: ' .
            $this->fam($json_settings['blog_h1']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h1']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h1']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h1']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h1']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h1']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h1']['c']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h1']['s']['d'], 'font-size') . ' !important;
        }

        .post-type-post .editor-styles-wrapper h2 {
            font-family: ' .
            $this->fam($json_settings['blog_h2']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h2']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h2']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h2']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h2']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h2']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h2']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h2']['s']['d'], 'font-size') . ' !important;
        }

        .post-type-post .editor-styles-wrapper h3 {
            font-family: ' .
            $this->fam($json_settings['blog_h3']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h3']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h3']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h3']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h3']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h3']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h3']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h3']['s']['d'], 'font-size') . ' !important;
        }

        .post-type-post .editor-styles-wrapper h4 {
            font-family: ' .
            $this->fam($json_settings['blog_h4']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h4']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h4']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h4']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h4']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h4']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h4']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h4']['s']['d'], 'font-size') . ' !important;
        }

        .post-type-post  .editor-styles-wrapper h5 {
            font-family: ' .
            $this->fam($json_settings['blog_h5']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h5']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h5']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h5']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h5']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h5']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h5']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h5']['s']['d'], 'font-size') . ' !important;
        }

        .post-type-post .editor-styles-wrapper h6 {
            font-family: ' .
            $this->fam($json_settings['blog_h6']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_h6']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h6']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_h6']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_h6']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_h6']) .
            ';
            color: ' .
            $this->color($json_settings['blog_h6']['c']).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_h6']['s']['d'], 'font-size') . ' !important;
        }

		.post-type-post .editor-styles-wrapper,
        .post-type-post .editor-styles-wrapper p,
		.post-type-post .editor-styles-wrapper ol,
		.post-type-post .editor-styles-wrapper ul{
            font-family: ' .
            $this->fam($json_settings['blog_p']['f']) .
            ';
            font-weight: ' .
            $this->wt($json_settings['blog_p']) .
            ';
            ' . $this->get_size_and_unit($json_settings['blog_p']['h']['d'], 'line-height') . ' !important;
            ' . $this->get_size_and_unit($json_settings['blog_p']['ls']['d'], 'letter-spacing') . ' !important;
            text-transform: ' .
            $json_settings['blog_p']['t'] .
            ';
            font-style: ' .
            $this->st($json_settings['blog_p']) .
            ';
            color: ' .
            $this->color($json_settings['blog_p']['c'] ).
            ';
            ' . $this->get_size_and_unit($json_settings['blog_p']['s']['d'], 'font-size') . ' !important;
        }
		.post-type-post .editor-styles-wrapper table{
		border-color: '.$json_settings['lColor'] .'
		}
        .post-type-post .editor-styles-wrapper{
            max-width:' .
            $json_settings['gen_full_w'] .
            'px!important;
            width:100%;
            margin: 0 auto;
        }
        .post-type-post .editor-styles-wrapper a{
            color:' .
            $json_settings['pColor'] .
            '!important;
            text-decoration:none;
        }
        .post-type-post .editor-styles-wrapper a:hover{
            color:' .
            $json_settings['sColor'] .
            '!important;
            text-decoration:none;
        }
        ';
        //removed for better blocks compatibility
        // .post-type-post .editor-styles-wrapper .editor-post-title, .editor-styles-wrapper .wp-block{
        //     padding: 0!important;
        // }

        $blog_conditional = '';
        $general_bg = '';
        if ($json_settings['gen_layout'] == 'boxed') {
            $general_bg .=
                '
            .is-root-container {
                max-width: ' . $json_settings['gen_boxed_w'] . 'px;
                margin:0 auto;
                background-color: ' .$this->color($json_settings['gen_boxed_bg']) .';
            } ';
        }
        if ($json_settings['gen_bg']['type'] == 'solid') {
            $general_bg .=
                '
            .editor-styles-wrapper {
                background-color: ' .
                $json_settings['gen_bg']['solid'] .
                ';
            } ';
        }elseif ($json_settings['gen_bg']['type'] == 'gradient') {
            $general_bg .=
                '
            .editor-styles-wrapper {
                background-image: linear-gradient(' .
                $json_settings['gen_bg']['gradient']['angle'] .
                'deg,' .
                $json_settings['gen_bg']['gradient']['color1'] .
                ', ' .
                $json_settings['gen_bg']['gradient']['color2'] .
                ');
            } ';
        }elseif ($json_settings['gen_bg']['type'] == 'image') {
            $general_bg .=
                '
            .editor-styles-wrapper {
                background: url(' .
                $json_settings['gen_bg']['image']['url'] .
                ') ' .
                $json_settings['gen_bg']['image']['position']['d'] .
                '/' .
                $json_settings['gen_bg']['image']['size']['d'] .
                ' ' .
                $json_settings['gen_bg']['image']['repeat'] .
                ' ' .
                $json_settings['gen_bg']['image']['attachment'] .
                ' ' .
                $json_settings['gen_bg']['solid'] .
                ';
            } ';
        }else{
            $general_bg .=
            '.editor-styles-wrapper {
                background: ' .$this->color( $json_settings['gen_bg']['type'] ).';
            } ';
        }
        

        if ($json_settings['blogs_narrow'] == 'true') {
            $blog_conditional .= '
            .post-type-post .wp-block {
                max-width: 65%
            }';
        } else {
            $blog_conditional .= '
            .post-type-post .wp-block {
                max-width: 100%
            }
            .post-type-post .block-editor-block-list__layout{
                padding: 0 36px;!important
            }';
        }
        if ($json_settings['blogs_sidebar_id'] != 'none') {
            $blog_conditional .= '
            .post-type-post .wp-block {
                max-width: 75%
            }';
        }

        $blog_overrides = '
        .wp-block-image {
          display: block !important;
        }

        .wp-block-image figcaption {
          display: block !important;
          font-size: 75% !important;
        }

        blockquote {
          margin: 20px auto !important;
          padding: 50px 70px !important;
          background-color: rgba(250,250,250,.9);
          border: none !important;
        }

        blockquote p {
          font-size: 120% !important;
          line-height: 1.5 !important;
          margin-bottom: 0 !important;
        }

        blockquote .wp-block-quote__citation {
          font-style: normal !important;
          font-size: inherit !important;
          padding-top: 15px !important;
          display: block !important;
          font-weight: bold !important;
        }

        blockquote .wp-block-quote__citation:before {
          content: "—" !important;
          margin-right: 10px !important;
        }

        ';

        $blog_css = $blog_typo . $general_bg . $blog_conditional . $blog_overrides;

        update_option('uicore_blog_css', $blog_css, false);
    }

    /**
     * Generate Fonts Family array from Theme Options
     *
     * @param $json_settings
     * @return void
     */
    public function generate_fonts_import($json_settings)
    {
        $blog_fonts =
            $this->f($json_settings['blog_h1']).
            $this->f($json_settings['blog_h2']).
            $this->f($json_settings['blog_h3']).
            $this->f($json_settings['blog_h4']).
            $this->f($json_settings['blog_h5']).
            $this->f($json_settings['blog_h6']).
            $this->f($json_settings['blog_p'], true);

        if (strlen($blog_fonts) > 0) {
            update_option('uicore_blog_fonts', '//fonts.googleapis.com/css?family=' . $blog_fonts);
        } else {
            update_option('uicore_blog_fonts', false);
        }

        $blog_grid_fonts =
        $this->f($json_settings['blog_title']).
        $this->f($json_settings['blog_ex']);

        if (strlen($blog_grid_fonts) > 0) {
            update_option('uicore_blog_grid_fonts', '//fonts.googleapis.com/css?family=' . $blog_grid_fonts );
        } else {
            update_option('uicore_blog_grid_fonts', false);
        }


        $front_fonts = [];
        $front_fonts[] = $this->f2($json_settings['menu_typo']);
        $front_fonts[] = $this->f2($json_settings['h1']);
        $front_fonts[] = $this->f2($json_settings['h2']);
        $front_fonts[] = $this->f2($json_settings['h3']);
        $front_fonts[] = $this->f2($json_settings['h4']);
        $front_fonts[] = $this->f2($json_settings['h5']);
        $front_fonts[] = $this->f2($json_settings['h6']);
        $front_fonts[] = $this->f2($json_settings['p']);
        $front_fonts[] = $this->f2(['pFont']);
        $front_fonts[] = $this->f2(['sFont']);
        $front_fonts[] = $this->f2(['tFont']);
        $front_fonts[] = $this->f2(['aFont']);
        $front_fonts = array_filter($front_fonts);

        if (count( $front_fonts )) {
            update_option('uicore_fonts_array',  $front_fonts );
        } else {
            update_option('uicore_fonts_array', false);
        }
    }
    function f($font, $with_bold=false)
    {
        $non_google = ["Arial", "Tahoma", "Verdana","Helvetica", "Times New Roman", "Trebuchet MS","Georgia"];
        if (!in_array($font['f'], $non_google)) {
            $font_string = str_replace(' ', '+', $this->fam($font['f']) ) . ':' . $font['st'];
            if ($with_bold) {
                $font_string .= '|' . $this->fam($font['f']) . ':600';
            }
            return $font_string . '|';
        } else {
            return null;
        }
    }
    function f2($font)
    {
        $non_google = ["Arial", "Tahoma", "Verdana","Helvetica", "Times New Roman", "Trebuchet MS","Georgia"];
        if (isset($font['f']) && !in_array($font['f'], $non_google)) {
            return [
                'fam'   =>$this->fam($font['f']),
                'style' =>$font['st']
            ];
        } else {
            return null;
        }
    }

    function st($for)
    {
        if (strpos($for['st'], 'italic') !== false) {
            return 'italic';
        } else {
            return 'normal';
        }
    }
    function wt($for)
    {
        if ((strpos($for['st'], 'regular') !== false) ||(strpos($for['st'], 'normal') !== false)) {
            return 'normal';
        } else {
            if (strlen(str_replace('italic', '', $for['st'])) < 2) {
                return 'normal';
            } else {
                return str_replace('italic', '', $for['st']);
            }
        }
    }

    /**
     * Helper to return font-size CSS property with unit, or empty string if not set
     */
    public function get_size_and_unit($size, $prop)
    {
        if (!is_array($size) || !isset($size['value']) || $size['value'] === '') {
            return '';
        }
        if (isset($size['unit']) && $size['unit'] == 'ct') {
            return $prop . ':' . $size['value'] . ';';
        }
        return $prop . ':' . $size['value'] . $size['unit'] . ';';
    }

}
