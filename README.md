# Amigo — API backend

Amigo est le backend REST de l’application TripVibe. Il est développé avec Laravel et expose une API versionnée sous `/api/v1`, consommée notamment par l’application mobile du dépôt `amigoApp`.

## Fonctionnalités

L’API regroupe les fonctionnalités suivantes :

- comptes, profils, vérification d’adresse e-mail, réinitialisation du mot de passe et authentification à deux facteurs ;
- voyages, membres, invitations, propositions de destination, sondages et réactions ;
- lieux, activités, participants, points de rendez-vous, rappels, checklist de préparation et suggestion d’ordre de visite ;
- budgets, lignes de budget, contributions, dépenses, participants aux dépenses, règlements et taux de change ;
- notifications, préférences de notification et jetons d’appareil (FCM) ;
- photos, commentaires et carnet de voyage, partage de position, alertes d’urgence ;
- abonnements, offres, partenaires, services, réservations et recommandations ;
- signalements, modération et file de synchronisation hors ligne.

Les routes métier sont protégées par authentification. Les identifiants de ressources exposés dans ces routes sont des UUID. Les voyages sont privés par défaut ; leurs organisateurs peuvent les rendre publics avec le champ `visibility`, puis les proposer dans `GET /api/v1/discover/trips`. Le carnet est accessible via `trip-journal-entries`. La suggestion d’itinéraire est disponible sur `GET /api/v1/trips/{trip}/route-plan` ; elle utilise une heuristique de voisin le plus proche et des distances à vol d’oiseau à partir des coordonnées des lieux.

## Technologies

- PHP 8.3 ou plus récent
- Laravel 13
- SQLite par défaut (configurable via les variables Laravel `DB_*`)
- Laravel Sanctum et JWT pour l’authentification
- Scramble pour la documentation OpenAPI
- Firebase Cloud Messaging pour les notifications push

## Installation locale

Depuis le dossier `amigo` :

```bash
composer install
cp .env.example .env
php artisan key:generate
```

L’exemple de configuration utilise SQLite. Crée le fichier de base de données s’il n’existe pas, puis applique les migrations :

```bash
touch database/database.sqlite
php artisan migrate
```

Configure ensuite les secrets et services nécessaires dans `.env`. En particulier, définis un secret JWT avec :

```bash
php artisan jwt:secret
```

Pour le développement local, le courrier est journalisé par défaut (`MAIL_MAILER=log`). Les identifiants Firebase ne sont nécessaires que pour activer les notifications push ; le chemin d’exemple est `storage/app/firebase-auth.json`. Ne commite pas de secrets ni de fichiers d’identifiants.

Démarre le serveur :

```bash
php artisan serve
```

L’API sera alors disponible à `http://127.0.0.1:8000/api/v1`.

## Documentation de l’API

La documentation interactive Scramble est disponible à :

```
http://127.0.0.1:8000/docs/api
```

Le document OpenAPI est exporté à `/api.json`. Les routes documentées sont celles de `api/v1`.

Pour importer les opérations dans Postman, consulter [POSTMAN.md](POSTMAN.md). Pour le diagramme de cas d’utilisation, le parcours d’intégration recommandé et les critères de validation, consulter [BACKEND_USE_CASES.md](BACKEND_USE_CASES.md).

## Authentification et requêtes

Les routes publiques d’authentification comprennent l’inscription, la connexion, le renouvellement de jeton, la vérification d’adresse e-mail, la récupération du mot de passe et la vérification du second facteur.

Après connexion, envoie le jeton d’accès dans l’en-tête HTTP :

```http
Authorization: Bearer <jeton>
Accept: application/json
```

Exemple d’appel à la route du profil connecté :

```bash
curl http://127.0.0.1:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <jeton>"
```

Les routes d’inscription et de connexion sont limitées à cinq requêtes par minute par client. Les autres limites sont définies par route dans `routes/api.php`.

## Organisation du code

- `app/Http/Controllers/Api/V1` : contrôleurs de l’API version 1
- `app/Http/Requests` : validation des requêtes
- `app/Http/Resources` : formatage des réponses API
- `app/Models` : modèles Eloquent
- `app/Services` : logique métier et intégrations
- `routes/api.php` : routes versionnées
- `database/migrations` : schéma de la base de données

## Tests

Lancer la suite de tests :

```bash
php artisan test
```

Ou utiliser le script Composer :

```bash
composer test
```
