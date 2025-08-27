<?php

namespace ElementPack\Includes\Builder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use ElementPack\Includes\Builder\Builder_Template_Helper;
use \ElementPack\Base\Singleton;

class Meta {

    use Singleton;

    const POST_TYPE = 'bdt-template-builder';

    const EDIT_WITH = '_bdthemes_builder_edit_with';

    const TEMPLATE_TYPE = '_bdthemes_builder_template_type';

    const TEMPLATE_ID = '_bdthemes_builder_';

    const SAMPLE_POST_ID = 'sample_post_id';
}
