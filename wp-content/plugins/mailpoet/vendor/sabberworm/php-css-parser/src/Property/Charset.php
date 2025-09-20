<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Property;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Value\CSSString;
class Charset implements AtRule, Positionable
{
 use CommentContainer;
 use Position;
 private $charset;
 public function __construct(CSSString $charset, ?int $lineNumber = null)
 {
 $this->charset = $charset;
 $this->setPosition($lineNumber);
 }
 public function setCharset($charset): void
 {
 $charset = $charset instanceof CSSString ? $charset : new CSSString($charset);
 $this->charset = $charset;
 }
 public function getCharset(): string
 {
 return $this->charset->getString();
 }
 public function render(OutputFormat $outputFormat): string
 {
 return "{$outputFormat->getFormatter()->comments($this)}@charset {$this->charset->render($outputFormat)};";
 }
 public function atRuleName(): string
 {
 return 'charset';
 }
 public function atRuleArgs(): CSSString
 {
 return $this->charset;
 }
}
