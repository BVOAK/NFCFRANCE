<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ApacheException;
function apache_get_version(): string
{
 error_clear_last();
 $result = \apache_get_version();
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
 return $result;
}
function apache_getenv(string $variable, bool $walk_to_top = false): string
{
 error_clear_last();
 $result = \apache_getenv($variable, $walk_to_top);
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
 return $result;
}
function apache_request_headers(): array
{
 error_clear_last();
 $result = \apache_request_headers();
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
 return $result;
}
function apache_reset_timeout(): void
{
 error_clear_last();
 $result = \apache_reset_timeout();
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
}
function apache_response_headers(): array
{
 error_clear_last();
 $result = \apache_response_headers();
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
 return $result;
}
function apache_setenv(string $variable, string $value, bool $walk_to_top = false): void
{
 error_clear_last();
 $result = \apache_setenv($variable, $value, $walk_to_top);
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
}
function getallheaders(): array
{
 error_clear_last();
 $result = \getallheaders();
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
 return $result;
}
function virtual(string $filename): void
{
 error_clear_last();
 $result = \virtual($filename);
 if ($result === false) {
 throw ApacheException::createFromPhpError();
 }
}
