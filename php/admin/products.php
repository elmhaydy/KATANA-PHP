<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";

require_admin();

$stmt = $pdo->query("SELECT id, name, price, stock, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Produits</title>
  <link rel="stylesheet" href="/mini-ecommerce/php/public/assets/style.css">
</head>

<body>
<div class="page">

  <!-- ===== TOP BAR ===== -->
  <div class="topbar">
    <!-- LOGO -->
    <div class="logo-header">
      <div class="logo-wrap">
        <img src="/mini-ecommerce/php/public/assets/logo-katana.png"
             class="logo-img"
             alt="Katana Admin">
      </div>
      <div class="logo-text">
        <div class="logo-title">
          KATANA<span class="logo-accent">刃</span>
        </div>
        <div class="logo-subtitle">
          Administration
        </div>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">Voir site</a>
      <a class="btn btn-red" href="/mini-ecommerce/php/admin/product_form.php">+ Produit</a>
      <a class="btn btn-soft" href="/mini-ecommerce/php/admin/orders.php">Commandes</a>
      <a class="btn btn-outline" href="/mini-ecommerce/php/public/logout.php">Logout</a>
    </div>
  </div>

  <!-- ===== FLASH ===== -->
  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <b><?= htmlspecialchars($flash['type']) ?>:</b>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- ===== TABLE ===== -->
  <div class="card" style="margin-top:14px; padding:0;">
    <table>
      <thead>
        <tr>
          <th>Image</th>
          <th>Nom</th>
          <th class="right">Prix</th>
          <th class="center">Stock</th>
          <th class="center">Action</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td>
              <div class="item-row">
                <?php if (!empty($p['image'])): ?>
                  <img class="thumb"
                       src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($p['image']) ?>"
                       alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                  <div class="thumb placeholder">—</div>
                <?php endif; ?>
              </div>
            </td>

            <td>
              <b><?= htmlspecialchars($p['name']) ?></b>
            </td>

            <td class="right">
              <?= number_format((float)$p['price'], 2) ?> DH
            </td>

            <td class="center">
              <span class="badge"><?= (int)$p['stock'] ?></span>
            </td>

            <td class="center">
              <a class="btn btn-soft"
                 href="/mini-ecommerce/php/admin/product_form.php?id=<?= (int)$p['id'] ?>">
                Modifier
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
