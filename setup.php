<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/Database.php';

if (!defined('DEBUG') || !DEBUG) {
    die('Setup script disabled in production environment');
}

if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
    die("<h1>Database Setup</h1>\n        <p>This script will DESTROY and RECREATE your database tables!</p>\n        <form method='POST'>\n            <input type='hidden' name='confirm' value='yes'>\n            <button type='submit'>Confirm Database Reset</button>\n        </form>");
}

try {
    $db = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $db->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    Database::resetConnection([
        'driver' => DB_DRIVER,
        'host' => DB_HOST,
        'dbname' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS
    ]);

    $stmt = Database::get()->query("SELECT TABLE_NAME 
        FROM information_schema.tables 
        WHERE table_schema = '".DB_NAME."'");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error checking existing tables: ".$e->getMessage());
}

try {
    if (!empty($existingTables)) {
        foreach ($existingTables as $table) {
            Database::get()->exec("DROP TABLE `$table`");
        }
    }

    $sql = file_get_contents(__DIR__.'/schema.sql');
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if (!empty($query)) {
            Database::get()->exec($query);
        }
    }

    echo "<h1>Database Setup Complete</h1>";
    echo "<p>Successfully executed " . count($queries) . " SQL statements</p>";

    // Always show user creation form
    ob_start();
?>
    <form method='post' action='' class='mt-3'>
        <input type='hidden' name='confirm' value='yes'>
        <h3>Create Admin Account</h3>
        <?php echo (!empty($errors) ? "<div class='alert alert-danger'>" . implode('<br>', $errors) . "</div>" : ''); ?>
        <div class='mb-3'>
            <label for='username' class='form-label'>Username</label>
            <input type='text' class='form-control' id='username' name='username' required>
        </div>
        <div class='mb-3'>
            <label for='role' class='form-label'>Role</label>
            <select class='form-control' id='role' name='role' required>
                <?php foreach(array_diff($VALID_ROLES, ['logged_in']) as $role): ?>
                    <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='mb-3'>
            <label for='password' class='form-label'>Password</label>
            <input type='password' class='form-control' id='password' name='password' required>
        </div>
        <button type='submit' class='btn btn-primary' name='create_admin'>Create User Account</button>
    </form>
    <?php
    echo ob_get_clean();
} catch (PDOException $e) {
    die("<h1>Setup Failed</h1>\n<p>Error: " . $e->getMessage() . "</p>");
}

$username = trim($_POST['username'] ?? '');
$role = $_POST['role'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $errors[] = 'Username and password are required';
}

if (!in_array($role, $VALID_ROLES)) {
    $errors[] = 'Invalid role selected';
}

if (empty($errors)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    Database::get()->prepare("INSERT INTO users (username, role, password) VALUES (?, ?, ?)")
        ->execute([$username, $role, $passwordHash]);
    
    echo '<div class="alert alert-success">User account created successfully</div>';
}