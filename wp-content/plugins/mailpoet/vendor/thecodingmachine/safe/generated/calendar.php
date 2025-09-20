<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\CalendarException;
function jdtounix(int $jday): int
{
 error_clear_last();
 $result = \jdtounix($jday);
 if ($result === false) {
 throw CalendarException::createFromPhpError();
 }
 return $result;
}
