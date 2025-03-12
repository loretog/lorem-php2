<?php

Auth::get()->checkAccess('admin');
?>

<?php ob_start(); ?>
<h1>Admin Dashboard</h1>
<p>Welcome <?= htmlspecialchars(Auth::get()->getUserRole()) ?>!</p>
<a href="<?= SITE_URL ?>/logout" class="btn btn-danger">Logout</a>

<?php
$content = ob_get_clean();
require '../layouts/main.php';