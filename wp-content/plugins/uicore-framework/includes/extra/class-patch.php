<?php

namespace UiCore;


defined('ABSPATH') || exit();

/**
 * Brisk Core Utils Functions
 */
class Patch
{

    public function __construct($content, $patch_id)
    {
        Helper::init_filesystem();
        $write = $this->write_patch($content);
        if($write){
            update_option('uicore_current_patch',$patch_id);
        }
    }

    private function write_patch($content)
    {
        global $wp_filesystem;
        // For FS_METHOD ftpext we need to change target paths
		// as FTP root dir might not be server's root dir.
		if ( 'ftpext' === $wp_filesystem->method ) {
				$target     = $wp_filesystem->wp_plugins_dir() . '/uicore-framework';
		}


		// Build the path.
		$path = wp_normalize_path( $target );
		// Define constants if undefined.
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
		}
		// Try to put the contents in the file.
		return $wp_filesystem->put_contents( $path, $content, FS_CHMOD_FILE );
    }
}