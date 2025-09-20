<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ClassobjException;
function class_alias(string $original, string $alias, bool $autoload = true): void
{
 error_clear_last();
 $result = \class_alias($original, $alias, $autoload);
 if ($result === false) {
 throw ClassobjException::createFromPhpError();
 }
}
