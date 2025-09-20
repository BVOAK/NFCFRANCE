<?php
declare(strict_types=1);
namespace Sabberworm\CSS\CSSList;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\RuleSet\RuleContainer;
use Sabberworm\CSS\RuleSet\RuleSet;
use Sabberworm\CSS\Value\CSSFunction;
use Sabberworm\CSS\Value\Value;
use Sabberworm\CSS\Value\ValueList;
abstract class CSSBlockList extends CSSList
{
 public function getAllDeclarationBlocks(): array
 {
 $result = [];
 foreach ($this->contents as $item) {
 if ($item instanceof DeclarationBlock) {
 $result[] = $item;
 } elseif ($item instanceof CSSBlockList) {
 $result = \array_merge($result, $item->getAllDeclarationBlocks());
 }
 }
 return $result;
 }
 public function getAllRuleSets(): array
 {
 $result = [];
 foreach ($this->contents as $item) {
 if ($item instanceof RuleSet) {
 $result[] = $item;
 } elseif ($item instanceof CSSBlockList) {
 $result = \array_merge($result, $item->getAllRuleSets());
 } elseif ($item instanceof DeclarationBlock) {
 $result[] = $item->getRuleSet();
 }
 }
 return $result;
 }
 public function getAllValues(
 ?CSSElement $element = null,
 ?string $ruleSearchPattern = null,
 bool $searchInFunctionArguments = false
 ): array {
 $element = $element ?? $this;
 $result = [];
 if ($element instanceof CSSBlockList) {
 foreach ($element->getContents() as $contentItem) {
 // Statement at-rules are skipped since they do not contain values.
 if ($contentItem instanceof CSSElement) {
 $result = \array_merge(
 $result,
 $this->getAllValues($contentItem, $ruleSearchPattern, $searchInFunctionArguments)
 );
 }
 }
 } elseif ($element instanceof RuleContainer) {
 foreach ($element->getRules($ruleSearchPattern) as $rule) {
 $result = \array_merge(
 $result,
 $this->getAllValues($rule, $ruleSearchPattern, $searchInFunctionArguments)
 );
 }
 } elseif ($element instanceof Rule) {
 $value = $element->getValue();
 // `string` values are discarded.
 if ($value instanceof CSSElement) {
 $result = \array_merge(
 $result,
 $this->getAllValues($value, $ruleSearchPattern, $searchInFunctionArguments)
 );
 }
 } elseif ($element instanceof ValueList) {
 if ($searchInFunctionArguments || !($element instanceof CSSFunction)) {
 foreach ($element->getListComponents() as $component) {
 // `string` components are discarded.
 if ($component instanceof CSSElement) {
 $result = \array_merge(
 $result,
 $this->getAllValues($component, $ruleSearchPattern, $searchInFunctionArguments)
 );
 }
 }
 }
 } elseif ($element instanceof Value) {
 $result[] = $element;
 }
 return $result;
 }
 protected function getAllSelectors(?string $specificitySearch = null): array
 {
 $result = [];
 foreach ($this->getAllDeclarationBlocks() as $declarationBlock) {
 foreach ($declarationBlock->getSelectors() as $selector) {
 if ($specificitySearch === null) {
 $result[] = $selector;
 } else {
 $comparator = '===';
 $expressionParts = \explode(' ', $specificitySearch);
 $targetSpecificity = $expressionParts[0];
 if (\count($expressionParts) > 1) {
 $comparator = $expressionParts[0];
 $targetSpecificity = $expressionParts[1];
 }
 $targetSpecificity = (int) $targetSpecificity;
 $selectorSpecificity = $selector->getSpecificity();
 $comparatorMatched = false;
 switch ($comparator) {
 case '<=':
 $comparatorMatched = $selectorSpecificity <= $targetSpecificity;
 break;
 case '<':
 $comparatorMatched = $selectorSpecificity < $targetSpecificity;
 break;
 case '>=':
 $comparatorMatched = $selectorSpecificity >= $targetSpecificity;
 break;
 case '>':
 $comparatorMatched = $selectorSpecificity > $targetSpecificity;
 break;
 default:
 $comparatorMatched = $selectorSpecificity === $targetSpecificity;
 break;
 }
 if ($comparatorMatched) {
 $result[] = $selector;
 }
 }
 }
 }
 return $result;
 }
}
