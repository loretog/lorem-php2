<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'Lorem PHP' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>"><?= SITE_NAME ?></a>
            <div class="navbar-nav">
                <a class="nav-link" href="<?= SITE_URL ?>/">Home</a>
                <a class="nav-link" href="<?= SITE_URL ?>/about">About</a>
                <a class="nav-link" href="<?= SITE_URL ?>/contact">Contact</a>
                <?php if( Auth::get()->isLoggedIn() ) : ?>
                <a class="nav-link" href="<?= SITE_URL ?>/logout">Logout</a>                
                <?php else: ?>
                    <a class="nav-link" href="<?= SITE_URL ?>/login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
    <?= $content ?? '' ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>