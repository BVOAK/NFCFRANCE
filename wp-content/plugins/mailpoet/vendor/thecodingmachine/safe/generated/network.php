<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\NetworkException;
function closelog(): void
{
 error_clear_last();
 $result = \closelog();
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
}
function dns_get_record(string $hostname, int $type = DNS_ANY, ?array &$authns = null, ?array &$addtl = null, bool $raw = false): array
{
 error_clear_last();
 $result = \dns_get_record($hostname, $type, $authns, $addtl, $raw);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function fsockopen(string $hostname, int $port = -1, ?int &$errno = null, ?string &$errstr = null, float $timeout = null)
{
 error_clear_last();
 if ($timeout !== null) {
 $result = \fsockopen($hostname, $port, $errno, $errstr, $timeout);
 } else {
 $result = \fsockopen($hostname, $port, $errno, $errstr);
 }
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function gethostname(): string
{
 error_clear_last();
 $result = \gethostname();
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function getprotobyname(string $name): int
{
 error_clear_last();
 $result = \getprotobyname($name);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function getprotobynumber(int $number): string
{
 error_clear_last();
 $result = \getprotobynumber($number);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function header_register_callback(callable $callback): void
{
 error_clear_last();
 $result = \header_register_callback($callback);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
}
function inet_ntop(string $in_addr): string
{
 error_clear_last();
 $result = \inet_ntop($in_addr);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
 return $result;
}
function openlog(string $ident, int $option, int $facility): void
{
 error_clear_last();
 $result = \openlog($ident, $option, $facility);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
}
function syslog(int $priority, string $message): void
{
 error_clear_last();
 $result = \syslog($priority, $message);
 if ($result === false) {
 throw NetworkException::createFromPhpError();
 }
}
