<?php
add_action('wp_ajax_envoyer_form_fourche', 'envoyer_form_fourche');
add_action('wp_ajax_nopriv_envoyer_form_fourche', 'envoyer_form_fourche');

function envoyer_form_fourche() {
    // Récupération des données
    $url = isset($_POST['url']) ? sanitize_text_field($_POST['url']) : '';
    $type_fourche = isset($_POST['type_fourche']) ? sanitize_text_field($_POST['type_fourche']) : '';
    $prestation = isset($_POST['prestation']) ? sanitize_text_field($_POST['prestation']) : '';
    $usages = isset($_POST['usage']) ? $_POST['usage'] : [];
    $date_revision = isset($_POST['date_revision']) ? sanitize_text_field($_POST['date_revision']) : '';
    $poids_pilote = isset($_POST['poids_pilote']) ? sanitize_text_field($_POST['poids_pilote']) : '';
    $modele_annee = isset($_POST['modele_annee']) ? sanitize_text_field($_POST['modele_annee']) : '';
    $remarques = isset($_POST['remarques']) ? sanitize_textarea_field($_POST['remarques']) : '';

    // Construction du message
    $message = "Type de fourche : $type_fourche\n";
    $message .= "Prestation : $prestation\n";
    $message .= "Options supplémentaires : ".(is_array($usages) ? implode(', ', array_map('sanitize_text_field', $usages)) : '')."\n";
    $message .= "Date de la dernière révision : $date_revision\n";
    $message .= "Poids du pilote : $poids_pilote kg\n";
    $message .= "Modèle et année du vélo : $modele_annee\n";
    $message .= "Remarques : $remarques\n";

    // Destinataire
    $admin_email = get_option('admin_email');
    $user_email = is_user_logged_in() ? wp_get_current_user()->user_email : '';

    // Gestion du fichier joint
    $attachments = [];
    if (!empty($_FILES['fichier']['tmp_name'])) {
        $uploaded = wp_handle_upload($_FILES['fichier'], ['test_form' => false]);
        if (!isset($uploaded['error']) && isset($uploaded['file'])) {
            $attachments[] = $uploaded['file'];
        }
    }

    // Envoi à l'admin
    $subject = 'Nouvelle demande de prestation fourche';
    // Ajout du contexte selon l'URL
    $current_url = $url;
    if (strpos($current_url, 'fourche-lefty-ocho') !== false) {
        $subject .= ' - Lefty Ocho/Oliver';
    } elseif (strpos($current_url, 'fourche-lefty-hybrid') !== false) {
        $subject .= ' - Lefty Hybrid';
    } elseif (strpos($current_url, 'fourche-fatty') !== false) {
        $subject .= ' - Fatty';
    } elseif (strpos($current_url, 'fourche-lefty-a-soufflet') !== false) {
        $subject .= ' - Lefty A Soufflet';
    }  else {
        $subject .= ' - '. $current_url;
    }
    wp_mail($admin_email, $subject, $message, '', $attachments);

    // Envoi à l'utilisateur connecté (optionnel)
    if ($user_email) {
        wp_mail($user_email, 'Copie de votre demande de prestation fourche', $message, '', $attachments);
    }

    wp_send_json_success('Formulaire envoyé avec succès !');
}
