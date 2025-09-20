<?php
declare(strict_types=1);
namespace Pelago\Emogrifier\Utilities;
if (!defined('ABSPATH')) exit;
final class DeclarationBlockParser
{
 private static $cache = [];
 public function normalizePropertyName(string $name): string
 {
 if (\substr($name, 0, 2) === '--') {
 return $name;
 }
 return \strtolower($name);
 }
 public function parse(string $declarationBlock): array
 {
 $trimmedDeclarationBlock = \trim($declarationBlock, "; \n\r\t\v\x00");
 if ($trimmedDeclarationBlock === '') {
 return [];
 }
 if (isset(self::$cache[$trimmedDeclarationBlock])) {
 return self::$cache[$trimmedDeclarationBlock];
 }
 $preg = new Preg();
 $declarations = $preg->split('/;(?!base64|charset)/', $trimmedDeclarationBlock);
 $properties = [];
 foreach ($declarations as $declaration) {
 $matches = [];
 if ($preg->match('/^([A-Za-z\\-]+)\\s*:\\s*(.+)$/s', \trim($declaration), $matches) === 0) {
 continue;
 }
 $propertyName = $matches[1];
 if ($propertyName === '') {
 // This cannot happen since the regular expression matches one or more characters.
 throw new \UnexpectedValueException('An empty property name was encountered.', 1727046409);
 }
 $propertyValue = $matches[2];
 $properties[$this->normalizePropertyName($propertyName)] = $propertyValue;
 }
 self::$cache[$trimmedDeclarationBlock] = $properties;
 return $properties;
 }
}
