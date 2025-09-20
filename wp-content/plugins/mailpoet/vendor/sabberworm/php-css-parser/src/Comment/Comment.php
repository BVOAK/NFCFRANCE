<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Comment;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Renderable;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
class Comment implements Positionable, Renderable
{
 use Position;
 protected $commentText;
 public function __construct(string $commentText = '', ?int $lineNumber = null)
 {
 $this->commentText = $commentText;
 $this->setPosition($lineNumber);
 }
 public function getComment(): string
 {
 return $this->commentText;
 }
 public function setComment(string $commentText): void
 {
 $this->commentText = $commentText;
 }
 public function render(OutputFormat $outputFormat): string
 {
 return '/*' . $this->commentText . '*/';
 }
}
