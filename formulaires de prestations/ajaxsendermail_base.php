<?php
add_action('wp_ajax_envoyer_form_fox', 'envoyer_form_fox');
add_action('wp_ajax_nopriv_envoyer_form_fox', 'envoyer_form_fox');

function envoyer_form_fox() {
    // Récupération des données
    $url = isset($_POST['url']) ? sanitize_text_field($_POST['url']) : '';
    $prestation = sanitize_text_field($_POST['option1']);
    $date_revision = sanitize_text_field($_POST['date_revision']);
    $poids_pilote = sanitize_text_field($_POST['poids_pilote']);
    $modele_annee = sanitize_text_field($_POST['modele_annee']);
    $remarques = sanitize_textarea_field($_POST['remarques']);

    // Construction du message
    $message = "Prestation : $prestation\n";
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
    $subject = 'Nouvelle demande de prestation';
    // Ajout du type d'amortisseur selon l'URL
    
    $current_url = $url;
    if (strpos($current_url, 'amortiseur-fox') !== false) {
        $subject .= ' - amortisseur fox';
    } elseif (strpos($current_url, 'amortiseur-rockshox') !== false) {
        $subject .= ' - amortisseur rockshox';
    } elseif (strpos($current_url, 'amortiseur-dt-swiss') !== false) {
        $subject .= ' - amortisseur-dt-swiss';
    } elseif (strpos($current_url, 'amortiseur-manitou') !== false) {
        $subject .= ' - amortisseur-manitou';
    } elseif (strpos($current_url, 'tige-de-selle-downlow') !== false) {
        $subject .= ' - tige-de-selle-downlow';
    } elseif (strpos($current_url, 'tige-de-selle-downlow') !== false) {
        $subject .= ' - tige-de-selle-downlow';
    } else {
        $subject .= $current_url;
    }
    wp_mail($admin_email, $subject, $message, '', $attachments);

    // Envoi à l'utilisateur connecté (optionnel)
    if ($user_email) {
        wp_mail($user_email, 'Copie de votre demande de prestation', $message, '', $attachments);
    }

    wp_send_json_success('Formulaire envoyé avec succès !');
}