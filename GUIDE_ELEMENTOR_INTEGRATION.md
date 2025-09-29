# 🎯 Guide d'intégration Elementor - Synchronisation Prestations

## 📋 Situation actuelle
- ✅ Formulaires dans des pages Elementor 
- ✅ Code sous forme de snippets HTML/CSS/JS
- ✅ Envoi d'emails via AJAX fonctionnel
- 🎯 **Objectif** : Ajouter la synchronisation avec l'historique

## 🚀 **MÉTHODE RECOMMANDÉE : Script inline dans chaque snippet**

### **Avantages de cette approche :**
- ✅ Pas de gestion de fichiers externes
- ✅ Tout reste dans les snippets Elementor
- ✅ Contrôle total sur chaque formulaire
- ✅ Pas de conflit de chargement

---

## 📝 **ÉTAPE 1 : Code à ajouter dans CHAQUE snippet de formulaire**

### **À placer AVANT la fermeture `</script>` de votre formulaire :**

```javascript
// === SYNCHRONISATION PRESTATIONS - DÉBUT ===
// Variables AJAX (à inclure une seule fois par page)
window.ajax_object = window.ajax_object || {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('sauvegarder_prestation_nonce'); ?>',
    user_logged_in: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>
};

// Fonction de synchronisation
function synchroniserPrestation(formData, typePrestation) {
    if (!window.ajax_object.user_logged_in) {
        console.log('👤 Utilisateur non connecté, synchronisation ignorée');
        return Promise.resolve();
    }

    const prestationData = {
        action: 'sauvegarder_prestation_ajax',
        nonce: window.ajax_object.nonce,
        type_prestation: typePrestation,
        modele_velo: formData.get('modèle') || formData.get('modele') || '',
        annee_velo: formData.get('année') || formData.get('annee') || '',
        description: '',
        statut: 'attente'
    };

    // Description personnalisée selon le type
    prestationData.description = `${typePrestation} demandé pour ${prestationData.modele_velo} (${prestationData.annee_velo})`;

    return fetch(window.ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(prestationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Prestation synchronisée:', typePrestation);
        } else {
            console.error('❌ Erreur synchronisation:', data);
        }
        return data;
    })
    .catch(error => {
        console.error('❌ Erreur réseau:', error);
    });
}
// === SYNCHRONISATION PRESTATIONS - FIN ===
```

---

## 📝 **ÉTAPE 2 : Modification du code d'envoi dans chaque formulaire**

### **Cherchez cette section dans vos snippets :**
```javascript
// Après l'envoi réussi du mail
$successMessage.style.display = 'block';
$form.style.display = 'none';
```

### **Remplacez par :**
```javascript
// Après l'envoi réussi du mail
$successMessage.style.display = 'block';
$form.style.display = 'none';

// 🆕 SYNCHRONISATION AVEC L'HISTORIQUE
synchroniserPrestation(formData, 'TYPE_DE_PRESTATION_ICI');
```

---

## 🎨 **ÉTAPE 3 : Types de prestations par formulaire**

### **Remplacez `TYPE_DE_PRESTATION_ICI` par :**

| **Formulaire** | **Type à utiliser** |
|---|---|
| `form-base.html` | `'Entretien Base'` |
| `form-fourche.html` | `'Fourche'` |
| `form-amortisseur1.html` | `'Amortisseur'` |
| `form-amortisseur2.html` | `'Amortisseur'` |
| `form-lefty.html` | `'Lefty'` |
| `form-lefty-hybrid.html` | `'Lefty Hybrid'` |
| `form-lefty-ocho.html` | `'Lefty Ocho'` |
| `form-fatty.html` | `'Fatty'` |
| `form-fox.html` | `'Fox'` |
| `form-soufflet.html` | `'Soufflet'` |
| `form-tige-selle.html` | `'Tige de Selle'` |

---

## 💡 **EXEMPLE COMPLET - Formulaire de base**

```html
<!-- Dans votre snippet Elementor -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const $form = document.getElementById('form-entretien-base');
    const $successMessage = document.getElementById('success-message');
    
    // === SYNCHRONISATION PRESTATIONS - DÉBUT ===
    window.ajax_object = window.ajax_object || {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('sauvegarder_prestation_nonce'); ?>',
        user_logged_in: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>
    };

    function synchroniserPrestation(formData, typePrestation) {
        if (!window.ajax_object.user_logged_in) {
            console.log('👤 Utilisateur non connecté, synchronisation ignorée');
            return Promise.resolve();
        }

        const prestationData = {
            action: 'sauvegarder_prestation_ajax',
            nonce: window.ajax_object.nonce,
            type_prestation: typePrestation,
            modele_velo: formData.get('modèle') || formData.get('modele') || '',
            annee_velo: formData.get('année') || formData.get('annee') || '',
            description: `${typePrestation} demandé pour ${formData.get('modèle')} (${formData.get('année')})`,
            statut: 'attente'
        };

        return fetch(window.ajax_object.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(prestationData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Entretien base synchronisé');
            }
            return data;
        });
    }
    // === SYNCHRONISATION PRESTATIONS - FIN ===

    $form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData($form);
        
        // Envoi email habituel...
        fetch('path/to/your/ajax/handler', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Afficher le message de succès
                $successMessage.style.display = 'block';
                $form.style.display = 'none';
                
                // 🆕 SYNCHRONISER AVEC L'HISTORIQUE
                synchroniserPrestation(formData, 'Entretien Base');
            }
        });
    });
});
</script>
```

---

## ✅ **AVANTAGES de cette méthode :**

1. **🔧 Simple à implémenter** - Juste copier/coller dans chaque snippet
2. **🎯 Ciblé** - Chaque formulaire gère sa propre synchronisation  
3. **🔒 Sécurisé** - Nonce et vérifications utilisateur
4. **📱 Compatible** - Fonctionne avec n'importe quelle configuration Elementor
5. **🚀 Performant** - Pas de fichier externe à charger
6. **🐛 Debuggable** - Messages console pour chaque étape

---

## 🔍 **TEST ET VÉRIFICATION :**

### **1. Test rapide :**
```javascript
// Dans la console du navigateur
console.log(window.ajax_object); // Doit afficher l'objet avec ajax_url et nonce
```

### **2. Test de synchronisation :**
1. Connectez-vous sur le site
2. Remplissez un formulaire de prestation  
3. Soumettez le formulaire
4. Ouvrez F12 > Console : devrait afficher "✅ [Type] synchronisé"
5. Allez sur Mon Compte > Historique des prestations
6. Vérifiez que la prestation apparaît

### **3. Debugging :**
- ❌ "Utilisateur non connecté" → L'utilisateur doit être connecté
- ❌ "ajax_object undefined" → Vérifier le code PHP dans le snippet
- ❌ "Erreur 403" → Problème de nonce, vérifier functions.php
- ❌ "Erreur 500" → Vérifier les logs WordPress

---

## 🎯 **RÉSULTAT FINAL :**

Chaque formulaire enverra l'email ET synchronisera automatiquement avec l'historique des prestations, visible dans la page Mon Compte ! 🚀