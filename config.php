<?php


    define( 'DEBUG', true );
    ini_set('error_log', __DIR__.'/logs/php_errors.log');

    define( 'DB_DRIVER', 'mysql' );
    define( 'DB_HOST', 'localhost' );
    define( 'DB_NAME', 'lorem_php2' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASS', 'p@ssword' );

    define( 'SITE_NAME', 'Lorem PHP 2' );
    define( 'SITE_URL', 'http://localhost/lorem-php2' );

    define( 'PAGE_DEFAULT', 'pages/home.php');
    define( 'PAGE_404', 'pages/404.php');
    define( 'PAGE_403', 'pages/403.php');
    define( 'PAGE_LOGIN', 'pages/login.php');

    // Role hierarchy (higher roles inherit lower roles' permissions)
    // List of all valid roles in the system
    $VALID_ROLES = ['logged_in', 'admin', 'user'];