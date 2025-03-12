<?php

require_once 'lib/Route.php';

// Use static Route class methods
$route = Route::current();
$route_parts = Route::parts();

ob_start();
if(isset($route_parts[ 0 ]) && !empty($route_parts[ 0 ])) {
    if( file_exists( 'pages/' . $route_parts[ 0 ] . '.php' ) ) {
        include "pages/{$route_parts[0]}.php";
    } else {
        http_response_code(404);
        include 'pages/404.php';
    }
} else {
    include 'pages/home.php';
}

$content = ob_get_clean();

include 'layouts/main.php';