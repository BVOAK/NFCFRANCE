<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ZipException;
function zip_entry_close($zip_entry): void
{
 error_clear_last();
 $result = \zip_entry_close($zip_entry);
 if ($result === false) {
 throw ZipException::createFromPhpError();
 }
}
function zip_entry_open($zip, $zip_entry, string $mode = null): void
{
 error_clear_last();
 if ($mode !== null) {
 $result = \zip_entry_open($zip, $zip_entry, $mode);
 } else {
 $result = \zip_entry_open($zip, $zip_entry);
 }
 if ($result === false) {
 throw ZipException::createFromPhpError();
 }
}
function zip_entry_read($zip_entry, int $length = 1024): string
{
 error_clear_last();
 $result = \zip_entry_read($zip_entry, $length);
 if ($result === false) {
 throw ZipException::createFromPhpError();
 }
 return $result;
}
