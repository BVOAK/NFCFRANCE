<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\PcntlException;
function pcntl_exec(string $path, array $args = null, array $envs = null): void
{
 error_clear_last();
 if ($envs !== null) {
 $result = \pcntl_exec($path, $args, $envs);
 } elseif ($args !== null) {
 $result = \pcntl_exec($path, $args);
 } else {
 $result = \pcntl_exec($path);
 }
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
}
function pcntl_getpriority(int $pid = null, int $process_identifier = PRIO_PROCESS): int
{
 error_clear_last();
 if ($process_identifier !== PRIO_PROCESS) {
 $result = \pcntl_getpriority($pid, $process_identifier);
 } elseif ($pid !== null) {
 $result = \pcntl_getpriority($pid);
 } else {
 $result = \pcntl_getpriority();
 }
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
 return $result;
}
function pcntl_setpriority(int $priority, int $pid = null, int $process_identifier = PRIO_PROCESS): void
{
 error_clear_last();
 if ($process_identifier !== PRIO_PROCESS) {
 $result = \pcntl_setpriority($priority, $pid, $process_identifier);
 } elseif ($pid !== null) {
 $result = \pcntl_setpriority($priority, $pid);
 } else {
 $result = \pcntl_setpriority($priority);
 }
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
}
function pcntl_signal_dispatch(): void
{
 error_clear_last();
 $result = \pcntl_signal_dispatch();
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
}
function pcntl_sigprocmask(int $how, array $set, ?array &$oldset = null): void
{
 error_clear_last();
 $result = \pcntl_sigprocmask($how, $set, $oldset);
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
}
function pcntl_strerror(int $errno): string
{
 error_clear_last();
 $result = \pcntl_strerror($errno);
 if ($result === false) {
 throw PcntlException::createFromPhpError();
 }
 return $result;
}
