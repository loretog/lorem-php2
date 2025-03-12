<?php
$users = Database::fetchAll('users');
?>
<h1>Welcome to Our Site</h1>
<p>This is the home page of our PHP routing example.</p>

<?php if(!empty($users)): ?>
    <h2>User List</h2>
    <ul>
    <?php foreach($users as $user): ?>
        <li><?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['password']) ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

