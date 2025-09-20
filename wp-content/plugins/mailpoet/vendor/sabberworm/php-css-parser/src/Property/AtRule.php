<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Property;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\CSSList\CSSListItem;
interface AtRule extends CSSListItem
{
 public const BLOCK_RULES = 'media/document/supports/region-style/font-feature-values';
 public function atRuleName(): string;
}
