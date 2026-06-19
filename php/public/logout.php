<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";


session_destroy();

// Optionnel : redirige vers login
header("Location: /mini-ecommerce/php/public/login.php");
exit;
