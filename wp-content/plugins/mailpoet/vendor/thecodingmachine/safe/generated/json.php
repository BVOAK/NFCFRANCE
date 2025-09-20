<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\JsonException;
function json_encode($value, int $options = 0, int $depth = 512): string
{
 error_clear_last();
 $result = \json_encode($value, $options, $depth);
 if ($result === false) {
 throw JsonException::createFromPhpError();
 }
 return $result;
}
function json_last_error_msg(): string
{
 error_clear_last();
 $result = \json_last_error_msg();
 if ($result === false) {
 throw JsonException::createFromPhpError();
 }
 return $result;
}
