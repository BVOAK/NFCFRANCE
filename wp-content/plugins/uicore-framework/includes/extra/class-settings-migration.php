<?php

namespace UiCore;
defined('ABSPATH') || exit();

// Here we store and define all the needed settings
class SettingsMigration
{

  const MIGRATIONS = [
    '6.2.0' 
  ];

  public static function migrate($settings, $force = false)
  {
    foreach (self::MIGRATIONS as $version) {
      $method = 'migrate_' . str_replace('.', '_', $version);
      if($force || (UICORE_VERSION >= $version && method_exists(__CLASS__, $method) && !get_option('uicore_settings_migrated_' . $version))) {
        $settings = self::$method($settings);
        update_option('uicore_settings_migrated_' . $version, true);
      }
    }
    return $settings;
  }

    private static function migrate_6_2_0($settings)
    {
        \error_log('UiCore: Migrating settings to 6.2.0');
        // Typography keys to migrate
        $typography_keys = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blog_h1', 'blog_h2', 'blog_h3', 'blog_h4', 'blog_h5', 'blog_h6', 'blog_p'];
        foreach ($typography_keys as $key) {
            if (!isset($settings[$key])) continue;
            // s
            if (isset($settings[$key]['s'])) {
                foreach (['d', 't', 'm'] as $device) {
                    if (isset($settings[$key]['s'][$device]) && !is_array($settings[$key]['s'][$device])) {
                        $settings[$key]['s'][$device] = [
                            'value' => $settings[$key]['s'][$device],
                            'unit'  => 'px'
                        ];
                    }
                }
            }
            // h
            if (isset($settings[$key]['h']) && (!is_array($settings[$key]['h']) || (is_array($settings[$key]['h']) && !isset($settings[$key]['h']['d']['value'])))) {
                $h_val = $settings[$key]['h'];
                $settings[$key]['h'] = [
                    'd' => ['value' => $h_val, 'unit' => 'em'],
                    't' => ['value' => $h_val, 'unit' => 'em'],
                    'm' => ['value' => $h_val, 'unit' => 'em'],
                ];
            }
            if (isset($settings[$key]['ls']) && (!is_array($settings[$key]['ls']) || (is_array($settings[$key]['ls']) && !isset($settings[$key]['ls']['d']['value'])))) {
                $ls_val = $settings[$key]['ls'];
                $settings[$key]['ls'] = [
                    'd' => ['value' => $ls_val, 'unit' => 'em'],
                    't' => ['value' => $ls_val, 'unit' => 'em'],
                    'm' => ['value' => $ls_val, 'unit' => 'em'],
                ];
            }
        }
        return $settings;
    }

}