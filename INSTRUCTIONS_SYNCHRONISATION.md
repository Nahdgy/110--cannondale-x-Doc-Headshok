# Instructions pour ajouter la synchronisation des prestations

## Étapes à suivre pour chaque formulaire

Pour chaque formulaire de prestation, ajoutez ces lignes **APRÈS** l'envoi réussi du mail, mais **AVANT** la redirection ou l'affichage du message de succès.

### Code à ajouter dans chaque fichier :

#### 1. form-base.html (Entretien Base)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Entretien Base');
}
```

#### 2. form-fourche.html (Service Fourche)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Fourche');
}
```

#### 3. form-amortisseur1.html et form-amortisseur2.html (Service Amortisseur)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Amortisseur');
}
```

#### 4. form-lefty.html (Service Lefty)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Lefty');
}
```

#### 5. form-lefty-hybrid.html (Service Lefty Hybrid)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Lefty Hybrid');
}
```

#### 6. form-lefty-ocho.html (Service Lefty Ocho)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Lefty Ocho');
}
```

#### 7. form-fatty.html (Service Fatty)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Fatty');
}
```

#### 8. form-fox.html (Service Fox)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Fox');
}
```

#### 9. form-soufflet.html (Remplacement Soufflet)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Soufflet');
}
```

#### 10. form-tige-selle.html (Service Tige de Selle)
```javascript
// Après l'envoi réussi du mail, ajouter :
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'Tige de Selle');
}
```

## Emplacement exact où ajouter le code

Recherchez dans chaque formulaire la section qui ressemble à ceci :
```javascript
// Après l'envoi réussi
$successMessage.style.display = 'block';
$form.style.display = 'none';
```

Et ajoutez le code de synchronisation **AVANT** ces lignes, comme ceci :
```javascript
// NOUVEAU CODE DE SYNCHRONISATION ICI
if (typeof synchroniserPrestation !== 'undefined') {
    const formData = new FormData(form);
    synchroniserPrestation(formData, 'TYPE_DE_PRESTATION');
}

// Code existant
$successMessage.style.display = 'block';
$form.style.display = 'none';
```

## Vérifications importantes

1. **Script inclus** : Le fichier `synchronisation-prestations.js` doit être chargé sur toutes les pages contenant des formulaires
2. **WordPress** : Le code dans `functions.php` doit être actif pour que les variables AJAX soient disponibles
3. **Base de données** : La table `wp_demandes_prestations` doit être créée (code fourni dans `my_account.php`)
4. **Utilisateur connecté** : La synchronisation ne fonctionne que pour les utilisateurs connectés

## Test de fonctionnement

1. Connectez-vous sur le site
2. Remplissez et soumettez un formulaire de prestation
3. Allez sur la page "Mon Compte" 
4. Cliquez sur "Historique des prestations"
5. Vérifiez que la prestation apparaît dans la liste

## Dépannage

- Ouvrez la console du navigateur (F12) pour voir les éventuels messages d'erreur
- Vérifiez que `ajax_object` est défini dans la console
- Vérifiez que la fonction `synchroniserPrestation` est disponible
- Assurez-vous que l'utilisateur est bien connecté