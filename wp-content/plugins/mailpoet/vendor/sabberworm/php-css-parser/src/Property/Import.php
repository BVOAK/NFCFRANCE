<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Property;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Comment\CommentContainer;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
use Sabberworm\CSS\Value\URL;
class Import implements AtRule, Positionable
{
 use CommentContainer;
 use Position;
 private $location;
 private $mediaQuery;
 public function __construct(URL $location, ?string $mediaQuery, ?int $lineNumber = null)
 {
 $this->location = $location;
 $this->mediaQuery = $mediaQuery;
 $this->setPosition($lineNumber);
 }
 public function setLocation(URL $location): void
 {
 $this->location = $location;
 }
 public function getLocation(): URL
 {
 return $this->location;
 }
 public function render(OutputFormat $outputFormat): string
 {
 return $outputFormat->getFormatter()->comments($this) . '@import ' . $this->location->render($outputFormat)
 . ($this->mediaQuery === null ? '' : ' ' . $this->mediaQuery) . ';';
 }
 public function atRuleName(): string
 {
 return 'import';
 }
 public function atRuleArgs(): array
 {
 $result = [$this->location];
 if (\is_string($this->mediaQuery) && $this->mediaQuery !== '') {
 $result[] = $this->mediaQuery;
 }
 return $result;
 }
 public function getMediaQuery(): ?string
 {
 return $this->mediaQuery;
 }
}
