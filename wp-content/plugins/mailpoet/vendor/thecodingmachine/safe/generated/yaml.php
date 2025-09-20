<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\YamlException;
function yaml_parse_file(string $filename, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
 error_clear_last();
 $result = \yaml_parse_file($filename, $pos, $ndocs, $callbacks);
 if ($result === false) {
 throw YamlException::createFromPhpError();
 }
 return $result;
}
function yaml_parse_url(string $url, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
 error_clear_last();
 $result = \yaml_parse_url($url, $pos, $ndocs, $callbacks);
 if ($result === false) {
 throw YamlException::createFromPhpError();
 }
 return $result;
}
function yaml_parse(string $input, int $pos = 0, ?int &$ndocs = null, array $callbacks = null)
{
 error_clear_last();
 $result = \yaml_parse($input, $pos, $ndocs, $callbacks);
 if ($result === false) {
 throw YamlException::createFromPhpError();
 }
 return $result;
}
