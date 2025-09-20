<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\CubridException;
function cubrid_free_result($req_identifier): void
{
 error_clear_last();
 $result = \cubrid_free_result($req_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
}
function cubrid_get_charset($conn_identifier): string
{
 error_clear_last();
 $result = \cubrid_get_charset($conn_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_get_client_info(): string
{
 error_clear_last();
 $result = \cubrid_get_client_info();
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_get_db_parameter($conn_identifier): array
{
 error_clear_last();
 $result = \cubrid_get_db_parameter($conn_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_get_server_info($conn_identifier): string
{
 error_clear_last();
 $result = \cubrid_get_server_info($conn_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_insert_id($conn_identifier = null): string
{
 error_clear_last();
 if ($conn_identifier !== null) {
 $result = \cubrid_insert_id($conn_identifier);
 } else {
 $result = \cubrid_insert_id();
 }
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_lob2_new($conn_identifier = null, string $type = "BLOB")
{
 error_clear_last();
 if ($type !== "BLOB") {
 $result = \cubrid_lob2_new($conn_identifier, $type);
 } elseif ($conn_identifier !== null) {
 $result = \cubrid_lob2_new($conn_identifier);
 } else {
 $result = \cubrid_lob2_new();
 }
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_lob2_size($lob_identifier): int
{
 error_clear_last();
 $result = \cubrid_lob2_size($lob_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_lob2_size64($lob_identifier): string
{
 error_clear_last();
 $result = \cubrid_lob2_size64($lob_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_lob2_tell($lob_identifier): int
{
 error_clear_last();
 $result = \cubrid_lob2_tell($lob_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_lob2_tell64($lob_identifier): string
{
 error_clear_last();
 $result = \cubrid_lob2_tell64($lob_identifier);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
 return $result;
}
function cubrid_set_db_parameter($conn_identifier, int $param_type, int $param_value): void
{
 error_clear_last();
 $result = \cubrid_set_db_parameter($conn_identifier, $param_type, $param_value);
 if ($result === false) {
 throw CubridException::createFromPhpError();
 }
}
