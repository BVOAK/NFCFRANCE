<?php
declare(strict_types=1);
namespace Sabberworm\CSS\CSSList;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parsing\ParserState;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Property\Selector;
class Document extends CSSBlockList
{
 public static function parse(ParserState $parserState): Document
 {
 $document = new Document($parserState->currentLine());
 CSSList::parseList($parserState, $document);
 return $document;
 }
 public function getSelectorsBySpecificity(?string $specificitySearch = null): array
 {
 return $this->getAllSelectors($specificitySearch);
 }
 public function render(?OutputFormat $outputFormat = null): string
 {
 if ($outputFormat === null) {
 $outputFormat = new OutputFormat();
 }
 return $outputFormat->getFormatter()->comments($this) . $this->renderListContents($outputFormat);
 }
 public function isRootList(): bool
 {
 return true;
 }
}
