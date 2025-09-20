<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ComException;
function com_event_sink(object $comobject, object $sinkobject, $sinkinterface = null): void
{
 error_clear_last();
 if ($sinkinterface !== null) {
 $result = \com_event_sink($comobject, $sinkobject, $sinkinterface);
 } else {
 $result = \com_event_sink($comobject, $sinkobject);
 }
 if ($result === false) {
 throw ComException::createFromPhpError();
 }
}
function com_load_typelib(string $typelib_name, bool $case_sensitive = true): void
{
 error_clear_last();
 $result = \com_load_typelib($typelib_name, $case_sensitive);
 if ($result === false) {
 throw ComException::createFromPhpError();
 }
}
function com_print_typeinfo(object $comobject, string $dispinterface = null, bool $wantsink = false): void
{
 error_clear_last();
 $result = \com_print_typeinfo($comobject, $dispinterface, $wantsink);
 if ($result === false) {
 throw ComException::createFromPhpError();
 }
}
