<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/cart.php";
require_once __DIR__ . "/../includes/mail.php";

// Si déjà connecté => redirige vers catalogue
if (is_logged_in()) {
  header("Location: /mini-ecommerce/php/public/index.php");
  exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = "Email et mot de passe obligatoires.";
  } else {
    $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
      $error = "Identifiants invalides.";
    } else {
      // ✅ Login OK => stocker user en session
      $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
      ];

      set_flash("success", "Connexion réussie ✅");

      // Redirection simple : admin vers admin, user vers catalogue
      if ($user['role'] === 'ROLE_ADMIN') {
        header("Location: /mini-ecommerce/php/admin/products.php");
      } else {
        header("Location: /mini-ecommerce/php/public/index.php");
      }
      exit;
    }
  }
}

$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="page" style="max-width:560px;">

    <div class="topbar">
      <div>
        <h2 style="margin:0;">Connexion</h2>
        <div class="muted" style="margin-top:6px;">
          Accès client / admin — Boutique Katana
        </div>
      </div>

      <div class="actions">
        <a class="btn btn-soft" href="/mini-ecommerce/php/public/index.php">← Catalogue</a>
      </div>
    </div>

    <div class="card" style="padding:16px;">
      <div class="badge" style="margin-bottom:10px;">Compte démo</div>
      <p class="muted" style="margin:0 0 12px 0;">
        <b>admin@katana.test</b> / <b>test1234</b>
      </p>

      <?php if ($flash): ?>
        <div class="flash <?= htmlspecialchars($flash['type']) ?>">
          <b><?= htmlspecialchars($flash['type']) ?>:</b>
          <?= htmlspecialchars($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="flash error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" style="display:grid;gap:10px;margin-top:12px;">
        <div>
          <label>Email</label>
          <input
            name="email"
            type="email"
            required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder="ex: user@katana.com"
          >
        </div>

        <div>
          <label>Mot de passe</label>
          <input
            name="password"
            type="password"
            required
            placeholder="••••••••"
          >
        </div>

        <button class="btn btn-red" type="submit" style="width:100%;">
          Se connecter
        </button>

        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:space-between;margin-top:4px;">
          <a class="btn btn-soft" href="/mini-ecommerce/php/public/signup.php">
            Créer un compte
          </a>
          <a class="btn btn-outline" href="/mini-ecommerce/php/public/index.php">
            Continuer sans compte
          </a>
        </div>
      </form>
    </div>

  </div>
</body>
</html>
