<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/Database.php';

// New database configuration form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['db_settings'])) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup</title>
        <style>
            .container { max-width: 500px; margin: 50px auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; }
            input { width: 100%; padding: 8px; }
            button { background: #007bff; color: white; border: none; padding: 10px 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Database Configuration</h1>
            <form method="POST">
                <div class="form-group">
                    <label>Database Name:</label>
                    <input type="text" name="db_name" required>
                </div>
                <div class="form-group">
                    <label>Database Username:</label>
                    <input type="text" name="db_user" required>
                </div>
                <div class="form-group">
                    <label>Database Password:</label>
                    <input type="password" name="db_pass">
                </div>
                <button type="submit" name="db_settings">Configure Database</button>
            </form>
        </div>
    </body>
    </html>';
    exit;
}

if (!defined('DEBUG') || !DEBUG) {
    die('Setup script disabled in production environment');
}

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("<h1>Database Setup</h1>
        <p>This script will DESTROY and RECREATE your database tables!</p>
        <p>Add ?confirm=yes to the URL to execute</p>");
}

try {
    // Create database if not exists
    $db = new PDO("mysql:host=localhost", $_POST['db_user'], $_POST['db_pass']);
    $db->exec("CREATE DATABASE IF NOT EXISTS `".$_POST['db_name']."` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // Reconnect with selected database
    Database::resetConnection([
        'host' => 'localhost',
        'dbname' => $_POST['db_name'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass']
    ]);

    $stmt = Database::get()->query("SELECT TABLE_NAME 
        FROM information_schema.tables 
        WHERE table_schema = '".$_POST['db_name']."'");
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

    // Handle admin account creation separately
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if (empty($errors)) {
            $existingUser = Database::fetch('users', ['username' => $username]);
            if ($existingUser) {
                $errors[] = 'Username already exists';
            }
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            Database::insert('users', [
                'username' => $username,
                'password' => $hashedPassword,
                'role' => 'admin',
                'created' => date('Y-m-d H:i:s')
            ]);
            echo "<div class='alert alert-success mt-3'>Admin account created successfully!</div>";
            unset($errors);  // Clear errors after success
        }
    }

    if (!empty($errors)) {
        echo "<div class='alert alert-danger mt-3'>".implode('<br>', $errors)."</div>";
    }
    ob_start();
?>
    <form method='post' action='?confirm=yes' class='mt-3'>
        <h3>Create Admin Account</h3>
        <?php echo (!empty($errors) ? "<div class='alert alert-danger'>" . implode('<br>', $errors) . "</div>" : ''); ?>
        <div class='mb-3'>
            <label for='username' class='form-label'>Username</label>
            <input type='text' class='form-control' id='username' name='username' required>
        </div>
        <div class='mb-3'>
            <label for='password' class='form-label'>Password</label>
            <input type='password' class='form-control' id='password' name='password' required>
        </div>
        <button type='submit' class='btn btn-primary' name='create_admin'>Create Admin Account</button>
    </form>
    <?php
    echo ob_get_clean();
} catch (PDOException $e) {
    die("<h1>Setup Failed</h1>\n<p>Error: " . $e->getMessage() . "</p>");
}