# Système de Gestion des Retours de Commande

## Vue d'ensemble

Système complet permettant aux clients de demander le retour de produits commandés, avec interface de gestion administrateur et suivi client.

## Fichiers créés

### 1. `gestion-retours.php`
Système complet de gestion des retours avec :
- **Base de données** : Table `wp_demandes_retours` avec tous les champs nécessaires
- **Fonctions principales** :
  - `creer_demande_retour()` : Créer une nouvelle demande
  - `obtenir_retours_utilisateur()` : Récupérer les retours d'un client
  - `obtenir_tous_retours()` : Récupérer tous les retours (admin)
  - `mettre_a_jour_statut_retour()` : Modifier le statut
  - `generer_numero_retour()` : Générer numéro unique (RET-YYYYMMDD-XXXXXX)
- **Notifications email** :
  - Confirmation au client lors de la demande
  - Notification admin nouveau retour
  - Notification client changement de statut
- **Interface admin WordPress** :
  - Menu "Retours" dans l'admin
  - Dashboard avec statistiques
  - Liste des demandes avec filtres par statut
  - Modal de gestion avec modification de statut
- **Actions AJAX** :
  - `creer_demande_retour` : Créer demande (client)
  - `maj_statut_retour` : Mettre à jour statut (admin)
  - `get_retour_details` : Récupérer détails (admin)

### 2. `my_account.php` (modifié)
Ajout de la section "Mes retours" dans Mon Compte :
- **Menu** : Lien "Mes retours" entre "Mes commandes" et "Historique des prestations"
- **Liste des retours** : Affichage tableau avec :
  - Numéro de retour
  - Date de demande
  - Statut avec badge coloré
  - Commande concernée
  - Motif et description
  - Produits retournés
  - Montant
  - Notes de l'équipe
  - Numéro de suivi retour
  - Indicateur remboursement
- **Bouton nouvelle demande** : Ouvre modal de création
- **Modal de demande** :
  - Sélection commande éligible (completed, < 30 jours)
  - Sélection produits (checkboxes dynamiques)
  - Motif (liste déroulante)
  - Description détaillée
  - Validation et envoi AJAX
  - Page de confirmation avec numéro de retour

## Structure de la base de données

### Table `wp_demandes_retours`

```sql
CREATE TABLE wp_demandes_retours (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    numero_retour VARCHAR(50) UNIQUE NOT NULL,
    user_id INT(11) NOT NULL,
    order_id INT(11) NOT NULL,
    motif VARCHAR(255) NOT NULL,
    description TEXT,
    produits_concernes TEXT NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    statut VARCHAR(50) DEFAULT 'en_attente',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes_admin TEXT,
    numero_suivi_retour VARCHAR(100),
    remboursement_effectue TINYINT(1) DEFAULT 0,
    KEY user_id (user_id),
    KEY order_id (order_id),
    KEY statut (statut)
);
```

### Champs détaillés

- **id** : Identifiant unique auto-incrémenté
- **numero_retour** : Numéro de retour unique (format : RET-YYYYMMDD-XXXXXX)
- **user_id** : ID de l'utilisateur WordPress
- **order_id** : ID de la commande WooCommerce
- **motif** : Raison du retour (produit défectueux, changement d'avis, etc.)
- **description** : Description détaillée du client
- **produits_concernes** : JSON avec détails produits (item_id, name, quantity, total)
- **montant_total** : Montant total des produits retournés
- **statut** : État de la demande (voir ci-dessous)
- **date_demande** : Date de création de la demande
- **date_maj** : Date de dernière modification
- **notes_admin** : Notes de l'équipe pour le client
- **numero_suivi_retour** : Numéro de suivi du colis retour
- **remboursement_effectue** : Indicateur de remboursement (0/1)

## Statuts des retours

| Statut | Label | Couleur | Description |
|--------|-------|---------|-------------|
| `en_attente` | En attente d'examen | Orange | Demande reçue, en attente de traitement |
| `approuve` | Approuvé | Vert | Retour accepté, client peut renvoyer |
| `refuse` | Refusé | Rouge | Demande refusée |
| `en_cours` | Retour en cours | Bleu | Colis en transit vers l'entreprise |
| `recu` | Colis reçu | Violet | Colis retour réceptionné |
| `rembourse` | Remboursé | Vert | Remboursement effectué |
| `termine` | Terminé | Gris | Processus terminé |

## Motifs de retour disponibles

1. **Produit défectueux** : Article avec défaut de fabrication
2. **Produit non conforme** : Ne correspond pas à la description
3. **Mauvaise taille** : Taille ou modèle incorrect
4. **Changement d'avis** : Le client a changé d'avis
5. **Article endommagé** : Endommagé pendant la livraison
6. **Autre** : Autre raison (avec description)

## Règles de retour

- **Délai** : 30 jours après la date de livraison (date_completed)
- **Statut** : Commande doit être "completed"
- **Produits** : Au moins 1 produit doit être sélectionné
- **Montant** : Calculé automatiquement selon les produits sélectionnés

## Workflow complet

### 1. Client crée une demande

```
Client → Mon Compte → Mes retours → + Nouvelle demande
↓
Sélectionne commande éligible
↓
Coche produits à retourner
↓
Choisit motif + ajoute description
↓
Soumet la demande
↓
Reçoit numéro de retour + email confirmation
```

### 2. Admin traite la demande

```
Admin → Dashboard WP → Retours
↓
Voit nouvelle demande (statut: en_attente)
↓
Clique sur "Gérer"
↓
Examine détails (client, commande, produits, motif)
↓
Change statut vers "approuve" ou "refuse"
↓
Ajoute notes pour le client
↓
Ajoute numéro de suivi si besoin
↓
Enregistre
↓
Client reçoit email de mise à jour
```

### 3. Suivi du retour

```
Client renvoie le colis
↓
Admin met statut "en_cours"
↓
Colis reçu → statut "recu"
↓
Admin vérifie produits
↓
Effectue remboursement WooCommerce
↓
Coche "Remboursement effectué"
↓
Statut "rembourse"
↓
Client reçoit notification
↓
Statut final "termine"
```

## Fonctionnalités admin

### Dashboard statistiques

- **Total retours** : Nombre total de demandes
- **En attente** : Demandes nécessitant attention
- **Approuvés** : Retours validés
- **Terminés** : Processus complétés

### Filtres

- Filtrage par statut
- Tri par date
- Recherche par numéro de retour (futur)

### Actions disponibles

- **Gérer** : Ouvrir modal de modification
  - Changer statut
  - Ajouter notes admin
  - Ajouter numéro de suivi
  - Marquer remboursement
- **Détails** : Voir informations complètes
  - Infos client
  - Détails commande
  - Liste produits
  - Historique des changements

### Modal de gestion

```php
- Numéro de retour (lecture seule)
- Infos client (lecture seule)
- Commande liée (lien cliquable)
- Motif et description (lecture seule)
- Liste produits concernés (lecture seule)
- [Modification]
  - Statut (select)
  - N° de suivi retour (input)
  - Remboursement effectué (checkbox)
  - Notes admin (textarea)
- Boutons : Enregistrer / Annuler
```

## Emails automatiques

### 1. Confirmation création (client)

**Sujet** : 📦 Demande de retour enregistrée - [NUMERO]

**Contenu** :
- Message de confirmation
- Numéro de retour en gros
- Détails : commande, motif, montant
- Prochaines étapes (3 étapes)
- Lien vers Mon Compte

### 2. Notification admin (nouveau retour)

**Sujet** : 🔄 Nouvelle demande de retour - [NUMERO]

**Contenu** :
- Alerte nouvelle demande
- N° de retour
- Infos client
- Numéro de commande
- Motif
- Montant
- Lien vers back-office

### 3. Changement de statut (client)

**Sujet** : 📦 Mise à jour de votre demande de retour - [NUMERO]

**Contenu** :
- Message de mise à jour
- N° de retour
- Nouveau statut (coloré)
- Notes de l'équipe (si présentes)
- Lien vers Mon Compte

## Installation

### 1. Ajouter dans functions.php

```php
// Inclure le système de gestion des retours
require_once get_template_directory() . '/gestion-retours.php';
```

### 2. La table sera créée automatiquement

Le hook `init` crée la table si elle n'existe pas.

### 3. Vérifier les permissions

- L'admin doit avoir la capability `manage_woocommerce`
- Les clients doivent être connectés

## Styles CSS

### Classes principales

```css
.retours-container       /* Conteneur principal */
.retours-liste           /* Liste des retours */
.retour-item             /* Carte de retour */
.retour-header           /* En-tête avec numéro/date/statut */
.retour-details          /* Corps avec infos */
.retour-info             /* Colonne informations */
.retour-actions          /* Colonne actions/montant */
.retour-statut           /* Badge de statut */
.statut-[nom]           /* Couleurs spécifiques */
.retour-notes-admin      /* Notes admin (fond jaune) */
.modal-retour            /* Modal de demande */
.form-retour             /* Formulaire */
.produits-selection      /* Liste checkboxes produits */
```

### Statuts badges

- `.statut-attente` : Orange (#FFA500)
- `.statut-approuve` : Vert (#4CAF50)
- `.statut-refuse` : Rouge (#F44336)
- `.statut-cours` : Bleu (#2196F3)
- `.statut-recu` : Violet (#9C27B0)
- `.statut-rembourse` : Vert clair (#22C55E)
- `.statut-terminee` : Gris (#888)

## JavaScript

### Fonctions globales

```javascript
window.ouvrirModalRetour()   // Ouvre modal de création
window.fermerModalRetour()   // Ferme modal
```

### Événements

- Change commande → Charge produits dynamiquement
- Submit form → Envoi AJAX avec validation
- Success → Affiche confirmation avec numéro

## Sécurité

### Vérifications côté serveur

1. **Nonces** : Tous les formulaires utilisent wp_nonce
2. **Authentification** : is_user_logged_in()
3. **Permissions** : current_user_can('manage_woocommerce') pour admin
4. **Validation** : Commande appartient à l'utilisateur
5. **Éligibilité** : Statut completed + délai 30 jours
6. **Sanitization** : sanitize_text_field(), sanitize_textarea_field()
7. **Préparation SQL** : $wpdb->prepare() partout

### Protection XSS

- esc_html() pour affichage texte
- esc_attr() pour attributs
- esc_js() pour JavaScript
- esc_url() pour URLs

## Extensions futures possibles

### Fonctionnalités additionnelles

1. **Étiquettes de retour** : Générer PDF avec adresse + code-barres
2. **Photos** : Upload photos produits défectueux
3. **Tracking automatique** : Intégration API transporteurs
4. **Remboursement automatique** : Déclencher remboursement WooCommerce
5. **Statistiques avancées** : Graphiques, taux de retour par produit
6. **Export** : Export CSV/Excel des retours
7. **Notifications SMS** : Via Twilio ou autre
8. **Chat support** : Intégré dans la demande de retour
9. **Historique** : Log de tous les changements de statut
10. **Conditions personnalisées** : Règles de retour par catégorie/produit

### Améliorations UX

1. **Recherche** : Recherche par numéro, client, commande
2. **Pagination** : Pour grandes listes
3. **Tri** : Tri par colonne dans l'admin
4. **Actions groupées** : Changer statut plusieurs retours
5. **Templates email** : Personnalisation des emails
6. **Raisons détaillées** : Sous-catégories de motifs
7. **Évaluation** : Client note le processus de retour

## Support et maintenance

### Logs

Les erreurs sont loggées dans les logs WordPress standard.

### Debug

Activer WP_DEBUG pour voir les messages d'erreur détaillés.

### Base de données

Pour réinitialiser :
```sql
DROP TABLE IF EXISTS wp_demandes_retours;
```

Puis recharger la page admin pour recréer.

## Compatibilité

- **WordPress** : 5.0+
- **WooCommerce** : 3.0+
- **PHP** : 7.4+
- **MySQL** : 5.6+

## Contact et support

Pour toute question ou problème :
- Vérifier les logs WordPress
- Tester avec WP_DEBUG activé
- Vérifier que la table est créée
- Vérifier les permissions utilisateur

---

**Version** : 1.0.0  
**Date** : Novembre 2024  
**Auteur** : Doc-Headshok Development Team
