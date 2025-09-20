<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SimplexmlException;
function simplexml_import_dom(\DOMNode $node, string $class_name = "SimpleXMLElement"): \SimpleXMLElement
{
 error_clear_last();
 $result = \simplexml_import_dom($node, $class_name);
 if ($result === false) {
 throw SimplexmlException::createFromPhpError();
 }
 return $result;
}
function simplexml_load_file(string $filename, string $class_name = "SimpleXMLElement", int $options = 0, string $ns = "", bool $is_prefix = false): \SimpleXMLElement
{
 error_clear_last();
 $result = \simplexml_load_file($filename, $class_name, $options, $ns, $is_prefix);
 if ($result === false) {
 throw SimplexmlException::createFromPhpError();
 }
 return $result;
}
function simplexml_load_string(string $data, string $class_name = "SimpleXMLElement", int $options = 0, string $ns = "", bool $is_prefix = false): \SimpleXMLElement
{
 error_clear_last();
 $result = \simplexml_load_string($data, $class_name, $options, $ns, $is_prefix);
 if ($result === false) {
 throw SimplexmlException::createFromPhpError();
 }
 return $result;
}
