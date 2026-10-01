# API Amigo : guide complet Postman

Ce guide explique comment importer et tester l’ensemble de l’API Laravel. L’OpenAPI généré décrit **125 chemins et 258 opérations HTTP**; le parcours de sortie complet est donné comme exemple pratique. Pour l’installation initiale, suivre le [README backend](README.md).

## 1. Démarrer l’API

Dans un terminal, depuis le dossier `amigo` :

```bash
php artisan migrate
php artisan serve
```

L’API locale est disponible sur `http://127.0.0.1:8000/api/v1`.

### Préparer l’envoi du code de vérification

Les notifications de vérification sont mises en file. Pour un test local simple, configure `.env` ainsi :

```dotenv
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

Puis vide la configuration en cache et relance le serveur :

```bash
php artisan config:clear
```

Le code envoyé par e-mail sera écrit dans `storage/logs/laravel.log`. Si tu gardes `QUEUE_CONNECTION=database`, laisse plutôt tourner `php artisan queue:work` dans un second terminal. Ne partage pas le code OTP ni le jeton Bearer.

## 2. Créer un environnement Postman

Dans Postman, crée un environnement avec ces variables :

| Variable | Valeur initiale |
| --- | --- |
| `base_url` | `http://127.0.0.1:8000/api/v1` |
| `email` | une adresse de test unique |
| `token` | vide |
| `outing_id` | vide |
| `photo_id` | vide |

Pour les requêtes authentifiées, choisis **Authorization > Bearer Token** et saisis `{{token}}`. Ajoute aussi l’en-tête `Accept: application/json`. Pour les corps JSON, choisis **Body > raw > JSON**.

## Importer toutes les routes dans Postman

L’import OpenAPI est le moyen le plus rapide d’obtenir une requête Postman pour chaque opération, avec les paramètres et schémas de corps documentés :

1. Depuis `amigo`, régénère le fichier si le backend a changé : `php artisan scramble:export`.
2. Dans Postman, choisis **Import > File** et sélectionne `amigo/api.json`.
3. Importe le document comme collection OpenAPI. Il utilise par défaut `http://localhost:8000/api/v1`; modifie cette URL si le serveur est lancé sur un autre hôte ou port.
4. Dans la collection, configure **Authorization > Bearer Token** avec `{{token}}`. Les routes d’inscription, de connexion et de vérification de code sont publiques : sélectionne **No Auth** pour ces requêtes si Postman leur hérite le Bearer de la collection.
5. Dans l’onglet **Body** de chaque requête, Postman affiche le schéma OpenAPI. Remplis les champs requis indiqués par le schéma. Remplace chaque paramètre de chemin, par exemple `{id}`, par l’UUID renvoyé par une requête précédente.

Le fichier [api.json](api.json) reste la référence exacte pour les types, champs requis, paramètres de recherche, réponses et codes HTTP. Après une modification du backend, relance l’export et réimporte le fichier pour mettre la collection à jour.

## 3. Obtenir un jeton

### Option A : créer et vérifier un compte

Envoie `POST {{base_url}}/auth/register` :

```json
{
  "first_name": "Awa",
  "last_name": "Test",
  "email": "{{email}}",
  "password": "ChangeMe123!",
  "password_confirmation": "ChangeMe123!"
}
```

La réponse `201` confirme la création, mais ne contient pas encore de jeton. Copie le code à 6 chiffres depuis `storage/logs/laravel.log`, puis envoie `POST {{base_url}}/auth/email/verification/verify` :

```json
{
  "email": "{{email}}",
  "code": "123456"
}
```

Remplace `123456` par le code du journal. La vérification retourne le jeton à la racine de la réponse, sous `access_token`. Dans l’onglet **Scripts > Post-response** de cette requête, tu peux l’enregistrer automatiquement :

```javascript
pm.environment.set("token", pm.response.json().access_token);
```

### Option B : utiliser un compte déjà vérifié

Envoie `POST {{base_url}}/auth/login` avec :

```json
{
  "email": "{{email}}",
  "password": "ChangeMe123!"
}
```

Enregistre `access_token` avec le même script Post-response. Un compte non vérifié ne peut pas se connecter; utilise l’option A pour un nouveau compte.

## 4. Tester une sortie de bout en bout

Envoie les requêtes dans cet ordre avec le jeton enregistré :

1. **Lister les sorties** — `GET {{base_url}}/outings`
2. **Créer une sortie** — `POST {{base_url}}/outings`, avec ce corps :

```json
{
  "title": "Sortie de test",
  "place": "Place de l’Étoile, Cotonou",
  "location_type": "public",
  "category": "Restaurant",
  "date_label": "Demain",
  "time_label": "19:00",
  "note": "Rendez-vous à l’entrée principale.",
  "activity": "Dîner entre amis",
  "budget_target": 50000,
  "guests": ["Ami de test"],
  "latitude": 6.3702,
  "longitude": 2.4251
}
```

   La réponse contient l’identifiant de la sortie dans `data.id`. Enregistre-le dans l’onglet Post-response :

```javascript
pm.environment.set("outing_id", pm.response.json().data.id);
```

3. **Lire le détail** — `GET {{base_url}}/outings/{{outing_id}}`
4. **Confirmer sa présence** — `POST {{base_url}}/outings/{{outing_id}}/rsvp`, corps JSON `{ "attending": true }`.
5. **Confirmer son arrivée** — `POST {{base_url}}/outings/{{outing_id}}/check-in`.
6. **Lancer la sortie** — `POST {{base_url}}/outings/{{outing_id}}/start`.
7. **Ajouter une cotisation** — `POST {{base_url}}/outings/{{outing_id}}/contributions`, corps JSON `{ "amount": 5000 }`.
8. **Ajouter une photo** — `POST {{base_url}}/outings/{{outing_id}}/photos`; sélectionne **Body > form-data**, ajoute `image` de type **File** et sélectionne `caption` de type **Text**. Après l’envoi, enregistre `data.id` sous `photo_id`.
9. **Partager la photo dans la Story** — `PATCH {{base_url}}/outings/{{outing_id}}/photos/{{photo_id}}/story`, sans corps. Cette route inverse l’état actuel de partage.
10. **Terminer la sortie** — `POST {{base_url}}/outings/{{outing_id}}/finish`.

Tu peux relire `GET {{base_url}}/outings/{{outing_id}}` après chaque action pour vérifier les changements. L’organisateur qui a créé la sortie doit utiliser le même jeton pour la modifier, la lancer et la terminer. Les identifiants d’utilisateurs utilisés dans `participant_user_ids` doivent être des UUID de comptes existants; les valeurs dans `guests` sont seulement des noms et n’envoient pas d’invitation.

## Catalogue complet des routes

Toutes les routes métier ci-dessous sont sous `{{base_url}}`, donc `http://127.0.0.1:8000/api/v1` dans l’environnement local. Elles exigent un Bearer JWT sauf les routes publiques d’authentification précisées plus bas. Les routes de ressources utilisent des identifiants UUID.

### Convention des ressources CRUD

Chaque ressource de la table expose les cinq opérations suivantes, sauf indication contraire dans la table ou dans `api.json` :

| Méthode et chemin | Utilisation dans Postman |
| --- | --- |
| `GET /{ressource}` | Lister les enregistrements visibles par le compte; les listes peuvent être paginées. |
| `POST /{ressource}` | Créer un enregistrement. Mets dans **Body > raw > JSON** les champs requis indiqués par le schéma OpenAPI. |
| `GET /{ressource}/{id}` | Lire le détail d’un enregistrement à partir de son UUID. |
| `PUT /{ressource}/{id}` | Modifier les champs autorisés de cet enregistrement. |
| `DELETE /{ressource}/{id}` | Supprimer l’enregistrement, si le compte connecté en a le droit. |

Par exemple, pour une activité : `GET {{base_url}}/activities`, `POST {{base_url}}/activities`, `GET {{base_url}}/activities/{{id}}`, `PUT {{base_url}}/activities/{{id}}` et `DELETE {{base_url}}/activities/{{id}}`. La collection importée fournit les URL exactes et le schéma adapté à chaque ressource.

### Comptes, authentification et profil

Les routes publiques ne demandent pas de jeton; les autres utilisent le Bearer enregistré dans `token`.

| Méthode et route | Utilité |
| --- | --- |
| `POST /auth/register` | Créer un compte. La réponse indique qu’une vérification e-mail est requise. |
| `POST /auth/login` | Connecter un compte déjà vérifié et obtenir `access_token`. |
| `POST /auth/refresh` | Renouveler le JWT avant son expiration. |
| `POST /auth/email/verification/send` | Renvoyer un code de vérification e-mail. |
| `POST /auth/email/verification/verify` | Valider le code OTP et activer le compte; retourne un jeton. |
| `POST /auth/password/forgot` | Demander un code de réinitialisation de mot de passe. |
| `POST /auth/password/reset` | Remplacer le mot de passe avec le code reçu. |
| `POST /auth/two-factor/verify` | Valider le code 2FA reçu pendant une connexion. |
| `GET /me` | Lire le profil du compte authentifié. |
| `PATCH /me` | Modifier les champs de profil autorisés. Utilise les noms snake_case, par exemple `first_name` et `last_name`. |
| `PUT /me/email` | Demander le changement d’adresse e-mail; une nouvelle vérification est requise. |
| `POST /me/avatar` | Envoyer un avatar en multipart. |
| `DELETE /me/avatar` | Retirer l’avatar du compte. |
| `DELETE /auth/logout` | Invalider le jeton courant. |
| `PUT /auth/password` | Changer le mot de passe depuis une session authentifiée. |
| `POST /auth/two-factor/enable` | Démarrer l’activation de la double authentification. |
| `POST /auth/two-factor/enable/verify` | Confirmer l’activation avec le code reçu. |
| `DELETE /auth/two-factor` | Désactiver la double authentification. |
| `GET /me/sessions` | Lister les sessions du compte. |
| `POST /me/sessions/revoke-others` | Révoquer les autres sessions sans fermer celle qui effectue l’appel. |

### Voyages, activités et préparation

Les routes de ressources suivantes suivent la convention CRUD ci-dessus; elles servent à organiser le voyage, ses participants et ses informations :

| Ressource | À quoi elle sert |
| --- | --- |
| `/trips` | Créer et gérer un voyage. |
| `/trip-members` | Gérer les membres d’un voyage. |
| `/trip-places` | Associer des lieux à un voyage. |
| `/trip-journal-entries` | Gérer les entrées du carnet de voyage. |
| `/activities` | Gérer les activités prévues. |
| `/activity-attendees` | Gérer les participants à une activité. |
| `/meeting-points` | Gérer les points de rendez-vous. |
| `/packing-items` | Gérer les éléments de la checklist de préparation. |
| `/budgets` | Gérer les budgets de voyage. |
| `/budget-lines` | Gérer les lignes d’un budget. |
| `/contributions` | Gérer les cotisations liées au budget. |
| `/expenses` | Gérer les dépenses partagées. |
| `/expense-participants` | Associer des participants aux dépenses. |
| `/settlements` | Gérer les remboursements et soldes entre participants. |
| `/reminders` | Gérer les rappels liés à l’organisation. |

Routes complémentaires : `GET /discover/trips` liste les voyages publics accessibles, `GET /discover/trips/{id}` lit un voyage public, `GET /trips/{trip}/route-plan` demande une proposition d’ordre de visite, et `POST /trips/{trip}/notifications` envoie une notification de voyage. Ces routes sont elles aussi protégées par authentification dans la configuration actuelle.

### Sorties et groupes

Les routes de ressources `/outings`, `/circles`, `/invitations`, `/destination-proposals`, `/exclusion-requests`, `/polls`, `/poll-options`, `/poll-answers` et `/reactions` suivent la convention CRUD. Elles gèrent respectivement les sorties, cercles, invitations, propositions de destination, demandes d’exclusion, sondages, options, réponses et réactions.

Les opérations de sortie qui complètent le CRUD sont :

| Méthode et route | Utilité |
| --- | --- |
| `POST /outings/{id}/rsvp` | Enregistrer la réponse d’un participant; corps `{ "attending": true }` ou `false`. |
| `POST /outings/{id}/check-in` | Enregistrer l’arrivée d’un participant au rendez-vous. |
| `POST /outings/{id}/start` | Marquer le début de la sortie; organisateur uniquement. |
| `POST /outings/{id}/finish` | Clôturer la sortie; organisateur uniquement. |
| `POST /outings/{id}/contributions` | Ajouter une cotisation à la sortie. |
| `POST /outings/{id}/photos` | Ajouter une photo avec légende facultative en multipart. |
| `PATCH /outings/{id}/photos/{photoId}/story` | Inverser l’état de partage Story de la photo. |
| `POST /invitations/respond` | Répondre à une invitation à partir de son code. |
| `POST /invitations/{id}/respond` | Répondre à une invitation identifiée par UUID. |
| `GET /group-activities` | Lire le fil d’activité des groupes accessibles. |
| `POST /group-activities` | Enregistrer un événement dans le fil d’activité. |

Le détail de la création d’une sortie, les corps de requêtes et l’ordre conseillé pour les tester sont dans la section 4 plus haut. Les champs exacts de chaque action sont aussi indiqués par la requête correspondante importée depuis OpenAPI.

### Lieux et itinéraires

`/places` suit la convention CRUD et sert à gérer les lieux. Les deux recherches sont des lectures spécialisées : `GET /places/search` recherche par texte et `GET /places/nearby` recherche autour de coordonnées; leurs paramètres sont indiqués dans les requêtes importées. `GET /route-suggestions` renvoie des suggestions d’itinéraire à partir des critères de recherche fournis.

### Photos, position et sécurité

Les ressources ci-dessous suivent la convention CRUD; les autorisations restreignent les données visibles et les opérations possibles :

| Ressource | À quoi elle sert |
| --- | --- |
| `/photos` | Gérer les photos associées aux contenus. |
| `/photo-comments` | Gérer les commentaires de photos. |
| `/location-shares` | Gérer le partage de position. |
| `/location-points` | Gérer les points de position partagés. |
| `/emergency-alerts` | Gérer les alertes d’urgence. |
| `/emergency-alert-recipients` | Gérer les destinataires d’une alerte d’urgence. |

### Notifications et appareils

| Méthode et route | Utilité |
| --- | --- |
| `GET /notifications` | Lister les notifications du compte. |
| `GET /notifications/{id}` | Lire une notification. |
| `PATCH /notifications/{id}/read` | Marquer une notification comme lue. |
| `POST /notifications/read-all` | Marquer toutes les notifications comme lues. |
| `DELETE /notifications/{id}` | Supprimer une notification. |

`/notification-preferences` suit la convention CRUD pour les préférences du compte. `/device-tokens` suit la même convention pour enregistrer et gérer les jetons d’appareils utilisés pour les notifications push.

### Services, réservations et finances

Les ressources suivantes suivent la convention CRUD : `/plans` (offres), `/subscriptions` (abonnements), `/partners` (partenaires), `/services` (services proposés), `/bookings` (réservations), `/recommendations` (recommandations) et `/exchange-rates` (taux de change). Utilise l’opération `POST` pour créer l’enregistrement, puis conserve son UUID pour le lire, le modifier ou le supprimer.

### Modération et synchronisation

`/reports` gère les signalements et `/moderation-actions` les actions de modération; ces deux ressources suivent la convention CRUD et peuvent être soumises à des droits administrateur. `/offline-sync-queue` suit également cette convention et sert à enregistrer et traiter des opérations en attente de synchronisation.

## Erreurs courantes

- `401` : jeton absent, invalide ou expiré; refais la connexion et remets à jour `token`.
- `403` : le compte n’est pas participant, ou l’action est réservée à l’organisateur.
- `404` : vérifie `outing_id` et que le compte connecté a accès à cette sortie.
- `422` : vérifie les champs requis, les formats et les valeurs autorisées. `location_type` doit être `public` ou `private`.
- Code OTP absent du journal : vérifie `QUEUE_CONNECTION`, démarre `php artisan queue:work` si la queue est `database`, et confirme `MAIL_MAILER=log`.
- Les routes de connexion et d’inscription sont limitées à cinq requêtes par minute. Évite de les relancer en boucle.

## Limites du test

Les appels Postman testent le backend et stockent les sorties en base. Ils sont distincts de l’application Expo, qui utilise encore des données locales de démonstration. Aucune invitation externe ni aucun paiement réel n’est envoyé par ces routes.