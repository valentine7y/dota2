<?php

// dota2 api key (you can get_info it here - http://steamcommunity.com/dev/apikey)
define ('API_KEY', '*******************');
// player's account_id you want to parse
define ('ACCOUNT_ID', 1234);

error_reporting(E_ALL);

set_time_limit(0);

/**
 * Basic class with system's configuration data
 */
class config {
    /**
     * Configuration data
     * @access private
     * @static
     * @var array
     */
    private static $_data = array(
        'db_user' => 'root',
        'db_pass' => 'KronuS',
        'db_host' => 'localhost',
        'db_name' => 'dota2',
        'db_table_prefix' => ''
    );

    /**
     * Private construct to avoid object initializing
     * @access private
     */
    private function __construct() {}
    public static function init() {
        self::$_data['base_path'] = dirname(__FILE__).DIRECTORY_SEPARATOR.'includes';
        $db = db::obtain(self::get('db_host'), self::get('db_user'), self::get('db_pass'), self::get('db_name'), self::get('db_table_prefix'));
        if (!$db->connect_pdo()) {
            die();
        };
    }
    /**
     * Get configuration parameter by key
     * @param string $key data-array key
     * @return null
     */
    public static function get($key) {
        if(isset(self::$_data[$key])) {
            return self::$_data[$key];
        }
        return null;
    }
}

config::init();

function __autoload($class) {
    scan(config::get('base_path'), $class);
}

function scan($path = '.', $class) {
    $ignore = array('.', '..');
    $dh = opendir($path);
    while(false !== ($file = readdir($dh))){
        if(!in_array($file, $ignore)) {
            if(is_dir($path.DIRECTORY_SEPARATOR.$file)) {
                scan($path.DIRECTORY_SEPARATOR.$file, $class);
            }
            else {
                if ($file === 'class.'.$class.'.php') {
                    require_once($path.DIRECTORY_SEPARATOR.$file);
                    return;
                }
            }
        }
    }
    closedir($dh);
}
