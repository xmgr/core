<?php
    
    use Xmgr\Frontend\AssetCompiler;
    use Xmgr\System;
    
    const XM_BASEDIR = __DIR__;
    const XM_VERSION = '1.0.1';
    
    /**
     * Enable/disable error output
     *
     * @return void
     */
    function __debug(bool $set = true) {
        if ($set) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }
    
    define('APP_START', microtime(true));
    
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', __DIR__);
    }
    
    # Load helpers and handle PSR-4 autoloader
    require_once __DIR__ . '/functions/core.php';
    require_once __DIR__ . '/functions/app.php';
    
    # ################################
    # Autoloader
    # ################################
    
    spl_autoload_register(function ($class) {
        $class  = str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "/\\"));
        $class2 = substr($class, strpos($class, DIRECTORY_SEPARATOR) + 1);
        $files  = [
            path($class . '.php'),
            xmpath('src', $class2 . '.php'),
        ];
        foreach ($files as $file) {
            if (is_file($file)) {
                require_once $file;
                break;
            }
        }
    });
    
    # ################################
    # Handle program exit
    # ################################
    
    register_shutdown_function(function () {
    
    });
    
    # Set document root
    if (!defined('WEB_ROOT')) {
        define('WEB_ROOT', APP_ROOT);
    }
    
    if (System::isWebRequest()) {
        AssetCompiler::compileCss(AssetCompiler::DEFAULT_CSS, 'xmgr-ui.min');
        AssetCompiler::compileJs(AssetCompiler::DEFAULT_JS, 'xmgr-ui.min');
    }
