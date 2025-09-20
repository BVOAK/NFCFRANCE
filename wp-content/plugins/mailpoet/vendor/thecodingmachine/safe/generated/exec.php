<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ExecException;
function proc_get_status($process): array
{
 error_clear_last();
 $result = \proc_get_status($process);
 if ($result === false) {
 throw ExecException::createFromPhpError();
 }
 return $result;
}
function proc_nice(int $increment): void
{
 error_clear_last();
 $result = \proc_nice($increment);
 if ($result === false) {
 throw ExecException::createFromPhpError();
 }
}
function system(string $command, int &$return_var = null): string
{
 error_clear_last();
 $result = \system($command, $return_var);
 if ($result === false) {
 throw ExecException::createFromPhpError();
 }
 return $result;
}
