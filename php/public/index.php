<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

// Liste produits (AJOUT image)
$stmt = $pdo->query("SELECT id, name, price, stock, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

$flash = get_flash();
$user  = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catalogue</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="logo-header">
  <div class="logo-wrap">
    <img
      src="/mini-ecommerce/php/public/assets/logo_katana.png"
      alt="Katana Store"
      class="logo-img"
    >
  </div>

  <div class="logo-text">
    <div class="logo-title">
      KATANA<span class="logo-accent">刃</span>
    </div>
    <div class="logo-subtitle">
      Premium Japanese Blades
    </div>
  </div>
</div>


    <div class="actions">
      <?php if ($user): ?>
        <span class="muted">👤 <?= htmlspecialchars($user['email']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>

        <a class="btn btn-soft" href="/mini-ecommerce/php/public/my_orders.php">
          📜 Mes commandes
        </a>

        <a class="btn btn-soft" href="/mini-ecommerce/php/public/logout.php">
          Logout
        </a>

        <?php if (is_admin()): ?>
          <a class="btn btn-red" href="/mini-ecommerce/php/admin/products.php">
            Admin
          </a>
        <?php endif; ?>
      <?php else: ?>
        <a class="btn btn-soft" href="/mini-ecommerce/php/public/login.php">
          Login
        </a>
      <?php endif; ?>

      <a class="btn btn-outline" href="/mini-ecommerce/php/public/cart.php">
        🛒 Panier <span class="badge"><?= count(cart_get()) ?></span>
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <b><?= htmlspecialchars($flash['type']) ?>:</b>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- GRID PRODUITS -->
  <div class="products-grid">
    <?php foreach ($products as $p): ?>
      <div class="product-card">

        <?php if (!empty($p['image'])): ?>
          <img
            class="product-img"
            src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($p['image']) ?>"
            alt="<?= htmlspecialchars($p['name']) ?>"
          >
        <?php else: ?>
          <div class="product-img" style="display:grid;place-items:center;opacity:.65;border-bottom:1px solid rgba(255,255,255,.06);">
            Aucune image
          </div>
        <?php endif; ?>

        <div class="product-body">
          <div class="product-title"><?= htmlspecialchars($p['name']) ?></div>

          <div class="product-meta">
            Prix : <b style="color:var(--gold);"><?= number_format((float)$p['price'], 2) ?> DH</b><br>
            Stock : <b><?= (int)$p['stock'] ?></b>
          </div>

          <div class="product-actions">
            <a class="btn btn-soft" href="/mini-ecommerce/php/public/product.php?id=<?= (int)$p['id'] ?>">
              Détails
            </a>

            <?php if ((int)$p['stock'] > 0): ?>
              <form method="post" action="/mini-ecommerce/php/public/cart.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn btn-green" type="submit">
                  Ajouter
                </button>
              </form>
            <?php else: ?>
              <span class="btn btn-soft" style="cursor:default;opacity:.7;">
                Rupture
              </span>
            <?php endif; ?>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>
