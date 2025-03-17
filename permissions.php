<?php

$ROLE_HIERARCHY = [
    'logged_in' => [],
    // Removed duplicate entry
    'admin' => ['tech', 'logged_in'],
    'tech' => ['faculty', 'logged_in'],
    'faculty' => ['student', 'personnel', 'logged_in'],
    'student' => ['logged_in'],
    'personnel' => ['logged_in'],
    'user' => ['logged_in']
];

// Page access permissions
$PAGE_PERMISSIONS = [
    '/' => ['*'],
    '/home' => ['*'],
    '/login' => ['*'],
    '/logout' => ['*'],
    '/admin' => ['admin'],
    '/tech' => ['tech', 'admin'],
    '/profile' => ['student', 'tech', 'admin', 'personnel'],
    '/sites' => ['tech', 'admin'],
    '/about' => ['*'],
    '/dashboard' => [ 'buko' ],
];

// Default permissions for unspecified pages
$DEFAULT_PERMISSIONS = ['logged_in'];
