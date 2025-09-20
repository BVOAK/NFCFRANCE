<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SodiumException;
function sodium_crypto_pwhash_str(string $password, int $opslimit, int $memlimit): string
{
 error_clear_last();
 $result = \sodium_crypto_pwhash_str($password, $opslimit, $memlimit);
 if ($result === false) {
 throw SodiumException::createFromPhpError();
 }
 return $result;
}
function sodium_crypto_pwhash(int $length, string $password, string $salt, int $opslimit, int $memlimit, int $alg = null): string
{
 error_clear_last();
 if ($alg !== null) {
 $result = \sodium_crypto_pwhash($length, $password, $salt, $opslimit, $memlimit, $alg);
 } else {
 $result = \sodium_crypto_pwhash($length, $password, $salt, $opslimit, $memlimit);
 }
 if ($result === false) {
 throw SodiumException::createFromPhpError();
 }
 return $result;
}
