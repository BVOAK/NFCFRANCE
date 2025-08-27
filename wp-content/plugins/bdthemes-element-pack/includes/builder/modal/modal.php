

<div class="bdt-modal-overlay" id="bdthemes-templates-builder-modal" style="display: none">
    <div id="bdthemes-templates-builder-modal-wrapper">
        <div class="bdt-template-modal-header">
            <div class="bdt-modal-logo-wrap">
                <span class="bdt-logo-text">New Template</span>
            </div>
            <div class="bdt-modal-close-button">
                <a href="javascript:void(0)">
                    <i class="eicon-editor-close"></i>
                </a>
            </div>
        </div>
        <div class="bdt-template-modal-main-wrap">
            <div class="bdt-modal-content-wrap">
                <h3 class="bdt-modal-title">Templates Help You <span>Work Efficiently</span>
                </h3>
                <div class="bdt-modal-desc">Use templates to create the
                    different pieces of your site, and reuse them
                    with one click whenever needed.
                </div>
            </div>
            <div class="bdt-modal-form-wrap">
                <form class="bdt-modal-form" method="post">
                    <input type="hidden" name="template_id" value="" class="template_id" />
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'ep-builder' ); ?>" />
                    <div class="bdt-form-title">Choose Template Type</div>
                    <label for="template_type">Select the type of template you want to work on</label>
                    <select name="template_type" id="template_type">
                        <option value="">select</option>
                        <?php

                        $templates = \ElementPack\Includes\Builder\Builder_Template_Helper::templateForSelectDropdown();
                        $separator = \ElementPack\Includes\Builder\Builder_Template_Helper::separator();


                        // It is single
                        if (count($templates) == 1) {
                            $templateKey = array_key_last($templates);
                            $template    = $templates[$templateKey];
                            foreach ($template as $key => $item) :
                                $selectValue = "{$templateKey}{$separator}{$key}";
                        ?>
                                <option value="<?php echo esc_attr($selectValue) ?>"><?php echo esc_attr($item) ?></option>
                                <?php
                            endforeach;
                        }

                        if (count($templates) > 1) {
                            foreach ($templates as $keys => $items) :
                                $label = ucwords(str_replace(['-', '_'], [' '], $keys));
                                if (is_array($items)) {
                                ?>
                                    <optgroup label="<?php echo wp_kses_post($label); ?>">
                                        <?php
                                        foreach ($items as $key => $item) :
                                            $itemValue = "{$keys}{$separator}{$key}"
                                        ?>
                                            <option value="<?php echo esc_attr($itemValue) ?>"><?php echo esc_attr($item) ?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </optgroup>
                        <?php
                                }
                            endforeach;
                        }
                        ?>
                    </select>
                    <label for="fname">Name your template</label>
                    <input type="text" name="template_name" id="template_name" placeholder="Enter template name">

                    <div class="bdt-header-footer-option-container" style="display:none;">
						<div class="ekit-input-group">
							<label class="attr-input-label bdt-display-block"><?php esc_html_e( 'Conditions:', 'bdthemes-element-pack' ); ?></label>
							<select name="condition_a" class="bdt-template-modalinput-condition_a attr-form-control">
								<option value="entire_site"><?php esc_html_e( 'Entire Site', 'bdthemes-element-pack' ); ?></option>
								<option value="singular"><?php esc_html_e( 'Singular', 'bdthemes-element-pack' ); ?></option>
								<option value="archive"><?php esc_html_e( 'Archive', 'bdthemes-element-pack' ); ?></option>
							</select>
						</div>
						<br>
					
						<div class="bdt-template-modalinput-condition_singular-container" style="display:none;">
							<div class="ekit-input-group">
								<label class="attr-input-label"></label>
								<select name="condition_singular" class="bdt-template-modalinput-condition_singular attr-form-control">
									<option value="all"><?php esc_html_e( 'All Singulars', 'bdthemes-element-pack' ); ?></option>
									<option value="front_page"><?php esc_html_e( 'Front Page', 'bdthemes-element-pack' ); ?></option>
									<option value="all_posts"><?php esc_html_e( 'All Posts', 'bdthemes-element-pack' ); ?></option>
									<option value="all_pages"><?php esc_html_e( 'All Pages', 'bdthemes-element-pack' ); ?></option>
									<option value="selective"><?php esc_html_e( 'Selective Singular', 'bdthemes-element-pack' ); ?>
									</option>
									<option value="404page"><?php esc_html_e( '404 Page', 'bdthemes-element-pack' ); ?></option>
                                </select>
                            </div>
                            <br>
                    
                            <div class="bdt-template-modalinput-condition_singular_id-container ekit_multipile_ajax_search_filed">
                                <div class="ekit-input-group">
                                    <label class="attr-input-label"></label>
                                    <select multiple name="condition_singular_id[]"
                                        class="bdt-template-modalinput-condition_singular_id" style="width:100%;">
                                    </select>
                                </div>
                                <br />
                            </div>
                            <br>
                        </div>
                    </div>

                    <select name="template_status" id="template_status">
                        <option value="">select</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <input class="bdt-modal-submit-btn" type="submit" value="Create Template">
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .input-error {
        border: 1px solid red !important;
    }
</style>
