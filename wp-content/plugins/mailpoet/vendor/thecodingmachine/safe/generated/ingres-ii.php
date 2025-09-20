<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\IngresiiException;
function ingres_autocommit($link): void
{
 error_clear_last();
 $result = \ingres_autocommit($link);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_close($link): void
{
 error_clear_last();
 $result = \ingres_close($link);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_commit($link): void
{
 error_clear_last();
 $result = \ingres_commit($link);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_connect(string $database = null, string $username = null, string $password = null, array $options = null)
{
 error_clear_last();
 if ($options !== null) {
 $result = \ingres_connect($database, $username, $password, $options);
 } elseif ($password !== null) {
 $result = \ingres_connect($database, $username, $password);
 } elseif ($username !== null) {
 $result = \ingres_connect($database, $username);
 } elseif ($database !== null) {
 $result = \ingres_connect($database);
 } else {
 $result = \ingres_connect();
 }
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
 return $result;
}
function ingres_execute($result, array $params = null, string $types = null): void
{
 error_clear_last();
 if ($types !== null) {
 $result = \ingres_execute($result, $params, $types);
 } elseif ($params !== null) {
 $result = \ingres_execute($result, $params);
 } else {
 $result = \ingres_execute($result);
 }
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_field_name($result, int $index): string
{
 error_clear_last();
 $result = \ingres_field_name($result, $index);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
 return $result;
}
function ingres_field_type($result, int $index): string
{
 error_clear_last();
 $result = \ingres_field_type($result, $index);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
 return $result;
}
function ingres_free_result($result): void
{
 error_clear_last();
 $result = \ingres_free_result($result);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_pconnect(string $database = null, string $username = null, string $password = null, array $options = null)
{
 error_clear_last();
 if ($options !== null) {
 $result = \ingres_pconnect($database, $username, $password, $options);
 } elseif ($password !== null) {
 $result = \ingres_pconnect($database, $username, $password);
 } elseif ($username !== null) {
 $result = \ingres_pconnect($database, $username);
 } elseif ($database !== null) {
 $result = \ingres_pconnect($database);
 } else {
 $result = \ingres_pconnect();
 }
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
 return $result;
}
function ingres_result_seek($result, int $position): void
{
 error_clear_last();
 $result = \ingres_result_seek($result, $position);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_rollback($link): void
{
 error_clear_last();
 $result = \ingres_rollback($link);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
function ingres_set_environment($link, array $options): void
{
 error_clear_last();
 $result = \ingres_set_environment($link, $options);
 if ($result === false) {
 throw IngresiiException::createFromPhpError();
 }
}
