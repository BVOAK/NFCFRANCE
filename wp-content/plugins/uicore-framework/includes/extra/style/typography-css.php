<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .='    
body{
    ' . self::generate_typography_css('h1', $json_settings) . '
    ' . self::generate_typography_css('h2', $json_settings) . '
    ' . self::generate_typography_css('h3', $json_settings) . '
    ' . self::generate_typography_css('h4', $json_settings) . '
    ' . self::generate_typography_css('h5', $json_settings) . '
    ' . self::generate_typography_css('h6', $json_settings) . '
    ' . self::generate_typography_css('p', $json_settings) . '
}

@media (max-width: ' . $br_points['lg'] . 'px) {
    body{
        ' . self::get_size_and_unit($json_settings['h1']['s']['t'], '--uicore-typography--h1-s') . '
        ' . self::get_size_and_unit($json_settings['h2']['s']['t'], '--uicore-typography--h2-s') . '
        ' . self::get_size_and_unit($json_settings['h3']['s']['t'], '--uicore-typography--h3-s') . '
        ' . self::get_size_and_unit($json_settings['h4']['s']['t'], '--uicore-typography--h4-s') . '
        ' . self::get_size_and_unit($json_settings['h5']['s']['t'], '--uicore-typography--h5-s') . '
        ' . self::get_size_and_unit($json_settings['h6']['s']['t'], '--uicore-typography--h6-s') . '
        ' . self::get_size_and_unit($json_settings['p']['s']['t'], '--uicore-typography--p-s') . '
    }
    .uicore-single-header h1.entry-title{
        ' . self::get_size_and_unit($json_settings['h1']['s']['t'], '--uicore-typography--h1-s') . '
    }
    .uicore-blog .uicore-post-content:not(.uicore-archive) .entry-content{
        ' . self::get_size_and_unit($json_settings['blog_h1']['s']['t'], '--uicore-typography--h1-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h2']['s']['t'], '--uicore-typography--h2-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h3']['s']['t'], '--uicore-typography--h3-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h4']['s']['t'], '--uicore-typography--h4-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h5']['s']['t'], '--uicore-typography--h5-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h6']['s']['t'], '--uicore-typography--h6-s') . '
        ' . self::get_size_and_unit($json_settings['blog_p']['s']['t'], '--uicore-typography--p-s') . '
    }
    .uicore-blog-grid {
        --uicore-typography--blog_title-s:' . $json_settings['blog_title']['s']['t'] . 'px;
        --uicore-typography--p-s:' . $json_settings['blog_ex']['s']['t'] . 'px;
    }
}
@media (max-width: ' . $br_points['md'] . 'px) {
    body{
        ' . self::get_size_and_unit($json_settings['h1']['s']['m'], '--uicore-typography--h1-s') . '
        ' . self::get_size_and_unit($json_settings['h2']['s']['m'], '--uicore-typography--h2-s') . '
        ' . self::get_size_and_unit($json_settings['h3']['s']['m'], '--uicore-typography--h3-s') . '
        ' . self::get_size_and_unit($json_settings['h4']['s']['m'], '--uicore-typography--h4-s') . '
        ' . self::get_size_and_unit($json_settings['h5']['s']['m'], '--uicore-typography--h5-s') . '
        ' . self::get_size_and_unit($json_settings['h6']['s']['m'], '--uicore-typography--h6-s') . '
        ' . self::get_size_and_unit($json_settings['p']['s']['m'], '--uicore-typography--p-s') . '
    }
    .uicore-single-header h1.entry-title{
         ' . self::get_size_and_unit($json_settings['h1']['s']['m'], '--uicore-typography--h1-s') . '
    }
    .uicore-blog .uicore-post-content:not(.uicore-archive) .entry-content{
        ' . self::get_size_and_unit($json_settings['blog_h1']['s']['m'], '--uicore-typography--h1-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h2']['s']['m'], '--uicore-typography--h2-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h3']['s']['m'], '--uicore-typography--h3-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h4']['s']['m'], '--uicore-typography--h4-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h5']['s']['m'], '--uicore-typography--h5-s') . '
        ' . self::get_size_and_unit($json_settings['blog_h6']['s']['m'], '--uicore-typography--h6-s') . '
        ' . self::get_size_and_unit($json_settings['blog_p']['s']['m'], '--uicore-typography--p-s') . '
    }
    .uicore-blog-grid {
        ' . (!empty($json_settings['blog_title']['s']['m']) ? '--uicore-typography--blog_title-s:' . $json_settings['blog_title']['s']['m'] . 'px;' : '') . '
        ' . (!empty($json_settings['blog_ex']['s']['m']) ? '--uicore-typography--p-s:' . $json_settings['blog_ex']['s']['m'] . 'px;' : '') . '
    }
}
';