<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Value;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Parsing\UnexpectedEOFException;
use Sabberworm\CSS\Parsing\UnexpectedTokenException;
class CSSString extends PrimitiveValue
{
 private $string;
 public function __construct(string $string, ?int $lineNumber = null)
 {
 $this->string = $string;
 parent::__construct($lineNumber);
 }
 public static function parse(ParserState $parserState): CSSString
 {
 $begin = $parserState->peek();
 $quote = null;
 if ($begin === "'") {
 $quote = "'";
 } elseif ($begin === '"') {
 $quote = '"';
 }
 if ($quote !== null) {
 $parserState->consume($quote);
 }
 $result = '';
 $content = null;
 if ($quote === null) {
 // Unquoted strings end in whitespace or with braces, brackets, parentheses
 while (\preg_match('/[\\s{}()<>\\[\\]]/isu', $parserState->peek()) !== 1) {
 $result .= $parserState->parseCharacter(false);
 }
 } else {
 while (!$parserState->comes($quote)) {
 $content = $parserState->parseCharacter(false);
 if ($content === null) {
 throw new SourceException(
 "Non-well-formed quoted string {$parserState->peek(3)}",
 $parserState->currentLine()
 );
 }
 $result .= $content;
 }
 $parserState->consume($quote);
 }
 return new CSSString($result, $parserState->currentLine());
 }
 public function setString(string $string): void
 {
 $this->string = $string;
 }
 public function getString(): string
 {
 return $this->string;
 }
 public function render(OutputFormat $outputFormat): string
 {
 $string = \addslashes($this->string);
 $string = \str_replace("\n", '\\A', $string);
 return $outputFormat->getStringQuotingType() . $string . $outputFormat->getStringQuotingType();
 }
}
