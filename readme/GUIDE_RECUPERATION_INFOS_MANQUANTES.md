# Guide pour récupérer les informations manquantes des prestations

## Problème
Les commandes suivantes ont été créées avant l'ajout des nouveaux champs dans la base de données :
- CMD-20260105-2cf19e
- CMD-20260105-2bcf5a
- CMD-20260106-de924a
- CMD-20260106-3a960c
- CMD-20260106-2dcc4d
- CMD-20260106-8fe25f
- CMD-20260107-bd5e1e
- CMD-20260108-a830a0
- CMD-20260108-e6e5f8
- CMD-20260111-c98006
- CMD-20260112-9b2aa2

## Informations manquantes à retrouver
Pour chaque commande, il faut retrouver :
1. ✅ **Prestations choisies** : Ex: "Révision 200h", "Révision standard + complète"
2. ✅ **Options choisies** : Ex: "Joint SKF, Ressort Léger, Ajustement Lockout"
3. ✅ **Type de prestation** : "Express" ou "Standard"
4. ✅ **Date de la dernière révision** : Format YYYY-MM-DD
5. ✅ **Poids du pilote** : En kg
6. ✅ **Remarques** : Message de description/problème signalé

## Méthode 1 : Retrouver dans les emails 📧

### A. Emails de confirmation client
1. Cherchez dans votre boîte de réception les emails avec l'objet contenant le numéro de commande
2. Les emails devraient contenir toutes les informations du formulaire
3. Pour chaque email, notez les informations dans un tableur

### B. Emails administrateur
Si vous recevez une copie des demandes :
1. Allez dans votre boîte email administrative
2. Filtrez par date (5 au 12 janvier 2026)
3. Cherchez les emails de formulaire avec les numéros de commande

### Template de collecte :
```
CMD-20260105-2cf19e
- Prestations : _________________
- Options : _________________
- Type : Express / Standard
- Date révision : ____-__-__
- Poids : ___ kg
- Remarques : _________________
```

## Méthode 2 : Vérifier la base de données WooCommerce 🗃️

Si les commandes sont liées à WooCommerce :

```sql
-- Vérifier les métadonnées des commandes
SELECT 
    post_id,
    meta_key,
    meta_value
FROM wp_postmeta
WHERE post_id IN (
    SELECT ID FROM wp_posts 
    WHERE post_title LIKE '%CMD-20260105-2cf19e%'
    OR post_title LIKE '%CMD-20260105-2bcf5a%'
    -- ... ajouter les autres numéros
)
ORDER BY post_id, meta_key;
```

## Méthode 3 : Consulter les logs du serveur 📝

Si votre serveur conserve les logs :

### Logs Apache/Nginx
```bash
# Chercher les soumissions POST vers les endpoints AJAX
grep "envoyer_form_fourche\|envoyer_form_fox" /var/log/apache2/access.log
grep "20260105\|20260106\|20260107\|20260108" /var/log/apache2/access.log
```

### Logs WordPress (si activés)
Vérifiez le fichier `wp-content/debug.log` si `WP_DEBUG_LOG` est activé.

## Méthode 4 : Utiliser l'interface d'administration 🖥️

### Étape 1 : Installer la page d'administration
1. Ajoutez le contenu du fichier `admin-edit-prestations.php` dans votre `functions.php`
2. Ou incluez-le : `require_once get_template_directory() . '/admin-edit-prestations.php';`

### Étape 2 : Accéder à l'interface
1. Connectez-vous à l'administration WordPress
2. Allez dans le menu **Prestations** dans la barre latérale
3. Vous verrez la liste des 11 commandes à compléter

### Étape 3 : Compléter les informations
1. Cliquez sur "Compléter" pour chaque commande
2. Remplissez les champs avec les informations retrouvées
3. Cliquez sur "Mettre à jour"
4. La ligne deviendra verte quand toutes les infos essentielles sont remplies

## Méthode 5 : Mise à jour SQL directe 💾

Si vous avez toutes les informations, utilisez le fichier `update-prestations-manquantes.sql` :

### Étape 1 : Compléter le fichier SQL
1. Ouvrez `update-prestations-manquantes.sql`
2. Remplissez les valeurs entre guillemets pour chaque commande
3. Exemple :
```sql
UPDATE wp_demandes_prestations 
SET 
    prestations_choisies = 'Révision 200h',
    options_choisies = 'Joint SKF, Ressort Léger',
    type_prestation_choisie = 'Express',
    date_derniere_revision = '2025-06-15',
    poids_pilote = '75',
    remarques = 'Fourche fait un bruit bizarre au freinage'
WHERE numero_suivi = 'CMD-20260105-2cf19e';
```

### Étape 2 : Exécuter dans phpMyAdmin
1. Connectez-vous à phpMyAdmin
2. Sélectionnez votre base de données WordPress
3. Allez dans l'onglet "SQL"
4. Copiez-collez les requêtes UPDATE complétées
5. Cliquez sur "Exécuter"

### Étape 3 : Vérifier
```sql
-- Vérifier que les mises à jour ont fonctionné
SELECT 
    numero_suivi,
    prestations_choisies,
    options_choisies,
    date_derniere_revision,
    poids_pilote,
    CASE 
        WHEN prestations_choisies IS NOT NULL 
        AND date_derniere_revision IS NOT NULL 
        AND poids_pilote IS NOT NULL 
        THEN '✓ Complet' 
        ELSE '✗ Incomplet' 
    END as statut
FROM wp_demandes_prestations
WHERE numero_suivi IN (
    'CMD-20260105-2cf19e',
    'CMD-20260105-2bcf5a',
    'CMD-20260106-de924a',
    'CMD-20260106-3a960c',
    'CMD-20260106-2dcc4d',
    'CMD-20260106-8fe25f',
    'CMD-20260107-bd5e1e',
    'CMD-20260108-a830a0',
    'CMD-20260108-e6e5f8',
    'CMD-20260111-c98006',
    'CMD-20260112-9b2aa2'
)
ORDER BY numero_suivi;
```

## Checklist finale ✅

Après avoir complété les informations :

- [ ] Vérifier que toutes les commandes ont des prestations_choisies
- [ ] Vérifier que date_derniere_revision est renseignée (obligatoire)
- [ ] Vérifier que poids_pilote est renseigné (obligatoire)
- [ ] Options et remarques peuvent rester vides si non fournies
- [ ] Tester l'affichage dans l'espace client (my_account.php)
- [ ] Vérifier que les informations s'affichent correctement

## Contact client en dernier recours

Si impossible de retrouver les informations, créez un email type :

```
Objet : Complément d'information - Demande [NUMERO_COMMANDE]

Bonjour,

Nous finalisons votre demande de prestation (n° [NUMERO_COMMANDE]).
Pour optimiser l'intervention, pourriez-vous nous confirmer :

1. Date de la dernière révision : _______________
2. Votre poids : _____ kg
3. Options souhaitées (si applicable) : _______________

Merci de votre retour rapide.

Cordialement,
L'équipe Doc-Headshok
```

## Support technique

Si vous rencontrez des difficultés :
1. Vérifiez les logs d'erreurs PHP
2. Assurez-vous que les colonnes existent dans la table
3. Vérifiez les permissions de la base de données
4. Testez d'abord sur une commande avant de tout mettre à jour
