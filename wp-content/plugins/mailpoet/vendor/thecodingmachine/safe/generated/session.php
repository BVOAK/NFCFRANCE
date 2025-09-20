<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SessionException;
function session_abort(): void
{
 error_clear_last();
 $result = \session_abort();
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_decode(string $data): void
{
 error_clear_last();
 $result = \session_decode($data);
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_destroy(): void
{
 error_clear_last();
 $result = \session_destroy();
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_regenerate_id(bool $delete_old_session = false): void
{
 error_clear_last();
 $result = \session_regenerate_id($delete_old_session);
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_reset(): void
{
 error_clear_last();
 $result = \session_reset();
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_unset(): void
{
 error_clear_last();
 $result = \session_unset();
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
function session_write_close(): void
{
 error_clear_last();
 $result = \session_write_close();
 if ($result === false) {
 throw SessionException::createFromPhpError();
 }
}
