<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>403</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body style="font-family:system-ui;padding:24px;max-width:700px;margin:auto;">
  <h2>403 — Accès interdit</h2>
  <p style="opacity:.8">
    Vous n’avez pas le droit d’accéder à cette ressource (règle Owner / rôle).
  </p>

  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
    <a href="/mini-ecommerce/php/public/index.php"
       style="padding:10px 12px;border:1px solid #ddd;border-radius:10px;text-decoration:none;color:#111;">
      Retour catalogue
    </a>

    <?php if (is_logged_in()): ?>
      <a href="/mini-ecommerce/php/public/logout.php"
         style="padding:10px 12px;border:1px solid #ddd;border-radius:10px;text-decoration:none;color:#111;">
        Se déconnecter
      </a>
    <?php else: ?>
      <a href="/mini-ecommerce/php/public/login.php"
         style="padding:10px 12px;border:1px solid #ddd;border-radius:10px;text-decoration:none;color:#111;">
        Se connecter
      </a>
    <?php endif; ?>
  </div>
</body>
</html>
