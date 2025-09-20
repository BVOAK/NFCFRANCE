<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\GmpException;
function gmp_binomial($n, int $k): \GMP
{
 error_clear_last();
 $result = \gmp_binomial($n, $k);
 if ($result === false) {
 throw GmpException::createFromPhpError();
 }
 return $result;
}
function gmp_export($gmpnumber, int $word_size = 1, int $options = GMP_MSW_FIRST | GMP_NATIVE_ENDIAN): string
{
 error_clear_last();
 $result = \gmp_export($gmpnumber, $word_size, $options);
 if ($result === false) {
 throw GmpException::createFromPhpError();
 }
 return $result;
}
function gmp_import(string $data, int $word_size = 1, int $options = GMP_MSW_FIRST | GMP_NATIVE_ENDIAN): \GMP
{
 error_clear_last();
 $result = \gmp_import($data, $word_size, $options);
 if ($result === false) {
 throw GmpException::createFromPhpError();
 }
 return $result;
}
function gmp_random_seed($seed): void
{
 error_clear_last();
 $result = \gmp_random_seed($seed);
 if ($result === false) {
 throw GmpException::createFromPhpError();
 }
}
