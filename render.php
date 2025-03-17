<?php

/**
 * Main rendering workflow for handling routes and permissions:
 * 1. Determine current route from URL
 * 2. Validate user permissions for route
 * 3. Load appropriate template based on route
 * 4. Render content within main layout
 */

// Use static Route class methods
// Get current route path and its components
$route = Route::current();
$route_parts = Route::parts();

global $PAGE_PERMISSIONS;
global $DEFAULT_PERMISSIONS;

// Check permissions for exact route match
if (isset($PAGE_PERMISSIONS[$route])) {
    Auth::get()->checkAccess($PAGE_PERMISSIONS[$route]);
} else {
    // Check if route without trailing slash exists
    // Fallback: Check parent route by removing last path segment
$base_route = preg_replace('/\/[^\/]+$/', '', $route);
    $base_route = $base_route === '' ? '/' : $base_route;
    
    if (isset($PAGE_PERMISSIONS[$base_route])) {
        Auth::get()->checkAccess($PAGE_PERMISSIONS[$base_route]);
    } else {
        Auth::get()->checkAccess($DEFAULT_PERMISSIONS);
    }
}

ob_start();
// Build template path from route components (exclude first empty element)
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

// Render final content within main layout structure
include 'layouts/main.php';