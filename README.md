# 📋 Application de Facturation Laravel 11

**Une API RESTful développée avec Laravel 11 pour gérer clients, factures et authentification sécurisée via Laravel Sanctum.**

---

## ✨ Fonctionnalités principales

- **👥 Gestion des clients** : Création, consultation, recherche et validation des données (email, téléphone, SIRET).
- **📄 Gestion des factures** : Création avec lignes détaillées, calcul automatique (HT, TVA, TTC), export JSON, recherche par client/date.
- **🔐 Authentification : Inscription, connexion, déconnexion, tokens via Laravel Sanctum, protection des routes.
- **🔍 Recherche avancée** : Filtrage des factures par client, date, tri des résultats.

---

## 🛠 Prérequis

- PHP 8.2 ou supérieur
- Composer
- PostgreSQL (j'ai travailler avec la version 17)
- Git
---

## 🚀 Installation rapide

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/amine-dev1/Facturation-App.git
   cd Facturation-App
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l’environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurer la base de données**

   **PostgreSQL** :
   Dans `.env` :
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

6. **Démarrer le serveur**
   ```bash
   php artisan serve
   ```
   Accès : [http://localhost:8000](http://localhost:8000)

---

## 📂 Structure du projet

```
Facturation-App/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ClientController.php
│   │   │   ├── Controller.php
│   │   │   ├── FactureController.php
│   │   │   └── UserController.php
│   └── Models/
│       ├── Client.php
│       ├── Facture.php
│       ├── FactureLine.php
│       └── User.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_08_19_04726_create_personal_access_tokens_table.php
│   │   ├── 2025_08_19_055143_create_clients_table.php
│   │   ├── 2025_08_19_055155_create_factures_table.php
│   │   └── 2025_08_19_055724_create_facture_lines_table.php
│   ├── seeders/
│   └── .gitignore
├── node_modules/
├── public/
├── resources/
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage/
└── vendor/
```
⚙️ Configuration de Laravel Sanctum
Laravel Sanctum est utilisé pour l'authentification par token. Suivez ces étapes pour le configurer correctement :

Vérifier les dépendances

Assurez-vous que le package laravel/sanctum est installé via Composer (`composer require laravel/sanctum`).


Publier les configurations

Exécutez la commande suivante pour publier les fichiers de configuration de Sanctum :
`--php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`



Mettre à jour .env

Ajoutez ou vérifiez la variable suivante :
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DRIVER=cookie
SESSION_LIFETIME=120 
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax


## 🧩 Structure de l’API

### 🔑 Authentification
- `POST /api/register` : Créer un compte → retourne un **token**
- `POST /api/login` : Connexion → retourne un **token**
- `POST /api/logout` : Déconnexion (invalide le token)

### 👥 Clients
- `GET /api/clients` : Liste des clients
- `POST /api/clients/storeClient` : Créer un client
- `GET /api/clients/getclient/{clientid}` : Détails d’un client

### 📄 Factures
- `POST /api/factures/createfacture` : Créer une facture
- `GET /api/factures/listeFactures` : Liste des factures
- `GET /api/factures/search` : Recherche (client, date)
- `GET /api/factures/{factureid}/export` : Export JSON
- `GET /api/factures/{factureid}` : Détails d’une facture

---

## 🔐 Tester l’API avec Postman

Postman est un outil puissant pour tester les API. Voici un guide détaillé pour configurer et tester ton API étape par étape :

### 1️⃣ Installer Postman
- Télécharge et installe Postman depuis : [https://www.postman.com/downloads/](https://www.postman.com/downloads/)
- Ouvre Postman une fois installé.

### 2️⃣ Importer la collection
- Clique sur l’icône **Import** (en haut à gauche).
- Sélectionne **Link** et colle l’URL de la collection :  
  `(https://web.postman.co/workspace/0ded4289-c089-4f17-8a29-4cc743591aaf/collection/38806485-c9885b7c-b46b-49a0-8203-22855fe80f7f?action=share&source=copy-link&creator=38806485)`

  ou vous le trouver sous format de json dans le dossier ici il est nomme `collectionFacture.json`
- Valide pour importer la collection. Tu verras une nouvelle collection avec des dossiers comme "Auth", "Clients", et "Factures" correspondant aux endpoints de ton API.

### 3️⃣ Configurer un environnement
- Clique sur l’icône **Environments** (en haut à droite) et sélectionne **Create Environment**.
- Nomme ton environnement (ex. : `FacturationEnv`).
- Ajoute les variables suivantes :
  - `base_url` : `http://localhost:8000/api`
  - `token` : Laisse vide pour l’instant (ce champ sera rempli après l’authentification).
- Clique sur **Save** et sélectionne cet environnement dans le menu déroulant en haut à droite.

### 4️⃣ Générer un token d’authentification
- **Étape 1 : Créer un compte**
  - Ouvre la requête `POST {{base_url}}/register` dans la collection "Auth".
  - Dans l’onglet **Body**, sélectionne **raw** et **JSON**, puis entre les données suivantes :
    ```json
    {
        "name": "Test User",
        "email": "test@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }
    ```
  - Clique sur **Send**. Si tout est correct, tu recevras une réponse avec un **token** dans le corps de la réponse (ex. : `"token": "votre-token-ici"`).
- **Étape 2 : Ou se connecter**
  - Ouvre la requête `POST {{base_url}}/login` dans la collection "Auth".
  - Dans l’onglet **Body**, entre :
    ```json
    {
        "email": "test@example.com",
        "password": "password123"
    }
    ```
  - Clique sur **Send**. Tu recevras également un **token**.
- **Étape 3 : Stocker le token**
  - Copie le **token** retourné (par exemple, `votre-token-ici`).
  - Va dans l’onglet **Environments**, trouve la variable `token`, colle le token et clique sur **Save**.

### 5️⃣ Tester les endpoints protégés
- Sélectionne une requête protégée, comme `GET {{base_url}}/clients` ou `POST {{base_url}}/factures/createfacture`.
- Vérifie que l’environnement est actif ({{token}} doit être utilisé automatiquement).
- Pour les requêtes nécessitant des données (ex. : création d’un client ou d’une facture), utilise l’onglet **Body** avec le format JSON approprié :
  - **Créer un client** :
    ```json
    {
        "nom": "Nom du client",
        "email": "client@example.com",
        "siret": "12345678901234",
        "date_creation": "2025-08-19"
    }
    ```
  - **Créer une facture** :
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
- Clique sur **Send** pour exécuter la requête. Vérifie la réponse pour confirmer que l’API fonctionne (ex. : statut 200 OK ou 201 Created).

### 6️⃣ Explorer et tester davantage
- Teste les autres endpoints comme la recherche (`GET /api/factures/search`) ou l’export JSON (`GET /api/factures/{factureid}/export`).
- Si une erreur survient (ex. : 401 Unauthorized), vérifie que le token est valide et correctement configuré.

---

## 📝 Exemples de requêtes

**Créer un client**  
```json
{
    "nom": "Nom du client",
    "email": "client@example.com",
    "siret": "12345678901234",
    "date_creation": "2025-08-19"
}
```

**Créer une facture**  
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

---

## 📌 Règles de gestion

- Une facture doit avoir **au moins une ligne**.
- Taux de TVA autorisés : **0%, 5.5%, 10%, 20%**.
- Tous les champs sont **obligatoires**.
- Calculs automatiques : **HT, TVA, TTC**.
---

merci pour l'opportunité 
