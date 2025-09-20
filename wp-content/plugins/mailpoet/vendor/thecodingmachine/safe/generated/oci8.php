<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\Oci8Exception;
function oci_bind_array_by_name($statement, string $name, array &$var_array, int $max_table_length, int $max_item_length = -1, int $type = SQLT_AFC): void
{
 error_clear_last();
 $result = \oci_bind_array_by_name($statement, $name, $var_array, $max_table_length, $max_item_length, $type);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_bind_by_name($statement, string $bv_name, &$variable, int $maxlength = -1, int $type = SQLT_CHR): void
{
 error_clear_last();
 $result = \oci_bind_by_name($statement, $bv_name, $variable, $maxlength, $type);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_cancel($statement): void
{
 error_clear_last();
 $result = \oci_cancel($statement);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_close($connection): void
{
 error_clear_last();
 $result = \oci_close($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_commit($connection): void
{
 error_clear_last();
 $result = \oci_commit($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_connect(string $username, string $password, string $connection_string = null, string $character_set = null, int $session_mode = null)
{
 error_clear_last();
 if ($session_mode !== null) {
 $result = \oci_connect($username, $password, $connection_string, $character_set, $session_mode);
 } elseif ($character_set !== null) {
 $result = \oci_connect($username, $password, $connection_string, $character_set);
 } elseif ($connection_string !== null) {
 $result = \oci_connect($username, $password, $connection_string);
 } else {
 $result = \oci_connect($username, $password);
 }
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_define_by_name($statement, string $column_name, &$variable, int $type = SQLT_CHR): void
{
 error_clear_last();
 $result = \oci_define_by_name($statement, $column_name, $variable, $type);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_execute($statement, int $mode = OCI_COMMIT_ON_SUCCESS): void
{
 error_clear_last();
 $result = \oci_execute($statement, $mode);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_fetch_all($statement, ?array &$output, int $skip = 0, int $maxrows = -1, int $flags = OCI_FETCHSTATEMENT_BY_COLUMN + OCI_ASSOC): int
{
 error_clear_last();
 $result = \oci_fetch_all($statement, $output, $skip, $maxrows, $flags);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_name($statement, $field): string
{
 error_clear_last();
 $result = \oci_field_name($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_precision($statement, $field): int
{
 error_clear_last();
 $result = \oci_field_precision($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_scale($statement, $field): int
{
 error_clear_last();
 $result = \oci_field_scale($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_size($statement, $field): int
{
 error_clear_last();
 $result = \oci_field_size($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_type_raw($statement, $field): int
{
 error_clear_last();
 $result = \oci_field_type_raw($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_field_type($statement, $field)
{
 error_clear_last();
 $result = \oci_field_type($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_free_descriptor($descriptor): void
{
 error_clear_last();
 $result = \oci_free_descriptor($descriptor);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_free_statement($statement): void
{
 error_clear_last();
 $result = \oci_free_statement($statement);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_new_collection($connection, string $tdo, string $schema = null)
{
 error_clear_last();
 $result = \oci_new_collection($connection, $tdo, $schema);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_new_connect(string $username, string $password, string $connection_string = null, string $character_set = null, int $session_mode = null)
{
 error_clear_last();
 if ($session_mode !== null) {
 $result = \oci_new_connect($username, $password, $connection_string, $character_set, $session_mode);
 } elseif ($character_set !== null) {
 $result = \oci_new_connect($username, $password, $connection_string, $character_set);
 } elseif ($connection_string !== null) {
 $result = \oci_new_connect($username, $password, $connection_string);
 } else {
 $result = \oci_new_connect($username, $password);
 }
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_new_cursor($connection)
{
 error_clear_last();
 $result = \oci_new_cursor($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_new_descriptor($connection, int $type = OCI_DTYPE_LOB)
{
 error_clear_last();
 $result = \oci_new_descriptor($connection, $type);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_num_fields($statement): int
{
 error_clear_last();
 $result = \oci_num_fields($statement);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_num_rows($statement): int
{
 error_clear_last();
 $result = \oci_num_rows($statement);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_parse($connection, string $sql_text)
{
 error_clear_last();
 $result = \oci_parse($connection, $sql_text);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_pconnect(string $username, string $password, string $connection_string = null, string $character_set = null, int $session_mode = null)
{
 error_clear_last();
 if ($session_mode !== null) {
 $result = \oci_pconnect($username, $password, $connection_string, $character_set, $session_mode);
 } elseif ($character_set !== null) {
 $result = \oci_pconnect($username, $password, $connection_string, $character_set);
 } elseif ($connection_string !== null) {
 $result = \oci_pconnect($username, $password, $connection_string);
 } else {
 $result = \oci_pconnect($username, $password);
 }
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_result($statement, $field): string
{
 error_clear_last();
 $result = \oci_result($statement, $field);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_rollback($connection): void
{
 error_clear_last();
 $result = \oci_rollback($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_server_version($connection): string
{
 error_clear_last();
 $result = \oci_server_version($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_set_action($connection, string $action_name): void
{
 error_clear_last();
 $result = \oci_set_action($connection, $action_name);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_call_timeout($connection, int $time_out): void
{
 error_clear_last();
 $result = \oci_set_call_timeout($connection, $time_out);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_client_identifier($connection, string $client_identifier): void
{
 error_clear_last();
 $result = \oci_set_client_identifier($connection, $client_identifier);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_client_info($connection, string $client_info): void
{
 error_clear_last();
 $result = \oci_set_client_info($connection, $client_info);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_db_operation($connection, string $dbop): void
{
 error_clear_last();
 $result = \oci_set_db_operation($connection, $dbop);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_edition(string $edition): void
{
 error_clear_last();
 $result = \oci_set_edition($edition);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_module_name($connection, string $module_name): void
{
 error_clear_last();
 $result = \oci_set_module_name($connection, $module_name);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_set_prefetch($statement, int $rows): void
{
 error_clear_last();
 $result = \oci_set_prefetch($statement, $rows);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
function oci_statement_type($statement): string
{
 error_clear_last();
 $result = \oci_statement_type($statement);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
 return $result;
}
function oci_unregister_taf_callback($connection): void
{
 error_clear_last();
 $result = \oci_unregister_taf_callback($connection);
 if ($result === false) {
 throw Oci8Exception::createFromPhpError();
 }
}
