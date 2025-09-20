<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FilesystemException;
function chgrp(string $filename, $group): void
{
 error_clear_last();
 $result = \chgrp($filename, $group);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function chmod(string $filename, int $mode): void
{
 error_clear_last();
 $result = \chmod($filename, $mode);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function chown(string $filename, $user): void
{
 error_clear_last();
 $result = \chown($filename, $user);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function copy(string $source, string $dest, $context = null): void
{
 error_clear_last();
 if ($context !== null) {
 $result = \copy($source, $dest, $context);
 } else {
 $result = \copy($source, $dest);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function disk_free_space(string $directory): float
{
 error_clear_last();
 $result = \disk_free_space($directory);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function disk_total_space(string $directory): float
{
 error_clear_last();
 $result = \disk_total_space($directory);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fclose($handle): void
{
 error_clear_last();
 $result = \fclose($handle);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function fflush($handle): void
{
 error_clear_last();
 $result = \fflush($handle);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function file_get_contents(string $filename, bool $use_include_path = false, $context = null, int $offset = 0, int $maxlen = null): string
{
 error_clear_last();
 if ($maxlen !== null) {
 $result = \file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
 } elseif ($offset !== 0) {
 $result = \file_get_contents($filename, $use_include_path, $context, $offset);
 } elseif ($context !== null) {
 $result = \file_get_contents($filename, $use_include_path, $context);
 } else {
 $result = \file_get_contents($filename, $use_include_path);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function file_put_contents(string $filename, $data, int $flags = 0, $context = null): int
{
 error_clear_last();
 if ($context !== null) {
 $result = \file_put_contents($filename, $data, $flags, $context);
 } else {
 $result = \file_put_contents($filename, $data, $flags);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function file(string $filename, int $flags = 0, $context = null): array
{
 error_clear_last();
 if ($context !== null) {
 $result = \file($filename, $flags, $context);
 } else {
 $result = \file($filename, $flags);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fileatime(string $filename): int
{
 error_clear_last();
 $result = \fileatime($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function filectime(string $filename): int
{
 error_clear_last();
 $result = \filectime($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fileinode(string $filename): int
{
 error_clear_last();
 $result = \fileinode($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function filemtime(string $filename): int
{
 error_clear_last();
 $result = \filemtime($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fileowner(string $filename): int
{
 error_clear_last();
 $result = \fileowner($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function filesize(string $filename): int
{
 error_clear_last();
 $result = \filesize($filename);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function flock($handle, int $operation, ?int &$wouldblock = null): void
{
 error_clear_last();
 $result = \flock($handle, $operation, $wouldblock);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function fopen(string $filename, string $mode, bool $use_include_path = false, $context = null)
{
 error_clear_last();
 if ($context !== null) {
 $result = \fopen($filename, $mode, $use_include_path, $context);
 } else {
 $result = \fopen($filename, $mode, $use_include_path);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fputcsv($handle, array $fields, string $delimiter = ",", string $enclosure = '"', string $escape_char = "\\"): int
{
 error_clear_last();
 $result = \fputcsv($handle, $fields, $delimiter, $enclosure, $escape_char);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function fread($handle, int $length): string
{
 error_clear_last();
 $result = \fread($handle, $length);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function ftruncate($handle, int $size): void
{
 error_clear_last();
 $result = \ftruncate($handle, $size);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function fwrite($handle, string $string, int $length = null): int
{
 error_clear_last();
 if ($length !== null) {
 $result = \fwrite($handle, $string, $length);
 } else {
 $result = \fwrite($handle, $string);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function glob(string $pattern, int $flags = 0): array
{
 error_clear_last();
 $result = \glob($pattern, $flags);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function lchgrp(string $filename, $group): void
{
 error_clear_last();
 $result = \lchgrp($filename, $group);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function lchown(string $filename, $user): void
{
 error_clear_last();
 $result = \lchown($filename, $user);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function link(string $target, string $link): void
{
 error_clear_last();
 $result = \link($target, $link);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function mkdir(string $pathname, int $mode = 0777, bool $recursive = false, $context = null): void
{
 error_clear_last();
 if ($context !== null) {
 $result = \mkdir($pathname, $mode, $recursive, $context);
 } else {
 $result = \mkdir($pathname, $mode, $recursive);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function parse_ini_file(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array
{
 error_clear_last();
 $result = \parse_ini_file($filename, $process_sections, $scanner_mode);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function parse_ini_string(string $ini, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL): array
{
 error_clear_last();
 $result = \parse_ini_string($ini, $process_sections, $scanner_mode);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function readfile(string $filename, bool $use_include_path = false, $context = null): int
{
 error_clear_last();
 if ($context !== null) {
 $result = \readfile($filename, $use_include_path, $context);
 } else {
 $result = \readfile($filename, $use_include_path);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function readlink(string $path): string
{
 error_clear_last();
 $result = \readlink($path);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function realpath(string $path): string
{
 error_clear_last();
 $result = \realpath($path);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function rename(string $oldname, string $newname, $context = null): void
{
 error_clear_last();
 if ($context !== null) {
 $result = \rename($oldname, $newname, $context);
 } else {
 $result = \rename($oldname, $newname);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function rewind($handle): void
{
 error_clear_last();
 $result = \rewind($handle);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function rmdir(string $dirname, $context = null): void
{
 error_clear_last();
 if ($context !== null) {
 $result = \rmdir($dirname, $context);
 } else {
 $result = \rmdir($dirname);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function symlink(string $target, string $link): void
{
 error_clear_last();
 $result = \symlink($target, $link);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function tempnam(string $dir, string $prefix): string
{
 error_clear_last();
 $result = \tempnam($dir, $prefix);
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function tmpfile()
{
 error_clear_last();
 $result = \tmpfile();
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
 return $result;
}
function touch(string $filename, int $time = null, int $atime = null): void
{
 error_clear_last();
 if ($atime !== null) {
 $result = \touch($filename, $time, $atime);
 } elseif ($time !== null) {
 $result = \touch($filename, $time);
 } else {
 $result = \touch($filename);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
function unlink(string $filename, $context = null): void
{
 error_clear_last();
 if ($context !== null) {
 $result = \unlink($filename, $context);
 } else {
 $result = \unlink($filename);
 }
 if ($result === false) {
 throw FilesystemException::createFromPhpError();
 }
}
