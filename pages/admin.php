<?php
require_once '../lib/Updater.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_update'])) {
    try {
        $updater = new Updater();
        $latest = $updater->getLatestRelease();
        $_SESSION['update_info'] = $latest;
    } catch (Exception $e) {
        $error = "Update check failed: " . $e->getMessage();
    }
}

echo '<h2>System Updates</h2>';
if (!empty($error)) {
    echo "<div class='alert alert-danger'>$error</div>";
}
if (isset($_SESSION['update_success'])) {
    echo "<div class='alert alert-success'>{$_SESSION['update_success']}</div>";
    unset($_SESSION['update_success']);
}
if (isset($_SESSION['update_error'])) {
    echo "<div class='alert alert-danger'>{$_SESSION['update_error']}</div>";
    unset($_SESSION['update_error']);
}

if (isset($_SESSION['update_info'])) {
    $update = $_SESSION['update_info'];
    echo "<div class='update-info'>
            <h3>Version {$update['tag_name']}</h3>
            <p>{$update['body']}</p>
            <form method='post' action='update.php'>
                <button type='submit' name='apply_update' class='btn btn-primary'>
                    Apply Update
                </button>
            </form>
          </div>";
}