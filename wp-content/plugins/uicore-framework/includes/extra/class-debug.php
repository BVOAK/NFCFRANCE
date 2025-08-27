<?php

namespace UiCore;


defined('ABSPATH') || exit();

/**
 * Brisk Core Utils Functions
 */
class Debug
{
    private static $instance;
    private $settings;
    private $errors;

    public static $option_name = 'uicore_debug_settings';
    public static $log_path = ABSPATH . '/wp-content/debug.log';
    public static $default_settings = [
        'display' => 'false',
        'log' => 'true',
    ];

    /**
	 * Init
	 *
	 * @return mixexd
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 4.1.1
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    public function __construct()
    {
        if(!is_array($this->settings)){
            $this->settings = Settings_Helper::get_all_settings(self::$default_settings,self::$option_name);
        }
        add_action('admin_head', [$this, 'get_data_to_set'], 100);

        $this->set_variables();

    }

    // /**
    //  * Register Admin Bar Menu
    //  *
    //  * @return void
    //  * @author Andrei Voica <andrei@uicore.co
    //  * @since 1.0.0
    //  */
    // public function admin_bar_menu($admin_bar)
    // {
    //     $class = is_array($this->errors) ? 'ui-is-error' : '';
    //     // prettier-ignore
    //     if (current_user_can('manage_options')) {
    //         $admin_bar->add_menu(array('meta'=>['class'=>$class],'id' => 'uicore-debug', 'title' => 'DEBUG', 'href' => '#show-debug'));
    //     }

    // }

    function get_data_to_set()
    {

        if(isset($_GET['ui_debug'])){
            $settings = [];
            parse_str($_GET['ui_debug'], $settings);
            if(isset($settings['name']) && isset($settings['value'])){
                $settings = [
                    $settings['name'] => $settings['value']
                ];
                Settings_Helper::update_all_settings(self::$default_settings,self::$option_name,$settings);
            }
        }

        if(isset($_GET['ui_clean'])){
            if( str_replace('=', '',$_GET['ui_clean']) === 'remove_log'){
                \unlink(self::$log_path);
            }

        }

    }


    function set_variables()
    {
        if($this->settings['display'] == 'true'){
            @ini_set('display_errors', 1);
            @ini_set('display_errors_startup', 1);
            @ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            set_error_handler([$this, 'add_error']);
            set_exception_handler([$this, 'error_handler']);

            // add_action('admin_bar_menu', [$this, 'admin_bar_menu'], 100);
            \add_action('wp_footer', [$this, 'display_errors']);
            \add_action('admin_footer', [$this, 'display_errors']);
        }else{
            @ini_set('display_errors', 0);
            @ini_set('display_errors_startup', 0);
        }
        if($this->settings['log'] == 'true'){
            @ini_set('log_errors', 1);
            @ini_set('error_log', self::$log_path);
            @ini_set('error_reporting', E_ALL ^ E_NOTICE);
        }else{
            ini_set('log_errors', 0);
        }
    }

    function display_errors(){
        if(!is_array($this->errors)){
            return;
        }
        ?>
        <div class="ui-errors-down">
        <div class="ui-errors-display-wrapp">
            <header>
                <span>UiCore Debug <span class="ui-badge ui-beta">BETA</span></span>
                <div>
                <a class="ui-log-download">Download Log</a>
                <span class="ui-debug-toggle">Close</span>
                </div>
            </header>
            <ul class="ui-errors-display">
            <?php
            foreach($this->errors as $error){
                ?>
                <li class="ui-error-item">
                    <span class="ui-error-name"> <?php echo $error['msg']; ?></span> <span class="ui-error-file"><?php echo $error['file']; ?> @ <?php echo $error['line']; ?></span>
                    <span class="ui-error-type ui-badge ui-type-<?php echo strtolower(str_replace(' ', '_',$error['type'])); ?>"><?php echo $error['type']; ?></span>
                    <ul class="ui-error-trace" style="display:none">
                        <?php
                        foreach($error['trace'] as $trace){
                            ?>
                            <li class="ui-trace-line">
                                <span class="ui-trace-function">
                                    <?php echo $trace['function']; ?>(<span title="<?php echo substr( htmlspecialchars(print_r($trace['args'],true)) ,0,1000); ?>">$args</span>)
                                    <span class="ui-error-file"><?php echo $trace['file']; ?></span>
                                </span>

                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </li>
                <?php
            }
            ?>
            </ul>
        </div>
        <style>
            .ui-errors-display-wrapp {
                background: #1a1a1e;
                color: #eee;
                font-family: Monaco, Consolas, "Courier New", Courier, monospace;
                font-size: 15px;
                position: fixed;
                bottom: 0;
                z-index: 9999999999999;
                width: 100%;
                max-height: 80vh;
                display: flex;
                flex-direction: column;
            }
            .ui-errors-display-wrapp header{
                display:flex;
                justify-content: space-between;
                padding:9px 20px;
                background:#0c0c0d;
            }
            a.ui-log-download{
                color:#532df5!important;
            }
			.ui-debug-toggle {
				cursor: pointer;
			}
            .ui-beta{
                --ui-badge-color:#532df5!important;
            }
            .ui-errors-display{
                display: flex;
                flex-direction: column;
                gap:10px;
                padding: 20px;
                margin: 0;
                overflow: auto;
            }
            .ui-error-item{
                background-color: #2f3033;
                padding: 6px 100px 6px 12px;
                position: relative;
                border-radius:6px;
                cursor: pointer;
            }
            .ui-error-file {
                color: #999;
                font-size: 11px;
                line-height: 1.2;
            }
            .ui-error-item > .ui-error-file{
                display: block;
            }
            .ui-error-type.ui-badge {
                --ui-badge-color: #c80202;
                position: absolute;
                top: 10px;
                right: 10px;
            }
            .ui-type-warning.ui-badge{
                --ui-badge-color: #f28d12;
            }
            .ui-type-deprecated.ui-badge{
                --ui-badge-color:#0d619e;
            }
            .ui-error-trace{
                padding: 10px;
                font-size: 14px;
                background: #1a1a1e;
                border-radius: 4px;
                margin: 10px -96px 0 -8px;
            }
            #wpadminbar ul li.ui-is-error{
                background: #941111;
            }
            .ui-badge {
                color: white;
                background: var(--ui-badge-color);
                display: inline-block;
                vertical-align: middle;
                padding: 0.6em 0.9em;
                margin-left: 1em;
                border-radius: 4px;
                position: relative;
                font-size: clamp(10px, .7em, 18px);
                line-height: 1em;
                white-space: nowrap;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
            }
            ul{
                list-style-type: none;
            }

        </style>
        <script>
            jQuery('.ui-error-item').bind('click', function(){
                jQuery(this).find('.ui-error-trace').slideToggle()
            });
            jQuery('.ui-debug-toggle, .ui-is-error').bind('click', function(){
                jQuery('.ui-errors-display').slideToggle()
            });
            var anchor = document.querySelector('.ui-log-download');
            var content = document.querySelector('.ui-errors-down').outerHTML;
            var extra = "<style>header{display:none!important} .ui-error-trace{display:block!important} .ui-errors-display-wrapp {position: relative;max-height: 100%;}</style>"
            anchor.setAttribute('download', 'uicore-log--'+window.location.href+'.html');
            anchor.setAttribute('href', 'data:text/html;charset=UTF-8,'+encodeURIComponent(content + extra));
        </script>
        </div>
        <?php
    }

    function error_handler(\Throwable $error)
    {

        self::add_error(0, $error->getMessage(), $error->getFile(), $error->getLine());
        echo $this->display_errors();
    }


    function add_error($level, $message, $file = '', $line = 0)
    {
        $err = new \ErrorException($message, 0, $level, $file, $line);

        switch ( $err->getSeverity() ) {
            case E_USER_ERROR:
            case E_ERROR:
                $type = 'Fatal Error';
            break;
            case E_USER_WARNING:
            case E_WARNING:
                $type = 'Warning';
            break;
            case E_PARSE:
                $type = 'Parse';
            break;
            case E_USER_NOTICE:
            case E_NOTICE:
            case @E_STRICT:
                $type = 'Notice';
            break;
            case @E_RECOVERABLE_ERROR:
                $type = 'Catchable';
            break;
            case E_USER_DEPRECATED:
            case E_DEPRECATED:
                $type = 'Deprecated';
                return; //show deprecated or not? Not Yet!
            break;
            default:
                $type = 'Unknown Error - '.$err->getSeverity() ;
            break;
        }

        $this->errors[] = [
            'msg' => $err->getMessage(),
            'file' => $err->getFile(),
            'line' => $err->getLine(),
            'type' => $type,
            'trace'  => array_slice($err->getTrace(), 1, 15)
        ];
    }



}
Debug::get_instance();
