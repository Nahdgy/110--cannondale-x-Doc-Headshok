<?php
add_action('wp_ajax_envoyer_form_roue', 'envoyer_form_roue');
add_action('wp_ajax_nopriv_envoyer_form_roue', 'envoyer_form_roue');

// Vérification de la connexion utilisateur
add_action('wp_ajax_check_user_logged_in', 'check_user_logged_in');
add_action('wp_ajax_nopriv_check_user_logged_in', 'check_user_logged_in');

function check_user_logged_in() {
	wp_send_json_success(['logged_in' => is_user_logged_in()]);
}

//Formulaire de dévoilage roue
function envoyer_form_roue() {
	// Récupération des données
	$remarque = isset($_POST['remarque']) ? sanitize_textarea_field($_POST['remarque']) : '';
	
	// Champs utilisateur additionnels
	$user_email = '';
	$user_nom = '';
	$user_prenom = '';
	$user_tel = '';
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$user_email = $current_user->user_email;
		$user_nom = $current_user->last_name;
		$user_prenom = $current_user->first_name;
		$user_tel = get_user_meta($current_user->ID, 'billing_phone', true);
	} else {
		if (!empty($_POST['email'])) $user_email = sanitize_email($_POST['email']);
		if (!empty($_POST['nom'])) $user_nom = sanitize_text_field($_POST['nom']);
		if (!empty($_POST['prenom'])) $user_prenom = sanitize_text_field($_POST['prenom']);
		if (!empty($_POST['telephone'])) $user_tel = sanitize_text_field($_POST['telephone']);
	}

	// Génération du numéro de commande unique
	$order_number = 'CMD-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);

	// Construction du message avec formatage amélioré
	$message = "Numéro de commande : $order_number\n";
	$message .= "=== DEMANDE DE DÉVOILAGE ROUE ===\n\n";
	$message .= "Prestation : Dévoilage roue\n";
	$message .= "Prix : 19€\n\n";
	
	$message .= "=== TOLÉRANCES DT SWISS ===\n";
	$message .= "• Voile : 0.4 mm\n";
	$message .= "• Saut : 0.5 mm\n";
	$message .= "• Centrage : 0.2 mm\n\n";
	
	if (!empty($remarque)) {
		$message .= "=== REMARQUES ===\n";
		$message .= "$remarque\n\n";
	}
	
	// Informations utilisateur
	$message .= "=== INFORMATIONS CLIENT ===\n";
	if ($user_email) $message .= "Email : $user_email\n";
	if ($user_nom) $message .= "Nom : $user_nom\n";
	if ($user_prenom) $message .= "Prénom : $user_prenom\n";
	if ($user_tel) $message .= "Téléphone : $user_tel\n";

    // Destinataire
    $admin_email = get_option('admin_email');

    // Gestion des fichiers joints (multiple)
    $attachments = [];
    if (!empty($_FILES['piece_jointe']['tmp_name'])) {
		if (is_array($_FILES['piece_jointe']['tmp_name'])) {
			// Gestion de plusieurs fichiers
			foreach ($_FILES['piece_jointe']['tmp_name'] as $key => $tmp_name) {
				if (!empty($tmp_name)) {
					$file = [
						'name'     => $_FILES['piece_jointe']['name'][$key],
						'type'     => $_FILES['piece_jointe']['type'][$key],
						'tmp_name' => $_FILES['piece_jointe']['tmp_name'][$key],
						'error'    => $_FILES['piece_jointe']['error'][$key],
						'size'     => $_FILES['piece_jointe']['size'][$key]
					];
					$uploaded = wp_handle_upload($file, ['test_form' => false]);
					if (!isset($uploaded['error']) && isset($uploaded['file'])) {
						$attachments[] = $uploaded['file'];
					}
				}
			}
		} else {
			// Gestion d'un seul fichier
			$uploaded = wp_handle_upload($_FILES['piece_jointe'], ['test_form' => false]);
			if (!isset($uploaded['error']) && isset($uploaded['file'])) {
				$attachments[] = $uploaded['file'];
			}
		}
    }

    // Envoi à l'admin avec l'utilisateur en copie (CC)
	$subject = 'Nouvelle demande de prestation - Dévoilage roue';
	
	// Ajout infos utilisateur dans l'objet
	$infos = [];
	if ($user_email) $infos[] = 'Email: ' . $user_email;
	if ($user_nom) $infos[] = 'Nom: ' . $user_nom;
	if ($user_prenom) $infos[] = 'Prénom: ' . $user_prenom;
	if ($user_tel) $infos[] = 'Tel: ' . $user_tel;
	if (!empty($infos)) $subject .= ' | ' . implode(' | ', $infos);

    $headers = '';
    if ($user_email) {
        $headers = array('Cc: ' . $user_email);
    }
	wp_mail($admin_email, $subject, $message, $headers, $attachments);

	// Retourne le numéro de commande dans la réponse AJAX
	wp_send_json_success(['message' => 'Demande envoyée avec succès !', 'order_number' => $order_number]);
}
