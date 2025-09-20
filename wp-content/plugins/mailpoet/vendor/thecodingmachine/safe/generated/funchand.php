<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FunchandException;
function create_function(string $args, string $code): string
{
 error_clear_last();
 $result = \create_function($args, $code);
 if ($result === false) {
 throw FunchandException::createFromPhpError();
 }
 return $result;
}
function register_tick_function(callable $function, ...$params): void
{
 error_clear_last();
 if ($params !== []) {
 $result = \register_tick_function($function, ...$params);
 } else {
 $result = \register_tick_function($function);
 }
 if ($result === false) {
 throw FunchandException::createFromPhpError();
 }
}
