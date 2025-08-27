<?php

namespace ElementPack\Admin;

use ElementPack\Admin\AssetMinifier\Asset_Minifier;

use ElementPack\Admin\ElementPack_Admin_Settings;

if (!class_exists('ElementPack_Settings_API')):

	class ElementPack_Settings_API
	{

		/**
		 * settings sections array
		 *
		 * @var array
		 */
		protected $settings_sections = array();

		/**
		 * Settings fields array
		 *
		 * @var array
		 */
		protected $settings_fields = array();

		public function __construct()
		{
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			add_action('wp_ajax_element_pack_settings_save', [$this, "element_pack_settings_save"]);

			/**
			 * Ajax for Cache Clear
			 */
			add_action('elementor/core/files/clear_cache', [$this, "clear_cache"]);

		}

		/**
		 * Clear Cache
		 */
		public function clear_cache()
		{
			if (element_pack_is_asset_optimization_enabled()) {
				$optimize_assets = new Asset_Minifier();
				$optimize_assets->minifyCss();
				$optimize_assets->minifyJs();
				update_option('element-pack-minified-asset-manager-version', time());
			} else {
				delete_option('element-pack-minified-asset-manager-version');
			}

			return rest_ensure_response(['success' => true]);
		}

		/**
		 * Enqueue scripts and styles
		 */
		function admin_enqueue_scripts()
		{
			wp_enqueue_script('jquery');
		}

		/**
		 * Set settings sections
		 *
		 * @param array   $sections setting sections array
		 */
		function set_sections($sections)
		{
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section
		 *
		 * @param array   $section
		 */
		function add_section($section)
		{
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields
		 *
		 * @param array   $fields settings fields array
		 */
		function set_fields($fields)
		{
			$this->settings_fields = $fields;

			return $this;
		}

		function add_field($section, $field)
		{
			$defaults = array(
				'name' => '',
				'label' => '',
				'desc' => '',
				'type' => 'text'
			);

			$arg = wp_parse_args($field, $defaults);
			$this->settings_fields[$section][] = $arg;

			return $this;
		}

		function do_settings_sections($page)
		{
			global $wp_settings_sections, $wp_settings_fields;

			if (!isset($wp_settings_sections[$page])) {
				return;
			}

			$matched_height = ' bdt-grid bdt-height-match="target: > div > .ep-option-item-inner"';
			$data_settings = '';

			foreach ((array) $wp_settings_sections[$page] as $section) {

				if ($section['id'] == 'element_pack_api_settings') {
					$section_class = ' bdt-grid-medium bdt-child-width-1-3@xl';
				} elseif ($section['id'] == 'element_pack_other_settings') {
					// $data_settings = $matched_height;
					$section_class = ' bdt-grid-medium bdt-child-width-1-3@xl';
				} else {
					$section_class = ' bdt-grid-small bdt-child-width-1-3@xl';
				}



				if ($section['callback']) {
					call_user_func($section['callback'], $section);
				}

				if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
					continue;
				}
				echo '<div class="ep-options" role="presentation" ' . esc_attr($data_settings) . '>';

				echo '<p class="ep-no-result bdt-text-center bdt-width-1-1 bdt-margin-small-top bdt-padding bdt-h4">' . esc_html__('Ops! Your Searched widget not found! Do you have any idea? If yes, ', 'bdthemes-element-pack') . '<a href="https://feedback.elementpack.pro/b/3v2gg80n/feature-requests/idea/new" target="_blank">' . esc_html__('Submit here', 'bdthemes-element-pack') . '</a></p>';

				$this->do_settings_fields($page, $section['id']);

				echo '</div>';
			}
		}


		function do_settings_fields($page, $section)
		{
			global $wp_settings_fields;

			if (!isset($wp_settings_fields[$page][$section])) {
				return;
			}

			foreach ((array) $wp_settings_fields[$page][$section] as $field) {
				$class = '';

				if (!empty($field['args']['class'])) {
					$class .= ' ' . esc_attr($field['args']['class']);
				}

				if (!empty($field['args']['widget_type'])) {
					$class .= ' ep-widget-' . esc_attr($field['args']['widget_type']);
				}

				if (!empty($field['args']['widget_type']) && 'pro' == $field['args']['widget_type'] && true !== element_pack_pro_activated()) {
					$class .= ' ep-pro-inactive';
				}

				$used_widgets = self::get_used_widgets_obj();
				$widget_name = 'bdt-' . str_replace(' ', '-', strtolower($field['args']['id']));
				$used_widgets_count = 0;

				if (isset($used_widgets)) {
					$used_widgets_count = (in_array($widget_name, array_keys($used_widgets)) ? $used_widgets[$widget_name] : 0);
					if ($used_widgets_count === 0) {
						$widget_name = str_replace('_', '-', $widget_name);
						$used_widgets_count = (in_array($widget_name, array_keys($used_widgets)) ? $used_widgets[$widget_name] : 0);
					}
				}

				$widget_used_status = ' ep-used';
				if ($used_widgets_count === 0) {
					$widget_used_status = ' ep-unused';
				}

				$data_type = ' data-widget-type="' . esc_attr($field['args']['widget_type']) . '" data-content-type="' . esc_attr($field['args']['content_type']) . esc_attr($widget_used_status) . '" data-widget-name="' . strtolower($field['args']['name']) . '"';

				if (!empty($field['args']['widget_type']) && 'pro' == $field['args']['widget_type'] && true !== element_pack_pro_activated()) {
					$data_type .= ' bdt-tooltip="Sorry, Element Pack License is not activated!"';
				}

				if (!empty($field['args']['tooltip'])) {
					$data_type .= ' title="' . esc_attr($field['args']['tooltip']) . '"';
				}

				printf('<div class="ep-option-item %1$s %2$s" %3$s>', esc_attr($class), esc_attr($widget_used_status), wp_kses_post($data_type));

				call_user_func($field['callback'], $field['args']);

				echo '</div>';
			}
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 */
		function admin_init()
		{
			//register settings sections
			foreach ($this->settings_sections as $section) {
				if (false == get_option($section['id'])) {
					add_option($section['id']);
				}

				if (isset($section['desc']) && !empty($section['desc'])) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
					$callback = function () use ($section) {
						echo wp_kses_post(str_replace('"', '\"', $section['desc']));
					};
				} else if (isset($section['callback'])) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				add_settings_section($section['id'], $section['title'], $callback, $section['id']);
			}

			//register settings fields
			foreach ($this->settings_fields as $section => $field) {
				foreach ($field as $option) {

					$name = $option['name'];
					$type = isset($option['type']) ? $option['type'] : 'text';
					$label = isset($option['label']) ? $option['label'] : '';
					$callback = isset($option['callback']) ? $option['callback'] : array($this, 'callback_' . $type);

					$args = array(
						'id' => $name,
						'class' => isset($option['class']) ? 'ep-' . $name . ' ' . $option['class'] : 'ep-' . $name,
						'label_for' => "ep-{$section}[{$name}]",
						'desc' => isset($option['desc']) ? $option['desc'] : '',
						'name' => $label,
						'section' => $section,
						'size' => isset($option['size']) ? $option['size'] : null,
						'options' => isset($option['options']) ? $option['options'] : '',
						'std' => isset($option['default']) ? $option['default'] : '',
						'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
						'type' => $type,
						'placeholder' => isset($option['placeholder']) ? $option['placeholder'] : '',
						'min' => isset($option['min']) ? $option['min'] : '',
						'max' => isset($option['max']) ? $option['max'] : '',
						'step' => isset($option['step']) ? $option['step'] : '',
						'plugin_name' => !empty($option['plugin_name']) ? $option['plugin_name'] : null,
						'plugin_path' => !empty($option['plugin_path']) ? $option['plugin_path'] : null,
						'paid' => !empty($option['paid']) ? $option['paid'] : null,
						'widget_type' => !empty($option['widget_type']) ? $option['widget_type'] : null,
						'content_type' => !empty($option['content_type']) ? $option['content_type'] : null,
						'demo_url' => !empty($option['demo_url']) ? $option['demo_url'] : null,
						'video_url' => !empty($option['video_url']) ? $option['video_url'] : null,
						'tooltip' => !empty($option['tooltip']) ? $option['tooltip'] : null,
						'ep_parent_switcher' => !empty($option['ep_parent_switcher']) ? $option['ep_parent_switcher'] : null,

					);

					add_settings_field("{$section}[{$name}]", $label, $callback, $section, $section, $args);
				}
			}

			// creates our settings in the options table
			foreach ($this->settings_sections as $section) {
				register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
			}
		}

		/**
		 * Get field description for display
		 *
		 * @param array   $args settings field args
		 */
		public function get_field_description($args)
		{
			if (!empty($args['desc'])) {
				$desc = sprintf('<p class="description">%s</p>', $args['desc']);
			} else {
				$desc = '';
			}

			return $desc;
		}

		public function get_control_output($output)
		{


			$tags = [
				'div' => ['class' => [], 'bdt-grid' => []],
				'a' => ['href' => [], 'id' => [], 'target' => [], 'class' => [], 'data-bdt-tooltip' => [], 'data-tab-index' => []],
				'label' => ['for' => []],
				'span' => ['scope' => [], 'class' => []],
				'input' => ['type' => [], 'class' => [], 'id' => [], 'name' => [], 'value' => [], 'placeholder' => [], 'checked' => []],
				'i' => ['class' => [], 'aria-hidden' => []],
				'fieldset' => [],
				'br' => [],
				'select' => [
					'class' => [],
					'name' => [],
					'id' => [],
				],
				'option' => [
					'value' => [],
					'selected' => []
				],
				'h3' => [
					'class' => []
				],
				'hr' => [
					'class' => []
				],
				'ul' => [
					'class' => [],
					'bdt-tab' => [],
				],
				'li' => [],
			];

			if (isset($output)) {
				echo wp_kses($output, $tags);
			}
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_text($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$class = 'bdt-input';
			$type = isset($args['type']) ? $args['type'] : 'text';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			?>
			<div class="ep-option-item-inner">
				<?php
				if ($args['video_url']):
					?>
					<a href="<?php echo esc_url($args['video_url']); ?>" target="_blank" class="ep-option-video"
						bdt-tooltip="View <?php echo esc_html($args['name']); ?> Video Tutorial">
						<i class="bdt-wi-tutorial" aria-hidden="true"></i>
					</a>
					<?php
				endif;

				$label = sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
				$label .= '<span scope="row" class="ep-option-label">' . esc_html($args['name']) . '</span>';
				$label .= '</label>';
				echo wp_kses_post($label);

				?>
				<input type="<?php echo esc_attr($type); ?>" class="<?php echo esc_attr($class); ?>"
					id="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]"
					name="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]"
					value="<?php echo esc_attr($value); ?>" <?php echo wp_kses_post($placeholder); ?> />
				<?php

				echo wp_kses_post($this->get_field_description($args));
				?>
			</div>
			<?php
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_url($args)
		{
			$this->callback_text($args);
		}

		/**
		 * Get used widgets.
		 *
		 * @access public
		 * @since 6.0.0
		 *
		 * @return array
		 */

		public static function get_used_widgets_obj()
		{
			return ElementPack_Admin_Settings::get_used_widgets();
		}

		/**
		 * Get unused widgets.
		 *
		 * @access public
		 * @since 6.0.0
		 *
		 * @return array
		 */

		public static function get_unused_widgets_obj()
		{
			return ElementPack_Admin_Settings::get_unused_widgets();
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_checkbox($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$plugin_name = isset($args['plugin_name']) ? $args['plugin_name'] : '';
			$plugin_path = isset($args['plugin_path']) ? $args['plugin_path'] : '';
			$paid = isset($args['paid']) ? $args['paid'] : '';

			$parent_class = isset($args['ep_parent_switcher']) ? ' ep-feature-option-parent' : '';

			$used_widgets = self::get_used_widgets_obj();
			$widget_name = 'bdt-' . $args['id'];
			$used_widgets_count = 0;


			if (isset($used_widgets)) {
				$used_widgets_count = (in_array($widget_name, array_keys($used_widgets)) ? $used_widgets[$widget_name] : 0);
				if ($used_widgets_count === 0) {
					$widget_name = str_replace('_', '-', $widget_name);
					$used_widgets_count = (in_array($widget_name, array_keys($used_widgets)) ? $used_widgets[$widget_name] : 0);
				}
			}

			// $widget_using_status = '</span> <br><span class="ep-widget-count-text">' . esc_html__('Total Used', 'bdthemes-element-pack') . '  - ' . esc_html($used_widgets_count) . ' </span>';

			// remove counts
			// if (isset($args['id']) && $args['id'] == 'not') {
			// 	$widget_using_status = '';
			// }

			$html = '';

			$html .= '<div class="ep-option-item-inner">';
			$html .= '<div class="bdt-grid bdt-grid-collapse bdt-flex bdt-flex-middle">';

			$html .= '<div class="bdt-width-expand bdt-flex-inline bdt-flex-middle">';


			$html .= '<i class="bdt-wi-' . esc_attr($args['id']) . '" aria-hidden="true"></i>';
			$html .= '<div class="ep-option-label-wrap">';
			$html .= sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
			$html .= '<span scope="row" class="ep-option-label">' . $args['name'] . '</span>';
			$html .= '</label>';

			$html .= '<div class="ep-option-links">';
			if ($args['demo_url']) {
				$html .= '<a href=' . $args['demo_url'] . ' target="_blank" class="ep-option-demo" title="' . esc_html__('View ' . $args['name'] . ' Widget Demo', 'bdthemes-element-pack') . '">' . esc_html__('Demo', 'bdthemes-element-pack') . '<i class="bdt-wi-preview" aria-hidden="true"></i></a>';
			}
			if ($args['video_url']) {
				$html .= '<a href=' . $args['video_url'] . ' target="_blank" class="ep-option-video" title="View ' . $args['name'] . ' Video Tutorial">Video<i class="bdt-wi-tutorial" aria-hidden="true"></i></a>';
			}
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			$html .= '<div class="bdt-width-auto">';



			// 3rd party widgets 
			if ($plugin_name and $plugin_path) {

				if ($this->_is_plugin_installed($plugin_name, $plugin_path)) {
					if (!current_user_can('activate_plugins')) {
						return;
					}
					if (!is_plugin_active($plugin_path)) {
						$active_link = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_path);
						$html .= '<a href="' . $active_link . '" class="element-pack-3pp-active" bdt-tooltip="' . esc_html__('Activate the plugin first then you can activate this widget.', 'bdthemes-element-pack') . '"><span class="dashicons dashicons-admin-plugins"></span></a>';
					}
				} else {
					if ($paid) {
						$html .= '<a href="' . $paid . '" class="element-pack-3pp-download" bdt-tooltip="' . esc_html__('Download and install plugin first then you can activate this widget.', 'bdthemes-element-pack') . '"><span class="dashicons dashicons-download"></span></a>';
					} else {
						$install_link = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_name), 'install-plugin_' . $plugin_name);
						$html .= '<a href="' . $install_link . '" class="element-pack-3pp-install" bdt-tooltip="' . esc_html__('Install the plugin first then you can activate this widget.', 'bdthemes-element-pack') . '"><span class="dashicons dashicons-download"></span></a>';
					}
				}
				if ($this->_is_plugin_installed($plugin_name, $plugin_path) and is_plugin_active($plugin_path)) {

					$html .= '<fieldset>';
					$html .= sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
					$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
					$html .= sprintf('<input type="checkbox" class="checkbox' . $parent_class . '" id="bdt_ep_%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked($value, 'on', false));
					$html .= '<span class="switch"></span>';
					$html .= '</label>';
					$html .= '</fieldset>';
				}
			} else { // core widgets

				$html .= '<fieldset>';
				$html .= sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
				$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
				$html .= sprintf('<input type="checkbox" class="checkbox" id="bdt_ep_%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked($value, 'on', false));
				$html .= '<span class="switch"></span>';
				$html .= '</label>';
				$html .= '</fieldset>';
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			echo wp_kses($html, array(
				'div' => array(
					'class' => array(),
				),
				'span' => array(
					'class' => array(),
				),
				'label' => array(
					'for' => array(),
				),
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
					'checked' => array(),
				),
				'i' => array(
					'class' => array(),
					'aria-hidden' => array(),
				),
				'a' => array(
					'href' => array(),
					'target' => array(),
					'class' => array(),
					'bdt-tooltip' => array(),
				),
				'fieldset' => array(),
			));
		}

		function _is_plugin_installed($plugin, $plugin_path)
		{
			$installed_plugins = get_plugins();
			return isset($installed_plugins[$plugin_path]);
		}


		/**
		 * Displays a multicheckbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_multicheck($args)
		{

			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$html = '<fieldset>';
			$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id']);
			foreach ($args['options'] as $key => $label) {
				$checked = isset($value[$key]) ? $value[$key] : '0';
				$html .= sprintf('<label for="bdt_ep_%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html .= sprintf('<input type="checkbox" class="checkbox" id="bdt_ep_%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($checked, $key, false));
				$html .= '<span class="switch"></span>';
				$html .= sprintf('%1$s</label><br>', $label);
			}

			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			echo wp_kses($html, array(
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
					'checked' => array(),
				),
				'label' => array(
					'for' => array(),
				),
				'span' => array(
					'class' => array(),
				),
				'br' => array(),
				'fieldset' => array(),
				'p' => array(
					'class' => array(),
				),
			));
		}

		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_radio($args)
		{

			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$html = '<fieldset>';

			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<label for="bdt_ep_%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html .= sprintf('<input type="radio" class="radio" id="bdt_ep_%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($value, $key, false));
				$html .= sprintf('%1$s</label><br>', $label);
			}

			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			echo wp_kses($html, array(
				'fieldset' => array(),
				'label' => array(
					'for' => true,
				),
				'input' => array(
					'type' => true,
					'class' => true,
					'id' => true,
					'name' => true,
					'value' => true,
					'checked' => true,
				),
				'br' => array(),
			));
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_number($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$type = isset($args['type']) ? $args['type'] : 'number';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min = ($args['min'] == '') ? '' : ' min="' . $args['min'] . '"';
			$max = ($args['max'] == '') ? '' : ' max="' . $args['max'] . '"';
			$step = ($args['step'] == '') ? '' : ' step="' . $args['step'] . '"';


			$html = '';
			$html .= '<div class="bdt-grid bdt-grid-collapse bdt-flex bdt-flex-middle">';

			$html .= '<div class="bdt-width-1-2 bdt-flex-inline bdt-flex-left">';
			$html .= sprintf('<label for="bdt_%1$s[%2$s]">%3$s</label>', $args['section'], $args['id'], $args['name']);
			$html .= '</div>';

			$html .= '<div class="bdt-width-1-2 bdt-flex-inline bdt-flex-right">';
			$html .= sprintf('<input type="%1$s" class="bdt-width-1-2 %2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step);
			$html .= '</div>';

			$html .= '</div>';

			$html .= $this->get_field_description($args);
			$this->get_control_output($html);
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_select($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = '';
			$html .= '<div class="bdt-grid bdt-grid-collapse bdt-flex bdt-flex-middle">';
			$html .= '<div class="bdt-width-1-2 bdt-flex-inline bdt-flex-left">';
			$html .= sprintf('<label for="bdt_%1$s[%2$s]">%3$s</label>', $args['section'], $args['id'], $args['name']);
			$html .= '</div>';

			$html .= '<div class="bdt-width-1-2 bdt-flex-inline bdt-flex-right">';
			$html .= sprintf('<select class="bdt-width-1-2 %1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);
			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
			}
			$html .= sprintf('</select>');
			$html .= '</div>';
			$html .= '</div>';

			$html .= $this->get_field_description($args);
			$this->get_control_output($html);
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_textarea($args)
		{

			$value = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html = '';
			$html .= sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
			$html .= '<span scope="row" class="ep-option-label">' . $args['name'] . '</span>';
			$html .= '</label>';

			$html .= sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" %4$s >%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value);
			$html .= $this->get_field_description($args);

			echo wp_kses_post($html);
		}

		/**
		 * Displays the html for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_html($args)
		{
			echo wp_kses_post($args['desc']);
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_file($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$id = $args['section'] . '[' . $args['id'] . ']';
			$label = isset($args['options']['button_label']) ? $args['options']['button_label'] : __('Choose File', 'bdthemes-element-pack');

			$html = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html .= $this->get_field_description($args);

			echo wp_kses_post($html);
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_password($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html .= $this->get_field_description($args);

			echo wp_kses($html, array(
				'input' => array(
					'type' => true,
					'class' => true,
					'id' => true,
					'name' => true,
					'value' => true,
				),
			));
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_color($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std']);
			$html .= $this->get_field_description($args);

			echo wp_kses($html, array(
				'input' => array(
					'type' => true,
					'class' => true,
					'id' => true,
					'name' => true,
					'value' => true,
					'data-default-color' => true,
				),
			));
		}

		/**
		 * Displays a  2 colspan subheading field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_subheading($args)
		{

			$html = '<h3 class="setting_subheading column-merge">' . $args['name'] . '</h3>';
			$html .= $this->get_field_description($args);
			$html .= '<hr class="setting_separator">';

			echo wp_kses($html, array(
				'h3' => array(
					'class' => true,
				),
				'hr' => array(
					'class' => true,
				),
			));
		}

		function callback_start_group($args)
		{

			$html = '<div class="ep-option-item-inner ep-option-group">';

			$html .= sprintf('<label for="bdt_ep_%1$s[%2$s]">', $args['section'], $args['id']);
			$html .= '<span scope="row" class="ep-option-label">' . $args['name'] . '</span>';
			$html .= '</label>';

			if ($args['video_url']) {
				$html .= '<a href="' . $args['video_url'] . '" target="_blank" class="ep-option-video" bdt-tooltip="View ' . $args['name'] . ' Video Tutorial"><i class="bdt-wi-tutorial" aria-hidden="true"></i></a>';
			}

			$html .= $this->get_field_description($args);

			$html .= '<div class="bdt-grid" bdt-grid>';

			echo wp_kses($html, array(
				'div' => array(
					'class' => true,
					'bdt-grid' => true,
				),
				'label' => array(
					'for' => true,
				),
				'span' => array(
					'class' => true,
					'scope' => true,
				),
				'a' => array(
					'href' => true,
					'target' => true,
					'class' => true,
					'bdt-tooltip' => true,
				),
				'i' => array(
					'class' => true,
					'aria-hidden' => true,
				),
				'p' => array(
					'class' => true,
				),
			));
		}

		function callback_end_group($args)
		{

			$html = '</div>';
			$html .= '</div>';

			echo wp_kses_post($html);
		}

		/**
		 * Displays a  2 colspan separator field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_separator($args)
		{

			$html = '<hr class="setting_separator column-merge">';
			$html .= $this->get_field_description($args);


			echo wp_kses_post($html);
		}


		/**
		 * Displays a select box for creating the pages select box
		 *
		 * @param array   $args settings field args
		 */
		function callback_pages($args)
		{

			$dropdown_args = array(
				'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'])),
				'name' => $args['section'] . '[' . $args['id'] . ']',
				'id' => $args['section'] . '[' . $args['id'] . ']',
				'echo' => 0
			);
			$html = wp_dropdown_pages($dropdown_args);
			echo wp_kses_post($html);
		}

		/**
		 * Sanitize callback for Settings API
		 *
		 * @return mixed
		 */
		function sanitize_options($options)
		{

			if (!$options) {
				return $options;
			}

			foreach ($options as $option_slug => $option_value) {
				$sanitize_callback = $this->get_sanitize_callback($option_slug);

				// If callback is set, call it
				if ($sanitize_callback) {
					$options[$option_slug] = call_user_func($sanitize_callback, $option_value);
					continue;
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug
		 *
		 * @return mixed string or bool false
		 */
		function get_sanitize_callback($slug = '')
		{
			if (empty($slug)) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback
			foreach ($this->settings_fields as $section => $options) {
				foreach ($options as $option) {
					if ($option['name'] != $slug) {
						continue;
					}

					// Return the callback name
					return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string  $option  settings field name
		 * @param string  $section the section name this field belongs to
		 * @param string  $default default text if it's not found
		 * @return string
		 */
		function get_option($option, $section, $default = '')
		{

			$options = get_option($section);

			if (isset($options[$option])) {
				return $options[$option];
			}

			return $default;
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 */
		function show_navigation()
		{
			$html = '<div class="bdt-dashboard-navigation">';
			$html .= '<ul class="bdt-tab bdt-flex-column" bdt-tab="animation: bdt-animation-slide-bottom-small;connect: .bdt-tab-container;">';

			// Dashboard - always first
			$html .= sprintf('<li><a href="#%1$s" class="bdt-tab-item" id="bdt-%1$s" data-tab-index="0"><i class="dashicons dashicons-admin-home"></i>%2$s</a></li>', 'element_pack_welcome', esc_html__('Dashboard', 'bdthemes-element-pack'));

			$count = 1;

			// Get all sections including manually created ones
			$all_sections = $this->get_all_sections();

			foreach ($all_sections as $tab) {
				$html .= sprintf('<li><a href="#%1$s" class="bdt-tab-item" id="bdt-%1$s" data-tab-index="%2$s"><i class="%4$s"></i>%3$s</a></li>', $tab['id'], $count++, $tab['title'], $tab['icon']);
			}

			// License section
			$license_wl_status = ElementPack_Admin_Settings::license_wl_status();

			if (!defined('BDTEP_LO') || false == $license_wl_status) {
				$html .= sprintf('<li><a href="#%1$s" class="bdt-tab-item" id="bdt-%1$s" data-tab-index="%2$s"><i class="dashicons dashicons-admin-network"></i>%3$s</a></li>', 'element_pack_license_settings', $count, esc_html__('License', 'bdthemes-element-pack'));
			}

			$html .= '</ul>';
			$html .= '</div>';

			echo wp_kses($html, array(
				'div' => array(
					'class' => true,
				),
				'ul' => array(
					'class' => true,
					'bdt-tab' => true,
				),
				'li' => array(
					'class' => true,
				),
				'a' => array(
					'href' => true,
					'class' => true,
					'id' => true,
					'data-tab-index' => true,
				),
				'i' => array(
					'class' => true,
				)
			));
		}

		/**
		 * Get all sections including manually created content pages
		 */
		private function get_all_sections()
		{
			// Start with the settings sections that have forms
			$all_sections = $this->settings_sections;
			
			// Add manually created content sections that don't have settings forms
			$content_only_sections = [
				[
					'id' => 'element_pack_extra_options',
					'title' => esc_html__('Extra Options', 'bdthemes-element-pack'),
					'icon' => 'dashicons dashicons-smiley',
				],
				[
					'id' => 'element_pack_analytics_system_req',
					'title' => esc_html__('System Status', 'bdthemes-element-pack'),
					'icon' => 'dashicons dashicons-chart-bar',
				],
				[
					'id' => 'element_pack_other_plugins',
					'title' => esc_html__('Other Plugins', 'bdthemes-element-pack'),
					'icon' => 'dashicons dashicons-admin-plugins',
				],
				[
					'id' => 'element_pack_affiliate',
					'title' => esc_html__('Get 50% Payout', 'bdthemes-element-pack'),
					'icon' => 'dashicons dashicons-money-alt',
				],
			];
			
			// Check if each content section exists in settings sections, if not add it
			foreach ($content_only_sections as $content_section) {
				$exists = false;
				foreach ($all_sections as $existing_section) {
					if ($existing_section['id'] === $content_section['id']) {
						$exists = true;
						break;
					}
				}
				if (!$exists) {
					$all_sections[] = $content_section;
				}
			}
			
			return $all_sections;
		}

		function element_pack_settings_save()
		{

			if (!check_ajax_referer('element-pack-settings-save-nonce')) {
				wp_send_json_error();
			}

			if (!current_user_can('manage_options')) {
				return;
			}

			$moudle_id = sanitize_text_field($_POST['id']);

			unset($_POST['id']);

			if (isset($_POST[$moudle_id])) {
				update_option($moudle_id, $_POST[$moudle_id]); // need to check
			}

			if (element_pack_is_asset_optimization_enabled()) {
				$optimize_assets = new Asset_Minifier();
				$optimize_assets->minifyCss();
				$optimize_assets->minifyJs();
				update_option('element-pack-minified-asset-manager-version', time());
			} else {
				delete_option('element-pack-minified-asset-manager-version');
			}

			wp_send_json_success();
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		function show_forms()
		{
			?>

			<?php $i = 0;
			foreach ($this->settings_sections as $form) {
				$i++; ?>
				<div id="<?php echo esc_attr($form['id']); ?>_page" class="ep-option-page">

					<div bdt-filter="target: .ep-options" class="ep-options-parent" id="ep-options-parent-<?php echo esc_attr($i); ?>">


						<?php if ($form['id'] == 'element_pack_active_modules' or $form['id'] == 'element_pack_third_party_widget' or $form['id'] == 'element_pack_elementor_extend'): ?>

							<div class="bdt-widget-filter-wrapper bdt-flex bdt-flex-column bdt-flex-wrap"
								bdt-sticky="end: !.ep-dashboard-container; offset: 115; animation: bdt-animation-slide-top-small; duration: 300">

								<!-- Filter Shape Elements -->
								<div class="ep-filter-elements">
									<span class="ep-filter-element ep-filter-circle"></span>
									<span class="ep-filter-element ep-filter-dots"></span>
									<span class="ep-filter-element ep-filter-wave"></span>
									<span class="ep-filter-element ep-filter-hexagon"></span>
									<span class="ep-filter-element ep-filter-zigzag"></span>
								</div>

								<div class="bdt-widget-filter-header">

									<div class="bdt-flex bdt-flex-wrap">

										<div class="bdt-width-expand@l ep-widget-filter-nav bdt-visible@l">
											<div class="bdt-flex-inline bdt-flex-middle">

												<div>
													<ul
														class="bdt-subnav bdt-subnav-pill ep-widget-filter bdt-widget-type-content bdt-flex-inline">
														<li class="ep-widget-all bdt-active" bdt-filter-control="*"><a
																href="#"><?php esc_html_e('All', 'bdthemes-element-pack'); ?></a></li>
														<li class="ep-widget-free"
															bdt-filter-control="filter: [data-widget-type='free']; group: data-content-type">
															<a href="#"><?php esc_html_e('Free', 'bdthemes-element-pack'); ?></a>
														</li>
														<li class="ep-widget-pro"
															bdt-filter-control="filter: [data-widget-type='pro']; group: data-content-type">
															<a href="#"><?php esc_html_e('Pro', 'bdthemes-element-pack'); ?></a>
														</li>

													</ul>
												</div>

												<?php if ($form['id'] == 'element_pack_active_modules' or $form['id'] == 'element_pack_third_party_widget'): ?>


													<?php if ($form['id'] != 'element_pack_elementor_extend' or $form['id'] == 'element_pack_third_party_widget'): ?>

														<div>
															<ul
																class="bdt-subnav bdt-subnav-pill ep-widget-filter ep-used-unused-widgets bdt-flex-inline bdt-visible@xl">
																<li class="ep-widget--"
																	bdt-filter-control="filter: [data-content-type*='ep-used']; group: data-content-type">
																	<a href="#"><?php esc_html_e('Used', 'bdthemes-element-pack'); ?>
																		<span class="bdt-badge ep-used-widget"></span>
																	</a>
																</li>
																<li class="ep-widget--"
																	bdt-filter-control="filter: [data-content-type*='ep-unused']; group: data-content-type">
																	<a href="#"
																		bdt-tooltip="<?php esc_html_e('Don\'t need unused widget? Click on the Deactivate All button.', 'bdthemes-element-pack'); ?>"><?php esc_html_e('Unused', 'bdthemes-element-pack'); ?>
																		<span class="bdt-badge ep-unused-widget bdt-danger"></span>
																	</a>
																</li>
															</ul>

														</div>
													<?php endif; ?>

												<?php endif; ?>
											</div>
										</div>


										<div class="bdt-width-auto@l bdt-search-active-wrap bdt-flex bdt-flex-middle bdt-flex-between">
											<div class="bdt-widget-search">
												<input data-id="ep-options-parent-<?php echo esc_attr($i); ?>" onkeyup="filterSearch(this);"
													bdt-filter-control="" class="bdt-search-input bdt-flex-middle" type="search"
													placeholder="<?php esc_html_e('Search widget...', 'bdthemes-element-pack'); ?>"
													autofocus>
											</div>

											<?php //if ($form['id'] == 'element_pack_active_modules' or $form['id'] == 'element_pack_third_party_widget' ) : 
																?>
											<div>
												<ul class="bdt-subnav bdt-subnav-pill ep-widget-onoff">
													<li>
														<a href="#" class="ep-active-all-widget">
															<?php esc_html_e('Activate All', 'bdthemes-element-pack'); ?>
														</a>
													</li>
													<li>
														<a href="#" class="ep-deactive-all-widget">
															<?php esc_html_e('Deactivate All', 'bdthemes-element-pack'); ?>
														</a>
													</li>
												</ul>
											</div>
										</div>
									</div>

									<?php if ($form['id'] == 'element_pack_active_modules' or $form['id'] == 'element_pack_third_party_widget'): ?>
										<div class="ep-content-type-filter bdt-margin-top">
											<div class="bdt-flex bdt-flex-wrap bdt-flex-middle bdt-visible@l">
												<div class="ep-filter-by-text bdt-visible@xl">
													<?php esc_html_e('Filter By: ', 'bdthemes-element-pack'); ?>
												</div>
												<ul
													class="bdt-nav xbdt-subnav-pill xbdt-dropdown-nav ep-widget-filter ep-widget-content-type bdt-flex bdt-flex-wrap ">
													<li class="ep-widget-new"
														bdt-filter-control="filter: [data-content-type*='new']; group: data-widget-type"><a
															href="#"><?php esc_html_e('New', 'bdthemes-element-pack'); ?></a></li>
													<li class="ep-widget-post"
														bdt-filter-control="filter: [data-content-type*='post']; group: data-widget-type"><a
															href="#"><?php esc_html_e('Post', 'bdthemes-element-pack'); ?></a></li>
													<?php if ($form['id'] == 'element_pack_active_modules'): ?>
														<li class="ep-widget-custom"
															bdt-filter-control="filter: [data-content-type*='custom']; group: data-widget-type">
															<a href="#"><?php esc_html_e('Custom', 'bdthemes-element-pack'); ?></a>
														</li>
													<?php endif; ?>
													<li class="ep-widget-gallery"
														bdt-filter-control="filter: [data-content-type*='gallery']; group: data-widget-type">
														<a href="#"><?php esc_html_e('Gallery', 'bdthemes-element-pack'); ?></a>
													</li>
													<li class="ep-widget-slider"
														bdt-filter-control="filter: [data-content-type*='slider']; group: data-widget-type">
														<a href="#"><?php esc_html_e('Slider', 'bdthemes-element-pack'); ?></a>
													</li>
													<li class="ep-widget-carousel"
														bdt-filter-control="filter: [data-content-type*='carousel']; group: data-widget-type">
														<a href="#"><?php esc_html_e('Carousel', 'bdthemes-element-pack'); ?></a>
													</li>
													<?php if ($form['id'] == 'element_pack_third_party_widget'): ?>
														<li class="ep-widget-acf"
															bdt-filter-control="filter: [data-content-type*='acf']; group: data-widget-type">
															<a href="#"><?php esc_html_e('ACF', 'bdthemes-element-pack'); ?></a>
														</li>
														<li class="ep-widget-forms"
															bdt-filter-control="filter: [data-content-type*='forms']; group: data-widget-type">
															<a href="#"><?php esc_html_e('Forms', 'bdthemes-element-pack'); ?></a>
														</li>
														<li class="ep-widget-ecommerce"
															bdt-filter-control="filter: [data-content-type*='ecommerce']; group: data-widget-type">
															<a href="#"><?php esc_html_e('eCommerce', 'bdthemes-element-pack'); ?></a>
														</li>
													<?php endif; ?>
													<?php if ($form['id'] == 'element_pack_active_modules'): ?>
														<li class="ep-widget-template-builder"
															bdt-filter-control="filter: [data-content-type*='template-builder']; group: data-widget-type">
															<a href="#"><?php esc_html_e('Template Builder', 'bdthemes-element-pack'); ?></a>
														</li>
													<?php endif; ?>
													<li class="ep-widget-others"
														bdt-filter-control="filter: [data-content-type*='others']; group: data-widget-type">
														<a href="#"><?php esc_html_e('Others', 'bdthemes-element-pack'); ?></a>
													</li>
												</ul>
											</div>
										</div>
									<?php endif; ?>

								</div>

							</div>

						<?php endif; ?>

						<form class="settings-save" method="post" action="admin-ajax.php?action=element_pack_settings_save">
							<input type="hidden" name="id" value="<?php echo esc_attr($form['id']); ?>">

							<?php

							if (!current_user_can('manage_options')) {
								return;
							}

							wp_nonce_field('element-pack-settings-save-nonce');

							do_action('wsa_form_top_' . $form['id'], $form);

							$this->do_settings_sections($form['id']);

							do_action('wsa_form_bottom_' . $form['id'], $form);

							?>

						</form>
					</div>
				</div>
			<?php }
		}
	}

endif;
