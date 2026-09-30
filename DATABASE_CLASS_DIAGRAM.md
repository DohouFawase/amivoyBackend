# TripVibe — diagramme des classes de la base de données

Ce document décrit **le schéma Laravel actuellement présent dans `database/migrations`**, y compris les tables ajoutées pour les listes de préparation, le carnet de voyage et la visibilité publique. Les diagrammes couvrent les fonctions au-delà du MVP : paiements, remboursements, albums, partage de position, SOS, modération, réservations, abonnements et synchronisation.

Les classes représentent les entités persistées. Les tables Laravel d’infrastructure (`cache`, `jobs`, `sessions`, etc.) ne sont pas des entités métier et sont listées à la fin. Les cardinalités ci-dessous suivent les clés étrangères de la base ; plusieurs FK sont nullable dans le schéma.

## 1. Comptes, voyages, décisions et programme

```mermaid
classDiagram
    direction LR
    class User {
      +UUID id
      +string email
      +string phone
      +string first_name
      +string last_name
      +string avatar_url
      +string language
      +boolean email_verified
      +boolean phone_verified
      +boolean two_factor_enabled
      +string platform_role
      +string status
    }
    class Trip {
      +UUID id
      +UUID creator_id
      +string name
      +text description
      +string cover_url
      +string destination_label
      +date start_date
      +date end_date
      +string currency
      +bigint planned_budget
      +string status
      +string visibility
      +json governance_rules
    }
    class TripMember {
      +UUID id
      +UUID trip_id
      +UUID user_id
      +string role
      +string status
      +datetime joined_at
      +datetime left_at
    }
    class Invitation {
      +UUID id
      +UUID trip_id
      +UUID invited_by
      +string token_hash
      +string channel
      +string target
      +integer max_uses
      +integer use_count
      +datetime expires_at
      +string status
    }
    class Poll {
      +UUID id
      +UUID trip_id
      +UUID created_by
      +string type
      +string title
      +boolean multiple_choice
      +boolean anonymous
      +integer quorum_percent
      +datetime closes_at
      +UUID winning_option_id
    }
    class PollOption {
      +UUID id
      +UUID poll_id
      +string label
      +json payload
      +integer position
    }
    class PollAnswer {
      +UUID id
      +UUID option_id
      +UUID member_id
      +datetime voted_at
      +datetime changed_at
    }
    class DestinationProposal {
      +UUID id
      +UUID trip_id
      +UUID proposed_by
      +string name
      +decimal lat
      +decimal lng
      +text pitch
      +integer estimated_cost
    }
    class ExclusionRequest {
      +UUID id
      +UUID trip_id
      +UUID target_member_id
      +UUID requested_by
      +UUID poll_id
      +text reason
      +string status
      +datetime decided_at
    }
    class Reaction {
      +UUID id
      +UUID member_id
      +string target_type
      +UUID target_id
      +string emoji
    }
    class Place {
      +UUID id
      +string provider
      +string provider_place_id
      +string name
      +string category
      +decimal lat
      +decimal lng
      +string address
      +json opening_hours
      +json cached_data
    }
    class TripPlace {
      +UUID id
      +UUID trip_id
      +UUID place_id
      +UUID added_by
      +integer likes_count
      +string status
      +text note
    }
    class Activity {
      +UUID id
      +UUID trip_id
      +UUID trip_place_id
      +UUID responsible_id
      +string title
      +text description
      +datetime starts_at
      +integer duration_min
      +bigint estimated_cost
      +string status
      +text notes
    }
    class ActivityAttendee {
      +UUID id
      +UUID activity_id
      +UUID member_id
      +string rsvp
    }
    class MeetingPoint {
      +UUID id
      +UUID trip_id
      +UUID created_by
      +UUID activity_id
      +string name
      +decimal lat
      +decimal lng
      +datetime meet_at
      +text instructions
    }
    class Reminder {
      +UUID id
      +UUID trip_id
      +string type
      +string target_type
      +UUID target_id
      +datetime fire_at
      +string status
    }
    class PackingItem {
      +UUID id
      +UUID trip_id
      +UUID added_by
      +string title
      +string category
      +integer quantity
      +boolean is_packed
      +text notes
    }
    class TripJournalEntry {
      +UUID id
      +UUID trip_id
      +UUID author_id
      +string title
      +text content
      +string place_label
      +decimal lat
      +decimal lng
      +datetime happened_at
    }
    User "1" --> "0..*" Trip : crée
    Trip "1" --> "0..*" TripMember : regroupe
    User "1" --> "0..*" TripMember : rejoint
    Trip "1" --> "0..*" Invitation : invite
    User "1" --> "0..*" Invitation : envoie
    Trip "1" --> "0..*" Poll : organise
    User "1" --> "0..*" Poll : crée
    Poll "1" --> "0..*" PollOption : propose
    PollOption "1" --> "0..*" PollAnswer : reçoit
    TripMember "1" --> "0..*" PollAnswer : vote
    Trip "1" --> "0..*" DestinationProposal : reçoit
    TripMember "1" --> "0..*" DestinationProposal : propose
    Trip "1" --> "0..*" ExclusionRequest : gouverne
    TripMember "1" --> "0..*" ExclusionRequest : demande ou cible
    Poll "0..1" --> "0..*" ExclusionRequest : décision
    TripMember "1" --> "0..*" Reaction : réagit
    Trip "1" --> "0..*" TripPlace : collectionne
    Place "1" --> "0..*" TripPlace : est ajouté
    TripMember "1" --> "0..*" TripPlace : ajoute
    Trip "1" --> "0..*" Activity : planifie
    TripPlace "0..1" --> "0..*" Activity : lieu du programme
    TripMember "0..1" --> "0..*" Activity : responsable
    Activity "1" --> "0..*" ActivityAttendee : accueille
    TripMember "1" --> "0..*" ActivityAttendee : participe
    Trip "1" --> "0..*" MeetingPoint : définit
    Activity "0..1" --> "0..*" MeetingPoint : associé
    User "1" --> "0..*" MeetingPoint : crée
    Trip "1" --> "0..*" Reminder : rappelle
    Trip "1" --> "0..*" PackingItem : prépare
    User "0..1" --> "0..*" PackingItem : ajoute
    Trip "1" --> "0..*" TripJournalEntry : conserve
    User "0..1" --> "0..*" TripJournalEntry : rédige
```

## 2. Budgets, contributions, dépenses et paiements

```mermaid
classDiagram
    direction LR
    class Trip { +UUID id +string currency +bigint planned_budget }
    class TripMember { +UUID id +UUID trip_id +UUID user_id }
    class User { +UUID id +string email }
    class Budget { +UUID id +UUID trip_id +bigint total_planned +string currency +integer version }
    class BudgetLine { +UUID id +UUID budget_id +string category +bigint planned_amount }
    class Contribution { +UUID id +UUID trip_id +UUID member_id +bigint expected_amount +bigint paid_amount +string status +date due_date }
    class Expense { +UUID id +UUID trip_id +UUID paid_by +UUID budget_line_id +string title +bigint amount +string currency +decimal fx_rate +string split_mode +datetime spent_at +string receipt_url +text comment }
    class ExpenseParticipant { +UUID id +UUID expense_id +UUID member_id +bigint share_amount +decimal share_weight }
    class Settlement { +UUID id +UUID trip_id +UUID from_member +UUID to_member +bigint amount +string status +datetime confirmed_at }
    class Payment { +UUID id +UUID user_id +UUID contribution_id +UUID settlement_id +string provider +string provider_ref +bigint amount +string currency +string status +string idempotency_key }
    class PaymentEvent { +UUID id +UUID payment_id +string event_type +json raw_payload +boolean signature_valid +datetime received_at }
    class Refund { +UUID id +UUID payment_id +bigint amount +text reason +string status +string provider_ref }
    class ExchangeRate { +UUID id +string base +string quote +decimal rate +string source +datetime fetched_at }
    Trip "1" --> "0..*" Budget : budgète
    Budget "1" --> "0..*" BudgetLine : répartit
    Trip "1" --> "0..*" Contribution : collecte
    TripMember "1" --> "0..*" Contribution : contribue
    Trip "1" --> "0..*" Expense : comptabilise
    TripMember "1" --> "0..*" Expense : avance
    BudgetLine "0..1" --> "0..*" Expense : catégorise
    Expense "1" --> "0..*" ExpenseParticipant : répartit
    TripMember "1" --> "0..*" ExpenseParticipant : doit sa part
    Trip "1" --> "0..*" Settlement : solde
    TripMember "1" --> "0..*" Settlement : émet ou reçoit
    User "0..1" --> "0..*" Payment : initie
    Contribution "0..1" --> "0..*" Payment : règle
    Settlement "0..1" --> "0..*" Payment : règle
    Payment "1" --> "0..*" PaymentEvent : journalise
    Payment "1" --> "0..*" Refund : rembourse
```

Les soldes individuels sont **calculés** à partir des dépenses et participants ; il n’existe pas de table `balances` matérialisée. `ExchangeRate` stocke les cours, mais il ne porte pas de clé étrangère vers les dépenses. Les paiements restent délégués à un prestataire : le schéma conserve des références de prestataire, pas de numéro de carte.

## 3. Photos, notifications, position, SOS et audit

```mermaid
classDiagram
    direction LR
    class User { +UUID id +string email +string phone }
    class Trip { +UUID id +string name }
    class TripMember { +UUID id +UUID trip_id +UUID user_id }
    class Photo { +UUID id +UUID trip_id +UUID uploaded_by +string storage_key +string thumbnail_key +string mime_type +integer size_bytes +string status +datetime taken_at }
    class PhotoComment { +UUID id +UUID photo_id +UUID member_id +text content }
    class Notification { +UUID id +UUID user_id +UUID trip_id +string category +string type +string title +text body +json data +datetime read_at +datetime sent_at }
    class NotificationPreference { +UUID id +UUID user_id +boolean push_enabled +boolean email_enabled +boolean sms_enabled +json per_type_settings +time quiet_from +time quiet_to }
    class DeviceToken { +UUID id +UUID user_id +string platform +string fcm_apns_token +datetime last_seen_at }
    class LocationShare { +UUID id +UUID trip_id +UUID member_id +string duration_mode +datetime started_at +datetime expires_at +datetime stopped_at }
    class LocationPoint { +UUID id +UUID share_id +decimal lat +decimal lng +float accuracy_m +datetime recorded_at }
    class EmergencyAlert { +UUID id +UUID trip_id +UUID member_id +decimal lat +decimal lng +boolean position_is_last_known +string status +datetime triggered_at +datetime resolved_at }
    class EmergencyAlertRecipient { +UUID id +UUID alert_id +UUID member_id +datetime delivered_at +datetime acknowledged_at }
    class OfflineSyncQueue { +UUID id +UUID user_id +UUID trip_id +string operation +json payload +string client_op_id +string status }
    class AuditLog { +UUID id +UUID actor_id +UUID trip_id +string action +string entity_type +UUID entity_id +json before +json after +string ip_address +timestamp created_at }
    class Report { +UUID id +UUID reporter_id +string target_type +UUID target_id +string reason +text details +string status }
    class ModerationAction { +UUID id +UUID report_id +UUID moderator_id +string action +text note }
    Trip "1" --> "0..*" Photo : album
    User "1" --> "0..*" Photo : téléverse
    Photo "1" --> "0..*" PhotoComment : reçoit
    TripMember "1" --> "0..*" PhotoComment : commente
    User "1" --> "0..*" Notification : reçoit
    Trip "0..1" --> "0..*" Notification : contexte
    User "1" --> "0..*" NotificationPreference : règle
    User "1" --> "0..*" DeviceToken : utilise
    Trip "1" --> "0..*" LocationShare : autorise
    TripMember "1" --> "0..*" LocationShare : partage temporairement
    LocationShare "1" --> "0..*" LocationPoint : enregistre
    Trip "1" --> "0..*" EmergencyAlert : contient
    TripMember "1" --> "0..*" EmergencyAlert : déclenche
    EmergencyAlert "1" --> "0..*" EmergencyAlertRecipient : notifie
    TripMember "1" --> "0..*" EmergencyAlertRecipient : accuse réception
    User "1" --> "0..*" OfflineSyncQueue : synchronise
    Trip "0..1" --> "0..*" OfflineSyncQueue : changements
    User "0..1" --> "0..*" AuditLog : agit
    Trip "0..1" --> "0..*" AuditLog : trace
    User "1" --> "0..*" Report : signale
    Report "1" --> "0..*" ModerationAction : traitement
    User "1" --> "0..*" ModerationAction : modère
```

## 4. Offres, partenaires et réservations

```mermaid
classDiagram
    direction LR
    class User { +UUID id +string email }
    class Trip { +UUID id +string name }
    class TripMember { +UUID id +UUID trip_id +UUID user_id }
    class Plan { +UUID id +string code +integer max_members +integer photo_quota_mb +json features +bigint price +string billing_period }
    class Subscription { +UUID id +UUID plan_id +UUID user_id +UUID trip_id +string status +datetime current_period_end }
    class Partner { +UUID id +string name +string type +string country +decimal commission_rate +string contact_email +string status }
    class Place { +UUID id +string name +decimal lat +decimal lng }
    class Service { +UUID id +UUID partner_id +UUID place_id +string title +bigint price +string currency +json availability }
    class Booking { +UUID id +UUID trip_id +UUID service_id +UUID booked_by +UUID activity_id +integer quantity +bigint total_amount +string status +string partner_ref }
    class Activity { +UUID id +UUID trip_id +string title +datetime starts_at }
    class Recommendation { +UUID id +UUID user_id +UUID service_id +decimal score +string reason }
    Plan "1" --> "0..*" Subscription : décrit
    User "1" --> "0..*" Subscription : souscrit
    Trip "0..1" --> "0..*" Subscription : peut souscrire
    Partner "1" --> "0..*" Service : propose
    Place "0..1" --> "0..*" Service : situe
    Trip "1" --> "0..*" Booking : réserve
    Service "0..1" --> "0..*" Booking : réservé
    TripMember "0..1" --> "0..*" Booking : effectue
    Activity "0..1" --> "0..*" Booking : rattache
    User "1" --> "0..*" Recommendation : reçoit
    Service "0..1" --> "0..*" Recommendation : suggéré
```

## 5. Authentification et tables techniques

Les entités applicatives d’authentification sont `User`, `UserSession` (session/appareil et empreinte du jeton de renouvellement) et `AuthChallenge` (codes temporaires à usage limité). Laravel ajoute aussi `personal_access_tokens` pour Sanctum.

Les migrations de base Laravel créent également `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches` et `failed_jobs`. Ce sont des tables de support du framework et non des objets de TripVibe.

## 6. Éléments du cahier des charges à modéliser plus tard

Les entités ci-dessus couvrent déjà l’essentiel du cycle complet décrit dans le cahier. Les points suivants ne sont pas des tables actuelles et ne sont **pas représentés comme implémentés** :

- Un vrai itinéraire routier calculé par un fournisseur cartographique. Aujourd’hui, les trajets suggérés de l’interface mock sont seulement des données de démonstration ; les `Place`, `TripPlace`, `Activity` et `MeetingPoint` fournissent le socle persistant.
- Des documents de voyage (billets, visas, vouchers) attachés au voyage, à une réservation ou à un jour.
- Une messagerie libre entre membres. Les notes, commentaires de photos et notifications ne remplacent pas un fil de discussion.
- Des photos séparées en albums nommés ; aujourd’hui, `Photo` est rattachée directement à un trip.
- Un historique immuable et exhaustif des changements de rôles, votes et opérations financières. `AuditLog` existe, mais ses champs sont génériques et son alimentation doit être vérifiée.
- Les modèles détaillés de transport (segments, horaires, transporteurs) et de devises de compte utilisateur.
- Les reçus sont stockés par URL dans `Expense.receipt_url` ; la gestion de fichiers sécurisée, les pièces multiples et leurs métadonnées ne sont pas modélisées comme entité dédiée.

Ces ajouts peuvent être dessinés comme extensions de domaine lorsque leurs règles sont arrêtées ; ils ne doivent pas être confondus avec les migrations actuellement présentes.

## 7. Extension proposée pour couvrir tout le produit

Les classes de cette section sont une **proposition de conception**, pas des tables présentes dans les migrations actuelles. Elles couvrent les besoins décrits dans le cahier des charges qui ne sont pas encore persistés. Les trajets recommandés et leurs étapes seraient ainsi enregistrables et modifiables par le groupe.

```mermaid
classDiagram
    direction LR
    class User { +UUID id }
    class Trip { +UUID id }
    class TripMember { +UUID id +UUID trip_id +UUID user_id }
    class TripPlace { +UUID id +UUID trip_id +UUID place_id }
    class Activity { +UUID id +UUID trip_id +string title +datetime starts_at }
    class Photo { +UUID id +UUID trip_id +string storage_key }
    class Booking { +UUID id +UUID trip_id +UUID service_id }
    class TripRoute { +UUID id +UUID trip_id +UUID created_by +string title +string route_type +string status +integer position }
    class RouteStop { +UUID id +UUID route_id +UUID trip_place_id +integer position +datetime planned_arrival +integer duration_min +text notes }
    class TripDay { +UUID id +UUID trip_id +date date +string title +integer position }
    class TripDocument { +UUID id +UUID trip_id +UUID booking_id +UUID uploaded_by +string document_type +string storage_key +datetime expires_at }
    class TripAlbum { +UUID id +UUID trip_id +string title +string description +UUID created_by }
    class AlbumPhoto { +UUID id +UUID album_id +UUID photo_id +UUID added_by +integer position }
    class ChatMessage { +UUID id +UUID trip_id +UUID sender_member_id +text body +datetime edited_at +datetime deleted_at }
    class ChatAttachment { +UUID id +UUID message_id +string storage_key +string mime_type +integer size_bytes }
    class TripMemberRole { +UUID id +UUID trip_id +string code +string name +json permissions }
    class MemberRoleAssignment { +UUID id +UUID member_id +UUID role_id +UUID assigned_by +datetime assigned_at }
    Trip "1" --> "0..*" TripRoute : compose
    User "1" --> "0..*" TripRoute : crée
    TripRoute "1" --> "2..*" RouteStop : ordonne les étapes
    TripPlace "0..1" --> "0..*" RouteStop : référence un lieu du voyage
    Trip "1" --> "0..*" TripDay : organise par date
    TripDay "1" --> "0..*" Activity : programme
    Trip "1" --> "0..*" TripDocument : conserve
    Booking "0..1" --> "0..*" TripDocument : justificatifs
    User "1" --> "0..*" TripDocument : téléverse
    Trip "1" --> "0..*" TripAlbum : possède
    User "1" --> "0..*" TripAlbum : crée
    TripAlbum "1" --> "0..*" AlbumPhoto : contient
    Photo "1" --> "0..*" AlbumPhoto : est classée
    TripMember "1" --> "0..*" AlbumPhoto : ajoute
    Trip "1" --> "0..*" ChatMessage : discute
    TripMember "1" --> "0..*" ChatMessage : écrit
    ChatMessage "1" --> "0..*" ChatAttachment : joint
    Trip "1" --> "0..*" TripMemberRole : configure
    TripMemberRole "1" --> "0..*" MemberRoleAssignment : attribué
    TripMember "1" --> "0..*" MemberRoleAssignment : reçoit
```

Avant d’ajouter ces migrations, il faudra choisir si les étapes d’un itinéraire réutilisent toujours `TripPlace`, comment les `TripDay` s’articulent avec les dates des `Activity`, et quelles permissions de rôle sont configurables. La recherche de lieux reste un accès à un fournisseur cartographique et n’exige pas une table de recherche ; seuls les lieux sélectionnés par le groupe doivent être conservés dans la base.
