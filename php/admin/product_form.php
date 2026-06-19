<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";

require_admin();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$name = "";
$description = "";
$price = "0.00";
$stock = "0";
$image = "";

if ($isEdit) {
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $p = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$p) {
    http_response_code(404);
    echo "Produit introuvable";
    exit;
  }
  $name = $p['name'];
  $description = $p['description'] ?? "";
  $price = $p['price'];
  $stock = $p['stock'];
  $image = $p['image'] ?? "";
}

$error = null;

/* ===== UPLOAD IMAGE (sécurisé) ===== */
function upload_product_image(array $file): array {
  if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return [true, null, null];
  }

  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    return [false, null, "Erreur upload image."];
  }

  if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
    return [false, null, "Image trop grande (max 2MB)."];
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($allowed[$mime])) {
    return [false, null, "Format interdit (jpg, png, webp)."];
  }

  $newName = "p_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
  $dir = __DIR__ . "/../public/uploads";

  if (!is_dir($dir)) mkdir($dir, 0755, true);

  if (!move_uploaded_file($file['tmp_name'], $dir . "/" . $newName)) {
    return [false, null, "Impossible d'enregistrer l'image."];
  }

  return [true, $newName, null];
}

/* ===== SUBMIT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? "");
  $description = trim($_POST['description'] ?? "");
  $price = trim($_POST['price'] ?? "0");
  $stock = trim($_POST['stock'] ?? "0");

  if ($name === "" || strlen($name) < 2) {
    $error = "Nom obligatoire (min 2 caractères).";
  } elseif (!is_numeric($price) || (float)$price < 0) {
    $error = "Prix invalide.";
  } elseif (!ctype_digit((string)$stock)) {
    $error = "Stock invalide.";
  } else {
    [$ok, $newImg, $upErr] = upload_product_image($_FILES['image'] ?? []);
    if (!$ok) {
      $error = $upErr;
    } else {

      if ($newImg !== null) {
        if ($isEdit && $image) {
          @unlink(__DIR__ . "/../public/uploads/" . $image);
        }
        $image = $newImg;
      }

      if ($isEdit) {
        $stmt = $pdo->prepare(
          "UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?"
        );
        $stmt->execute([$name, $description, (float)$price, (int)$stock, $image ?: null, $id]);
        set_flash("success", "Produit modifié ✅");
      } else {
        $stmt = $pdo->prepare(
          "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, (float)$price, (int)$stock, $image ?: null]);
        set_flash("success", "Produit ajouté ✅");
      }

      header("Location: /mini-ecommerce/php/admin/products.php");
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $isEdit ? "Modifier" : "Ajouter" ?> produit</title>
  <link rel="stylesheet" href="/mini-ecommerce/php/public/assets/style.css">
</head>

<body>
<div class="page" style="max-width:760px;">

  <!-- ===== TOP BAR ===== -->
  <div class="topbar">
    <div>
      <h2 style="margin:0;">
        <?= $isEdit ? "✏️ Modifier" : "➕ Ajouter" ?> produit
      </h2>
      <div class="muted">Administration Katana</div>
    </div>

    <div class="actions">
      <a class="btn btn-soft" href="/mini-ecommerce/php/admin/products.php">
        ← Retour
      </a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="flash error">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- ===== FORM ===== -->
  <div class="card" style="padding:16px;">
    <form method="post" enctype="multipart/form-data" style="display:grid;gap:12px;">

      <div>
        <label>Nom</label>
        <input name="name" required value="<?= htmlspecialchars($name) ?>">
      </div>

      <div>
        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <label>Prix (DH)</label>
          <input name="price" type="number" step="0.01" min="0" required value="<?= htmlspecialchars($price) ?>">
        </div>
        <div>
          <label>Stock</label>
          <input name="stock" type="number" min="0" required value="<?= htmlspecialchars($stock) ?>">
        </div>
      </div>

      <div>
        <label>Image produit</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        <small>jpg, png, webp — max 2MB</small>
      </div>

      <?php if ($image): ?>
        <div>
          <label>Image actuelle</label>
          <img
            src="/mini-ecommerce/php/public/uploads/<?= htmlspecialchars($image) ?>"
            class="thumb"
            style="width:140px;height:auto;"
          >
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-red" type="submit">
          Enregistrer
        </button>
        <a class="btn btn-outline" href="/mini-ecommerce/php/admin/products.php">
          Annuler
        </a>
      </div>

    </form>
  </div>

</div>
</body>
</html>
