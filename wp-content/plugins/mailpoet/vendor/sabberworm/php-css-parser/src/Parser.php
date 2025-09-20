<?php
declare(strict_types=1);
namespace Sabberworm\CSS;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
class Parser
{
 private $parserState;
 public function __construct(string $text, ?Settings $parserSettings = null, int $lineNumber = 1)
 {
 if ($parserSettings === null) {
 $parserSettings = Settings::create();
 }
 $this->parserState = new ParserState($text, $parserSettings, $lineNumber);
 }
 public function parse(): Document
 {
 return Document::parse($this->parserState);
 }
}
