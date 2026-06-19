<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(404);
  echo "Produit introuvable";
  exit;
}

$stmt = $pdo->prepare("SELECT id, name, description, price, stock, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
  http_response_code(404);
  echo "Produit introuvable";
  exit;
}

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($p['name']) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>🗡️ <?= htmlspecialchars($p['name']) ?></h2>
      <div class="muted" style="margin-top:4px;">
        Détails du produit
      </div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">← Catalogue</a>
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

  <!-- FICHE PRODUIT -->
  <div class="card" style="padding:16px; margin-top:14px;">

    <?php if (!empty($p['image'])): ?>
      <img
        class="product-img"
        src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($p['image']) ?>"
        alt="<?= htmlspecialchars($p['name']) ?>"
        style="height:320px; border-radius: var(--radius);"
      >
    <?php else: ?>
      <div class="product-img" style="height:260px; display:grid; place-items:center; opacity:.65;">
        Aucune image
      </div>
    <?php endif; ?>

    <div style="padding-top:14px;">
      <h3 style="margin-bottom:6px; color: var(--chrome);">Description</h3>

      <div class="muted" style="margin-bottom:12px;">
        <?= nl2br(htmlspecialchars($p['description'] ?? 'Aucune description.')) ?>
      </div>

      <div class="card" style="padding:12px; border-radius: var(--radius); background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);">
        <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
          <div>
            <div class="muted">Prix</div>
            <div style="font-size:1.6rem; font-weight:800; color: var(--gold);">
              <?= number_format((float)$p['price'], 2) ?> DH
            </div>
          </div>

          <div>
            <div class="muted">Stock</div>
            <div style="font-size:1.2rem; font-weight:800;">
              <?= (int)$p['stock'] ?>
            </div>
          </div>

          <div style="min-width:260px; flex:1;">
            <?php if ((int)$p['stock'] > 0): ?>
              <form method="post" action="/mini-ecommerce/php/public/cart.php" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end; justify-content:flex-end;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">

                <div style="width:140px;">
                  <label for="qty">Quantité</label>
                  <input id="qty" type="number" name="qty" value="1" min="1">
                </div>

                <button class="btn btn-green" type="submit">
                  Ajouter au panier
                </button>
              </form>
            <?php else: ?>
              <div class="btn btn-soft" style="cursor:default; opacity:.7; width:100%; justify-content:center;">
                Rupture de stock
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

</body>
</html>
