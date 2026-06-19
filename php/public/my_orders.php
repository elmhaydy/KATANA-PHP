<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

require_login();

$user = current_user();

// ✅ Owner rule : uniquement commandes du user connecté
$stmt = $pdo->prepare("
  SELECT id, total, status, created_at
  FROM orders
  WHERE user_id = ?
  ORDER BY id DESC
");
$stmt->execute([(int)$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mes commandes</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>📜 Mes commandes</h2>
      <div class="muted">Historique de vos achats</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">← Catalogue</a>
      <a class="btn btn-outline" href="/mini-ecommerce/php/public/cart.php">
        🛒 Panier <span class="badge"><?= count(cart_get()) ?></span>
      </a>
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/logout.php">Logout</a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <b><?= htmlspecialchars($flash['type']) ?>:</b>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>

    <div class="card" style="padding:18px; margin-top:14px;">
      <div class="muted">Aucune commande pour le moment.</div>
    </div>

  <?php else: ?>

    <div class="card" style="padding:16px; margin-top:14px;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th class="center">Statut</th>
            <th class="right">Total</th>
            <th class="center">Action</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><b>#<?= (int)$o['id'] ?></b></td>

              <td>
                <span class="muted"><?= htmlspecialchars($o['created_at'] ?? '') ?></span>
              </td>

              <td class="center">
                <span class="pill">
                  <?= htmlspecialchars($o['status']) ?>
                </span>
              </td>

              <td class="right" style="font-weight:900; color: var(--gold);">
                <?= number_format((float)$o['total'], 2) ?> DH
              </td>

              <td class="center">
                <a class="btn btn-soft"
                   href="/mini-ecommerce/php/public/order_show.php?id=<?= (int)$o['id'] ?>">
                  Détails
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="muted" style="margin-top:12px;">
        🔒 Owner rule : vous voyez uniquement vos commandes.
      </div>
    </div>

  <?php endif; ?>

</body>
</html>
