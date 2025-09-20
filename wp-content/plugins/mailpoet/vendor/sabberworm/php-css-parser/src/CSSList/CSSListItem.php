<?php
declare(strict_types=1);
namespace Sabberworm\CSS\CSSList;
if (!defined('ABSPATH')) exit;
use Sabberworm\CSS\Comment\Commentable;
use Sabberworm\CSS\Renderable;
interface CSSListItem extends Commentable, Renderable {}
