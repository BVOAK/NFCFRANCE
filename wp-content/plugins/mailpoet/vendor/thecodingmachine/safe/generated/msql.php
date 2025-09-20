<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MsqlException;
function msql_affected_rows($result): int
{
 error_clear_last();
 $result = \msql_affected_rows($result);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_close($link_identifier = null): void
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_close($link_identifier);
 } else {
 $result = \msql_close();
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_connect(string $hostname = null)
{
 error_clear_last();
 if ($hostname !== null) {
 $result = \msql_connect($hostname);
 } else {
 $result = \msql_connect();
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_create_db(string $database_name, $link_identifier = null): void
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_create_db($database_name, $link_identifier);
 } else {
 $result = \msql_create_db($database_name);
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_data_seek($result, int $row_number): void
{
 error_clear_last();
 $result = \msql_data_seek($result, $row_number);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_db_query(string $database, string $query, $link_identifier = null)
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_db_query($database, $query, $link_identifier);
 } else {
 $result = \msql_db_query($database, $query);
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_drop_db(string $database_name, $link_identifier = null): void
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_drop_db($database_name, $link_identifier);
 } else {
 $result = \msql_drop_db($database_name);
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_field_len($result, int $field_offset): int
{
 error_clear_last();
 $result = \msql_field_len($result, $field_offset);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_field_name($result, int $field_offset): string
{
 error_clear_last();
 $result = \msql_field_name($result, $field_offset);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_field_seek($result, int $field_offset): void
{
 error_clear_last();
 $result = \msql_field_seek($result, $field_offset);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_field_table($result, int $field_offset): int
{
 error_clear_last();
 $result = \msql_field_table($result, $field_offset);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_field_type($result, int $field_offset): string
{
 error_clear_last();
 $result = \msql_field_type($result, $field_offset);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_free_result($result): void
{
 error_clear_last();
 $result = \msql_free_result($result);
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
function msql_pconnect(string $hostname = null)
{
 error_clear_last();
 if ($hostname !== null) {
 $result = \msql_pconnect($hostname);
 } else {
 $result = \msql_pconnect();
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_query(string $query, $link_identifier = null)
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_query($query, $link_identifier);
 } else {
 $result = \msql_query($query);
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
 return $result;
}
function msql_select_db(string $database_name, $link_identifier = null): void
{
 error_clear_last();
 if ($link_identifier !== null) {
 $result = \msql_select_db($database_name, $link_identifier);
 } else {
 $result = \msql_select_db($database_name);
 }
 if ($result === false) {
 throw MsqlException::createFromPhpError();
 }
}
