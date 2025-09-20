<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MiscException;
function define(string $name, $value, bool $case_insensitive = false): void
{
 error_clear_last();
 $result = \define($name, $value, $case_insensitive);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
}
function highlight_file(string $filename, bool $return = false)
{
 error_clear_last();
 $result = \highlight_file($filename, $return);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function highlight_string(string $str, bool $return = false)
{
 error_clear_last();
 $result = \highlight_string($str, $return);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function pack(string $format, ...$params): string
{
 error_clear_last();
 if ($params !== []) {
 $result = \pack($format, ...$params);
 } else {
 $result = \pack($format);
 }
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function sapi_windows_cp_conv($in_codepage, $out_codepage, string $subject): string
{
 error_clear_last();
 $result = \sapi_windows_cp_conv($in_codepage, $out_codepage, $subject);
 if ($result === null) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function sapi_windows_cp_set(int $cp): void
{
 error_clear_last();
 $result = \sapi_windows_cp_set($cp);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
}
function sapi_windows_generate_ctrl_event(int $event, int $pid = 0): void
{
 error_clear_last();
 $result = \sapi_windows_generate_ctrl_event($event, $pid);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
}
function sapi_windows_vt100_support($stream, bool $enable = null): void
{
 error_clear_last();
 if ($enable !== null) {
 $result = \sapi_windows_vt100_support($stream, $enable);
 } else {
 $result = \sapi_windows_vt100_support($stream);
 }
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
}
function sleep(int $seconds): int
{
 error_clear_last();
 $result = \sleep($seconds);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function time_nanosleep(int $seconds, int $nanoseconds)
{
 error_clear_last();
 $result = \time_nanosleep($seconds, $nanoseconds);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
function time_sleep_until(float $timestamp): void
{
 error_clear_last();
 $result = \time_sleep_until($timestamp);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
}
function unpack(string $format, string $data, int $offset = 0): array
{
 error_clear_last();
 $result = \unpack($format, $data, $offset);
 if ($result === false) {
 throw MiscException::createFromPhpError();
 }
 return $result;
}
