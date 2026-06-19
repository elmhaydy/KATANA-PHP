<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

require_login();

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

// Charger commande + email
$stmt = $pdo->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  http_response_code(404);
  echo "Commande introuvable";
  exit;
}

// ✅ Owner-check (ou admin)
require_owner_or_admin((int)$order['user_id']);

// Items (avec image)
$stmt = $pdo->prepare("
  SELECT oi.quantity, oi.unit_price, oi.line_total, p.name, p.image
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Email UNE SEULE FOIS
$mailSent = false;
if (!isset($_SESSION['mail_sent_for']) || (int)$_SESSION['mail_sent_for'] !== $orderId) {

  $lines = [];
  $lines[] = "Bonjour,";
  $lines[] = "";
  $lines[] = "Votre commande #{$orderId} est bien enregistrée.";
  $lines[] = "Statut: {$order['status']}";
  $lines[] = "";
  $lines[] = "Détails:";
  foreach ($items as $it) {
    $lines[] = "- {$it['name']} x{$it['quantity']} = " . number_format((float)$it['line_total'], 2) . " DH";
  }
  $lines[] = "";
  $lines[] = "Total: " . number_format((float)$order['total'], 2) . " DH";
  $lines[] = "";
  $lines[] = "Merci.";

  $body = implode("\n", $lines);

  $mailSent = smtp_send_mail(
    $order['email'],
    "Confirmation commande #{$orderId}",
    $body
  );

  $_SESSION['mail_sent_for'] = $orderId;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Merci</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body class="page">

  <!-- TOPBAR -->
  <div class="topbar">
    <div>
      <h2>🎉 Merci !</h2>
      <div class="muted">Commande <b>#<?= (int)$orderId ?></b> enregistrée</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">← Retour catalogue</a>
      <a class="btn btn-outline" href="/mini-ecommerce/php/public/cart.php">🛒 Panier</a>
    </div>
  </div>

  <!-- CARD -->
  <div class="card" style="padding:16px; margin-top:14px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
      <div>
        <div class="muted">Client</div>
        <div style="font-weight:800; color:var(--chrome);"><?= htmlspecialchars($order['email']) ?></div>
      </div>

      <div>
        <div class="muted">Statut</div>
        <div style="font-weight:800;"><?= htmlspecialchars($order['status']) ?></div>
      </div>

      <div>
        <div class="muted">Total</div>
        <div style="font-weight:900; color:var(--gold); font-size:1.35rem;">
          <?= number_format((float)$order['total'], 2) ?> DH
        </div>
      </div>
    </div>

    <div class="divider"></div>

    <h3 style="margin-bottom:10px;">Articles</h3>

    <div class="list">
      <?php foreach ($items as $it): ?>
        <div class="list-item">
          <?php if (!empty($it['image'])): ?>
            <img
              class="thumb"
              src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($it['image']) ?>"
              alt="<?= htmlspecialchars($it['name']) ?>"
            >
          <?php else: ?>
            <div class="thumb placeholder">—</div>
          <?php endif; ?>

          <div style="flex:1;">
            <div style="font-weight:800;"><?= htmlspecialchars($it['name']) ?></div>
            <div class="muted">
              x<?= (int)$it['quantity'] ?> —
              <?= number_format((float)$it['line_total'], 2) ?> DH
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card" style="padding:12px; margin-top:12px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);">
      <div class="muted">
        📩 Email :
        <b><?= $mailSent ? "envoyé (MailHog)" : "échec (vérifie MailHog 127.0.0.1:1025)" ?></b>
      </div>
      <div class="muted" style="margin-top:6px;">
        MailHog : <b>http://127.0.0.1:8025</b> — SMTP <b>127.0.0.1:1025</b>
      </div>
    </div>
  </div>

</body>
</html>
