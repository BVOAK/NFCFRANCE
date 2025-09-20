<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FileinfoException;
function finfo_close($finfo): void
{
 error_clear_last();
 $result = \finfo_close($finfo);
 if ($result === false) {
 throw FileinfoException::createFromPhpError();
 }
}
function finfo_open(int $options = FILEINFO_NONE, string $magic_file = "")
{
 error_clear_last();
 $result = \finfo_open($options, $magic_file);
 if ($result === false) {
 throw FileinfoException::createFromPhpError();
 }
 return $result;
}
function mime_content_type(string $filename): string
{
 error_clear_last();
 $result = \mime_content_type($filename);
 if ($result === false) {
 throw FileinfoException::createFromPhpError();
 }
 return $result;
}
