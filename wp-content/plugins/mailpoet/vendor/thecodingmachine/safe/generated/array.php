<?php
namespace Safe;
if (!defined('ABSPATH')) exit;
use Safe\Exceptions\ArrayException;
function array_combine(array $keys, array $values): array
{
 error_clear_last();
 $result = \array_combine($keys, $values);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
 return $result;
}
function array_flip(array $array): array
{
 error_clear_last();
 $result = \array_flip($array);
 if ($result === null) {
 throw ArrayException::createFromPhpError();
 }
 return $result;
}
function array_replace_recursive(array $array1, array ...$params): array
{
 error_clear_last();
 if ($params !== []) {
 $result = \array_replace_recursive($array1, ...$params);
 } else {
 $result = \array_replace_recursive($array1);
 }
 if ($result === null) {
 throw ArrayException::createFromPhpError();
 }
 return $result;
}
function array_replace(array $array1, array ...$params): array
{
 error_clear_last();
 if ($params !== []) {
 $result = \array_replace($array1, ...$params);
 } else {
 $result = \array_replace($array1);
 }
 if ($result === null) {
 throw ArrayException::createFromPhpError();
 }
 return $result;
}
function array_walk_recursive(array &$array, callable $callback, $userdata = null): void
{
 error_clear_last();
 $result = \array_walk_recursive($array, $callback, $userdata);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function arsort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \arsort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function asort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \asort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function krsort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \krsort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function ksort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \ksort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function natcasesort(array &$array): void
{
 error_clear_last();
 $result = \natcasesort($array);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function natsort(array &$array): void
{
 error_clear_last();
 $result = \natsort($array);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function rsort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \rsort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function shuffle(array &$array): void
{
 error_clear_last();
 $result = \shuffle($array);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function sort(array &$array, int $sort_flags = SORT_REGULAR): void
{
 error_clear_last();
 $result = \sort($array, $sort_flags);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function uasort(array &$array, callable $value_compare_func): void
{
 error_clear_last();
 $result = \uasort($array, $value_compare_func);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function uksort(array &$array, callable $key_compare_func): void
{
 error_clear_last();
 $result = \uksort($array, $key_compare_func);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
function usort(array &$array, callable $value_compare_func): void
{
 error_clear_last();
 $result = \usort($array, $value_compare_func);
 if ($result === false) {
 throw ArrayException::createFromPhpError();
 }
}
