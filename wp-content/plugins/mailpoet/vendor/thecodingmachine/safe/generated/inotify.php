<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\InotifyException;
function inotify_init()
{
 error_clear_last();
 $result = \inotify_init();
 if ($result === false) {
 throw InotifyException::createFromPhpError();
 }
 return $result;
}
function inotify_rm_watch($inotify_instance, int $watch_descriptor): void
{
 error_clear_last();
 $result = \inotify_rm_watch($inotify_instance, $watch_descriptor);
 if ($result === false) {
 throw InotifyException::createFromPhpError();
 }
}
