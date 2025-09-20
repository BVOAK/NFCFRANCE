<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\PosixException;
function posix_access(string $file, int $mode = POSIX_F_OK): void
{
 error_clear_last();
 $result = \posix_access($file, $mode);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_getgrnam(string $name): array
{
 error_clear_last();
 $result = \posix_getgrnam($name);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
 return $result;
}
function posix_getpgid(int $pid): int
{
 error_clear_last();
 $result = \posix_getpgid($pid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
 return $result;
}
function posix_initgroups(string $name, int $base_group_id): void
{
 error_clear_last();
 $result = \posix_initgroups($name, $base_group_id);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_kill(int $pid, int $sig): void
{
 error_clear_last();
 $result = \posix_kill($pid, $sig);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_mkfifo(string $pathname, int $mode): void
{
 error_clear_last();
 $result = \posix_mkfifo($pathname, $mode);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_mknod(string $pathname, int $mode, int $major = 0, int $minor = 0): void
{
 error_clear_last();
 $result = \posix_mknod($pathname, $mode, $major, $minor);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_setegid(int $gid): void
{
 error_clear_last();
 $result = \posix_setegid($gid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_seteuid(int $uid): void
{
 error_clear_last();
 $result = \posix_seteuid($uid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_setgid(int $gid): void
{
 error_clear_last();
 $result = \posix_setgid($gid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_setpgid(int $pid, int $pgid): void
{
 error_clear_last();
 $result = \posix_setpgid($pid, $pgid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_setrlimit(int $resource, int $softlimit, int $hardlimit): void
{
 error_clear_last();
 $result = \posix_setrlimit($resource, $softlimit, $hardlimit);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
function posix_setuid(int $uid): void
{
 error_clear_last();
 $result = \posix_setuid($uid);
 if ($result === false) {
 throw PosixException::createFromPhpError();
 }
}
