<?php


    define( 'DEBUG', true );
    ini_set('error_log', __DIR__.'/logs/php_errors.log');

    define( 'DB_DRIVER', 'mysql' );
    define( 'DB_HOST', 'localhost' );
    define( 'DB_NAME', 'lorem_php2' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASS', '' );

    define( 'SITE_NAME', 'Lorem PHP 2' );
    define( 'SITE_URL', 'http://localhost/lorem-php2' );


    // Role hierarchy (higher roles inherit lower roles' permissions)
    // List of all valid roles in the system
    $VALID_ROLES = ['logged_in', 'admin', 'tech', 'faculty', 'student', 'personnel', 'user'];