<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\PspellException;
function pspell_add_to_personal(int $dictionary_link, string $word): void
{
 error_clear_last();
 $result = \pspell_add_to_personal($dictionary_link, $word);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_add_to_session(int $dictionary_link, string $word): void
{
 error_clear_last();
 $result = \pspell_add_to_session($dictionary_link, $word);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_clear_session(int $dictionary_link): void
{
 error_clear_last();
 $result = \pspell_clear_session($dictionary_link);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_create(string $language, string $spelling = null, string $jargon = null, string $encoding = null): int
{
 error_clear_last();
 if ($encoding !== null) {
 $result = \pspell_config_create($language, $spelling, $jargon, $encoding);
 } elseif ($jargon !== null) {
 $result = \pspell_config_create($language, $spelling, $jargon);
 } elseif ($spelling !== null) {
 $result = \pspell_config_create($language, $spelling);
 } else {
 $result = \pspell_config_create($language);
 }
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
 return $result;
}
function pspell_config_data_dir(int $conf, string $directory): void
{
 error_clear_last();
 $result = \pspell_config_data_dir($conf, $directory);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_dict_dir(int $conf, string $directory): void
{
 error_clear_last();
 $result = \pspell_config_dict_dir($conf, $directory);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_ignore(int $dictionary_link, int $n): void
{
 error_clear_last();
 $result = \pspell_config_ignore($dictionary_link, $n);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_mode(int $dictionary_link, int $mode): void
{
 error_clear_last();
 $result = \pspell_config_mode($dictionary_link, $mode);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_personal(int $dictionary_link, string $file): void
{
 error_clear_last();
 $result = \pspell_config_personal($dictionary_link, $file);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_repl(int $dictionary_link, string $file): void
{
 error_clear_last();
 $result = \pspell_config_repl($dictionary_link, $file);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_runtogether(int $dictionary_link, bool $flag): void
{
 error_clear_last();
 $result = \pspell_config_runtogether($dictionary_link, $flag);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_config_save_repl(int $dictionary_link, bool $flag): void
{
 error_clear_last();
 $result = \pspell_config_save_repl($dictionary_link, $flag);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_new_config(int $config): int
{
 error_clear_last();
 $result = \pspell_new_config($config);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
 return $result;
}
function pspell_new(string $language, string $spelling = null, string $jargon = null, string $encoding = null, int $mode = 0): int
{
 error_clear_last();
 if ($mode !== 0) {
 $result = \pspell_new($language, $spelling, $jargon, $encoding, $mode);
 } elseif ($encoding !== null) {
 $result = \pspell_new($language, $spelling, $jargon, $encoding);
 } elseif ($jargon !== null) {
 $result = \pspell_new($language, $spelling, $jargon);
 } elseif ($spelling !== null) {
 $result = \pspell_new($language, $spelling);
 } else {
 $result = \pspell_new($language);
 }
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
 return $result;
}
function pspell_save_wordlist(int $dictionary_link): void
{
 error_clear_last();
 $result = \pspell_save_wordlist($dictionary_link);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
function pspell_store_replacement(int $dictionary_link, string $misspelled, string $correct): void
{
 error_clear_last();
 $result = \pspell_store_replacement($dictionary_link, $misspelled, $correct);
 if ($result === false) {
 throw PspellException::createFromPhpError();
 }
}
