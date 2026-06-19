<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";

require_admin();

$stmt = $pdo->query("
  SELECT o.id, o.total, o.status, o.created_at, u.email
  FROM orders o
  JOIN users u ON u.id = o.user_id
  ORDER BY o.id DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Commandes</title>
  <link rel="stylesheet" href="/mini-ecommerce/php/public/assets/style.css">
</head>

<body>
<div class="page">

  <!-- ===== TOP BAR ===== -->
  <div class="topbar">
    <div>
      <h2 style="margin:0;">📦 Commandes</h2>
      <div class="muted">Administration Katana</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/admin/products.php">
        Produits
      </a>
      <a class="btn btn-outline" href="/mini-ecommerce/php/public/index.php">
        Voir site
      </a>
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/logout.php">
        Logout
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- ===== TABLE COMMANDES ===== -->
  <div class="card" style="padding:0;margin-top:14px;">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Client</th>
          <th class="right">Total</th>
          <th class="center">Statut</th>
          <th class="center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>

            <td>
              <strong><?= htmlspecialchars($o['email']) ?></strong><br>
              <small class="muted"><?= htmlspecialchars($o['created_at']) ?></small>
            </td>

            <td class="right">
              <?= number_format((float)$o['total'], 2) ?> DH
            </td>

            <td class="center">
              <span class="status <?= strtolower($o['status']) ?>">
                <?= htmlspecialchars($o['status']) ?>
              </span>
            </td>

            <td class="center">
              <a class="btn btn-soft"
                 href="/mini-ecommerce/php/admin/order_show.php?id=<?= (int)$o['id'] ?>">
                Détails
              </a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="5" class="center muted">
              Aucune commande enregistrée
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
