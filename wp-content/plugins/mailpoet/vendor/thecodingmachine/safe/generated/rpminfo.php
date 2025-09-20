<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\RpminfoException;
function rpmaddtag(int $tag): void
{
 error_clear_last();
 $result = \rpmaddtag($tag);
 if ($result === false) {
 throw RpminfoException::createFromPhpError();
 }
}
