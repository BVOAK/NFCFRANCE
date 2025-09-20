<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SocketsException;
function socket_accept($socket)
{
 error_clear_last();
 $result = \socket_accept($socket);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_addrinfo_bind($addr)
{
 error_clear_last();
 $result = \socket_addrinfo_bind($addr);
 if ($result === null) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_addrinfo_connect($addr)
{
 error_clear_last();
 $result = \socket_addrinfo_connect($addr);
 if ($result === null) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_bind($socket, string $address, int $port = 0): void
{
 error_clear_last();
 $result = \socket_bind($socket, $address, $port);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_connect($socket, string $address, int $port = 0): void
{
 error_clear_last();
 $result = \socket_connect($socket, $address, $port);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_create_listen(int $port, int $backlog = 128)
{
 error_clear_last();
 $result = \socket_create_listen($port, $backlog);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_create_pair(int $domain, int $type, int $protocol, ?iterable &$fd): void
{
 error_clear_last();
 $result = \socket_create_pair($domain, $type, $protocol, $fd);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_create(int $domain, int $type, int $protocol)
{
 error_clear_last();
 $result = \socket_create($domain, $type, $protocol);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_export_stream($socket)
{
 error_clear_last();
 $result = \socket_export_stream($socket);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_get_option($socket, int $level, int $optname)
{
 error_clear_last();
 $result = \socket_get_option($socket, $level, $optname);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_getpeername($socket, string &$address, ?int &$port = null): void
{
 error_clear_last();
 $result = \socket_getpeername($socket, $address, $port);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_getsockname($socket, ?string &$addr, ?int &$port = null): void
{
 error_clear_last();
 $result = \socket_getsockname($socket, $addr, $port);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_import_stream($stream)
{
 error_clear_last();
 $result = \socket_import_stream($stream);
 if ($result === null) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_listen($socket, int $backlog = 0): void
{
 error_clear_last();
 $result = \socket_listen($socket, $backlog);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_read($socket, int $length, int $type = PHP_BINARY_READ): string
{
 error_clear_last();
 $result = \socket_read($socket, $length, $type);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_send($socket, string $buf, int $len, int $flags): int
{
 error_clear_last();
 $result = \socket_send($socket, $buf, $len, $flags);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_sendmsg($socket, array $message, int $flags = 0): int
{
 error_clear_last();
 $result = \socket_sendmsg($socket, $message, $flags);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_sendto($socket, string $buf, int $len, int $flags, string $addr, int $port = 0): int
{
 error_clear_last();
 $result = \socket_sendto($socket, $buf, $len, $flags, $addr, $port);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_set_block($socket): void
{
 error_clear_last();
 $result = \socket_set_block($socket);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_set_nonblock($socket): void
{
 error_clear_last();
 $result = \socket_set_nonblock($socket);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_set_option($socket, int $level, int $optname, $optval): void
{
 error_clear_last();
 $result = \socket_set_option($socket, $level, $optname, $optval);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_shutdown($socket, int $how = 2): void
{
 error_clear_last();
 $result = \socket_shutdown($socket, $how);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
function socket_wsaprotocol_info_export($socket, int $target_pid): string
{
 error_clear_last();
 $result = \socket_wsaprotocol_info_export($socket, $target_pid);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_wsaprotocol_info_import(string $info_id)
{
 error_clear_last();
 $result = \socket_wsaprotocol_info_import($info_id);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
function socket_wsaprotocol_info_release(string $info_id): void
{
 error_clear_last();
 $result = \socket_wsaprotocol_info_release($info_id);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
}
