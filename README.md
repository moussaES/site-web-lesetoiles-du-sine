# 🏠 ImmoAgence — Site Web Immobilier Dynamique

Site web complet pour agence immobilière avec interface **client** et panneau **admin**.
Stack : PHP 8+ | MySQL | Bootstrap 5 | JavaScript Vanilla

---

## 📁 Structure du projet

```
immo/
├── index.php                  # Page d'accueil
├── database.sql               # Script SQL complet
├── .htaccess                  # Sécurité & performance
├── includes/
│   ├── config.php             # Config BDD + fonctions globales
│   └── upload.php             # Gestion upload photos
├── assets/
│   ├── css/style.css          # Styles principaux
│   ├── js/main.js             # JavaScript principal
│   └── images/no-photo.jpg   # Image par défaut (à ajouter)
├── uploads/
│   └── biens/                 # Photos des biens (auto-créé)
├── client/
│   ├── login.php              # Connexion client/admin
│   ├── logout.php
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── pages/
│   │   ├── catalogue.php      # Liste des biens + filtres
│   │   ├── bien.php           # Détail d'un bien
│   │   ├── register.php       # Inscription
│   │   ├── dashboard.php      # Espace client (favoris, demandes)
│   │   └── contact.php
│   └── ajax/
│       └── favori.php         # Toggle favori (AJAX)
└── admin/
    ├── login.php              # Connexion admin
    ├── dashboard.php          # Tableau de bord
    ├── includes/
    │   ├── header.php
    │   └── footer.php
    ├── pages/
    │   ├── biens.php          # Liste + gestion biens
    │   ├── ajouter_bien.php   # Formulaire ajout
    │   ├── modifier_bien.php  # Formulaire modification
    │   ├── demandes.php       # Gestion demandes
    │   └── utilisateurs.php   # Gestion clients
    └── ajax/
        └── update_statut.php  # Changement statut AJAX
```

---

## ⚙️ Installation

### 1. Prérequis
- PHP 8.0+
- MySQL 8.0+
- Apache ou Nginx
- Extension PHP : PDO, PDO_MySQL, fileinfo, GD

### 2. Base de données
```sql
-- Dans phpMyAdmin ou ligne de commande :
mysql -u root -p < database.sql
```

> **Nouveauté** :
> 1. La table `utilisateurs` prend désormais en charge le rôle `gestionnaire`.
>    Si vous aviez importé la base de données avant la mise à jour, exécutez :
>    ```sql
>    ALTER TABLE utilisateurs MODIFY role ENUM('client','gestionnaire','admin')
>        DEFAULT 'client';
>    ```
> 2. La table `demandes` inclut maintenant une colonne `admin_response` pour
>    stocker les réponses internes des commerciaux. Pour l'ajouter à une base
>    existante, lancez :
>    ```sql
>    ALTER TABLE demandes ADD COLUMN admin_response TEXT DEFAULT NULL;
>    ```


### 3. Configuration
Ouvrir `includes/config.php` et modifier :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'agence_immo');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
define('SITE_URL', 'http://localhost/immo');
```

### 4. Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/biens/
```

### 5. Image par défaut
Placer une image `no-photo.jpg` dans `assets/images/`

---

## 🔐 Comptes par défaut

| Rôle        | Email               | Mot de passe |
|-------------|---------------------|--------------|
| Admin       | admin@agence.com    | Admin@1234   |
| Gestionnaire| (à créer via interface admin) | (au choix) |

**⚠️ Changer le mot de passe admin immédiatement en production !**

---

### Gestionnaires
Les gestionnaires sont des utilisateurs créés par l'admin. Ils peuvent ajouter/modifier des biens
et traiter des demandes, mais n'ont pas accès à la gestion des comptes utilisateurs. Il ne reste
visible que la section "Utilisateurs" pour les administrateurs.
**⚠️ Changer le mot de passe admin immédiatement en production !**

---

## 🌟 Fonctionnalités

### Interface Client
- ✅ Page d'accueil avec hero + recherche rapide
- ✅ Catalogue avec filtres (type, transaction, ville, prix, superficie)
- ✅ Pagination dynamique
- ✅ Page détail avec galerie photos + biens similaires
- ✅ Formulaire de demande de contact/visite
- ✅ Inscription & connexion sécurisée
- ✅ Espace client : favoris + historique des demandes

### Interface Admin
- ✅ Tableau de bord avec statistiques
- ✅ Gestion des biens (CRUD complet + 10 photos)
- ✅ Upload photos avec preview
- ✅ Changement de statut en temps réel (AJAX)
- ✅ Gestion des demandes avec filtres
- ✅ Gestion des utilisateurs (activer/désactiver/supprimer)
- ✅ Sidebar responsive

---

## 🔒 Sécurité incluse
- Protection CSRF sur tous les formulaires
- Requêtes préparées PDO (anti-injection SQL)
- Hachage bcrypt des mots de passe
- Validation type MIME réel des fichiers uploadés
- Renommage aléatoire des fichiers
- Vérification de rôle sur toutes les pages admin

---

## 🎨 Personnalisation
Modifier les variables CSS dans `assets/css/style.css` :
```css
:root {
  --primary:  #1A3C6E;   /* Couleur principale */
  --gold:     #C9A84C;   /* Couleur accent */
  --dark:     #0F1F38;   /* Couleur sombre */
}
```
