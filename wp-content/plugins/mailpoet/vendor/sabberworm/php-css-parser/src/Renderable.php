<?php
declare(strict_types=1);
namespace Sabberworm\CSS;
if (!defined('ABSPATH')) exit;
interface Renderable
{
 public function render(OutputFormat $outputFormat): string;
}
