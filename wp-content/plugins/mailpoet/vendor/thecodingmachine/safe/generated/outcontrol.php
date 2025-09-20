<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\OutcontrolException;
function ob_end_clean(): void
{
 error_clear_last();
 $result = \ob_end_clean();
 if ($result === false) {
 throw OutcontrolException::createFromPhpError();
 }
}
function ob_end_flush(): void
{
 error_clear_last();
 $result = \ob_end_flush();
 if ($result === false) {
 throw OutcontrolException::createFromPhpError();
 }
}
function output_add_rewrite_var(string $name, string $value): void
{
 error_clear_last();
 $result = \output_add_rewrite_var($name, $value);
 if ($result === false) {
 throw OutcontrolException::createFromPhpError();
 }
}
function output_reset_rewrite_vars(): void
{
 error_clear_last();
 $result = \output_reset_rewrite_vars();
 if ($result === false) {
 throw OutcontrolException::createFromPhpError();
 }
}
