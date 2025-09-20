<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ReadlineException;
function readline_add_history(string $line): void
{
 error_clear_last();
 $result = \readline_add_history($line);
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
function readline_callback_handler_install(string $prompt, callable $callback): void
{
 error_clear_last();
 $result = \readline_callback_handler_install($prompt, $callback);
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
function readline_clear_history(): void
{
 error_clear_last();
 $result = \readline_clear_history();
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
function readline_completion_function(callable $function): void
{
 error_clear_last();
 $result = \readline_completion_function($function);
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
function readline_read_history(string $filename = null): void
{
 error_clear_last();
 if ($filename !== null) {
 $result = \readline_read_history($filename);
 } else {
 $result = \readline_read_history();
 }
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
function readline_write_history(string $filename = null): void
{
 error_clear_last();
 if ($filename !== null) {
 $result = \readline_write_history($filename);
 } else {
 $result = \readline_write_history();
 }
 if ($result === false) {
 throw ReadlineException::createFromPhpError();
 }
}
