<?php


Auth::get()->logout();
header('Location: ' . SITE_URL);
exit();