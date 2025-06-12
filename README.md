# Projet de Location de Vélos

## Installation

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd bike-rental
```

2. Installer les dépendances avec Composer :
```bash
composer install
```

3. Créer la base de données :
- Ouvrir phpMyAdmin ou votre client MySQL
- Importer le fichier `database.sql`

4. Configurer la base de données :
- Copier `src/config/database.php` en `src/config/database.local.php`
- Modifier les paramètres de connexion selon votre environnement

## Tester l'API

Vous pouvez utiliser Postman ou cURL pour tester les endpoints. Voici quelques exemples :

### 1. Gestion des Vélos

#### Lister tous les vélos
```bash
curl http://localhost/bike-rental/public/api/bikes
```

#### Ajouter un vélo
```bash
curl -X POST http://localhost/bike-rental/public/api/bikes \
  -H "Content-Type: application/json" \
  -d '{
    "type": "electric",
    "status": "available",
    "description": "Vélo électrique en bon état",
    "hourly_rate": 15.00
  }'
```

### 2. Gestion des Clients

#### Lister tous les clients
```bash
curl http://localhost/bike-rental/public/api/customers
```

#### Ajouter un client
```bash
curl -X POST http://localhost/bike-rental/public/api/customers \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "0123456789",
    "subscription_type": "monthly",
    "address": "123 Rue Example"
  }'
```

### 3. Gestion des Locations

#### Lister toutes les locations
```bash
curl http://localhost/bike-rental/public/api/rentals
```

#### Créer une location
```bash
curl -X POST http://localhost/bike-rental/public/api/rentals \
  -H "Content-Type: application/json" \
  -d '{
    "bike_id": 1,
    "customer_id": 1,
    "start_time": "2024-03-20 10:00:00",
    "planned_end_time": "2024-03-20 12:00:00"
  }'
```

### 4. Gestion des Réservations

#### Lister toutes les réservations
```bash
curl http://localhost/bike-rental/public/api/reservations
```

#### Créer une réservation
```bash
curl -X POST http://localhost/bike-rental/public/api/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "bike_id": 1,
    "customer_id": 1,
    "start_time": "2024-03-21 14:00:00",
    "end_time": "2024-03-21 16:00:00"
  }'
```

## Structure du Projet

```
bike-rental/
├── public/
│   ├── index.php
│   └── .htaccess
├── src/
│   ├── config/
│   │   └── database.php
│   ├── Controllers/
│   │   ├── BikeController.php
│   │   ├── CustomerController.php
│   │   ├── RentalController.php
│   │   └── ReservationController.php
│   ├── Models/
│   │   ├── Bike.php
│   │   ├── Customer.php
│   │   ├── Rental.php
│   │   └── Reservation.php
│   ├── Repositories/
│   │   ├── BikeRepository.php
│   │   ├── CustomerRepository.php
│   │   ├── RentalRepository.php
│   │   └── ReservationRepository.php
│   └── Database.php
├── database.sql
├── composer.json
└── README.md
```

## Fonctionnalités Implémentées

- ✅ Gestion des vélos (CRUD)
- ✅ Gestion des clients (CRUD)
- ✅ Gestion des locations (CRUD)
- ✅ Gestion des réservations (CRUD)
- ✅ Calcul automatique des frais de location
- ✅ Gestion des statuts des vélos
- ✅ Validation des données
- ✅ Gestion des erreurs 