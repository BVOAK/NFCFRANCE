<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MysqlndMsException;
function mysqlnd_ms_dump_servers($connection): array
{
 error_clear_last();
 $result = \mysqlnd_ms_dump_servers($connection);
 if ($result === false) {
 throw MysqlndMsException::createFromPhpError();
 }
 return $result;
}
function mysqlnd_ms_fabric_select_global($connection, $table_name): array
{
 error_clear_last();
 $result = \mysqlnd_ms_fabric_select_global($connection, $table_name);
 if ($result === false) {
 throw MysqlndMsException::createFromPhpError();
 }
 return $result;
}
function mysqlnd_ms_fabric_select_shard($connection, $table_name, $shard_key): array
{
 error_clear_last();
 $result = \mysqlnd_ms_fabric_select_shard($connection, $table_name, $shard_key);
 if ($result === false) {
 throw MysqlndMsException::createFromPhpError();
 }
 return $result;
}
function mysqlnd_ms_get_last_used_connection($connection): array
{
 error_clear_last();
 $result = \mysqlnd_ms_get_last_used_connection($connection);
 if ($result === false) {
 throw MysqlndMsException::createFromPhpError();
 }
 return $result;
}
