<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\OpcacheException;
function opcache_compile_file(string $file): void
{
 error_clear_last();
 $result = \opcache_compile_file($file);
 if ($result === false) {
 throw OpcacheException::createFromPhpError();
 }
}
function opcache_get_status(bool $get_scripts = true): array
{
 error_clear_last();
 $result = \opcache_get_status($get_scripts);
 if ($result === false) {
 throw OpcacheException::createFromPhpError();
 }
 return $result;
}
