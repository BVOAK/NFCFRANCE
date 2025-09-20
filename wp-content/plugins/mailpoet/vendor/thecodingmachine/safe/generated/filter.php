<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\FilterException;
function filter_input_array(int $type, $definition = null, bool $add_empty = true)
{
 error_clear_last();
 if ($add_empty !== true) {
 $result = \filter_input_array($type, $definition, $add_empty);
 } elseif ($definition !== null) {
 $result = \filter_input_array($type, $definition);
 } else {
 $result = \filter_input_array($type);
 }
 if ($result === false) {
 throw FilterException::createFromPhpError();
 }
 return $result;
}
function filter_var_array(array $data, $definition = null, bool $add_empty = true)
{
 error_clear_last();
 if ($add_empty !== true) {
 $result = \filter_var_array($data, $definition, $add_empty);
 } elseif ($definition !== null) {
 $result = \filter_var_array($data, $definition);
 } else {
 $result = \filter_var_array($data);
 }
 if ($result === false) {
 throw FilterException::createFromPhpError();
 }
 return $result;
}
