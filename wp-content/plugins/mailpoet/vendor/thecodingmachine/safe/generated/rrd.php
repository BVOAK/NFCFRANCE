<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\RrdException;
function rrd_create(string $filename, array $options): void
{
 error_clear_last();
 $result = \rrd_create($filename, $options);
 if ($result === false) {
 throw RrdException::createFromPhpError();
 }
}
