# SP PHP To Do List — Étape 2 (Développement)

Application PHP procédurale-orientée-objet (PDO, MVC) de gestion de tâches avec affectation
à plusieurs collaborateurs et pondération par % d'investissement, conforme au retour
de M. Christoffel (22/05/2026) :

> *« le dév App PHP est maintenant un travail individuel. »*

## 🔗 Application en ligne

> **À DÉFINIR APRÈS DÉPLOIEMENT** — sera renseigné une fois le site activé sur AlwaysData.
>
> Pattern attendu : `https://<sous-domaine>.alwaysdata.net/`

## 📦 Export SQL

Le dump SQL avec données est disponible à la racine : [`database.sql`](./database.sql)
(8 tables, 5 collaborateurs, 10 tâches, 11 affectations avec % d'investissement).

## Auteur
**Mikail Lekesiz** — LP DWCA 2025/2026 — Université de Strasbourg
Tuteur : M. Eric Christoffel — UE 6.1 Développement Back-End

## Fonctionnalités
- CRUD complet des **tâches** (titre, description, priorité, statut, catégorie, échéance).
- CRUD complet des **collaborateurs** (nom).
- Affectation **many-to-many** `tache_collaborateur` avec colonne **`pourcentageInvestissement`**
  (contrainte CHECK 0–100).
- Tableau de bord : compteurs par statut, charge par collaborateur, 5 prochaines tâches.
- Filtre par statut sur la liste des tâches.
- Sécurité : protection CSRF, prepared statements PDO, échappement HTML, headers OWASP minimaux.

## Stack
- **PHP 8.1+** (typage strict, attributs)
- **MySQL 8** / MariaDB 10
- **PDO** (mode exception, fetch assoc, sans émulation)
- **Bootstrap 5.3** (CDN, aucun build)
- Architecture **MVC minimaliste maison** (front controller, routeur, contrôleurs, modèles, vues)

## Structure du projet

```
todoList-app/
├── public/
│   ├── index.php              # Front controller (seul point d'entrée web)
│   ├── .htaccess              # URL rewriting
│   └── assets/css/style.css
├── src/
│   ├── autoload.php           # PSR-4-light
│   ├── Database.php           # Singleton PDO
│   ├── Router.php             # Front controller / dispatch
│   ├── Controller.php         # Classe de base
│   ├── Csrf.php               # Anti-CSRF
│   ├── Flash.php              # Messages éphémères
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── TacheController.php
│   │   └── CollaborateurController.php
│   └── models/
│       ├── Tache.php          # CRUD + affectation many-to-many
│       ├── Collaborateur.php
│       └── Reference.php      # priorite, statut, categorie, tag
├── views/
│   ├── layout/                # header, footer
│   ├── home/                  # tableau de bord
│   ├── tache/                 # index, form, show
│   ├── collaborateur/         # index, form
│   └── error/                 # 404, 500
├── config/
│   ├── config.php             # env-driven, commité (pas de secrets)
│   ├── config.example.php     # modèle pour config.local.php
│   └── config.local.php       # (gitignored) override dev local
├── sql/install.sql            # schéma + données, version locale (avec CREATE DATABASE)
├── database.sql               # schéma + données, version prod (sans CREATE DATABASE)
└── README.md
```

## Installation locale (MAMP / XAMPP / WAMP)

1. **Cloner** le repo dans la docroot du serveur web local (ou créer un VirtualHost pointant sur `public/`) :
   ```bash
   git clone https://github.com/lekesiz/sp-php-todolist-mikail.git
   cd sp-php-todolist-mikail
   ```
2. **Importer la base** :
   ```bash
   mysql -u root -p < sql/install.sql
   ```
   ou via phpMyAdmin : *Importer* → `sql/install.sql`.
3. **Configuration** — deux options :
   - **A. Aucune action** si vos identifiants DB sont `root` / `root` (MAMP par défaut).
   - **B. Override local** : `cp config/config.example.php config/config.local.php`, puis éditer.
4. **Accéder à l'application** :
   - Si la doc-root est `todoList-app/public/` : `http://localhost/`
   - Sinon : `http://localhost/todoList-app/public/` et ajuster `APP_BASE_URL` ou `config.local.php`.

## 🚀 Déploiement en production (AlwaysData)

L'application est conçue pour fonctionner sur AlwaysData (recommandé par M. Christoffel) ou
tout hébergeur LAMP standard. Voici la procédure complète :

### 1. Préparer le compte AlwaysData

1. Créer un compte gratuit sur https://www.alwaysdata.com/
2. Dans le panel, noter votre nom d'utilisateur (ex. `lekesiz`).

### 2. Créer la base de données MySQL

1. Panel → **Bases de données** → **MySQL** → **Ajouter une base de données**.
2. Nom : `todolist` (sera préfixé automatiquement, ex. `lekesiz_todolist`).
3. Noter le **hôte** (ex. `mysql-lekesiz.alwaysdata.net`).
4. Créer un utilisateur MySQL ou utiliser celui par défaut, noter le mot de passe.

### 3. Importer le schéma + données

1. Panel → **Bases de données** → **MySQL** → cliquer sur la base → **phpMyAdmin**.
2. Onglet **Importer** → choisir [`database.sql`](./database.sql) (à la racine du repo).
3. Cliquer **Exécuter**. Vérifier que les 8 tables sont créées avec les données.

### 4. Déployer le code

```bash
# Connexion SSH (le panel fournit l'hôte, ex. ssh-lekesiz.alwaysdata.net)
ssh lekesiz@ssh-lekesiz.alwaysdata.net

# Cloner le repo dans le dossier www
cd ~/www
git clone https://github.com/lekesiz/sp-php-todolist-mikail.git
```

### 5. Configurer les variables d'environnement

Créer `~/www/sp-php-todolist-mikail/public/.htaccess` (ou éditer l'existant) en ajoutant :

```apache
# Variables d'environnement pour la production
SetEnv DB_HOST mysql-lekesiz.alwaysdata.net
SetEnv DB_NAME lekesiz_todolist
SetEnv DB_USER lekesiz
SetEnv DB_PASSWORD VOTRE_MOT_DE_PASSE
SetEnv APP_BASE_URL ""
SetEnv APP_DEBUG false
```

> Alternativement, créer `config/config.local.php` (gitignored, donc à recréer sur le serveur)
> à partir de `config.example.php` et y mettre vos identifiants.

### 6. Configurer le site web AlwaysData

1. Panel → **Sites Web** → **Ajouter un site**.
2. **Adresses** : `<sous-domaine>.alwaysdata.net` (ou un domaine personnalisé).
3. **Type de site** : `PHP`.
4. **Version PHP** : 8.2 ou supérieure.
5. **Chemin du document racine** : `/sp-php-todolist-mikail/public`
   (⚠️ pointer sur `public/`, pas sur la racine du projet, pour la sécurité).
6. Sauvegarder. Le site est actif sous quelques secondes.

### 7. Vérifier

Ouvrir `https://<sous-domaine>.alwaysdata.net/` — le tableau de bord doit s'afficher avec :
- 4 cartes de compteurs par statut (À faire / En cours / Bloquée / Terminée)
- Liste des 5 prochaines tâches
- Charge par collaborateur

## Routes principales

| Méthode | URL | Action |
|---|---|---|
| GET | `/?r=home/index` | Tableau de bord |
| GET | `/?r=tache/index[&statut=X]` | Liste des tâches (filtrable) |
| GET | `/?r=tache/create` | Formulaire création |
| POST | `/?r=tache/store` | Création |
| GET | `/?r=tache/show&id=X` | Détail |
| GET | `/?r=tache/edit&id=X` | Formulaire modification |
| POST | `/?r=tache/update&id=X` | Mise à jour |
| POST | `/?r=tache/delete&id=X` | Suppression |
| GET | `/?r=collaborateur/index` | Liste collaborateurs |
| GET, POST | `/?r=collaborateur/(create\|store\|edit\|update\|delete)` | CRUD |

## Sécurité

- **Prepared statements** PDO partout, jamais d'interpolation directe.
- **CSRF** : jeton en session, rotation après chaque action POST validée.
- **Échappement** systématique `htmlspecialchars` dans les vues.
- **Headers** : `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: same-origin`.
- **CHECK constraint** SQL : `pourcentageInvestissement BETWEEN 0 AND 100`.

## Points pédagogiques abordés (UE 6.1)

- PDO avec singleton et options recommandées (exception, fetch assoc, no emulation).
- Relations 1-N (priorité, statut, catégorie → tâche) avec FK et contraintes.
- Relation N-M avec attribut (% d'investissement, date d'affectation).
- Transaction PDO pour la synchronisation des affectations.
- Front controller + autoload PSR-4-light, sans framework lourd.
- MVC : contrôleurs maigres, modèles statiques (compromis simplicité/SP).
- Form processing avec validation, anti-CSRF, redirect-after-POST.

## Roadmap (post-rendu)

- Tests unitaires PHPUnit sur les modèles.
- Migration vers POPO (objets de domaine) si M. Christoffel demande.
- API JSON pour intégration future avec front React (`todo-JSON-Responsive-LocalStorage-`).
- Containerisation Docker (`docker-compose.yml` avec PHP-Apache + MariaDB).
