<?php
// ===============================
// includes/db.php
// Connexion PDO (MySQL)
// ===============================

// ✅ Ajuste ces valeurs selon ton environnement XAMPP/MAMP
$DB_HOST = "127.0.0.1";
$DB_NAME = "mini_ecommerce";
$DB_USER = "root";
$DB_PASS = ""; // XAMPP souvent vide

try {
  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // erreurs en exceptions
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // fetch assoc par défaut
      PDO::ATTR_EMULATE_PREPARES => false,               // vrais prepares
    ]
  );
} catch (PDOException $e) {
  // ⚠️ Message clair pour dev (soutenance)
  http_response_code(500);
  echo "<h2>Erreur connexion base de données</h2>";
  echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
}
