<?php
declare(strict_types=1);
namespace Sabberworm\CSS\RuleSet;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\CSSElement;
use Sabberworm\CSS\CSSList\CSSList;
use Sabberworm\CSS\CSSList\CSSListItem;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\OutputException;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Property\KeyframeSelector;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\Rule\Rule;
class DeclarationBlock implements CSSElement, CSSListItem, Positionable, RuleContainer
{
 use CommentContainer;
 use Position;
 private $selectors = [];
 private $ruleSet;
 public function __construct(?int $lineNumber = null)
 {
 $this->ruleSet = new RuleSet($lineNumber);
 $this->setPosition($lineNumber);
 }
 public static function parse(ParserState $parserState, ?CSSList $list = null): ?DeclarationBlock
 {
 $comments = [];
 $result = new DeclarationBlock($parserState->currentLine());
 try {
 $selectors = [];
 $selectorParts = [];
 $stringWrapperCharacter = null;
 $functionNestingLevel = 0;
 $consumedNextCharacter = false;
 static $stopCharacters = ['{', '}', '\'', '"', '(', ')', ','];
 do {
 if (!$consumedNextCharacter) {
 $selectorParts[] = $parserState->consume(1);
 }
 $selectorParts[] = $parserState->consumeUntil($stopCharacters, false, false, $comments);
 $nextCharacter = $parserState->peek();
 $consumedNextCharacter = false;
 switch ($nextCharacter) {
 case '\'':
 // The fallthrough is intentional.
 case '"':
 if (!\is_string($stringWrapperCharacter)) {
 $stringWrapperCharacter = $nextCharacter;
 } elseif ($stringWrapperCharacter === $nextCharacter) {
 if (\substr(\end($selectorParts), -1) !== '\\') {
 $stringWrapperCharacter = null;
 }
 }
 break;
 case '(':
 if (!\is_string($stringWrapperCharacter)) {
 ++$functionNestingLevel;
 }
 break;
 case ')':
 if (!\is_string($stringWrapperCharacter)) {
 if ($functionNestingLevel <= 0) {
 throw new UnexpectedTokenException('anything but', ')');
 }
 --$functionNestingLevel;
 }
 break;
 case ',':
 if (!\is_string($stringWrapperCharacter) && $functionNestingLevel === 0) {
 $selectors[] = \implode('', $selectorParts);
 $selectorParts = [];
 $parserState->consume(1);
 $consumedNextCharacter = true;
 }
 break;
 }
 } while (!\in_array($nextCharacter, ['{', '}'], true) || \is_string($stringWrapperCharacter));
 if ($functionNestingLevel !== 0) {
 throw new UnexpectedTokenException(')', $nextCharacter);
 }
 $selectors[] = \implode('', $selectorParts); // add final or only selector
 $result->setSelectors($selectors, $list);
 if ($parserState->comes('{')) {
 $parserState->consume(1);
 }
 } catch (UnexpectedTokenException $e) {
 if ($parserState->getSettings()->usesLenientParsing()) {
 if (!$parserState->comes('}')) {
 $parserState->consumeUntil('}', false, true);
 }
 return null;
 } else {
 throw $e;
 }
 }
 $result->setComments($comments);
 RuleSet::parseRuleSet($parserState, $result->getRuleSet());
 return $result;
 }
 public function setSelectors($selectors, ?CSSList $list = null): void
 {
 if (\is_array($selectors)) {
 $this->selectors = $selectors;
 } else {
 $this->selectors = \explode(',', $selectors);
 }
 foreach ($this->selectors as $key => $selector) {
 if (!($selector instanceof Selector)) {
 if ($list === null || !($list instanceof KeyFrame)) {
 if (!Selector::isValid($selector)) {
 throw new UnexpectedTokenException(
 "Selector did not match '" . Selector::SELECTOR_VALIDATION_RX . "'.",
 $selector,
 'custom'
 );
 }
 $this->selectors[$key] = new Selector($selector);
 } else {
 if (!KeyframeSelector::isValid($selector)) {
 throw new UnexpectedTokenException(
 "Selector did not match '" . KeyframeSelector::SELECTOR_VALIDATION_RX . "'.",
 $selector,
 'custom'
 );
 }
 $this->selectors[$key] = new KeyframeSelector($selector);
 }
 }
 }
 }
 public function removeSelector($selectorToRemove): bool
 {
 if ($selectorToRemove instanceof Selector) {
 $selectorToRemove = $selectorToRemove->getSelector();
 }
 foreach ($this->selectors as $key => $selector) {
 if ($selector->getSelector() === $selectorToRemove) {
 unset($this->selectors[$key]);
 return true;
 }
 }
 return false;
 }
 public function getSelectors(): array
 {
 return $this->selectors;
 }
 public function getRuleSet(): RuleSet
 {
 return $this->ruleSet;
 }
 public function addRule(Rule $ruleToAdd, ?Rule $sibling = null): void
 {
 $this->ruleSet->addRule($ruleToAdd, $sibling);
 }
 public function getRules(?string $searchPattern = null): array
 {
 return $this->ruleSet->getRules($searchPattern);
 }
 public function setRules(array $rules): void
 {
 $this->ruleSet->setRules($rules);
 }
 public function getRulesAssoc(?string $searchPattern = null): array
 {
 return $this->ruleSet->getRulesAssoc($searchPattern);
 }
 public function removeRule(Rule $ruleToRemove): void
 {
 $this->ruleSet->removeRule($ruleToRemove);
 }
 public function removeMatchingRules(string $searchPattern): void
 {
 $this->ruleSet->removeMatchingRules($searchPattern);
 }
 public function removeAllRules(): void
 {
 $this->ruleSet->removeAllRules();
 }
 public function render(OutputFormat $outputFormat): string
 {
 $formatter = $outputFormat->getFormatter();
 $result = $formatter->comments($this);
 if (\count($this->selectors) === 0) {
 // If all the selectors have been removed, this declaration block becomes invalid
 throw new OutputException(
 'Attempt to print declaration block with missing selector',
 $this->getLineNumber()
 );
 }
 $result .= $outputFormat->getContentBeforeDeclarationBlock();
 $result .= $formatter->implode(
 $formatter->spaceBeforeSelectorSeparator() . ',' . $formatter->spaceAfterSelectorSeparator(),
 $this->selectors
 );
 $result .= $outputFormat->getContentAfterDeclarationBlockSelectors();
 $result .= $formatter->spaceBeforeOpeningBrace() . '{';
 $result .= $this->ruleSet->render($outputFormat);
 $result .= '}';
 $result .= $outputFormat->getContentAfterDeclarationBlock();
 return $result;
 }
}
