# KATANA-PHP

Mini e-Commerce est une boutique en ligne developpee en PHP et MySQL, dediee a la vente de katanas et d'accessoires. Le projet permet de consulter un catalogue, ajouter des produits au panier, se connecter, passer des commandes et gerer un espace d'administration pour les produits et les commandes.

## Fonctionnalites

- Catalogue produits public
- Page detail produit
- Panier en session
- Authentification utilisateur et admin
- Validation de commande avec verification de stock cote serveur
- Back-office produits
- Back-office commandes
- Envoi d'e-mail SMTP local via MailHog

## Stack

- PHP
- MySQL
- HTML/CSS
- SMTP local sur `127.0.0.1:1025`

## Structure

```text
mini-ecommerce/
├─ php/
│  ├─ public/      # Front-office
│  ├─ admin/       # Back-office
│  └─ includes/    # Connexion DB, auth, panier, mail
├─ sql/
│  └─ schema.sql   # Schema + donnees de demo
└─ .env
```

## Prerequis

- XAMPP, WAMP ou un serveur Apache + PHP + MySQL
- Le projet doit etre servi sous le chemin `/mini-ecommerce`
- Base de donnees MySQL locale
- Optionnel : MailHog pour voir les e-mails de test

## Installation

1. Place le dossier `mini-ecommerce` dans le dossier web de ton serveur.
   Exemple XAMPP Windows : `C:\xampp\htdocs\mini-ecommerce`

2. Demarre `Apache` et `MySQL`.

3. Cree la base de donnees en important `sql/schema.sql`.
   Exemple via phpMyAdmin :
   - cree ou remplace la base `mini_ecommerce`
   - importe le fichier `sql/schema.sql`

4. Verifie la configuration MySQL dans `php/includes/db.php`.

```php
$DB_HOST = "127.0.0.1";
$DB_NAME = "mini_ecommerce";
$DB_USER = "root";
$DB_PASS = "";
```

5. Si tu veux tester les e-mails, lance MailHog sur `127.0.0.1:1025`.
   Le projet tente d'envoyer les messages via `php/includes/mail.php`.

## URL a ouvrir

- Site public : `http://localhost/mini-ecommerce/php/public/`
- Catalogue : `http://localhost/mini-ecommerce/php/public/index.php`
- Connexion : `http://localhost/mini-ecommerce/php/public/login.php`
- Admin produits : `http://localhost/mini-ecommerce/php/admin/products.php`
- Admin commandes : `http://localhost/mini-ecommerce/php/admin/orders.php`

## Comptes de demo

Le schema SQL ajoute des utilisateurs de demo.

- Admin : `admin@katana.test` / `test1234`
- User : `user1@katana.test` / `test1234`
- User : `user2@katana.test` / `test1234`

## Base de donnees

Le fichier `sql/schema.sql` cree :

- `users`
- `products`
- `orders`
- `order_items`

Il ajoute aussi :

- 3 utilisateurs de demo
- plusieurs produits
- 1 commande de demonstration

## Notes importantes

- Le projet utilise des URLs absolues du type `/mini-ecommerce/php/public/...`
  Il faut donc conserver ce nom de dossier, ou adapter les chemins dans les fichiers PHP.
- La connexion MySQL est en dur dans `php/includes/db.php`.
- Le fichier `.env` contient actuellement :

```env
MAILER_DSN=smtp://localhost:1025
```

- Le lien "Creer un compte" visible sur la page de login pointe vers `signup.php`, mais ce fichier n'est pas present dans le depot actuellement.

## Verification rapide

1. Ouvre `http://localhost/mini-ecommerce/php/public/`
2. Connecte-toi avec `admin@katana.test`
3. Va sur `http://localhost/mini-ecommerce/php/admin/products.php`
4. Passe une commande avec un compte utilisateur pour verifier le flux panier -> checkout -> commande
