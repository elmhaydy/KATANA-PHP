<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

// Charger commande
$stmt = $pdo->prepare("SELECT o.* FROM orders o WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

// ✅ Owner-check (ou admin)
require_owner_or_admin((int)$order['user_id']);

// Items + image
$stmt = $pdo->prepare("
  SELECT oi.quantity, oi.unit_price, oi.line_total, p.name, p.image
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Commande #<?= (int)$id ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>📦 Commande #<?= (int)$id ?></h2>
      <div class="muted">Détails de la commande</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/my_orders.php">← Mes commandes</a>
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

  <!-- SUMMARY -->
  <div class="card" style="padding:16px; margin-top:14px;">
    <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap;">
      <div>
        <div class="muted">Statut</div>
        <div style="font-weight:900;">
          <span class="pill"><?= htmlspecialchars($order['status']) ?></span>
        </div>
      </div>

      <div>
        <div class="muted">Total</div>
        <div style="font-weight:900; color:var(--gold); font-size:1.35rem;">
          <?= number_format((float)$order['total'], 2) ?> DH
        </div>
      </div>

      <?php if (!empty($order['created_at'])): ?>
        <div>
          <div class="muted">Date</div>
          <div style="font-weight:800; color:var(--chrome);">
            <?= htmlspecialchars($order['created_at']) ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ITEMS -->
  <div class="card" style="padding:16px; margin-top:14px;">
    <h3 style="margin-bottom:12px;">Articles</h3>

    <div class="list">
      <?php foreach ($items as $it): ?>
        <div class="list-item">
          <?php if (!empty($it['image'])): ?>
            <img class="thumb"
                 src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($it['image']) ?>"
                 alt="<?= htmlspecialchars($it['name']) ?>">
          <?php else: ?>
            <div class="thumb placeholder">—</div>
          <?php endif; ?>

          <div style="flex:1;">
            <div style="font-weight:900;"><?= htmlspecialchars($it['name']) ?></div>
            <div class="muted">
              x<?= (int)$it['quantity'] ?> —
              <?= number_format((float)$it['line_total'], 2) ?> DH
            </div>
          </div>

          <div style="text-align:right;">
            <div class="muted">PU</div>
            <div style="font-weight:800;">
              <?= number_format((float)$it['unit_price'], 2) ?> DH
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>
