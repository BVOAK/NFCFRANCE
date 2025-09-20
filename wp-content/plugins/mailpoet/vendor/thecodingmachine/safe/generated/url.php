<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\UrlException;
function base64_decode(string $data, bool $strict = false): string
{
 error_clear_last();
 $result = \base64_decode($data, $strict);
 if ($result === false) {
 throw UrlException::createFromPhpError();
 }
 return $result;
}
function get_headers(string $url, int $format = 0, $context = null): array
{
 error_clear_last();
 if ($context !== null) {
 $result = \get_headers($url, $format, $context);
 } else {
 $result = \get_headers($url, $format);
 }
 if ($result === false) {
 throw UrlException::createFromPhpError();
 }
 return $result;
}
function parse_url(string $url, int $component = -1)
{
 error_clear_last();
 $result = \parse_url($url, $component);
 if ($result === false) {
 throw UrlException::createFromPhpError();
 }
 return $result;
}
