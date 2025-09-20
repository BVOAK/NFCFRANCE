<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Position;
if (!defined('ABSPATH')) exit;
interface Positionable
{
 public function getLineNumber(): ?int;
 public function getColumnNumber(): ?int;
 public function setPosition(?int $lineNumber, ?int $columnNumber = null): Positionable;
}
