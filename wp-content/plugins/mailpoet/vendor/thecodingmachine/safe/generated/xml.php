<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\XmlException;
function xml_parser_create_ns(string $encoding = null, string $separator = ":")
{
 error_clear_last();
 if ($separator !== ":") {
 $result = \xml_parser_create_ns($encoding, $separator);
 } elseif ($encoding !== null) {
 $result = \xml_parser_create_ns($encoding);
 } else {
 $result = \xml_parser_create_ns();
 }
 if ($result === false) {
 throw XmlException::createFromPhpError();
 }
 return $result;
}
function xml_parser_create(string $encoding = null)
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \xml_parser_create($encoding);
 } else {
 $result = \xml_parser_create();
 }
 if ($result === false) {
 throw XmlException::createFromPhpError();
 }
 return $result;
}
function xml_set_object($parser, object &$object): void
{
 error_clear_last();
 $result = \xml_set_object($parser, $object);
 if ($result === false) {
 throw XmlException::createFromPhpError();
 }
}
