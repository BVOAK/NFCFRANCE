<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\LzfException;
function lzf_compress(string $data): string
{
 error_clear_last();
 $result = \lzf_compress($data);
 if ($result === false) {
 throw LzfException::createFromPhpError();
 }
 return $result;
}
function lzf_decompress(string $data): string
{
 error_clear_last();
 $result = \lzf_decompress($data);
 if ($result === false) {
 throw LzfException::createFromPhpError();
 }
 return $result;
}
