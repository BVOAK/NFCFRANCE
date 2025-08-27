<?php

namespace MatthiasMullie\Minify;

class CustomCSS extends \MatthiasMullie\Minify\CSS
{
    protected function getPathConverter($source, $target)
    {
        return new \MatthiasMullie\PathConverter\Converter($source, $target,'root');
    }
}
