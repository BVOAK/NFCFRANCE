<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\UodbcException;
function odbc_autocommit($connection_id, bool $OnOff = false)
{
 error_clear_last();
 $result = \odbc_autocommit($connection_id, $OnOff);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_binmode(int $result_id, int $mode): void
{
 error_clear_last();
 $result = \odbc_binmode($result_id, $mode);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_columnprivileges($connection_id, string $catalog, string $schema, string $table_name, string $column_name)
{
 error_clear_last();
 $result = \odbc_columnprivileges($connection_id, $catalog, $schema, $table_name, $column_name);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_columns($connection_id, string $catalog = null, string $schema = null, string $table_name = null, string $column_name = null)
{
 error_clear_last();
 if ($column_name !== null) {
 $result = \odbc_columns($connection_id, $catalog, $schema, $table_name, $column_name);
 } elseif ($table_name !== null) {
 $result = \odbc_columns($connection_id, $catalog, $schema, $table_name);
 } elseif ($schema !== null) {
 $result = \odbc_columns($connection_id, $catalog, $schema);
 } elseif ($catalog !== null) {
 $result = \odbc_columns($connection_id, $catalog);
 } else {
 $result = \odbc_columns($connection_id);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_commit($connection_id): void
{
 error_clear_last();
 $result = \odbc_commit($connection_id);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_data_source($connection_id, int $fetch_type): array
{
 error_clear_last();
 $result = \odbc_data_source($connection_id, $fetch_type);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_exec($connection_id, string $query_string, int $flags = null)
{
 error_clear_last();
 if ($flags !== null) {
 $result = \odbc_exec($connection_id, $query_string, $flags);
 } else {
 $result = \odbc_exec($connection_id, $query_string);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_execute($result_id, array $parameters_array = null): void
{
 error_clear_last();
 if ($parameters_array !== null) {
 $result = \odbc_execute($result_id, $parameters_array);
 } else {
 $result = \odbc_execute($result_id);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_fetch_into($result_id, ?array &$result_array, int $rownumber = null): int
{
 error_clear_last();
 if ($rownumber !== null) {
 $result = \odbc_fetch_into($result_id, $result_array, $rownumber);
 } else {
 $result = \odbc_fetch_into($result_id, $result_array);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_field_len($result_id, int $field_number): int
{
 error_clear_last();
 $result = \odbc_field_len($result_id, $field_number);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_field_name($result_id, int $field_number): string
{
 error_clear_last();
 $result = \odbc_field_name($result_id, $field_number);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_field_num($result_id, string $field_name): int
{
 error_clear_last();
 $result = \odbc_field_num($result_id, $field_name);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_field_scale($result_id, int $field_number): int
{
 error_clear_last();
 $result = \odbc_field_scale($result_id, $field_number);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_field_type($result_id, int $field_number): string
{
 error_clear_last();
 $result = \odbc_field_type($result_id, $field_number);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_foreignkeys($connection_id, string $pk_catalog, string $pk_schema, string $pk_table, string $fk_catalog, string $fk_schema, string $fk_table)
{
 error_clear_last();
 $result = \odbc_foreignkeys($connection_id, $pk_catalog, $pk_schema, $pk_table, $fk_catalog, $fk_schema, $fk_table);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_gettypeinfo($connection_id, int $data_type = null)
{
 error_clear_last();
 if ($data_type !== null) {
 $result = \odbc_gettypeinfo($connection_id, $data_type);
 } else {
 $result = \odbc_gettypeinfo($connection_id);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_longreadlen($result_id, int $length): void
{
 error_clear_last();
 $result = \odbc_longreadlen($result_id, $length);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_prepare($connection_id, string $query_string)
{
 error_clear_last();
 $result = \odbc_prepare($connection_id, $query_string);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_primarykeys($connection_id, string $catalog, string $schema, string $table)
{
 error_clear_last();
 $result = \odbc_primarykeys($connection_id, $catalog, $schema, $table);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_result_all($result_id, string $format = null): int
{
 error_clear_last();
 if ($format !== null) {
 $result = \odbc_result_all($result_id, $format);
 } else {
 $result = \odbc_result_all($result_id);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_result($result_id, $field)
{
 error_clear_last();
 $result = \odbc_result($result_id, $field);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_rollback($connection_id): void
{
 error_clear_last();
 $result = \odbc_rollback($connection_id);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_setoption($id, int $function, int $option, int $param): void
{
 error_clear_last();
 $result = \odbc_setoption($id, $function, $option, $param);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
}
function odbc_specialcolumns($connection_id, int $type, string $catalog, string $schema, string $table, int $scope, int $nullable)
{
 error_clear_last();
 $result = \odbc_specialcolumns($connection_id, $type, $catalog, $schema, $table, $scope, $nullable);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_statistics($connection_id, string $catalog, string $schema, string $table_name, int $unique, int $accuracy)
{
 error_clear_last();
 $result = \odbc_statistics($connection_id, $catalog, $schema, $table_name, $unique, $accuracy);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_tableprivileges($connection_id, string $catalog, string $schema, string $name)
{
 error_clear_last();
 $result = \odbc_tableprivileges($connection_id, $catalog, $schema, $name);
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
function odbc_tables($connection_id, string $catalog = null, string $schema = null, string $name = null, string $types = null)
{
 error_clear_last();
 if ($types !== null) {
 $result = \odbc_tables($connection_id, $catalog, $schema, $name, $types);
 } elseif ($name !== null) {
 $result = \odbc_tables($connection_id, $catalog, $schema, $name);
 } elseif ($schema !== null) {
 $result = \odbc_tables($connection_id, $catalog, $schema);
 } elseif ($catalog !== null) {
 $result = \odbc_tables($connection_id, $catalog);
 } else {
 $result = \odbc_tables($connection_id);
 }
 if ($result === false) {
 throw UodbcException::createFromPhpError();
 }
 return $result;
}
