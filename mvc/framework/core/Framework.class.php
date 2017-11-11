<?php
class Framework {
    public static function run() {

        self::init();
        self::autoload();
        // Start session
        // session_destroy() destroys the active session. 
        // If you do not initialized the session, there will be nothing to be destroyed.
        // So PHP knows which session to destroy. session_start() looks whether a session cookie or ID is present. Only with that information can you destroy it.
        // https://stackoverflow.com/questions/5779744/php-session-start-function-why-i-need-it-everytime-i-use-anything-related-to        
        session_start();
        // Error container
        set_error_handler('Framework::errorHandler');
        self::dispatch();       
    }

    private static function init() {
        
        // Define user roles 
        define('ADMINISTRATOR',0);
        define('REGULAR',1);
        define('MODERATOR',2);
        define('GUEST',3);
        define('AUDITOR',4);

        // Define user status 
        define('LOCKED',0);
        define('ACTIVE',1);
        define('DEACTIVATED',2);

        // Define path constants
        // define('SERVER_ROOT', parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)); // Server root
        // define('SERVER_PATH',str_replace('index.php','',    // Server path
        // 'http://localhost:8080' .
        // parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)));

        // Change the server path here
        define('SERVER_PATH','http://localhost:8080/dashboard/myProject/mvc/');
        define('APP_CONTAINER', SERVER_PATH . 'index.php/');

        define('DS', DIRECTORY_SEPARATOR);
        define('ROOT', getcwd() . DS);
        define('APP_PATH', ROOT . 'application' . DS);              
        define('FRAMEWORK_PATH', ROOT . "framework" . DS);          // framework path
        define('PUBLIC_PATH', ROOT . 'public' . DS);                // public resources path
            
        define('CONFIG_PATH', APP_PATH . 'config' . DS);
        define('CONTROLLER_PATH', APP_PATH . 'controllers' . DS);   // controllers path
        define('MODEL_PATH', APP_PATH . 'models' . DS);             // models path
        define('VIEW_PATH', APP_PATH . 'views' . DS);               // views path
        define('DATAACCESS_PATH', APP_PATH . 'dataAccess' . DS);    // data access model path

        define('CORE_PATH', FRAMEWORK_PATH . 'core' . DS);
        define('DB_PATH', FRAMEWORK_PATH . 'database' . DS);

        /**
         * Define platform, controller, action, for example 
         * index.php/{platform}/{controller}/{action}  
         * e.g., index.php/home/Home/login
         * By default, indx.php
         * will call the action index.php/home/Home/index
         */     
             
        (function() {      
            @list($p,$c,$a) = explode('/',  trim($_SERVER['PATH_INFO'],'/'));  

            define('PLATFORM', !empty($p) ? $p : 'home');
            define('CONTROLLER', !empty($c) ? $c : 'Home');
            define('ACTION', !empty($a) ? $a : 'index');
        })();  
    
        define('CURR_CONTROLLER_PATH', CONTROLLER_PATH . PLATFORM . DS);
        define('CURR_VIEW_PATH', VIEW_PATH . PLATFORM . DS);

        // Load core classes
        require CORE_PATH . 'Controller.class.php';

        // Load configuration file. This file saves database connection parameters
        $GLOBALS['config'] = include CONFIG_PATH . 'config.php';

    }

    // Autoloading
    private static function autoload() {
        spl_autoload_register(array(__CLASS__,'load'));
    }

    // Define a custom load method
    private static function load($classname) {
        // Here simply autoload app's controller, model and data access classes
        if(substr($classname, -10) == "Controller") {
            // Controller
            require_once CURR_CONTROLLER_PATH . "$classname.class.php";
        } 
        elseif(substr($classname,-5) == "Model") {
            // Model
            require_once MODEL_PATH . "$classname.class.php";
        }
        elseif(substr($classname,-7) == "Context") {
            // DB access layer
            require_once DATAACCESS_PATH . "$classname.class.php";
        }
    }

    // Routing and dispatching
    private static function dispatch() {
        // Instantiate the controller class and call its action model
        $controller_name = CONTROLLER . "Controller";
        // Prefix the action method name with "get_" or "post_" according to the REQUEST_METHOD
        $action_name = strtolower($_SERVER['REQUEST_METHOD']) . '_' . ACTION;
        $controller = new $controller_name;
        // Load the view
        self::loadView($controller->$action_name()) ;         

    }

    public static function errorHandler($errno, $errstr, $errfile, $errline) {
        self::loadView(include ROOT . "error.php");
        exit;
    }
    /**
     * Fill in the partial view and show the complete view 
     * @param $body the partial view 
     */
    // Load the layout and fill in the partial view ($body)
    private static function loadView($body) {
        require VIEW_PATH . '_layout.php'; 
    }

}
?>