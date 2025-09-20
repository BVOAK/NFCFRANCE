<?php
declare(strict_types=1);
namespace Sabberworm\CSS\RuleSet;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Rule\Rule;
interface RuleContainer
{
 public function addRule(Rule $ruleToAdd, ?Rule $sibling = null): void;
 public function removeRule(Rule $ruleToRemove): void;
 public function removeMatchingRules(string $searchPattern): void;
 public function removeAllRules(): void;
 public function setRules(array $rules): void;
 public function getRules(?string $searchPattern = null): array;
 public function getRulesAssoc(?string $searchPattern = null): array;
}
