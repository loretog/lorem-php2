<?php

class Route
{
    private static $current_route;
    private static $route_parts;
    private static $parameters = [];

    public static function init()
    {        
        $url = trim(filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL) ?? '', '/');
        $url = $url === '' ? '/' : '/' . $url;
        self::$current_route = $url;
        self::$route_parts = explode('/', self::$current_route);
        
        // Parse key-value pairs from sequential segments
        for ($i = 1; $i < count(self::$route_parts); $i += 2) {
            if (isset(self::$route_parts[$i + 1])) {
                self::$parameters[self::$route_parts[$i]] = self::$route_parts[$i + 1];
            }
        }
    }

    public static function current()
    {        
        return self::$current_route;
    }

    public static function parts()
    {        
        return self::$route_parts;
    }

    public static function get($index)
    {        
        // Check parameters first, then fall back to positional parts
        return self::$parameters[$index] ?? self::$route_parts[$index] ?? null;
    }
}

// Initialize route when class is loaded
Route::init();