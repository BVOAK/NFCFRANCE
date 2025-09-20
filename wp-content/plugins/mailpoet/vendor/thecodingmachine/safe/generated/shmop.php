<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ShmopException;
function shmop_delete($shmid): void
{
 error_clear_last();
 $result = \shmop_delete($shmid);
 if ($result === false) {
 throw ShmopException::createFromPhpError();
 }
}
function shmop_read($shmid, int $start, int $count): string
{
 error_clear_last();
 $result = \shmop_read($shmid, $start, $count);
 if ($result === false) {
 throw ShmopException::createFromPhpError();
 }
 return $result;
}
function shmop_write($shmid, string $data, int $offset): int
{
 error_clear_last();
 $result = \shmop_write($shmid, $data, $offset);
 if ($result === false) {
 throw ShmopException::createFromPhpError();
 }
 return $result;
}
