<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\VarException;
function settype(&$var, string $type): void
{
 error_clear_last();
 $result = \settype($var, $type);
 if ($result === false) {
 throw VarException::createFromPhpError();
 }
}
