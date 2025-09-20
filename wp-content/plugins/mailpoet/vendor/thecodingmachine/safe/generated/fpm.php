<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FpmException;
function fastcgi_finish_request(): void
{
 error_clear_last();
 $result = \fastcgi_finish_request();
 if ($result === false) {
 throw FpmException::createFromPhpError();
 }
}
