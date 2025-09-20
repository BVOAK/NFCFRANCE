<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\DirException;
function chdir(string $directory): void
{
 error_clear_last();
 $result = \chdir($directory);
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
}
function chroot(string $directory): void
{
 error_clear_last();
 $result = \chroot($directory);
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
}
function getcwd(): string
{
 error_clear_last();
 $result = \getcwd();
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
 return $result;
}
function opendir(string $path, $context = null)
{
 error_clear_last();
 if ($context !== null) {
 $result = \opendir($path, $context);
 } else {
 $result = \opendir($path);
 }
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
 return $result;
}
function rewinddir($dir_handle = null): void
{
 error_clear_last();
 if ($dir_handle !== null) {
 $result = \rewinddir($dir_handle);
 } else {
 $result = \rewinddir();
 }
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
}
function scandir(string $directory, int $sorting_order = SCANDIR_SORT_ASCENDING, $context = null): array
{
 error_clear_last();
 if ($context !== null) {
 $result = \scandir($directory, $sorting_order, $context);
 } else {
 $result = \scandir($directory, $sorting_order);
 }
 if ($result === false) {
 throw DirException::createFromPhpError();
 }
 return $result;
}
