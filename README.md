# MiniBiblio

Application web de gestion des emprunts d'une bibliothèque.

## Technologies choisies

| Catégorie | Technologie |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue.js 3 (SPA avec Vite) |
| Base de données | MySQL |
| CSS | Tailwind CSS v4 |

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Philneris/minibliblio.git
cd minibliblio
```

### 2. Backend (Laravel)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configurer la base de données dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minibiblio
DB_USERNAME=root
DB_PASSWORD=
```

Créer la base de données MySQL :

```sql
CREATE DATABASE minibiblio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Exécuter les migrations et le seeder :

```bash
php artisan migrate
php artisan db:seed
```

### 3. Frontend (Vue.js)

```bash
cd frontend
npm install
```

## Lancer l'application

### Backend (terminal 1)

```bash
php artisan serve
```

### Frontend (terminal 2)

```bash
cd frontend
npm run dev
```

L'application est accessible sur **http://localhost:5173**.


## Ce qui a été réalisé

- **CRUD Livres** : création, consultation, modification, suppression
- **CRUD Membres** : création, consultation, modification, suppression
- **Emprunts** : emprunter un livre, retourner un livre, liste des emprunts en cours(pour les cas de retardataires le code en place ne fonctionne pas lorsqu'on teste)
- **Règles métier** (toutes implémentées côté serveur) :
  - R1 : Max 3 emprunts en cours par membre
  - R2 : Pas d'emprunt si aucun exemplaire disponible
  - R3 : Pas d'emprunt si le membre a un retard
  - R4 : Pas de suppression d'un livre avec emprunts en cours
- **Filtre par membre** sur la liste des emprunts
- **Validation** côté serveur avec messages d'erreur clairs
- **Seeders** : données de test réalistes (8 livres, 5 membres)
- **SPA séparée** : frontend Vue.js consommant l'API REST

## Ce qui n'a pas été fait

- **Tests automatisés** : aucun test n'a été écrit.

## Choix techniques importants

- **Séparation de la logique métier** : un `LoanService` encapsule toutes les règles métier (R1-R4), séparé des controllers HTTP et du frontend. Cela permet de tester la logique indépendamment.
- **API REST** : routes RESTful avec codes HTTP appropriés (200, 201, 204, 422, 404, 409).
- **Frontend SPA séparé** : Vue.js dans un dossier `frontend/` avec son propre `package.json`, communique avec le backend via un proxy Vite.
- **Factories et seeders** : pour faciliter les tests et le développement.
- **Contraintes en base** : clés étrangères avec `onDelete('restrict')`, unicité sur ISBN et email.
