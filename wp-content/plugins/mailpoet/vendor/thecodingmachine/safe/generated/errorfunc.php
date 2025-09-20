<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ErrorfuncException;
function error_log(string $message, int $message_type = 0, string $destination = null, string $extra_headers = null): void
{
 error_clear_last();
 if ($extra_headers !== null) {
 $result = \error_log($message, $message_type, $destination, $extra_headers);
 } elseif ($destination !== null) {
 $result = \error_log($message, $message_type, $destination);
 } else {
 $result = \error_log($message, $message_type);
 }
 if ($result === false) {
 throw ErrorfuncException::createFromPhpError();
 }
}
