# Solution .htaccess pour erreur 403 admin-ajax.php

## Problème détecté
L'erreur 403 sur admin-ajax.php indique que votre fichier .htaccess bloque les requêtes AJAX WordPress.

## Solution immédiate

### 1. Vérifiez votre .htaccess
Ajoutez ces règles au début de votre fichier .htaccess (avant les règles WordPress) :

```apache
# Autoriser admin-ajax.php
<Files "admin-ajax.php">
    Order allow,deny
    Allow from all
    Satisfy any
</Files>

# Autoriser wp-admin pour AJAX
RewriteRule ^wp-admin/admin-ajax\.php$ - [L]
```

### 2. .htaccess WordPress standard
Si le problème persiste, remplacez votre .htaccess par cette version standard :

```apache
# BEGIN WordPress
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
# END WordPress

# Autoriser admin-ajax.php
<Files "admin-ajax.php">
    Order allow,deny
    Allow from all
    Satisfy any
</Files>
```

### 3. Vérification du nonce
Dans votre code JavaScript, vérifiez que le nonce est correctement passé :

```javascript
// Exemple correct de requête AJAX WordPress
jQuery.ajax({
    url: ajax_object.ajax_url, // ou wp_admin_url('admin-ajax.php')
    type: 'POST',
    data: {
        action: 'your_action_name',
        nonce: ajax_object.nonce,
        // vos autres données
    },
    success: function(response) {
        // traitement du succès
    },
    error: function(xhr, status, error) {
        console.log('Erreur AJAX:', error);
    }
});
```

### 4. Localisation du script WordPress
Assurez-vous que vos scripts sont correctement localisés :

```php
// Dans votre functions.php ou plugin
function enqueue_ajax_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('your-script', 'path/to/your/script.js', array('jquery'), '1.0', true);
    
    wp_localize_script('your-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('your_nonce_action')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_script');
```

## Tests à effectuer

1. **Test .htaccess** : Renommez .htaccess en .htaccess-backup et testez
2. **Test console** : Vérifiez les erreurs JavaScript dans la console
3. **Test direct** : Accédez directement à `https://doc-headshok.com/wp-admin/admin-ajax.php`
4. **Test nonce** : Vérifiez que le nonce est valide et non expiré

## Vérifications supplémentaires

### Plugin de sécurité
- Wordfence : Settings > Firewall > Rate Limiting
- Sucuri : Vérifiez les règles de blocage AJAX
- iThemes Security : Vérifiez les restrictions wp-admin

### Hébergeur
- Certains hébergeurs bloquent admin-ajax.php par défaut
- Contactez le support pour vérifier les règles serveur

### Permissions
```bash
# Vérifiez les permissions du dossier wp-admin
chmod 755 wp-admin/
chmod 644 wp-admin/admin-ajax.php
```