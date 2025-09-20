<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Parsing;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;
class SourceException extends \Exception implements Positionable
{
 use Position;
 public function __construct(string $message, ?int $lineNumber = null)
 {
 $this->setPosition($lineNumber);
 if ($lineNumber !== null) {
 $message .= " [line no: $lineNumber]";
 }
 parent::__construct($message);
 }
}
