# 🔧 CORRECTION URGENTE - Erreur "Unknown column 'prestations_choisies'"

## ❌ Problème
L'erreur `Unknown column 'prestations_choisies' in 'INSERT INTO'` signifie que les nouvelles colonnes n'ont pas été créées dans la table `wp_demandes_prestations`.

## ✅ Solution RAPIDE (2 minutes)

### Option A : Migration automatique via WordPress (RECOMMANDÉ)

1. **J'ai déjà ajouté le code de migration** dans `function.php` (lignes 60-84)
2. **Allez dans l'administration WordPress**
3. **Rafraîchissez n'importe quelle page admin** (Dashboard, Pages, etc.)
4. La migration se lancera automatiquement au chargement
5. Vérifiez les logs si activés : `wp-content/debug.log` devrait contenir "✅ Migration table prestations"

**C'est tout !** Les colonnes seront ajoutées automatiquement.

### Option B : Migration manuelle via phpMyAdmin (si Option A ne fonctionne pas)

#### Étape 1 : Connexion
1. Connectez-vous à **phpMyAdmin**
2. Sélectionnez votre **base de données WordPress**

#### Étape 2 : Exécution
1. Cliquez sur l'onglet **SQL**
2. Copiez-collez ce code :

```sql
ALTER TABLE wp_demandes_prestations 
ADD COLUMN prestations_choisies text AFTER annee_velo,
ADD COLUMN options_choisies text AFTER prestations_choisies,
ADD COLUMN type_prestation_choisie varchar(255) AFTER options_choisies,
ADD COLUMN date_derniere_revision varchar(50) AFTER type_prestation_choisie,
ADD COLUMN poids_pilote varchar(50) AFTER date_derniere_revision,
ADD COLUMN remarques text AFTER poids_pilote;
```

3. **Remplacez `wp_`** par votre préfixe si différent
4. Cliquez sur **Exécuter**

#### Étape 3 : Vérification
```sql
SHOW COLUMNS FROM wp_demandes_prestations;
```

Vous devriez voir les nouvelles colonnes listées.

## 🧪 Test après correction

1. Allez sur un formulaire de prestation
2. Remplissez et soumettez le formulaire
3. Vérifiez dans la console : vous devriez voir "✅ Prestation synchronisée"
4. Vérifiez dans "Mon compte" > "Mes prestations" : les informations devraient s'afficher

## 📊 Vérification finale

### Dans phpMyAdmin :
```sql
-- Voir la structure complète
DESCRIBE wp_demandes_prestations;

-- Voir les dernières prestations avec les nouvelles colonnes
SELECT 
    numero_suivi,
    prestations_choisies,
    options_choisies,
    date_derniere_revision,
    poids_pilote,
    remarques
FROM wp_demandes_prestations
ORDER BY date_creation DESC
LIMIT 5;
```

## 🚨 Si l'erreur persiste

### Vérifiez les permissions de la base de données
```sql
-- Vérifier que l'utilisateur WordPress a les droits ALTER
SHOW GRANTS FOR CURRENT_USER;
```

L'utilisateur doit avoir le privilège `ALTER` sur la base de données.

### Vérifiez la version MySQL
```sql
SELECT VERSION();
```

Si version < 5.6, contactez votre hébergeur pour une mise à jour.

### Désactivez temporairement les caches
- Cache WordPress (plugins comme WP Super Cache, W3 Total Cache)
- Cache serveur (Redis, Memcached)
- Cache Cloudflare (si utilisé)

## 📝 Note technique

La fonction `dbDelta()` dans WordPress ne met à jour que la structure lors de la **création initiale** de la table. Pour ajouter des colonnes à une table existante, il faut utiliser `ALTER TABLE`.

C'est pourquoi j'ai ajouté :
1. Une fonction de migration automatique dans `function.php`
2. Un script SQL manuel dans `migration-ajout-colonnes.sql`

## ⏱️ Temps de résolution estimé
- **Option A** (auto) : 30 secondes
- **Option B** (manuel) : 2 minutes

## ✅ Après correction

Une fois les colonnes ajoutées :
- ✅ Les nouveaux formulaires enregistreront toutes les informations
- ✅ L'affichage dans "Mon compte" sera complet
- ✅ Vous pourrez compléter les anciennes commandes via `admin-edit-prestations.php`

---

**Dernière mise à jour** : 13 janvier 2026
