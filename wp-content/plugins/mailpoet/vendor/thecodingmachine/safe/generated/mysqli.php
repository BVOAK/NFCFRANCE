<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MysqliException;
function mysqli_get_cache_stats(): array
{
 error_clear_last();
 $result = \mysqli_get_cache_stats();
 if ($result === false) {
 throw MysqliException::createFromPhpError();
 }
 return $result;
}
function mysqli_get_client_stats(): array
{
 error_clear_last();
 $result = \mysqli_get_client_stats();
 if ($result === false) {
 throw MysqliException::createFromPhpError();
 }
 return $result;
}
