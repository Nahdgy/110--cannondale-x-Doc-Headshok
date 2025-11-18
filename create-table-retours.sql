-- Table de gestion des retours de commande
-- À exécuter dans phpMyAdmin ou via la ligne de commande MySQL
-- Remplacer 'wp_' par votre préfixe de table WordPress si différent

CREATE TABLE IF NOT EXISTS wp_demandes_retours (
    id INT(11) NOT NULL AUTO_INCREMENT,
    numero_retour VARCHAR(50) NOT NULL UNIQUE,
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
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY order_id (order_id),
    KEY statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Description des champs :
-- id : Identifiant unique auto-incrémenté
-- numero_retour : Numéro unique du retour (format: RET-YYYYMMDD-XXXXXX)
-- user_id : ID de l'utilisateur WordPress
-- order_id : ID de la commande WooCommerce
-- motif : Raison du retour
-- description : Description détaillée du client
-- produits_concernes : JSON avec les détails des produits (item_id, name, quantity, total)
-- montant_total : Montant total des produits retournés
-- statut : État de la demande (en_attente, approuve, refuse, en_cours, recu, rembourse, termine)
-- date_demande : Date de création de la demande
-- date_maj : Date de dernière modification (mise à jour automatique)
-- notes_admin : Notes de l'équipe pour le client
-- numero_suivi_retour : Numéro de suivi du colis retour
-- remboursement_effectue : Indicateur de remboursement (0=non, 1=oui)
