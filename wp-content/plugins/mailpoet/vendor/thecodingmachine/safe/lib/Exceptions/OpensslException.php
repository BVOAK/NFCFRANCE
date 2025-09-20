<?php
namespace Safe\Exceptions;
if (!defined('ABSPATH')) exit;
class OpensslException extends \Exception implements SafeExceptionInterface
{
 public static function createFromPhpError(): self
 {
 return new self(\openssl_error_string() ?: '', 0);
 }
}
