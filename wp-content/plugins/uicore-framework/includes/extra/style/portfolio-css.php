<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= '

.uicore-portfolio-img-container{
    border-radius:' . $json_settings['portfolio_img_radius'] . 'px;
}

@media (max-width: ' . $br_points['lg'] .'px) {
    .uicore-portfolio:not(.elementor-page) #main.uicore{
        padding:' . $json_settings['portfolio_padding']['t'] . 'px 0px;
    }
}

@media (max-width: ' .  $br_points['md'] . 'px) {
    .uicore-portfolio:not(.elementor-page) #main.uicore{
        padding:' . $json_settings['portfolio_padding']['m'] . 'px 0px;
    }
}

@media (min-width: ' . $br_points['lg'] .  'px) {
    .uicore-portfolio:not(.elementor-page) #main.uicore{
        padding:' . $json_settings['portfolio_padding']['d'] . 'px 0px;
    }
    .uicore-portfolio.elementor-page .uicore-sidebar {
        padding-top:' . $json_settings['portfolio_padding']['d'] . 'px;
        padding-bottom:' . $json_settings['portfolio_padding']['d'] . 'px;
    }
}
';


//animations
$css .= $this->grid_animation('portfolio');
