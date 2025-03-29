<?php
require_once 'lib/Auth.php';


$auth = new Auth();

/* 

 */
// Restrict admin page to admin role only
//$auth->checkAccess(['admin']);


/**
 * ROLE HIERARCHY CONFIGURATION
 * ----------------------------------------------------------------
 * Defines role inheritance structure using parent->child relationships
 * Format: 'role' => [inherited_roles]
 * 
 * Example:
 * 'admin' => ['tech'] means admin inherits all tech permissions
 * 
 * Notes:
 * 1. Roles are case-sensitive and must match VALID_ROLES in config.php
 * 2. Circular dependencies will cause permission check failures
 * 3. 'logged_in' is the base role for authenticated users
 */
$ROLE_HIERARCHY = [
    'logged_in' => [],
    'admin' => ['user', 'logged_in'],
    'user' => ['logged_in']
];

/**
 * PAGE ACCESS PERMISSIONS
 * ----------------------------------------------------------------
 * Defines access rules for application routes/pages
 * Format: 'route' => [allowed_roles]
 * 
 * Special values:
 * - '*' : Public access (no authentication required)
 * - 'logged_in' : Any authenticated user
 * 
 * Notes:
 * 1. Routes not listed here will use $DEFAULT_PERMISSIONS
 * 2. Role inheritance is automatically applied
 * 3. Order of declaration does not affect priority

 */
$PAGE_PERMISSIONS = [
    '/' => ['*'],
    '/home' => ['*'],
    '/login' => ['*'],
    '/logout' => ['*'],    
];

/**
 * DEFAULT PERMISSIONS
 * ----------------------------------------------------------------
 * Fallback permissions for routes not listed in PAGE_PERMISSIONS
 * 
 * Recommendation:
 * Keep this restricted to basic authenticated access unless
 * explicitly needing more permissive defaults
 */
$DEFAULT_PERMISSIONS = ['logged_in'];
