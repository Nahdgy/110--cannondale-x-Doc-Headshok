-- Script de migration pour ajouter les colonnes manquantes dans wp_demandes_prestations
-- À exécuter dans phpMyAdmin ou via la ligne de commande MySQL

-- IMPORTANT : Remplacez 'wp_' par votre préfixe de base de données si différent

-- Vérifier la structure actuelle de la table
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'wp_demandes_prestations' AND TABLE_SCHEMA = DATABASE();

-- Ajouter les nouvelles colonnes si elles n'existent pas
ALTER TABLE wp_demandes_prestations 
ADD COLUMN IF NOT EXISTS prestations_choisies text AFTER annee_velo,
ADD COLUMN IF NOT EXISTS options_choisies text AFTER prestations_choisies,
ADD COLUMN IF NOT EXISTS type_prestation_choisie varchar(255) AFTER options_choisies,
ADD COLUMN IF NOT EXISTS date_derniere_revision varchar(50) AFTER type_prestation_choisie,
ADD COLUMN IF NOT EXISTS poids_pilote varchar(50) AFTER date_derniere_revision,
ADD COLUMN IF NOT EXISTS remarques text AFTER poids_pilote;

-- Si la syntaxe IF NOT EXISTS ne fonctionne pas (MySQL < 8.0.13), utilisez ceci :
-- NOTE : Ces requêtes échoueront si la colonne existe déjà, c'est normal

-- Pour MySQL < 8.0.13, commentez le ALTER TABLE ci-dessus et utilisez celui-ci :
/*
ALTER TABLE wp_demandes_prestations ADD COLUMN prestations_choisies text AFTER annee_velo;
ALTER TABLE wp_demandes_prestations ADD COLUMN options_choisies text AFTER prestations_choisies;
ALTER TABLE wp_demandes_prestations ADD COLUMN type_prestation_choisie varchar(255) AFTER options_choisies;
ALTER TABLE wp_demandes_prestations ADD COLUMN date_derniere_revision varchar(50) AFTER type_prestation_choisie;
ALTER TABLE wp_demandes_prestations ADD COLUMN poids_pilote varchar(50) AFTER date_derniere_revision;
ALTER TABLE wp_demandes_prestations ADD COLUMN remarques text AFTER poids_pilote;
*/

-- Vérifier que les colonnes ont été ajoutées
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'wp_demandes_prestations' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;

-- Résultat attendu : vous devriez voir les nouvelles colonnes :
-- prestations_choisies (text)
-- options_choisies (text)
-- type_prestation_choisie (varchar)
-- date_derniere_revision (varchar)
-- poids_pilote (varchar)
-- remarques (text)
