<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

require_login();

$data  = cart_details($pdo);
$items = $data['items'];
$total = $data['total'];

if (empty($items)) {
  set_flash("info", "Panier vide.");
  header("Location: /mini-ecommerce/php/public/index.php");
  exit;
}

$user  = current_user();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();

    $cart = cart_get();
    $ids  = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // ✅ Rechargement produits (FOR UPDATE)
    $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byId = [];
    foreach ($products as $p) $byId[(int)$p['id']] = $p;

    // ✅ Vérif stock + recalcul total
    $serverTotal = 0.00;
    foreach ($cart as $pid => $qty) {
      $pid = (int)$pid;
      $qty = (int)$qty;

      if (!isset($byId[$pid])) {
        throw new Exception("Produit introuvable (ID: $pid).");
      }

      $stock = (int)$byId[$pid]['stock'];
      if ($qty <= 0) continue;

      if ($qty > $stock) {
        throw new Exception("Stock insuffisant pour: " . $byId[$pid]['name'] . " (demandé $qty, stock $stock).");
      }

      $price = (float)$byId[$pid]['price'];
      $serverTotal += ($price * $qty);
    }

    if ($serverTotal <= 0) {
      throw new Exception("Panier invalide.");
    }

    // ✅ Créer commande
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'PENDING')");
    $stmt->execute([(int)$user['id'], $serverTotal]);
    $orderId = (int)$pdo->lastInsertId();

    // ✅ Items + décrément stock
    $stmtItem = $pdo->prepare("
      INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmtUpd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart as $pid => $qty) {
      $pid = (int)$pid;
      $qty = (int)$qty;
      if ($qty <= 0) continue;

      $price = (float)$byId[$pid]['price'];
      $line  = $price * $qty;

      $stmtItem->execute([$orderId, $pid, $qty, $price, $line]);
      $stmtUpd->execute([$qty, $pid]);
    }

    $pdo->commit();

    cart_clear();
    $_SESSION['last_order_id'] = $orderId;
    unset($_SESSION['mail_sent_for']);

    header("Location: /mini-ecommerce/php/public/thanks.php?order_id=" . $orderId);
    exit;

  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $error = $e->getMessage();
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Checkout</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>✅ Checkout</h2>
      <div class="muted">Résumé avant confirmation</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/cart.php">← Retour panier</a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="flash error">
      <b>Erreur :</b> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <div class="card" style="padding:16px; margin-top:14px;">
    <h3 style="margin-bottom:12px;">Résumé commande</h3>

    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th class="right">Prix</th>
          <th class="center">Qté</th>
          <th class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td>
              <div class="item-row">
                <?php if (!empty($it['image'])): ?>
                  <img class="thumb"
                       src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($it['image']) ?>"
                       alt="<?= htmlspecialchars($it['name']) ?>">
                <?php else: ?>
                  <div class="thumb placeholder">—</div>
                <?php endif; ?>

                <div>
                  <b><?= htmlspecialchars($it['name']) ?></b><br>
                  <small>Stock : <?= (int)$it['stock'] ?></small>
                </div>
              </div>
            </td>

            <td class="right"><?= number_format((float)$it['price'], 2) ?> DH</td>
            <td class="center"><?= (int)$it['qty'] ?></td>
            <td class="right"><?= number_format((float)$it['line_total'], 2) ?> DH</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="card" style="padding:14px; margin-top:14px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div style="font-size:1.35rem;">
          <b>Total :</b>
          <span style="color:var(--gold); font-weight:900;">
            <?= number_format((float)$total, 2) ?> DH
          </span>
        </div>

        <form method="post">
          <button class="btn btn-red" type="submit">
            Confirmer la commande
          </button>
        </form>
      </div>

      <div class="muted" style="margin-top:10px;">
        🔒 Stock vérifié côté serveur + transaction SQL (FOR UPDATE)
      </div>
    </div>
  </div>

</body>
</html>
