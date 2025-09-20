<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ImapException;
function imap_append($imap_stream, string $mailbox, string $message, string $options = null, string $internal_date = null): void
{
 error_clear_last();
 $result = \imap_append($imap_stream, $mailbox, $message, $options, $internal_date);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_check($imap_stream): \stdClass
{
 error_clear_last();
 $result = \imap_check($imap_stream);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_clearflag_full($imap_stream, string $sequence, string $flag, int $options = 0): void
{
 error_clear_last();
 $result = \imap_clearflag_full($imap_stream, $sequence, $flag, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_close($imap_stream, int $flag = 0): void
{
 error_clear_last();
 $result = \imap_close($imap_stream, $flag);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_createmailbox($imap_stream, string $mailbox): void
{
 error_clear_last();
 $result = \imap_createmailbox($imap_stream, $mailbox);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_deletemailbox($imap_stream, string $mailbox): void
{
 error_clear_last();
 $result = \imap_deletemailbox($imap_stream, $mailbox);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_fetchstructure($imap_stream, int $msg_number, int $options = 0): \stdClass
{
 error_clear_last();
 $result = \imap_fetchstructure($imap_stream, $msg_number, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_gc($imap_stream, int $caches): void
{
 error_clear_last();
 $result = \imap_gc($imap_stream, $caches);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_headerinfo($imap_stream, int $msg_number, int $fromlength = 0, int $subjectlength = 0, string $defaulthost = null): \stdClass
{
 error_clear_last();
 $result = \imap_headerinfo($imap_stream, $msg_number, $fromlength, $subjectlength, $defaulthost);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_mail_compose(array $envelope, array $body): string
{
 error_clear_last();
 $result = \imap_mail_compose($envelope, $body);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_mail_copy($imap_stream, string $msglist, string $mailbox, int $options = 0): void
{
 error_clear_last();
 $result = \imap_mail_copy($imap_stream, $msglist, $mailbox, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_mail_move($imap_stream, string $msglist, string $mailbox, int $options = 0): void
{
 error_clear_last();
 $result = \imap_mail_move($imap_stream, $msglist, $mailbox, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_mail(string $to, string $subject, string $message, string $additional_headers = null, string $cc = null, string $bcc = null, string $rpath = null): void
{
 error_clear_last();
 $result = \imap_mail($to, $subject, $message, $additional_headers, $cc, $bcc, $rpath);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_mailboxmsginfo($imap_stream): \stdClass
{
 error_clear_last();
 $result = \imap_mailboxmsginfo($imap_stream);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_mutf7_to_utf8(string $in): string
{
 error_clear_last();
 $result = \imap_mutf7_to_utf8($in);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_num_msg($imap_stream): int
{
 error_clear_last();
 $result = \imap_num_msg($imap_stream);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_open(string $mailbox, string $username, string $password, int $options = 0, int $n_retries = 0, ?array $params = null)
{
 error_clear_last();
 $result = \imap_open($mailbox, $username, $password, $options, $n_retries, $params);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_renamemailbox($imap_stream, string $old_mbox, string $new_mbox): void
{
 error_clear_last();
 $result = \imap_renamemailbox($imap_stream, $old_mbox, $new_mbox);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_savebody($imap_stream, $file, int $msg_number, string $part_number = "", int $options = 0): void
{
 error_clear_last();
 $result = \imap_savebody($imap_stream, $file, $msg_number, $part_number, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_set_quota($imap_stream, string $quota_root, int $quota_limit): void
{
 error_clear_last();
 $result = \imap_set_quota($imap_stream, $quota_root, $quota_limit);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_setacl($imap_stream, string $mailbox, string $id, string $rights): void
{
 error_clear_last();
 $result = \imap_setacl($imap_stream, $mailbox, $id, $rights);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_setflag_full($imap_stream, string $sequence, string $flag, int $options = NIL): void
{
 error_clear_last();
 $result = \imap_setflag_full($imap_stream, $sequence, $flag, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_sort($imap_stream, int $criteria, int $reverse, int $options = 0, string $search_criteria = null, string $charset = null): array
{
 error_clear_last();
 $result = \imap_sort($imap_stream, $criteria, $reverse, $options, $search_criteria, $charset);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_subscribe($imap_stream, string $mailbox): void
{
 error_clear_last();
 $result = \imap_subscribe($imap_stream, $mailbox);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_thread($imap_stream, int $options = SE_FREE): array
{
 error_clear_last();
 $result = \imap_thread($imap_stream, $options);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_timeout(int $timeout_type, int $timeout = -1)
{
 error_clear_last();
 $result = \imap_timeout($timeout_type, $timeout);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
function imap_undelete($imap_stream, int $msg_number, int $flags = 0): void
{
 error_clear_last();
 $result = \imap_undelete($imap_stream, $msg_number, $flags);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_unsubscribe($imap_stream, string $mailbox): void
{
 error_clear_last();
 $result = \imap_unsubscribe($imap_stream, $mailbox);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
}
function imap_utf8_to_mutf7(string $in): string
{
 error_clear_last();
 $result = \imap_utf8_to_mutf7($in);
 if ($result === false) {
 throw ImapException::createFromPhpError();
 }
 return $result;
}
