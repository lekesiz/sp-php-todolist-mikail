# SP PHP To Do List — Étape 2 (Développement)

Application PHP procédurale-orientée-objet (PDO, MVC) de gestion de tâches avec affectation
à plusieurs collaborateurs et pondération par % d'investissement, conforme au retour
de M. Christoffel (22/05/2026) :

> *« le dév App PHP est maintenant un travail individuel. »*

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
├── config/config.php          # paramètres DB et app
├── sql/install.sql            # schéma v2 validé + jeux de tests
└── README.md
```

## Installation locale (MAMP / XAMPP / WAMP)

1. **Cloner ou copier** le dossier `todoList-app/` dans la docroot du serveur web local (ou créer un VirtualHost pointant sur `public/`).
2. **Importer la base** :
   ```bash
   mysql -u root -p < sql/install.sql
   ```
   ou via phpMyAdmin : *Importer* → `sql/install.sql`.
3. **Adapter** `config/config.php` (utilisateur/mot de passe DB).
4. **Accéder à l'application** :
   - Si la doc-root est `todoList-app/public/` : `http://localhost/`
   - Sinon : `http://localhost/todoList-app/public/` et ajuster `app.base_url` en conséquence.

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
