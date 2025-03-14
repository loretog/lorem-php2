<?php

// Use static Route class methods
$route = Route::current();
$route_parts = Route::parts();

global $PAGE_PERMISSIONS;
global $DEFAULT_PERMISSIONS;

if (isset($PAGE_PERMISSIONS[$route])) {
    Auth::get()->checkAccess($PAGE_PERMISSIONS[$route]);
} else {
    // Check if route without trailing slash exists
    $base_route = preg_replace('/\/[^\/]+$/', '', $route);
    $base_route = $base_route === '' ? '/' : $base_route;
    
    if (isset($PAGE_PERMISSIONS[$base_route])) {
        Auth::get()->checkAccess($PAGE_PERMISSIONS[$base_route]);
    } else {
        Auth::get()->checkAccess($DEFAULT_PERMISSIONS);
    }
}

ob_start();
$template_path = 'pages/' . implode('/', array_filter(array_slice($route_parts, 1))) . '.php';

error_log('Route parts: ' . print_r($route_parts, true));
error_log('Template path: ' . $template_path);

if(!empty($route_parts[1]) && file_exists($template_path)) {
    include $template_path;
} else if(file_exists('pages/home.php')) {
    include 'pages/home.php';
} else {
    http_response_code(404);
    include 'pages/404.php';
}
$content = ob_get_clean();

include 'layouts/main.php';