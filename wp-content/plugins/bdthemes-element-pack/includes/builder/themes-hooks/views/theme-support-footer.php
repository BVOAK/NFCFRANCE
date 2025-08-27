<?php do_action('bdt-templates-builder/template/before_footer'); ?>
<div class="bdt-template-content-markup bdt-template-content-footer bdt-template-content-theme-support">
    <?php
    $template = \ElementPack\Builder\Activator::template_ids();
    echo bdt_templates_render_elementor_content($template[1]);
    ?>
</div>
<?php do_action('bdt-templates-builder/template/after_footer'); ?>
<?php wp_footer(); ?>

</body>

</html>