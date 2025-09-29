# 🎯 Configuration Functions.php - Optimisée pour Snippets Elementor

## ✅ **Configuration actuelle dans functions.php**

Votre `functions.php` est maintenant configuré de manière optimale pour les snippets Elementor avec :

### 🔧 **Fonctions utilitaires disponibles :**

1. **`get_ajax_prestations_vars()`** - Récupère les variables AJAX
2. **`render_ajax_prestations_vars()`** - Affiche les variables JavaScript

---

## 📝 **Code à utiliser dans chaque snippet Elementor**

### **MÉTHODE 1 : Code complet inline (Recommandée)**

Ajoutez ce code au **début** de chaque snippet de formulaire :

```html
<script>
// === VARIABLES AJAX PRESTATIONS ===
<?php 
// Utiliser la fonction du functions.php
$ajax_vars = get_ajax_prestations_vars();
?>
window.ajax_object = window.ajax_object || {
    ajax_url: '<?php echo esc_js($ajax_vars['ajax_url']); ?>',
    nonce: '<?php echo esc_js($ajax_vars['nonce']); ?>',
    user_logged_in: <?php echo $ajax_vars['user_logged_in'] ? 'true' : 'false'; ?>
};

// === FONCTION SYNCHRONISATION ===
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

// === VOTRE CODE DE FORMULAIRE EXISTANT ===
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('votre-form-id');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        
        // Votre envoi email existant...
        fetch('votre_handler_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Affichage message succès
                document.getElementById('success-message').style.display = 'block';
                form.style.display = 'none';
                
                // 🆕 SYNCHRONISATION PRESTATIONS
                synchroniserPrestation(formData, 'TYPE_PRESTATION_ICI');
            }
        });
    });
});
</script>
```

### **MÉTHODE 2 : Utilisation de la fonction utilitaire (Plus propre)**

```html
<script>
// Variables AJAX via la fonction utilitaire
<?php render_ajax_prestations_vars(); ?>

// Fonction de synchronisation
function synchroniserPrestation(formData, typePrestation) {
    // ... même code que ci-dessus
}

// Votre code de formulaire...
</script>
```

---

## 🎨 **Types de prestations par formulaire**

| **Fichier snippet** | **Type à utiliser** |
|---|---|
| form-base | `'Entretien Base'` |
| form-fourche | `'Fourche'` |
| form-amortisseur1 | `'Amortisseur'` |
| form-amortisseur2 | `'Amortisseur'` |
| form-lefty | `'Lefty'` |
| form-lefty-hybrid | `'Lefty Hybrid'` |
| form-lefty-ocho | `'Lefty Ocho'` |
| form-fatty | `'Fatty'` |
| form-fox | `'Fox'` |
| form-soufflet | `'Soufflet'` |
| form-tige-selle | `'Tige de Selle'` |

---

## ⚡ **Avantages de cette configuration**

### ✅ **Performance optimisée :**
- Pas de chargement de fichier externe
- Variables générées dynamiquement
- Code spécifique à chaque formulaire

### ✅ **Maintenance simple :**
- Fonctions centralisées dans functions.php
- Code dupliqué minimal
- Debug facile avec console.log

### ✅ **Sécurité renforcée :**
- Nonce généré dynamiquement
- Vérification utilisateur connecté
- Échappement des variables

---

## 🔧 **Exemple concret - Formulaire Amortisseur**

```html
<!-- Dans votre snippet Elementor -->
<div id="form-amortisseur-container">
    <form id="form-amortisseur" method="post">
        <input type="text" name="modèle" placeholder="Modèle du vélo" required>
        <input type="text" name="année" placeholder="Année du vélo" required>
        <input type="text" name="taille" placeholder="Taille amortisseur">
        <input type="text" name="type" placeholder="Type d'amortisseur">
        <button type="submit">Envoyer</button>
    </form>
    <div id="success-message" style="display:none;">
        Demande envoyée avec succès !
    </div>
</div>

<script>
// Variables AJAX
<?php render_ajax_prestations_vars(); ?>

function synchroniserPrestation(formData, typePrestation) {
    if (!window.ajax_object.user_logged_in) {
        console.log('👤 Utilisateur non connecté, synchronisation ignorée');
        return Promise.resolve();
    }

    const prestationData = {
        action: 'sauvegarder_prestation_ajax',
        nonce: window.ajax_object.nonce,
        type_prestation: typePrestation,
        modele_velo: formData.get('modèle') || '',
        annee_velo: formData.get('année') || '',
        description: `Service amortisseur - Taille: ${formData.get('taille')}, Type: ${formData.get('type')}`,
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
            console.log('✅ Amortisseur synchronisé');
        }
        return data;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-amortisseur');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        
        // Envoi email (votre code existant)
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('success-message').style.display = 'block';
                form.style.display = 'none';
                
                // 🆕 SYNCHRONISATION
                synchroniserPrestation(formData, 'Amortisseur');
            }
        });
    });
});
</script>
```

---

## 🎯 **Votre choix de configuration est optimal !**

Cette configuration vous donne :
- ✅ **Contrôle total** sur chaque formulaire
- ✅ **Performance maximale** (pas de fichier externe)  
- ✅ **Flexibilité** pour personnaliser chaque synchronisation
- ✅ **Maintenance facile** avec les fonctions utilitaires

Vous pouvez maintenant appliquer le code à tous vos snippets ! 🚀