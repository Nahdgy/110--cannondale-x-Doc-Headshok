# Debug - Erreur 403 Forbidden

## 🔧 Actions de dépannage par ordre de priorité

### 1. Vérification .htaccess
```bash
# Via FTP/cPanel, renommez .htaccess en .htaccess-backup
# Testez si le site fonctionne sans .htaccess
# Si oui, le problème vient des règles .htaccess
```

### 2. Vérification des permissions
```bash
# Permissions correctes :
Dossiers : 755 (ou 750)
Fichiers : 644 (ou 640)
wp-config.php : 600
```

### 3. Vérification plugins de sécurité
- Désactivez temporairement tous les plugins de sécurité
- Wordfence > Tools > Live Traffic (cherchez votre IP bloquée)
- Sucuri > Settings > Hardening
- iThemes Security > Logs

### 4. Vérification logs d'erreur
```bash
# Chemins courants des logs :
/public_html/error_log
/logs/error_log
cPanel > Error Logs
```

### 5. Vérification IP bloquée
- Contactez votre hébergeur
- Vérifiez si votre IP est dans une blacklist
- Testez depuis un VPN/proxy

## ⚡ Solutions rapides

### Si .htaccess est le problème :
```apache
# .htaccess minimal pour WordPress
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
```

### Si permissions sont le problème :
```bash
# Via FTP ou SSH
find /public_html -type d -exec chmod 755 {} \;
find /public_html -type f -exec chmod 644 {} \;
chmod 600 wp-config.php
```

### Si plugin de sécurité :
1. Accédez à votre base de données (phpMyAdmin)
2. Table `wp_options`
3. Désactivez le plugin : `active_plugins` > supprimez l'entrée du plugin

## 📋 Informations à collecter

- **Heure exacte** de l'erreur
- **Adresse IP** d'où vous accédez
- **Navigateur** utilisé
- **Actions** effectuées juste avant l'erreur
- **Fréquence** : toujours ou parfois ?
- **Pages affectées** : toutes ou spécifiques ?

## 🆘 Contacts urgents

1. **Hébergeur** : Support technique
2. **Développeur WordPress** : Si modifications récentes
3. **Logs serveur** : Via cPanel/WHM

## 📞 Questions à poser à l'hébergeur

1. "Y a-t-il eu des blocages de sécurité sur mon domaine ?"
2. "Mon IP est-elle bloquée temporairement ?"
3. "Y a-t-il eu des modifications serveur récentes ?"
4. "Pouvez-vous vérifier les logs d'accès pour mon domaine ?"