<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\InfoException;
function cli_set_process_title(string $title): void
{
 error_clear_last();
 $result = \cli_set_process_title($title);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
function dl(string $library): void
{
 error_clear_last();
 $result = \dl($library);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
function getlastmod(): int
{
 error_clear_last();
 $result = \getlastmod();
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function getmygid(): int
{
 error_clear_last();
 $result = \getmygid();
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function getmyinode(): int
{
 error_clear_last();
 $result = \getmyinode();
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function getmypid(): int
{
 error_clear_last();
 $result = \getmypid();
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function getmyuid(): int
{
 error_clear_last();
 $result = \getmyuid();
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function getopt(string $options, array $longopts = null, ?int &$optind = null): array
{
 error_clear_last();
 if ($optind !== null) {
 $result = \getopt($options, $longopts, $optind);
 } elseif ($longopts !== null) {
 $result = \getopt($options, $longopts);
 } else {
 $result = \getopt($options);
 }
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function ini_get(string $varname): string
{
 error_clear_last();
 $result = \ini_get($varname);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function ini_set(string $varname, $newvalue): string
{
 error_clear_last();
 $result = \ini_set($varname, $newvalue);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function phpcredits(int $flag = CREDITS_ALL): void
{
 error_clear_last();
 $result = \phpcredits($flag);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
function phpinfo(int $what = INFO_ALL): void
{
 error_clear_last();
 $result = \phpinfo($what);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
function putenv(string $setting): void
{
 error_clear_last();
 $result = \putenv($setting);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
function set_include_path(string $new_include_path): string
{
 error_clear_last();
 $result = \set_include_path($new_include_path);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
 return $result;
}
function set_time_limit(int $seconds): void
{
 error_clear_last();
 $result = \set_time_limit($seconds);
 if ($result === false) {
 throw InfoException::createFromPhpError();
 }
}
