<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MysqlndQcException;
function mysqlnd_qc_clear_cache(): void
{
 error_clear_last();
 $result = \mysqlnd_qc_clear_cache();
 if ($result === false) {
 throw MysqlndQcException::createFromPhpError();
 }
}
function mysqlnd_qc_set_is_select(string $callback)
{
 error_clear_last();
 $result = \mysqlnd_qc_set_is_select($callback);
 if ($result === false) {
 throw MysqlndQcException::createFromPhpError();
 }
 return $result;
}
function mysqlnd_qc_set_storage_handler(string $handler): void
{
 error_clear_last();
 $result = \mysqlnd_qc_set_storage_handler($handler);
 if ($result === false) {
 throw MysqlndQcException::createFromPhpError();
 }
}
