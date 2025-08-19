# 📋 Application de Facturation Laravel

## 📖 Description
Application de gestion de facturation développée avec Laravel 11, offrant une API RESTful complète pour la gestion des clients, des factures et l'authentification des utilisateurs.

### ✨ Fonctionnalités principales

#### 👥 Gestion des Clients
- Création de nouveaux clients avec informations complètes
- Consultation de la liste des clients
- Affichage détaillé des informations client
- Recherche et filtrage des clients
- Validation des données client (email, téléphone, etc.)

#### 📄 Gestion des Factures
- Création de factures avec lignes détaillées
- Calcul automatique des montants (HT, TVA, TTC)
- Différents taux de TVA supportés (0%, 5.5%, 10%, 20%)
- Export des factures en format JSON
- Recherche de factures par client ou date
- Validation des montants et calculs automatiques
- Gestion des lignes de facture avec quantités et prix unitaires

#### 🔐 Système d'Authentification
- Inscription des utilisateurs
- Connexion sécurisée
- Authentification par token (Laravel Sanctum)
- Gestion des sessions
- Protection des routes sensibles

#### 🔍 Fonctionnalités de Recherche
- Recherche avancée de factures
- Filtrage par date
- Filtrage par client
- Tri des résultats

Cette application est un module de facturation développé avec Laravel, permettant la gestion des clients et des factures avec calcul automatique des montants et export au format JSON.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et NPM
- SQLite ou PostgreSQL
- Git

## Installation

1. **Cloner le dépôt**
```bash
git clone https://github.com/amine-dev1/Facturation-App.git
cd Facturation-App
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**

Ouvrez le fichier `.env` et configurez votre base de données :

Pour SQLite :
```
DB_CONNECTION=sqlite
# Commentez ou supprimez les autres lignes DB_*
```
Puis créez le fichier de base de données :
```bash
touch database/database.sqlite
```

Pour PostgreSQL :
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=facturation
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

5. **Exécuter les migrations**
```bash
php artisan migrate:fresh
```

6. **Installer les dépendances JavaScript (optionnel pour l'interface)**
```bash
npm install
npm run build
```

7. **Démarrer le serveur**
```bash
php artisan serve
```

L'application sera accessible à l'adresse : http://localhost:8000

## Structure de l'API

### Authentication

- **POST** `/api/register` : Création d'un compte
- **POST** `/api/login` : Connexion et récupération du token

### Clients

- **GET** `/api/clients` : Liste des clients
- **POST** `/api/clients` : Création d'un client
- **GET** `/api/clients/{id}` : Détails d'un client

### Factures

- **GET** `/api/factures` : Liste des factures
- **POST** `/api/factures` : Création d'une facture
- **GET** `/api/factures/{id}` : Détails d'une facture
- **GET** `/api/factures/{id}/export` : Export JSON d'une facture
- **GET** `/api/factures/search` : Recherche de factures

## Authentification

L'API utilise Laravel Sanctum pour l'authentification. Pour accéder aux endpoints protégés :

1. Créez un compte via `/api/register`
2. Connectez-vous via `/api/login` pour obtenir un token
3. Utilisez ce token dans le header Authorization : `Bearer {votre_token}`

## Format des requêtes

### Création d'un client
```json
{
    "nom": "Nom du client",
    "email": "client@example.com",
    "siret": "12345678901234",
    "date_creation": "2025-08-19"
}
```

### Création d'une facture
```json
{
    "client_id": 1,
    "date": "2025-08-19",
    "lignes": [
        {
            "description": "Prestation 1",
            "quantite": 2,
            "prix_unitaire_ht": 100,
            "taux_tva": 20
        }
    ]
}
```

## Tests

Pour exécuter les tests :
```bash
php artisan test
```

Les tests couvrent :
- La création de clients
- La création et le calcul des factures
- L'export des factures
- La recherche de factures

## Règles de gestion

- Une facture doit avoir au moins une ligne
- Les taux de TVA autorisés sont : 0%, 5.5%, 10% et 20%
- Tous les champs sont obligatoires
- Les calculs de TVA et totaux sont automatiques

## Contribution

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## License

Distribué sous la licence MIT. Voir `LICENSE` pour plus d'informations.
