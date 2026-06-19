<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";

require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

/* ===== COMMANDE + CLIENT ===== */
$stmt = $pdo->prepare("
  SELECT o.*, u.email
  FROM orders o
  JOIN users u ON u.id = o.user_id
  WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

/* ===== UPDATE STATUT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $_POST['status'] ?? 'PENDING';
  $allowed = ['PENDING','CONFIRMED','SHIPPED','CANCELLED'];

  if (!in_array($status, $allowed, true)) {
    set_flash("error", "Statut invalide.");
  } else {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
    set_flash("success", "Statut mis à jour ✅");
  }

  header("Location: /mini-ecommerce/php/admin/order_show.php?id=" . $id);
  exit;
}

/* ===== ITEMS ===== */
$stmt = $pdo->prepare("
  SELECT oi.quantity, oi.unit_price, oi.line_total, p.name
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
  <title>Admin — Commande #<?= (int)$id ?></title>
  <link rel="stylesheet" href="/mini-ecommerce/php/public/assets/style.css">
</head>

<body>
<div class="page">

  <!-- ===== TOP BAR ===== -->
  <div class="topbar">
    <div>
      <h2 style="margin:0;">📦 Commande #<?= (int)$id ?></h2>
      <div class="muted">Gestion des commandes</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/admin/orders.php">
        ← Commandes
      </a>
      <a class="btn btn-outline" href="/mini-ecommerce/php/admin/products.php">
        Produits
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- ===== INFOS COMMANDE ===== -->
  <div class="card" style="padding:14px;margin-top:14px;">
    <div style="display:grid;gap:6px;">
      <div><b>Client :</b> <?= htmlspecialchars($order['email']) ?></div>
      <div><b>Date :</b> <?= htmlspecialchars($order['created_at']) ?></div>
      <div><b>Total :</b> <?= number_format((float)$order['total'], 2) ?> DH</div>
      <div>
        <b>Statut :</b>
        <span class="status <?= strtolower($order['status']) ?>">
          <?= htmlspecialchars($order['status']) ?>
        </span>
      </div>
    </div>

    <!-- UPDATE STATUT -->
    <form method="post" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <label class="muted"><b>Modifier statut</b></label>

      <select name="status" style="max-width:220px;">
        <?php foreach (['PENDING','CONFIRMED','SHIPPED','CANCELLED'] as $st): ?>
          <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>>
            <?= $st ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn btn-red" type="submit">
        Mettre à jour
      </button>
    </form>
  </div>

  <!-- ===== ARTICLES ===== -->
  <div class="card" style="padding:14px;margin-top:14px;">
    <h3 style="margin-top:0;">Articles commandés</h3>

    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th class="center">Qté</th>
          <th class="right">Prix</th>
          <th class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td class="center"><?= (int)$it['quantity'] ?></td>
            <td class="right"><?= number_format((float)$it['unit_price'], 2) ?> DH</td>
            <td class="right"><?= number_format((float)$it['line_total'], 2) ?> DH</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
