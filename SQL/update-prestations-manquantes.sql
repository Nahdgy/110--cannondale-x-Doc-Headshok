-- Script SQL pour mettre à jour les prestations existantes avec les informations manquantes
-- À exécuter via phpMyAdmin ou la ligne de commande MySQL

-- IMPORTANT : Remplacez les valeurs entre guillemets par les vraies informations de chaque commande

-- Exemple de mise à jour pour une commande spécifique
-- UPDATE wp_demandes_prestations 
-- SET 
--     prestations_choisies = 'Révision 200h',
--     options_choisies = 'Joint SKF, Ressort Léger',
--     type_prestation_choisie = 'Express',
--     date_derniere_revision = '2025-06-15',
--     poids_pilote = '75',
--     remarques = 'Fourche fait un bruit bizarre au freinage'
-- WHERE numero_suivi = 'CMD-20260105-2cf19e';

-- Mise à jour pour CMD-20260105-2cf19e
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',  -- À compléter
    options_choisies = '',      -- À compléter
    type_prestation_choisie = '', -- À compléter (Express/Standard)
    date_derniere_revision = '', -- À compléter (format: YYYY-MM-DD)
    poids_pilote = '',          -- À compléter (en kg)
    remarques = ''              -- À compléter
WHERE numero_suivi = 'CMD-20260105-2cf19e';

-- Mise à jour pour CMD-20260105-2bcf5a
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260105-2bcf5a';

-- Mise à jour pour CMD-20260106-de924a
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260106-de924a';

-- Mise à jour pour CMD-20260106-3a960c
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260106-3a960c';

-- Mise à jour pour CMD-20260106-2dcc4d
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260106-2dcc4d';

-- Mise à jour pour CMD-20260106-8fe25f
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260106-8fe25f';

-- Mise à jour pour CMD-20260107-bd5e1e
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260107-bd5e1e';

-- Mise à jour pour CMD-20260108-a830a0
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260108-a830a0';

-- Mise à jour pour CMD-20260108-e6e5f8
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260108-e6e5f8';

-- Mise à jour pour CMD-20260111-c98006
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260111-c98006';

-- Mise à jour pour CMD-20260112-9b2aa2
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = '',
    options_choisies = '',
    type_prestation_choisie = '',
    date_derniere_revision = '',
    poids_pilote = '',
    remarques = ''
WHERE numero_suivi = 'CMD-20260112-9b2aa2';

-- APRÈS AVOIR COMPLÉTÉ LES INFORMATIONS CI-DESSUS :
-- 1. Connectez-vous à phpMyAdmin
-- 2. Sélectionnez votre base de données WordPress
-- 3. Allez dans l'onglet "SQL"
-- 4. Copiez-collez les requêtes UPDATE complétées
-- 5. Exécutez-les
