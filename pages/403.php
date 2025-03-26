<?php
http_response_code(403);
?>
<div class="container">
    <h1>Access Denied</h1>
    <p>You don't have permission to view this page.</p>
    <p>Please contact the administrator if you believe this is an error.</p>
    <a href="<?= SITE_URL ?>">Return to Homepage</a>
</div>