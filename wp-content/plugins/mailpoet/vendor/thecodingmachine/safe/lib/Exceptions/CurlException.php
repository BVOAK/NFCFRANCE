<?php
namespace Safe\Exceptions;
if (!defined('ABSPATH')) exit;
class CurlException extends \Exception implements SafeExceptionInterface
{
 public static function createFromCurlResource($ch): self
 {
 return new self(\curl_error($ch), \curl_errno($ch));
 }
}
