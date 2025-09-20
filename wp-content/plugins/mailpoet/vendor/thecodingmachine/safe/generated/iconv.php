<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\IconvException;
function iconv_get_encoding(string $type = "all")
{
 error_clear_last();
 $result = \iconv_get_encoding($type);
 if ($result === false) {
 throw IconvException::createFromPhpError();
 }
 return $result;
}
function iconv_set_encoding(string $type, string $charset): void
{
 error_clear_last();
 $result = \iconv_set_encoding($type, $charset);
 if ($result === false) {
 throw IconvException::createFromPhpError();
 }
}
function iconv(string $in_charset, string $out_charset, string $str): string
{
 error_clear_last();
 $result = \iconv($in_charset, $out_charset, $str);
 if ($result === false) {
 throw IconvException::createFromPhpError();
 }
 return $result;
}
