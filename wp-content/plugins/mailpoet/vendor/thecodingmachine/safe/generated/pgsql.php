<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\PgsqlException;
function pg_cancel_query($connection): void
{
 error_clear_last();
 $result = \pg_cancel_query($connection);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_client_encoding($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_client_encoding($connection);
 } else {
 $result = \pg_client_encoding();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_close($connection = null): void
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_close($connection);
 } else {
 $result = \pg_close();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_connect(string $connection_string, int $connect_type = null)
{
 error_clear_last();
 if ($connect_type !== null) {
 $result = \pg_connect($connection_string, $connect_type);
 } else {
 $result = \pg_connect($connection_string);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_connection_reset($connection): void
{
 error_clear_last();
 $result = \pg_connection_reset($connection);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_convert($connection, string $table_name, array $assoc_array, int $options = 0): array
{
 error_clear_last();
 $result = \pg_convert($connection, $table_name, $assoc_array, $options);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_copy_from($connection, string $table_name, array $rows, string $delimiter = null, string $null_as = null): void
{
 error_clear_last();
 if ($null_as !== null) {
 $result = \pg_copy_from($connection, $table_name, $rows, $delimiter, $null_as);
 } elseif ($delimiter !== null) {
 $result = \pg_copy_from($connection, $table_name, $rows, $delimiter);
 } else {
 $result = \pg_copy_from($connection, $table_name, $rows);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_copy_to($connection, string $table_name, string $delimiter = null, string $null_as = null): array
{
 error_clear_last();
 if ($null_as !== null) {
 $result = \pg_copy_to($connection, $table_name, $delimiter, $null_as);
 } elseif ($delimiter !== null) {
 $result = \pg_copy_to($connection, $table_name, $delimiter);
 } else {
 $result = \pg_copy_to($connection, $table_name);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_dbname($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_dbname($connection);
 } else {
 $result = \pg_dbname();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_delete($connection, string $table_name, array $assoc_array, int $options = PGSQL_DML_EXEC)
{
 error_clear_last();
 $result = \pg_delete($connection, $table_name, $assoc_array, $options);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_end_copy($connection = null): void
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_end_copy($connection);
 } else {
 $result = \pg_end_copy();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_execute($connection = null, string $stmtname = null, array $params = null)
{
 error_clear_last();
 if ($params !== null) {
 $result = \pg_execute($connection, $stmtname, $params);
 } elseif ($stmtname !== null) {
 $result = \pg_execute($connection, $stmtname);
 } elseif ($connection !== null) {
 $result = \pg_execute($connection);
 } else {
 $result = \pg_execute();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_field_name($result, int $field_number): string
{
 error_clear_last();
 $result = \pg_field_name($result, $field_number);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_field_table($result, int $field_number, bool $oid_only = false)
{
 error_clear_last();
 $result = \pg_field_table($result, $field_number, $oid_only);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_field_type($result, int $field_number): string
{
 error_clear_last();
 $result = \pg_field_type($result, $field_number);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_flush($connection)
{
 error_clear_last();
 $result = \pg_flush($connection);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_free_result($result): void
{
 error_clear_last();
 $result = \pg_free_result($result);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_host($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_host($connection);
 } else {
 $result = \pg_host();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_insert($connection, string $table_name, array $assoc_array, int $options = PGSQL_DML_EXEC)
{
 error_clear_last();
 $result = \pg_insert($connection, $table_name, $assoc_array, $options);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_last_error($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_last_error($connection);
 } else {
 $result = \pg_last_error();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_last_notice($connection, int $option = PGSQL_NOTICE_LAST): string
{
 error_clear_last();
 $result = \pg_last_notice($connection, $option);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_last_oid($result): string
{
 error_clear_last();
 $result = \pg_last_oid($result);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_lo_close($large_object): void
{
 error_clear_last();
 $result = \pg_lo_close($large_object);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_lo_export($connection = null, int $oid = null, string $pathname = null): void
{
 error_clear_last();
 if ($pathname !== null) {
 $result = \pg_lo_export($connection, $oid, $pathname);
 } elseif ($oid !== null) {
 $result = \pg_lo_export($connection, $oid);
 } elseif ($connection !== null) {
 $result = \pg_lo_export($connection);
 } else {
 $result = \pg_lo_export();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_lo_import($connection = null, string $pathname = null, $object_id = null): int
{
 error_clear_last();
 if ($object_id !== null) {
 $result = \pg_lo_import($connection, $pathname, $object_id);
 } elseif ($pathname !== null) {
 $result = \pg_lo_import($connection, $pathname);
 } elseif ($connection !== null) {
 $result = \pg_lo_import($connection);
 } else {
 $result = \pg_lo_import();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_lo_open($connection, int $oid, string $mode)
{
 error_clear_last();
 $result = \pg_lo_open($connection, $oid, $mode);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_lo_read_all($large_object): int
{
 error_clear_last();
 $result = \pg_lo_read_all($large_object);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_lo_read($large_object, int $len = 8192): string
{
 error_clear_last();
 $result = \pg_lo_read($large_object, $len);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_lo_seek($large_object, int $offset, int $whence = PGSQL_SEEK_CUR): void
{
 error_clear_last();
 $result = \pg_lo_seek($large_object, $offset, $whence);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_lo_truncate($large_object, int $size): void
{
 error_clear_last();
 $result = \pg_lo_truncate($large_object, $size);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_lo_unlink($connection, int $oid): void
{
 error_clear_last();
 $result = \pg_lo_unlink($connection, $oid);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_lo_write($large_object, string $data, int $len = null): int
{
 error_clear_last();
 if ($len !== null) {
 $result = \pg_lo_write($large_object, $data, $len);
 } else {
 $result = \pg_lo_write($large_object, $data);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_meta_data($connection, string $table_name, bool $extended = false): array
{
 error_clear_last();
 $result = \pg_meta_data($connection, $table_name, $extended);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_options($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_options($connection);
 } else {
 $result = \pg_options();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_parameter_status($connection = null, string $param_name = null): string
{
 error_clear_last();
 if ($param_name !== null) {
 $result = \pg_parameter_status($connection, $param_name);
 } elseif ($connection !== null) {
 $result = \pg_parameter_status($connection);
 } else {
 $result = \pg_parameter_status();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_pconnect(string $connection_string, int $connect_type = null)
{
 error_clear_last();
 if ($connect_type !== null) {
 $result = \pg_pconnect($connection_string, $connect_type);
 } else {
 $result = \pg_pconnect($connection_string);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_ping($connection = null): void
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_ping($connection);
 } else {
 $result = \pg_ping();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_port($connection = null): int
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_port($connection);
 } else {
 $result = \pg_port();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_prepare($connection = null, string $stmtname = null, string $query = null)
{
 error_clear_last();
 if ($query !== null) {
 $result = \pg_prepare($connection, $stmtname, $query);
 } elseif ($stmtname !== null) {
 $result = \pg_prepare($connection, $stmtname);
 } elseif ($connection !== null) {
 $result = \pg_prepare($connection);
 } else {
 $result = \pg_prepare();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_put_line($connection = null, string $data = null): void
{
 error_clear_last();
 if ($data !== null) {
 $result = \pg_put_line($connection, $data);
 } elseif ($connection !== null) {
 $result = \pg_put_line($connection);
 } else {
 $result = \pg_put_line();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_query_params($connection = null, string $query = null, array $params = null)
{
 error_clear_last();
 if ($params !== null) {
 $result = \pg_query_params($connection, $query, $params);
 } elseif ($query !== null) {
 $result = \pg_query_params($connection, $query);
 } elseif ($connection !== null) {
 $result = \pg_query_params($connection);
 } else {
 $result = \pg_query_params();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_query($connection = null, string $query = null)
{
 error_clear_last();
 if ($query !== null) {
 $result = \pg_query($connection, $query);
 } elseif ($connection !== null) {
 $result = \pg_query($connection);
 } else {
 $result = \pg_query();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_result_error_field($result, int $fieldcode): ?string
{
 error_clear_last();
 $result = \pg_result_error_field($result, $fieldcode);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_result_seek($result, int $offset): void
{
 error_clear_last();
 $result = \pg_result_seek($result, $offset);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_select($connection, string $table_name, array $assoc_array, int $options = PGSQL_DML_EXEC, int $result_type = PGSQL_ASSOC)
{
 error_clear_last();
 $result = \pg_select($connection, $table_name, $assoc_array, $options, $result_type);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_send_execute($connection, string $stmtname, array $params): void
{
 error_clear_last();
 $result = \pg_send_execute($connection, $stmtname, $params);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_send_prepare($connection, string $stmtname, string $query): void
{
 error_clear_last();
 $result = \pg_send_prepare($connection, $stmtname, $query);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_send_query_params($connection, string $query, array $params): void
{
 error_clear_last();
 $result = \pg_send_query_params($connection, $query, $params);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_send_query($connection, string $query): void
{
 error_clear_last();
 $result = \pg_send_query($connection, $query);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_socket($connection)
{
 error_clear_last();
 $result = \pg_socket($connection);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_trace(string $pathname, string $mode = "w", $connection = null): void
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_trace($pathname, $mode, $connection);
 } else {
 $result = \pg_trace($pathname, $mode);
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
}
function pg_tty($connection = null): string
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_tty($connection);
 } else {
 $result = \pg_tty();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_update($connection, string $table_name, array $data, array $condition, int $options = PGSQL_DML_EXEC)
{
 error_clear_last();
 $result = \pg_update($connection, $table_name, $data, $condition, $options);
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
function pg_version($connection = null): array
{
 error_clear_last();
 if ($connection !== null) {
 $result = \pg_version($connection);
 } else {
 $result = \pg_version();
 }
 if ($result === false) {
 throw PgsqlException::createFromPhpError();
 }
 return $result;
}
