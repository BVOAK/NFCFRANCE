<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ZlibException;
function deflate_add($context, string $data, int $flush_mode = ZLIB_SYNC_FLUSH): string
{
 error_clear_last();
 $result = \deflate_add($context, $data, $flush_mode);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function deflate_init(int $encoding, array $options = null)
{
 error_clear_last();
 $result = \deflate_init($encoding, $options);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzclose($zp): void
{
 error_clear_last();
 $result = \gzclose($zp);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
}
function gzcompress(string $data, int $level = -1, int $encoding = ZLIB_ENCODING_DEFLATE): string
{
 error_clear_last();
 $result = \gzcompress($data, $level, $encoding);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzdecode(string $data, int $length = null): string
{
 error_clear_last();
 if ($length !== null) {
 $result = \gzdecode($data, $length);
 } else {
 $result = \gzdecode($data);
 }
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzdeflate(string $data, int $level = -1, int $encoding = ZLIB_ENCODING_RAW): string
{
 error_clear_last();
 $result = \gzdeflate($data, $level, $encoding);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzencode(string $data, int $level = -1, int $encoding_mode = FORCE_GZIP): string
{
 error_clear_last();
 $result = \gzencode($data, $level, $encoding_mode);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzgets($zp, int $length = null): string
{
 error_clear_last();
 if ($length !== null) {
 $result = \gzgets($zp, $length);
 } else {
 $result = \gzgets($zp);
 }
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzgetss($zp, int $length, string $allowable_tags = null): string
{
 error_clear_last();
 if ($allowable_tags !== null) {
 $result = \gzgetss($zp, $length, $allowable_tags);
 } else {
 $result = \gzgetss($zp, $length);
 }
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzinflate(string $data, int $length = 0): string
{
 error_clear_last();
 $result = \gzinflate($data, $length);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzpassthru($zp): int
{
 error_clear_last();
 $result = \gzpassthru($zp);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function gzrewind($zp): void
{
 error_clear_last();
 $result = \gzrewind($zp);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
}
function gzuncompress(string $data, int $length = 0): string
{
 error_clear_last();
 $result = \gzuncompress($data, $length);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function inflate_get_read_len($resource): int
{
 error_clear_last();
 $result = \inflate_get_read_len($resource);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function inflate_get_status($resource): int
{
 error_clear_last();
 $result = \inflate_get_status($resource);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function inflate_add($context, string $encoded_data, int $flush_mode = ZLIB_SYNC_FLUSH): string
{
 error_clear_last();
 $result = \inflate_add($context, $encoded_data, $flush_mode);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function inflate_init(int $encoding, array $options = null)
{
 error_clear_last();
 $result = \inflate_init($encoding, $options);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function readgzfile(string $filename, int $use_include_path = 0): int
{
 error_clear_last();
 $result = \readgzfile($filename, $use_include_path);
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
function zlib_decode(string $data, int $max_decoded_len = null): string
{
 error_clear_last();
 if ($max_decoded_len !== null) {
 $result = \zlib_decode($data, $max_decoded_len);
 } else {
 $result = \zlib_decode($data);
 }
 if ($result === false) {
 throw ZlibException::createFromPhpError();
 }
 return $result;
}
