<?php
// ===============================
// includes/cart.php
// Panier en session (simple)
// ===============================

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Structure : $_SESSION['cart'] = [ product_id => quantity ]
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

function cart_get(): array {
  return $_SESSION['cart'];
}

function cart_add(int $productId, int $qty = 1): void {
  if ($qty <= 0) return;
  if (!isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] = 0;
  }
  $_SESSION['cart'][$productId] += $qty;
}

function cart_set(int $productId, int $qty): void {
  if ($qty <= 0) {
    unset($_SESSION['cart'][$productId]);
    return;
  }
  $_SESSION['cart'][$productId] = $qty;
}

function cart_remove(int $productId): void {
  unset($_SESSION['cart'][$productId]);
}

function cart_clear(): void {
  $_SESSION['cart'] = [];
}

/**
 * Récupérer les produits du panier depuis la DB
 * Retourne:
 * [
 *   'items' => [
 *      ['id'=>..,'name'=>..,'price'=>..,'stock'=>..,'qty'=>..,'line_total'=>..],
 *   ],
 *   'total' => 0.00
 * ]
 */
function cart_details(PDO $pdo): array {
  $cart = cart_get();
  if (empty($cart)) {
    return ['items' => [], 'total' => 0.00];
  }

  $ids = array_keys($cart);
  $placeholders = implode(',', array_fill(0, count($ids), '?'));

  // ✅ AJOUT image
  $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id IN ($placeholders)");
  $stmt->execute($ids);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $byId = [];
  foreach ($products as $p) {
    $byId[(int)$p['id']] = $p;
  }

  $items = [];
  $total = 0.00;

  foreach ($cart as $pid => $qty) {
    $pid = (int)$pid;
    $qty = (int)$qty;

    if (!isset($byId[$pid])) continue;

    $p = $byId[$pid];
    $price = (float)$p['price'];
    $lineTotal = $price * $qty;

    $items[] = [
      'id' => $pid,
      'name' => $p['name'],
      'price' => $price,
      'stock' => (int)$p['stock'],
      'image' => $p['image'] ?? null, // ✅
      'qty' => $qty,
      'line_total' => $lineTotal,
    ];

    $total += $lineTotal;
  }

  return ['items' => $items, 'total' => $total];
}