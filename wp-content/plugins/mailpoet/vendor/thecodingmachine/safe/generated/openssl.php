<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\OpensslException;
function openssl_cipher_iv_length(string $method): int
{
 error_clear_last();
 $result = \openssl_cipher_iv_length($method);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_csr_export_to_file($csr, string $outfilename, bool $notext = true): void
{
 error_clear_last();
 $result = \openssl_csr_export_to_file($csr, $outfilename, $notext);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_csr_export($csr, ?string &$out, bool $notext = true): void
{
 error_clear_last();
 $result = \openssl_csr_export($csr, $out, $notext);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_csr_get_subject($csr, bool $use_shortnames = true): array
{
 error_clear_last();
 $result = \openssl_csr_get_subject($csr, $use_shortnames);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_csr_new(array $dn, &$privkey, array $configargs = null, array $extraattribs = null)
{
 error_clear_last();
 if ($extraattribs !== null) {
 $result = \openssl_csr_new($dn, $privkey, $configargs, $extraattribs);
 } elseif ($configargs !== null) {
 $result = \openssl_csr_new($dn, $privkey, $configargs);
 } else {
 $result = \openssl_csr_new($dn, $privkey);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_csr_sign($csr, $cacert, $priv_key, int $days, array $configargs = null, int $serial = 0)
{
 error_clear_last();
 if ($serial !== 0) {
 $result = \openssl_csr_sign($csr, $cacert, $priv_key, $days, $configargs, $serial);
 } elseif ($configargs !== null) {
 $result = \openssl_csr_sign($csr, $cacert, $priv_key, $days, $configargs);
 } else {
 $result = \openssl_csr_sign($csr, $cacert, $priv_key, $days);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_decrypt(string $data, string $method, string $key, int $options = 0, string $iv = "", string $tag = "", string $aad = ""): string
{
 error_clear_last();
 $result = \openssl_decrypt($data, $method, $key, $options, $iv, $tag, $aad);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_dh_compute_key(string $pub_key, $dh_key): string
{
 error_clear_last();
 $result = \openssl_dh_compute_key($pub_key, $dh_key);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_digest(string $data, string $method, bool $raw_output = false): string
{
 error_clear_last();
 $result = \openssl_digest($data, $method, $raw_output);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_open(string $sealed_data, ?string &$open_data, string $env_key, $priv_key_id, string $method = "RC4", string $iv = null): void
{
 error_clear_last();
 if ($iv !== null) {
 $result = \openssl_open($sealed_data, $open_data, $env_key, $priv_key_id, $method, $iv);
 } else {
 $result = \openssl_open($sealed_data, $open_data, $env_key, $priv_key_id, $method);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pbkdf2(string $password, string $salt, int $key_length, int $iterations, string $digest_algorithm = "sha1"): string
{
 error_clear_last();
 $result = \openssl_pbkdf2($password, $salt, $key_length, $iterations, $digest_algorithm);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_pkcs12_export_to_file($x509, string $filename, $priv_key, string $pass, array $args = null): void
{
 error_clear_last();
 if ($args !== null) {
 $result = \openssl_pkcs12_export_to_file($x509, $filename, $priv_key, $pass, $args);
 } else {
 $result = \openssl_pkcs12_export_to_file($x509, $filename, $priv_key, $pass);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs12_export($x509, ?string &$out, $priv_key, string $pass, array $args = null): void
{
 error_clear_last();
 if ($args !== null) {
 $result = \openssl_pkcs12_export($x509, $out, $priv_key, $pass, $args);
 } else {
 $result = \openssl_pkcs12_export($x509, $out, $priv_key, $pass);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs12_read(string $pkcs12, ?array &$certs, string $pass): void
{
 error_clear_last();
 $result = \openssl_pkcs12_read($pkcs12, $certs, $pass);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs7_decrypt(string $infilename, string $outfilename, $recipcert, $recipkey = null): void
{
 error_clear_last();
 if ($recipkey !== null) {
 $result = \openssl_pkcs7_decrypt($infilename, $outfilename, $recipcert, $recipkey);
 } else {
 $result = \openssl_pkcs7_decrypt($infilename, $outfilename, $recipcert);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs7_encrypt(string $infile, string $outfile, $recipcerts, array $headers, int $flags = 0, int $cipherid = OPENSSL_CIPHER_RC2_40): void
{
 error_clear_last();
 $result = \openssl_pkcs7_encrypt($infile, $outfile, $recipcerts, $headers, $flags, $cipherid);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs7_read(string $infilename, ?array &$certs): void
{
 error_clear_last();
 $result = \openssl_pkcs7_read($infilename, $certs);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkcs7_sign(string $infilename, string $outfilename, $signcert, $privkey, array $headers, int $flags = PKCS7_DETACHED, string $extracerts = null): void
{
 error_clear_last();
 if ($extracerts !== null) {
 $result = \openssl_pkcs7_sign($infilename, $outfilename, $signcert, $privkey, $headers, $flags, $extracerts);
 } else {
 $result = \openssl_pkcs7_sign($infilename, $outfilename, $signcert, $privkey, $headers, $flags);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkey_export_to_file($key, string $outfilename, string $passphrase = null, array $configargs = null): void
{
 error_clear_last();
 if ($configargs !== null) {
 $result = \openssl_pkey_export_to_file($key, $outfilename, $passphrase, $configargs);
 } elseif ($passphrase !== null) {
 $result = \openssl_pkey_export_to_file($key, $outfilename, $passphrase);
 } else {
 $result = \openssl_pkey_export_to_file($key, $outfilename);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkey_export($key, ?string &$out, string $passphrase = null, array $configargs = null): void
{
 error_clear_last();
 if ($configargs !== null) {
 $result = \openssl_pkey_export($key, $out, $passphrase, $configargs);
 } elseif ($passphrase !== null) {
 $result = \openssl_pkey_export($key, $out, $passphrase);
 } else {
 $result = \openssl_pkey_export($key, $out);
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_pkey_get_private(string $key, string $passphrase = "")
{
 error_clear_last();
 $result = \openssl_pkey_get_private($key, $passphrase);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_pkey_get_public($certificate)
{
 error_clear_last();
 $result = \openssl_pkey_get_public($certificate);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_pkey_new(array $configargs = null)
{
 error_clear_last();
 if ($configargs !== null) {
 $result = \openssl_pkey_new($configargs);
 } else {
 $result = \openssl_pkey_new();
 }
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_private_decrypt(string $data, ?string &$decrypted, $key, int $padding = OPENSSL_PKCS1_PADDING): void
{
 error_clear_last();
 $result = \openssl_private_decrypt($data, $decrypted, $key, $padding);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_private_encrypt(string $data, ?string &$crypted, $key, int $padding = OPENSSL_PKCS1_PADDING): void
{
 error_clear_last();
 $result = \openssl_private_encrypt($data, $crypted, $key, $padding);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_public_decrypt(string $data, ?string &$decrypted, $key, int $padding = OPENSSL_PKCS1_PADDING): void
{
 error_clear_last();
 $result = \openssl_public_decrypt($data, $decrypted, $key, $padding);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_public_encrypt(string $data, ?string &$crypted, $key, int $padding = OPENSSL_PKCS1_PADDING): void
{
 error_clear_last();
 $result = \openssl_public_encrypt($data, $crypted, $key, $padding);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_random_pseudo_bytes(int $length, ?bool &$crypto_strong = null): string
{
 error_clear_last();
 $result = \openssl_random_pseudo_bytes($length, $crypto_strong);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_seal(string $data, ?string &$sealed_data, array &$env_keys, array $pub_key_ids, string $method = "RC4", string &$iv = null): int
{
 error_clear_last();
 $result = \openssl_seal($data, $sealed_data, $env_keys, $pub_key_ids, $method, $iv);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_sign(string $data, ?string &$signature, $priv_key_id, $signature_alg = OPENSSL_ALGO_SHA1): void
{
 error_clear_last();
 $result = \openssl_sign($data, $signature, $priv_key_id, $signature_alg);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_x509_export_to_file($x509, string $outfilename, bool $notext = true): void
{
 error_clear_last();
 $result = \openssl_x509_export_to_file($x509, $outfilename, $notext);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_x509_export($x509, ?string &$output, bool $notext = true): void
{
 error_clear_last();
 $result = \openssl_x509_export($x509, $output, $notext);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
}
function openssl_x509_fingerprint($x509, string $hash_algorithm = "sha1", bool $raw_output = false): string
{
 error_clear_last();
 $result = \openssl_x509_fingerprint($x509, $hash_algorithm, $raw_output);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
function openssl_x509_read($x509certdata)
{
 error_clear_last();
 $result = \openssl_x509_read($x509certdata);
 if ($result === false) {
 throw OpensslException::createFromPhpError();
 }
 return $result;
}
