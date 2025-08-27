<?php

namespace UiCoreElements;

use Elementor\Widget_Base;
use UiCoreElements\Helper;

abstract class UiCoreWidget extends Widget_Base
{

    /**
     * Get widget categories.
     *
     * custom_condition need to always return true if $this->is_edit_mode() is true
     * @return object ['assets-name' => ['condition' => ['key' => 'value'] , 'deps' => ['global-handler-name','other-handler-name'], 'external' => true]]
     */
    public abstract function get_styles();

    /**
     * Get widget categories.
     *
     * custom_condition need to always return true if $this->is_edit_mode() is true
     * @return object ['assets-name' => ['condition' => ['key' => 'value'] ,'custom_condition' => true , 'deps' => ['global-handler-name','other-handler-name'], 'external' => true]]
     */
    public abstract function get_scripts();

    public function get_style_depends()
    {
        $assets = $this->get_styles();
        $final_list = $this->parse_asset_list($assets, 'style');

        //TODO: combine all the styles into one file and return it
        return $final_list;
    }

    public function get_script_depends()
    {
        $assets = $this->get_scripts();
        $final_list = $this->parse_asset_list($assets, 'script');

        //TODO: combine all the styles into one file and return it
        return $final_list;
    }



    private function parse_asset_list($list, $type = 'style')
    {
        $final_list = [];
        foreach ($list as $key => $value) {
            $deps = (isset($value) && isset($value['deps'])) ? $value['deps'] : [];
            $external = (isset($value) && isset($value['external'])) ? $value['external'] : false;
            $name = $this->get_asset_name($key, $value);

            //if name is not empty then we need to add it to the list
            if ($name) {
                $method_name = "register_widget_$type";
                $final_list[] = Helper::$method_name($name, $deps, $external);
            }
        }
        return $final_list;
    }

    private function get_asset_name($key, $value)
    {
        //check the condition/s
        if (\is_array($value) && !empty($value)) {

            //custom condition
            if (isset($value['custom_condition'])) {
                if ($value['custom_condition']) {
                    return $key;
                } else {
                    return '';
                }
            }

            //check if the condition is true using elementor's function (always return true if we are in edit mode)
            if ($this->is_edit_mode() || $this->is_control_visible($value, $this->get_settings())) {
                return $key;
            } else {
                return '';
            }
        }
        // if list is only declaring the assets without any condition then we need to return the key
        return $value;
    }
    protected function is_edit_mode()
    {
        $elementor_instance = \Elementor\Plugin::instance();
        if ($elementor_instance->preview->is_preview_mode() || $elementor_instance->editor->is_edit_mode()) {
            return true;
        }

        return false;
    }

    /**
     * Verify if the control exists and if matches the given value.
     * Usefull for controls that may not be returned, in the settings stack, under certain conditions.
     *
     * @param string $control The control slug.
     * @param string|null $value Optional. The value to check against.
     * @param string $operator Optional. The operator to use for comparing `control` and `value`. Default is '==='.
     *
     * @return bool
     * @since 1.0.15
     */
    protected function is_option(string $control, $value = null, $operator = '==='): bool
    {
        $option = $this->get_settings_for_display($control);

        if (isset($option)) {

            if (!isset($value)) {
                return true;
            }

            switch ($operator) {
                case '===':
                    return $option === $value;
                case '!==':
                    return $option !== $value;
                case '<':
                    return $option < $value;
                case '<=':
                    return $option <= $value;
                case '>':
                    return $option > $value;
                case '>=':
                    return $option >= $value;
                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * Verify if the control exists and get both `size` and `unit` values, if requested.
     *
     * @param string $control The control slug.
     * @param array $fallback The fallback value to return if the control does not exist. Should have `size` and, if requested, `unit` keys.
     * @param bool $use_unit If true, the unit will also be checked and returned.
     *
     * @return string `size` and, if requested, `unit` values from the control or the fallback value.
     * @since 1.0.15
     */
    public function get_option_size($control, array $fallback, $use_unit = false)
    {
        $option = $this->get_settings_for_display($control);

        if (isset($option) && ! empty($option['size'])) {
            $size = $control['size'];
            $unit = $use_unit && isset($option['unit']) ? $option['unit'] : '';
            return $size . $unit;
        }

        $fallback_size = $fallback['size'];
        $fallback_unit = $use_unit ? $fallback['unit'] : '';
        return $fallback_size . $fallback_unit;
    }
}
