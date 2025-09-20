<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FtpException;
function ftp_alloc($ftp_stream, int $filesize, string &$result = null): void
{
 error_clear_last();
 $result = \ftp_alloc($ftp_stream, $filesize, $result);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_append($ftp, string $remote_file, string $local_file, int $mode = FTP_BINARY): void
{
 error_clear_last();
 $result = \ftp_append($ftp, $remote_file, $local_file, $mode);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_cdup($ftp_stream): void
{
 error_clear_last();
 $result = \ftp_cdup($ftp_stream);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_chdir($ftp_stream, string $directory): void
{
 error_clear_last();
 $result = \ftp_chdir($ftp_stream, $directory);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_chmod($ftp_stream, int $mode, string $filename): int
{
 error_clear_last();
 $result = \ftp_chmod($ftp_stream, $mode, $filename);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_close($ftp_stream): void
{
 error_clear_last();
 $result = \ftp_close($ftp_stream);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_connect(string $host, int $port = 21, int $timeout = 90)
{
 error_clear_last();
 $result = \ftp_connect($host, $port, $timeout);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_delete($ftp_stream, string $path): void
{
 error_clear_last();
 $result = \ftp_delete($ftp_stream, $path);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_fget($ftp_stream, $handle, string $remote_file, int $mode = FTP_BINARY, int $resumepos = 0): void
{
 error_clear_last();
 $result = \ftp_fget($ftp_stream, $handle, $remote_file, $mode, $resumepos);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_fput($ftp_stream, string $remote_file, $handle, int $mode = FTP_BINARY, int $startpos = 0): void
{
 error_clear_last();
 $result = \ftp_fput($ftp_stream, $remote_file, $handle, $mode, $startpos);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_get($ftp_stream, string $local_file, string $remote_file, int $mode = FTP_BINARY, int $resumepos = 0): void
{
 error_clear_last();
 $result = \ftp_get($ftp_stream, $local_file, $remote_file, $mode, $resumepos);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_login($ftp_stream, string $username, string $password): void
{
 error_clear_last();
 $result = \ftp_login($ftp_stream, $username, $password);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_mkdir($ftp_stream, string $directory): string
{
 error_clear_last();
 $result = \ftp_mkdir($ftp_stream, $directory);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_mlsd($ftp_stream, string $directory): array
{
 error_clear_last();
 $result = \ftp_mlsd($ftp_stream, $directory);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_nlist($ftp_stream, string $directory): array
{
 error_clear_last();
 $result = \ftp_nlist($ftp_stream, $directory);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_pasv($ftp_stream, bool $pasv): void
{
 error_clear_last();
 $result = \ftp_pasv($ftp_stream, $pasv);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_put($ftp_stream, string $remote_file, string $local_file, int $mode = FTP_BINARY, int $startpos = 0): void
{
 error_clear_last();
 $result = \ftp_put($ftp_stream, $remote_file, $local_file, $mode, $startpos);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_pwd($ftp_stream): string
{
 error_clear_last();
 $result = \ftp_pwd($ftp_stream);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_rename($ftp_stream, string $oldname, string $newname): void
{
 error_clear_last();
 $result = \ftp_rename($ftp_stream, $oldname, $newname);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_rmdir($ftp_stream, string $directory): void
{
 error_clear_last();
 $result = \ftp_rmdir($ftp_stream, $directory);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_site($ftp_stream, string $command): void
{
 error_clear_last();
 $result = \ftp_site($ftp_stream, $command);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
}
function ftp_ssl_connect(string $host, int $port = 21, int $timeout = 90)
{
 error_clear_last();
 $result = \ftp_ssl_connect($host, $port, $timeout);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
function ftp_systype($ftp_stream): string
{
 error_clear_last();
 $result = \ftp_systype($ftp_stream);
 if ($result === false) {
 throw FtpException::createFromPhpError();
 }
 return $result;
}
