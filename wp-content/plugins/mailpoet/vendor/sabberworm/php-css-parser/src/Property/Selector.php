<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Property;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Property\Selector\SpecificityCalculator;
use Sabberworm\CSS\Renderable;
use function Safe\preg_match;
class Selector implements Renderable
{
 public const SELECTOR_VALIDATION_RX = '/
 ^(
 (?:
 # any sequence of valid unescaped characters, except quotes
 [a-zA-Z0-9\\x{00A0}-\\x{FFFF}_^$|*=~\\[\\]()\\-\\s\\.:#+>,]++
 |
 # one or more escaped characters
 (?:\\\\.)++
 |
 # quoted text, like in `[id="example"]`
 (?:
 # opening quote
 ([\'"])
 (?:
 # sequence of characters except closing quote or backslash
 (?:(?!\\g{-1}|\\\\).)++
 |
 # one or more escaped characters
 (?:\\\\.)++
 )*+ # zero or more times
 # closing quote or end (unmatched quote is currently allowed)
 (?:\\g{-1}|$)
 )
 )*+ # zero or more times
 )$
 /ux';
 private $selector;
 public static function isValid(string $selector): bool
 {
 // Note: We need to use `static::` here as the constant is overridden in the `KeyframeSelector` class.
 $numberOfMatches = preg_match(static::SELECTOR_VALIDATION_RX, $selector);
 return $numberOfMatches === 1;
 }
 public function __construct(string $selector)
 {
 $this->setSelector($selector);
 }
 public function getSelector(): string
 {
 return $this->selector;
 }
 public function setSelector(string $selector): void
 {
 $this->selector = \trim($selector);
 }
 public function getSpecificity(): int
 {
 return SpecificityCalculator::calculate($this->selector);
 }
 public function render(OutputFormat $outputFormat): string
 {
 return $this->getSelector();
 }
}
