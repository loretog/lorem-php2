<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $errors = [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    // Check if username exists
    $existing = Database::fetch('users', ['username' => $username]);
    if ($existing) {
        $errors[] = 'Username already exists';
    }

    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        Database::insert('users', [
            'username' => $username,
            'password' => $hashedPassword,
            'role' => $role,
            'created' => date('Y-m-d H:i:s')
        ]);

        // Redirect to avoid resubmission
        header('Location: ' . SITE_URL . '/');
        exit;
    }
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    $existing = Database::fetch('users', ['username' => $username]);
    if ($existing && $existing['id'] != $id) {
        $errors[] = 'Username already exists';
    }

    if (empty($errors)) {
        $updateData = ['username' => $username, 'role' => $role];
        
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        Database::update('users', $updateData, ['id' => $id]);
        header('Location: ' . SITE_URL . '/');
        exit;
    }
}

// Fetch user for editing
$userToEdit = null;
if (isset($_GET['id'])) {
    $userToEdit = Database::fetch('users', ['id' => $_GET['id']]);
}
?>

<h1>Welcome to Our Site</h1>
<p>This is the home page of our PHP routing example.</p>

<?php if (!$userToEdit): ?>
<div class="container mt-4">
    <h2>Create New User</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
        <a href="<?= SITE_URL ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php endif; ?>

<?php if ($userToEdit): ?>
<div class="container mt-4">
    <h2>Edit User</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($userToEdit['id']) ?>">
        
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?= htmlspecialchars($userToEdit['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role">
                <option value="user" <?= $userToEdit['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $userToEdit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
        <a href="<?= SITE_URL ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php endif; ?>

<?php
$users = Database::fetchAll('users');
?>

<?php if(!empty($users)): ?>
    <h2>User List</h2>
    <ul class="list-group">
    <?php foreach($users as $user): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['role']) ?>
            <a href="?id=<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

