<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\HashException;
function hash_hkdf(string $algo, string $ikm, int $length = 0, string $info = '', string $salt = ''): string
{
 error_clear_last();
 $result = \hash_hkdf($algo, $ikm, $length, $info, $salt);
 if ($result === false) {
 throw HashException::createFromPhpError();
 }
 return $result;
}
function hash_update_file(\HashContext $hcontext, string $filename, ?\HashContext $scontext = null): void
{
 error_clear_last();
 $result = \hash_update_file($hcontext, $filename, $scontext);
 if ($result === false) {
 throw HashException::createFromPhpError();
 }
}
