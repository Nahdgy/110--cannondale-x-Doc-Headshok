/**
 * Fonction de synchronisation des prestations avec l'historique utilisateur
 * À inclure dans tous les formulaires de prestations
 */

function synchroniserPrestation(formData, typePrestation) {
    // Préparer les données à envoyer
    const prestationData = {
        action: 'sauvegarder_prestation_ajax',
        nonce: ajax_object.nonce,
        type_prestation: typePrestation,
        modele_velo: formData.get('modèle') || formData.get('modele') || '',
        annee_velo: formData.get('année') || formData.get('annee') || '',
        description: '',
        statut: 'attente'
    };

    // Ajouter les détails spécifiques selon le type de prestation
    switch(typePrestation) {
        case 'Entretien Base':
            prestationData.description = `Entretien de base demandé pour ${prestationData.modele_velo} (${prestationData.annee_velo})`;
            break;
            
        case 'Fourche':
            const tailleFourche = formData.get('taille') || '';
            const typeFourche = formData.get('type') || '';
            prestationData.description = `Service fourche - Taille: ${tailleFourche}, Type: ${typeFourche}`;
            break;
            
        case 'Amortisseur':
            const tailleAmortisseur = formData.get('taille') || '';
            const typeAmortisseur = formData.get('type') || '';
            prestationData.description = `Service amortisseur - Taille: ${tailleAmortisseur}, Type: ${typeAmortisseur}`;
            break;
            
        case 'Lefty':
            const modeleLefty = formData.get('modele_lefty') || formData.get('modèle_lefty') || '';
            prestationData.description = `Service Lefty - Modèle: ${modeleLefty}`;
            break;
            
        case 'Lefty Hybrid':
            const modeleHybrid = formData.get('modele_hybrid') || formData.get('modèle_hybrid') || '';
            prestationData.description = `Service Lefty Hybrid - Modèle: ${modeleHybrid}`;
            break;
            
        case 'Lefty Ocho':
            const modeleOcho = formData.get('modele_ocho') || formData.get('modèle_ocho') || '';
            prestationData.description = `Service Lefty Ocho - Modèle: ${modeleOcho}`;
            break;
            
        case 'Fatty':
            const modelePatty = formData.get('modele_fatty') || formData.get('modèle_fatty') || '';
            prestationData.description = `Service Fatty - Modèle: ${modelePatty}`;
            break;
            
        case 'Fox':
            const modeFox = formData.get('mode') || '';
            prestationData.description = `Service Fox - Mode: ${modeFox}`;
            break;
            
        case 'Soufflet':
            const tailleSoufflet = formData.get('taille') || '';
            prestationData.description = `Remplacement soufflet - Taille: ${tailleSoufflet}`;
            break;
            
        case 'Tige de Selle':
            const typeTige = formData.get('type') || '';
            prestationData.description = `Service tige de selle - Type: ${typeTige}`;
            break;
            
        default:
            prestationData.description = `Prestation ${typePrestation} demandée`;
    }

    // Envoyer la requête AJAX
    return fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(prestationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Prestation synchronisée avec succès:', data);
        } else {
            console.error('Erreur lors de la synchronisation:', data);
        }
        return data;
    })
    .catch(error => {
        console.error('Erreur réseau lors de la synchronisation:', error);
    });
}

// Fonction utilitaire pour ajouter la synchronisation à un formulaire existant
function ajouterSynchronisationFormulaire(formSelector, typePrestation) {
    const form = document.querySelector(formSelector);
    if (!form) {
        console.warn(`Formulaire ${formSelector} non trouvé`);
        return;
    }

    // Ajouter un écouteur d'événement sur la soumission du formulaire
    form.addEventListener('submit', function(e) {
        // Ne pas empêcher la soumission normale du formulaire
        // Juste ajouter la synchronisation en parallèle
        
        const formData = new FormData(form);
        
        // Synchroniser avec l'historique (en arrière-plan)
        synchroniserPrestation(formData, typePrestation)
            .then(() => {
                console.log(`Prestation ${typePrestation} synchronisée avec l'historique`);
            });
    });
}

// Auto-initialisation si la page contient des formulaires connus
document.addEventListener('DOMContentLoaded', function() {
    // Mapping des formulaires avec leurs types de prestations
    const formulaires = {
        '#form-entretien-base': 'Entretien Base',
        '#form-fourche': 'Fourche', 
        '#form-amortisseur1': 'Amortisseur',
        '#form-amortisseur2': 'Amortisseur',
        '#form-lefty': 'Lefty',
        '#form-lefty-hybrid': 'Lefty Hybrid',
        '#form-lefty-ocho': 'Lefty Ocho',
        '#form-fatty': 'Fatty',
        '#form-fox': 'Fox',
        '#form-soufflet': 'Soufflet',
        '#form-tige-selle': 'Tige de Selle'
    };

    // Ajouter la synchronisation à tous les formulaires trouvés
    Object.entries(formulaires).forEach(([selector, type]) => {
        ajouterSynchronisationFormulaire(selector, type);
    });
});