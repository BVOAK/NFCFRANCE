<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\StreamException;
function stream_context_set_params($stream_or_context, array $params): void
{
 error_clear_last();
 $result = \stream_context_set_params($stream_or_context, $params);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_copy_to_stream($source, $dest, int $maxlength = -1, int $offset = 0): int
{
 error_clear_last();
 $result = \stream_copy_to_stream($source, $dest, $maxlength, $offset);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_filter_append($stream, string $filtername, int $read_write = null, $params = null)
{
 error_clear_last();
 if ($params !== null) {
 $result = \stream_filter_append($stream, $filtername, $read_write, $params);
 } elseif ($read_write !== null) {
 $result = \stream_filter_append($stream, $filtername, $read_write);
 } else {
 $result = \stream_filter_append($stream, $filtername);
 }
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_filter_prepend($stream, string $filtername, int $read_write = null, $params = null)
{
 error_clear_last();
 if ($params !== null) {
 $result = \stream_filter_prepend($stream, $filtername, $read_write, $params);
 } elseif ($read_write !== null) {
 $result = \stream_filter_prepend($stream, $filtername, $read_write);
 } else {
 $result = \stream_filter_prepend($stream, $filtername);
 }
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_filter_register(string $filtername, string $classname): void
{
 error_clear_last();
 $result = \stream_filter_register($filtername, $classname);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_filter_remove($stream_filter): void
{
 error_clear_last();
 $result = \stream_filter_remove($stream_filter);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_get_contents($handle, int $maxlength = -1, int $offset = -1): string
{
 error_clear_last();
 $result = \stream_get_contents($handle, $maxlength, $offset);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_isatty($stream): void
{
 error_clear_last();
 $result = \stream_isatty($stream);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_resolve_include_path(string $filename): string
{
 error_clear_last();
 $result = \stream_resolve_include_path($filename);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_set_blocking($stream, bool $mode): void
{
 error_clear_last();
 $result = \stream_set_blocking($stream, $mode);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_set_timeout($stream, int $seconds, int $microseconds = 0): void
{
 error_clear_last();
 $result = \stream_set_timeout($stream, $seconds, $microseconds);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_socket_accept($server_socket, float $timeout = null, ?string &$peername = null)
{
 error_clear_last();
 if ($peername !== null) {
 $result = \stream_socket_accept($server_socket, $timeout, $peername);
 } elseif ($timeout !== null) {
 $result = \stream_socket_accept($server_socket, $timeout);
 } else {
 $result = \stream_socket_accept($server_socket);
 }
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_socket_client(string $remote_socket, int &$errno = null, string &$errstr = null, float $timeout = null, int $flags = STREAM_CLIENT_CONNECT, $context = null)
{
 error_clear_last();
 if ($context !== null) {
 $result = \stream_socket_client($remote_socket, $errno, $errstr, $timeout, $flags, $context);
 } elseif ($flags !== STREAM_CLIENT_CONNECT) {
 $result = \stream_socket_client($remote_socket, $errno, $errstr, $timeout, $flags);
 } elseif ($timeout !== null) {
 $result = \stream_socket_client($remote_socket, $errno, $errstr, $timeout);
 } else {
 $result = \stream_socket_client($remote_socket, $errno, $errstr);
 }
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_socket_pair(int $domain, int $type, int $protocol): iterable
{
 error_clear_last();
 $result = \stream_socket_pair($domain, $type, $protocol);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_socket_server(string $local_socket, int &$errno = null, string &$errstr = null, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context = null)
{
 error_clear_last();
 if ($context !== null) {
 $result = \stream_socket_server($local_socket, $errno, $errstr, $flags, $context);
 } else {
 $result = \stream_socket_server($local_socket, $errno, $errstr, $flags);
 }
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
 return $result;
}
function stream_socket_shutdown($stream, int $how): void
{
 error_clear_last();
 $result = \stream_socket_shutdown($stream, $how);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_supports_lock($stream): void
{
 error_clear_last();
 $result = \stream_supports_lock($stream);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_wrapper_register(string $protocol, string $classname, int $flags = 0): void
{
 error_clear_last();
 $result = \stream_wrapper_register($protocol, $classname, $flags);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_wrapper_restore(string $protocol): void
{
 error_clear_last();
 $result = \stream_wrapper_restore($protocol);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
function stream_wrapper_unregister(string $protocol): void
{
 error_clear_last();
 $result = \stream_wrapper_unregister($protocol);
 if ($result === false) {
 throw StreamException::createFromPhpError();
 }
}
