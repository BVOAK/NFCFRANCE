<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\MbstringException;
function mb_chr(int $cp, string $encoding = null): string
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_chr($cp, $encoding);
 } else {
 $result = \mb_chr($cp);
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_detect_order($encoding_list = null)
{
 error_clear_last();
 if ($encoding_list !== null) {
 $result = \mb_detect_order($encoding_list);
 } else {
 $result = \mb_detect_order();
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_encoding_aliases(string $encoding): array
{
 error_clear_last();
 $result = \mb_encoding_aliases($encoding);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ereg_replace_callback(string $pattern, callable $callback, string $string, string $option = "msr"): string
{
 error_clear_last();
 $result = \mb_ereg_replace_callback($pattern, $callback, $string, $option);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ereg_replace(string $pattern, string $replacement, string $string, string $option = "msr"): string
{
 error_clear_last();
 $result = \mb_ereg_replace($pattern, $replacement, $string, $option);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ereg_search_getregs(): array
{
 error_clear_last();
 $result = \mb_ereg_search_getregs();
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ereg_search_init(string $string, string $pattern = null, string $option = "msr"): void
{
 error_clear_last();
 if ($option !== "msr") {
 $result = \mb_ereg_search_init($string, $pattern, $option);
 } elseif ($pattern !== null) {
 $result = \mb_ereg_search_init($string, $pattern);
 } else {
 $result = \mb_ereg_search_init($string);
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
}
function mb_ereg_search_regs(string $pattern = null, string $option = "ms"): array
{
 error_clear_last();
 if ($option !== "ms") {
 $result = \mb_ereg_search_regs($pattern, $option);
 } elseif ($pattern !== null) {
 $result = \mb_ereg_search_regs($pattern);
 } else {
 $result = \mb_ereg_search_regs();
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ereg_search_setpos(int $position): void
{
 error_clear_last();
 $result = \mb_ereg_search_setpos($position);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
}
function mb_eregi_replace(string $pattern, string $replace, string $string, string $option = "msri"): string
{
 error_clear_last();
 $result = \mb_eregi_replace($pattern, $replace, $string, $option);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_http_output(string $encoding = null)
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_http_output($encoding);
 } else {
 $result = \mb_http_output();
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_internal_encoding(string $encoding = null)
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_internal_encoding($encoding);
 } else {
 $result = \mb_internal_encoding();
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_ord(string $str, string $encoding = null): int
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_ord($str, $encoding);
 } else {
 $result = \mb_ord($str);
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_parse_str(string $encoded_string, ?array &$result): void
{
 error_clear_last();
 $result = \mb_parse_str($encoded_string, $result);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
}
function mb_regex_encoding(string $encoding = null)
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_regex_encoding($encoding);
 } else {
 $result = \mb_regex_encoding();
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_send_mail(string $to, string $subject, string $message, $additional_headers = null, string $additional_parameter = null): void
{
 error_clear_last();
 $result = \mb_send_mail($to, $subject, $message, $additional_headers, $additional_parameter);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
}
function mb_split(string $pattern, string $string, int $limit = -1): array
{
 error_clear_last();
 $result = \mb_split($pattern, $string, $limit);
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
function mb_str_split(string $string, int $split_length = 1, string $encoding = null): array
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \mb_str_split($string, $split_length, $encoding);
 } else {
 $result = \mb_str_split($string, $split_length);
 }
 if ($result === false) {
 throw MbstringException::createFromPhpError();
 }
 return $result;
}
