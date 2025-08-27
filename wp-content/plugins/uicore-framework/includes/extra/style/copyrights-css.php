<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS


if ($json_settings['copyrights_border'] == 'true' && $json_settings['copyrights_border_whide'] == 'false') {
    $css .=
        '
    .uicore-copyrights .uicore-copyrights-wrapper{
        border-top:1px solid ' . $this->color($json_settings['copyrights_borderc']) .' ;
    }';
}

if ($json_settings['copyrights_border'] == 'true' && $json_settings['copyrights_border_whide'] == 'true') {
    $css .=
        '
    .uicore-copyrights{
        border-top:1px solid ' . $this->color($json_settings['copyrights_borderc']) . ' ;
    }';
}

$css .= '
.uicore-copyrights{
    background-color:' . $this->color($json_settings['copyrights_bg']) . ';
}
.uicore-copyrights .uicore-copyrights-wrapper{
    color: ' .$this->color($json_settings['copyrights_text']) . ';
}
.uicore-copyrights .uicore a{
    color: ' . $this->color($json_settings['copyrights_link']['m']) . ';
}
.uicore-copyrights .uicore a:hover{
    color: ' . $this->color($json_settings['copyrights_link']['h']) . ';
}


@media (max-width: ' .$br_points['lg'] .'px) {
    .uicore-copyrights .uicore-copyrights-wrapper{
        padding: ' .$json_settings['copyrights_padding']['t'] .'px  0px;
    }
    .uicore-copyrights .uicore-copy-content *, .uicore-copy-socials{
        font-size: ' .$json_settings['copyrights_texts']['t'] .'px;
    }
}

@media (min-width: ' .$br_points['lg'] .'px) {
    .uicore-copyrights .uicore-copyrights-wrapper{
        padding: ' .$json_settings['copyrights_padding']['d'] .'px  0px;
    }
    .uicore-copyrights .uicore-copy-content *, .uicore-copy-socials{
        font-size: ' .$json_settings['copyrights_texts']['d'] .'px;
    }
}

@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-copyrights .uicore-copyrights-wrapper{
        padding-top: ' . $json_settings['copyrights_padding']['m'] . 'px;
        padding-bottom: ' . $json_settings['copyrights_padding']['m'] . 'px;
    }
    .uicore-copyrights .uicore-copy-content *, .uicore-copy-socials{
        font-size: ' .  $json_settings['copyrights_texts']['m'] . 'px;
    }

}
';
