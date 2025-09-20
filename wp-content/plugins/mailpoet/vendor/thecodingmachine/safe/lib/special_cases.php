<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SocketsException;
use const PREG_NO_ERROR;
use Safe\Exceptions\ApcException;
use Safe\Exceptions\ApcuException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\OpensslException;
use Safe\Exceptions\PcreException;
function json_decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
{
 $data = \json_decode($json, $assoc, $depth, $options);
 if (JSON_ERROR_NONE !== json_last_error()) {
 throw JsonException::createFromPhpError();
 }
 return $data;
}
function apc_fetch($key)
{
 error_clear_last();
 $result = \apc_fetch($key, $success);
 if ($success === false) {
 throw ApcException::createFromPhpError();
 }
 return $result;
}
function apcu_fetch($key)
{
 error_clear_last();
 $result = \apcu_fetch($key, $success);
 if ($success === false) {
 throw ApcuException::createFromPhpError();
 }
 return $result;
}
function preg_replace($pattern, $replacement, $subject, int $limit = -1, int &$count = null)
{
 error_clear_last();
 $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
 if (preg_last_error() !== PREG_NO_ERROR || $result === null) {
 throw PcreException::createFromPhpError();
 }
 return $result;
}
function readdir($dir_handle = null)
{
 if ($dir_handle !== null) {
 $result = \readdir($dir_handle);
 } else {
 $result = \readdir();
 }
 return $result;
}
function openssl_encrypt(string $data, string $method, string $key, int $options = 0, string $iv = "", string &$tag = "", string $aad = "", int $tag_length = 16): string
{
 error_clear_last();
 // The $tag parameter is handled in a weird way by openssl_encrypt. It cannot be provided unless encoding is AEAD
 if (func_num_args() <= 5) {
 $result = \openssl_encrypt($data, $method, $key, $options, $iv);
 } else {
 $result = \openssl_encrypt($data, $method, $key, $options, $iv, $tag, $aad, $tag_length);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function socket_write($socket, string $buffer, int $length = 0): int
{
 error_clear_last();
 $result = $length === 0 ? \socket_write($socket, $buffer) : \socket_write($socket, $buffer, $length);
 if ($result === false) {
 throw SocketsException::createFromPhpError();
 }
 return $result;
}
