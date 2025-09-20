<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\SemException;
function msg_queue_exists(int $key): void
{
 error_clear_last();
 $result = \msg_queue_exists($key);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function msg_receive($queue, int $desiredmsgtype, ?int &$msgtype, int $maxsize, &$message, bool $unserialize = true, int $flags = 0, ?int &$errorcode = null): void
{
 error_clear_last();
 $result = \msg_receive($queue, $desiredmsgtype, $msgtype, $maxsize, $message, $unserialize, $flags, $errorcode);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function msg_remove_queue($queue): void
{
 error_clear_last();
 $result = \msg_remove_queue($queue);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function msg_send($queue, int $msgtype, $message, bool $serialize = true, bool $blocking = true, ?int &$errorcode = null): void
{
 error_clear_last();
 $result = \msg_send($queue, $msgtype, $message, $serialize, $blocking, $errorcode);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function msg_set_queue($queue, array $data): void
{
 error_clear_last();
 $result = \msg_set_queue($queue, $data);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function sem_acquire($sem_identifier, bool $nowait = false): void
{
 error_clear_last();
 $result = \sem_acquire($sem_identifier, $nowait);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function sem_get(int $key, int $max_acquire = 1, int $perm = 0666, int $auto_release = 1)
{
 error_clear_last();
 $result = \sem_get($key, $max_acquire, $perm, $auto_release);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
 return $result;
}
function sem_release($sem_identifier): void
{
 error_clear_last();
 $result = \sem_release($sem_identifier);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function sem_remove($sem_identifier): void
{
 error_clear_last();
 $result = \sem_remove($sem_identifier);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function shm_put_var($shm_identifier, int $variable_key, $variable): void
{
 error_clear_last();
 $result = \shm_put_var($shm_identifier, $variable_key, $variable);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function shm_remove_var($shm_identifier, int $variable_key): void
{
 error_clear_last();
 $result = \shm_remove_var($shm_identifier, $variable_key);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
function shm_remove($shm_identifier): void
{
 error_clear_last();
 $result = \shm_remove($shm_identifier);
 if ($result === false) {
 throw SemException::createFromPhpError();
 }
}
