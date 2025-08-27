<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= '
.ui-author-box{
    display:flex;
    gap:25px;
    padding:10px 0;
    align-items: center;
}
.ui-author-box > span {
	display: flex;
    flex-direction: column;
    gap: 7px;
}
.ui-author-box h4{
    margin: 0;
	color:var(--uicore-headline-color);
}
.ui-author-box h4 a, .ui-author-box .author-url{
	color:var(--uicore-headline-color);
}
.ui-author-box h4 a:hover, .ui-author-box .author-url:hover{
	color:var(--uicore-primary-color);
}
.ui-author-box p{
    margin: 0;
	font-size: clamp(12px, 85%, 15px);
}
.ui-author-box img{
    max-width: 105px;
    border-radius:var(--ui-radius);
}
.ui-author-box .author-url{
    font-size: clamp(12px, 85%, 15px);
	display: block;
    margin: 0;
    font-weight: 600;
}
@media (max-width: ' . $br_points['md'] . 'px) {
    .ui-author-box{
        flex-direction:column;
        text-align:center;
        gap:10px;
    }
}
';

if($json_settings['blogs_author_style'] === 'boxed'){
    $css .= '
    .ui-author-box{
        margin-top:27px;
        padding:30px;
        background:var(--uicore-light-color);

    }
    ';
}
