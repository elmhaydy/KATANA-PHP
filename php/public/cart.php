<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

// Actions panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($pid > 0) cart_add($pid, max(1, $qty));
    set_flash("success", "Produit ajouté au panier ✅");
    header("Location: /mini-ecommerce/php/public/cart.php");
    exit;
  }

  if ($action === 'update') {
    $qtys = $_POST['qty'] ?? [];
    if (is_array($qtys)) {
      foreach ($qtys as $pid => $q) {
        cart_set((int)$pid, (int)$q);
      }
    }
    set_flash("success", "Panier mis à jour ✅");
    header("Location: /mini-ecommerce/php/public/cart.php");
    exit;
  }

  if ($action === 'remove') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) cart_remove($pid);
    set_flash("success", "Article supprimé ✅");
    header("Location: /mini-ecommerce/php/public/cart.php");
    exit;
  }

  if ($action === 'clear') {
    cart_clear();
    set_flash("success", "Panier vidé ✅");
    header("Location: /mini-ecommerce/php/public/cart.php");
    exit;
  }
}

$flash = get_flash();
$data  = cart_details($pdo);
$items = $data['items'];
$total = $data['total'];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Panier</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>🛒 Panier</h2>
      <div class="muted">Vos articles sélectionnés</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">
        ← Continuer achats
      </a>

      <?php if (is_logged_in()): ?>
        <a class="btn btn-soft" href="/mini-ecommerce/php/public/logout.php">
          Logout
        </a>
      <?php else: ?>
        <a class="btn btn-soft" href="/mini-ecommerce/php/public/login.php">
          Login
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>">
      <b><?= htmlspecialchars($flash['type']) ?>:</b>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($items)): ?>

    <div class="card" style="padding:20px; margin-top:14px;">
      <p class="muted">Ton panier est vide.</p>
    </div>

  <?php else: ?>

    <!-- FORM UPDATE -->
    <form method="post" style="margin-top:14px;">
      <input type="hidden" name="action" value="update">

      <table>
        <thead>
          <tr>
            <th>Produit</th>
            <th class="right">Prix</th>
            <th class="center">Qté</th>
            <th class="right">Total</th>
            <th class="center">Action</th>
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

              <td class="right">
                <?= number_format((float)$it['price'], 2) ?> DH
              </td>

              <td class="center">
                <input
                  type="number"
                  name="qty[<?= (int)$it['id'] ?>]"
                  value="<?= (int)$it['qty'] ?>"
                  min="0"
                  style="max-width:80px; text-align:center;"
                >
              </td>

              <td class="right">
                <?= number_format((float)$it['line_total'], 2) ?> DH
              </td>

              <td class="center">
                <button
                  class="btn btn-soft"
                  type="submit"
                  formaction="/mini-ecommerce/php/public/cart.php"
                  formmethod="post"
                  name="action"
                  value="remove">
                  Supprimer
                </button>
                <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="card" style="padding:14px; margin-top:14px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
          <div style="font-size:1.3rem;">
            <b>Total :</b>
            <span style="color:var(--gold); font-weight:800;">
              <?= number_format((float)$total, 2) ?> DH
            </span>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-red" type="submit">
              Mettre à jour
            </button>

            <a class="btn btn-green" href="/mini-ecommerce/php/public/checkout.php">
              Passer commande →
            </a>
          </div>
        </div>
      </div>
    </form>

    <!-- CLEAR -->
    <form method="post" style="margin-top:10px;">
      <input type="hidden" name="action" value="clear">
      <button class="btn btn-outline" type="submit">
        Vider le panier
      </button>
    </form>

  <?php endif; ?>

</body>
</html>
