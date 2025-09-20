<?php
declare(strict_types=1);
namespace Pelago\Emogrifier\Css;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
final class StyleRule
{
 private $declarationBlock;
 private $containingAtRule;
 public function __construct(DeclarationBlock $declarationBlock, string $containingAtRule = '')
 {
 $this->declarationBlock = $declarationBlock;
 $this->containingAtRule = \trim($containingAtRule);
 }
 public function getSelectors(): array
 {
 $selectors = $this->declarationBlock->getSelectors();
 return \array_map(
 static function (Selector $selector): string {
 $selectorAsString = $selector->getSelector();
 \assert($selectorAsString !== '');
 return $selectorAsString;
 },
 $selectors
 );
 }
 public function getDeclarationAsText(): string
 {
 $rules = $this->declarationBlock->getRules();
 $renderedRules = [];
 $outputFormat = OutputFormat::create();
 foreach ($rules as $rule) {
 $renderedRules[] = $rule->render($outputFormat);
 }
 return \implode(' ', $renderedRules);
 }
 public function hasAtLeastOneDeclaration(): bool
 {
 return $this->declarationBlock->getRules() !== [];
 }
 public function getContainingAtRule(): string
 {
 return $this->containingAtRule;
 }
 public function hasContainingAtRule(): bool
 {
 return $this->getContainingAtRule() !== '';
 }
}
