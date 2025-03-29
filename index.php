<?php    
    require_once 'config.php';    
    require_once 'permissions.php';
        
    if (DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }    
    require_once 'lib/Database.php';
    require_once 'lib/Route.php';
    require_once 'lib/Auth.php';
    require_once 'render.php';