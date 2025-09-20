<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\StringsException;
function convert_uudecode(string $data): string
{
 error_clear_last();
 $result = \convert_uudecode($data);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function convert_uuencode(string $data): string
{
 error_clear_last();
 $result = \convert_uuencode($data);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function hex2bin(string $data): string
{
 error_clear_last();
 $result = \hex2bin($data);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function md5_file(string $filename, bool $raw_output = false): string
{
 error_clear_last();
 $result = \md5_file($filename, $raw_output);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function metaphone(string $str, int $phonemes = 0): string
{
 error_clear_last();
 $result = \metaphone($str, $phonemes);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function sha1_file(string $filename, bool $raw_output = false): string
{
 error_clear_last();
 $result = \sha1_file($filename, $raw_output);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function soundex(string $str): string
{
 error_clear_last();
 $result = \soundex($str);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function sprintf(string $format, ...$params): string
{
 error_clear_last();
 if ($params !== []) {
 $result = \sprintf($format, ...$params);
 } else {
 $result = \sprintf($format);
 }
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function substr(string $string, int $start, int $length = null): string
{
 error_clear_last();
 if ($length !== null) {
 $result = \substr($string, $start, $length);
 } else {
 $result = \substr($string, $start);
 }
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
function vsprintf(string $format, array $args): string
{
 error_clear_last();
 $result = \vsprintf($format, $args);
 if ($result === false) {
 throw StringsException::createFromPhpError();
 }
 return $result;
}
