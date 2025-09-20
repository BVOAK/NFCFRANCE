<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SplException;
function class_implements($class, bool $autoload = true): array
{
 error_clear_last();
 $result = \class_implements($class, $autoload);
 if ($result === false) {
 throw SplException::createFromPhpError();
 }
 return $result;
}
function class_parents($class, bool $autoload = true): array
{
 error_clear_last();
 $result = \class_parents($class, $autoload);
 if ($result === false) {
 throw SplException::createFromPhpError();
 }
 return $result;
}
function class_uses($class, bool $autoload = true): array
{
 error_clear_last();
 $result = \class_uses($class, $autoload);
 if ($result === false) {
 throw SplException::createFromPhpError();
 }
 return $result;
}
function spl_autoload_register(callable $autoload_function = null, bool $throw = true, bool $prepend = false): void
{
 error_clear_last();
 if ($prepend !== false) {
 $result = \spl_autoload_register($autoload_function, $throw, $prepend);
 } elseif ($throw !== true) {
 $result = \spl_autoload_register($autoload_function, $throw);
 } elseif ($autoload_function !== null) {
 $result = \spl_autoload_register($autoload_function);
 } else {
 $result = \spl_autoload_register();
 }
 if ($result === false) {
 throw SplException::createFromPhpError();
 }
}
function spl_autoload_unregister($autoload_function): void
{
 error_clear_last();
 $result = \spl_autoload_unregister($autoload_function);
 if ($result === false) {
 throw SplException::createFromPhpError();
 }
}
