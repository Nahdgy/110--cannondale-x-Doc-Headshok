<?php
/**
 * Système de gestion des retours de commande
 * 
 * Permet aux clients de demander un retour de commande depuis leur espace Mon Compte
 * et aux administrateurs de gérer ces demandes depuis le back-office WordPress
 */

// Créer/Mettre à jour la table des retours avec les colonnes avoirs
function verifier_table_retours_avoirs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    // Vérifier si les colonnes existent
    $column_avoir = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'avoir_genere'");
    $column_montant = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'montant_avoir'");
    
    // Ajouter la colonne avoir_genere si elle n'existe pas
    if (empty($column_avoir)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN avoir_genere VARCHAR(50) NULL AFTER remboursement_effectue");
    }
    
    // Ajouter la colonne montant_avoir si elle n'existe pas
    if (empty($column_montant)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN montant_avoir DECIMAL(10,2) NULL AFTER avoir_genere");
    }
}

// Exécuter la vérification au chargement
add_action('admin_init', 'verifier_table_retours_avoirs');
add_action('wp_loaded', 'verifier_table_retours_avoirs');

// Générer un numéro de retour unique
function generer_numero_retour() {
    $prefix = 'RET';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    return $prefix . '-' . $date . '-' . $random;
}

// Fonction pour créer une demande de retour
function creer_demande_retour($data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    // Générer un numéro de retour unique
    $numero_retour = generer_numero_retour();
    
    // Vérifier que le numéro est unique
    while ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE numero_retour = %s", $numero_retour))) {
        $numero_retour = generer_numero_retour();
    }
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'numero_retour' => $numero_retour,
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'],
            'motif' => $data['motif'],
            'description' => $data['description'] ?? '',
            'produits_concernes' => $data['produits_concernes'],
            'montant_total' => $data['montant_total'],
            'statut' => 'en_attente'
        ),
        array('%s', '%d', '%d', '%s', '%s', '%s', '%f', '%s')
    );
    
    if ($result) {
        // Envoyer un email de confirmation au client
        envoyer_email_confirmation_retour($numero_retour, $data['user_id']);
        
        // Notifier l'admin
        notifier_admin_nouveau_retour($numero_retour);
        
        return $numero_retour;
    }
    
    return false;
}

// Récupérer les demandes de retour d'un utilisateur
function obtenir_retours_utilisateur($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_demande DESC",
        $user_id
    ));
    
    return $results;
}

// Récupérer toutes les demandes de retour (admin)
function obtenir_tous_retours($statut = null, $limit = 100, $offset = 0) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    if ($statut) {
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE statut = %s ORDER BY date_demande DESC LIMIT %d OFFSET %d",
            $statut, $limit, $offset
        ));
    } else {
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY date_demande DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ));
    }
    
    return $results;
}

// Mettre à jour le statut d'un retour
function mettre_a_jour_statut_retour($retour_id, $nouveau_statut, $notes_admin = '') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    $data = array('statut' => $nouveau_statut);
    
    if ($notes_admin) {
        $data['notes_admin'] = $notes_admin;
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $retour_id),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($result !== false) {
        // Notifier le client du changement de statut
        $retour = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $retour_id
        ));
        
        if ($retour) {
            notifier_client_changement_statut($retour);
        }
    }
    
    return $result !== false;
}

// ========== SYSTÈME DE BONS D'AVOIR ==========

// Générer un code d'avoir unique
function generer_code_avoir() {
    $prefix = 'AVOIR';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $code = $prefix . '-' . $date . '-' . $random;
    
    // Vérifier que le code n'existe pas déjà
    $existing = get_posts(array(
        'post_type' => 'shop_coupon',
        'title' => $code,
        'posts_per_page' => 1
    ));
    
    if (!empty($existing)) {
        return generer_code_avoir(); // Régénérer si le code existe
    }
    
    return $code;
}

// Créer un bon d'avoir WooCommerce
function creer_bon_avoir($retour_id, $montant, $user_email, $notes = '') {
    global $wpdb;
    
    // Récupérer les infos du retour
    $table_name = $wpdb->prefix . 'demandes_retours';
    $retour = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $retour_id
    ));
    
    if (!$retour) {
        return false;
    }
    
    // Générer un code unique
    $code_avoir = generer_code_avoir();
    
    // Créer le coupon WooCommerce
    $coupon = array(
        'post_title' => $code_avoir,
        'post_content' => $notes ? $notes : 'Bon d\'avoir suite au retour #' . $retour->numero_retour,
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'shop_coupon'
    );
    
    $coupon_id = wp_insert_post($coupon);
    
    if ($coupon_id) {
        // Configurer le coupon
        update_post_meta($coupon_id, 'discount_type', 'fixed_cart');
        update_post_meta($coupon_id, 'coupon_amount', $montant);
        update_post_meta($coupon_id, 'individual_use', 'yes');
        update_post_meta($coupon_id, 'usage_limit', '1');
        update_post_meta($coupon_id, 'usage_limit_per_user', '1');
        update_post_meta($coupon_id, 'email_restrictions', array($user_email));
        update_post_meta($coupon_id, 'date_expires', strtotime('+1 year')); // Valable 1 an
        
        // Métadonnées personnalisées
        update_post_meta($coupon_id, '_est_avoir', 'yes');
        update_post_meta($coupon_id, '_retour_id', $retour_id);
        update_post_meta($coupon_id, '_date_emission', current_time('mysql'));
        
        // Lier l'avoir au retour
        $wpdb->update(
            $table_name,
            array('avoir_genere' => $code_avoir, 'montant_avoir' => $montant),
            array('id' => $retour_id),
            array('%s', '%f'),
            array('%d')
        );
        
        // Envoyer l'email de notification
        notifier_client_emission_avoir($code_avoir, $montant, $user_email, $retour);
        
        return $code_avoir;
    }
    
    return false;
}

// Récupérer les avoirs d'un utilisateur
function obtenir_avoirs_utilisateur($user_email) {
    if (empty($user_email)) {
        return array();
    }
    
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_est_avoir',
                'value' => 'yes'
            )
        )
    );
    
    $coupons = get_posts($args);
    $user_avoirs = array();
    
    foreach ($coupons as $coupon_post) {
        $coupon = new WC_Coupon($coupon_post->ID);
        $email_restrictions = $coupon->get_email_restrictions();
        
        if (!empty($email_restrictions) && in_array($user_email, $email_restrictions)) {
            $usage_count = $coupon->get_usage_count();
            $usage_limit = $coupon->get_usage_limit();
            
            // Vérifier si l'avoir n'a pas été utilisé
            if ($usage_count < $usage_limit) {
                $user_avoirs[] = array(
                    'code' => $coupon_post->post_title,
                    'montant' => $coupon->get_amount(),
                    'date_expiration' => $coupon->get_date_expires(),
                    'retour_id' => get_post_meta($coupon_post->ID, '_retour_id', true),
                    'date_emission' => get_post_meta($coupon_post->ID, '_date_emission', true),
                    'description' => $coupon_post->post_content,
                    'utilise' => $usage_count >= $usage_limit
                );
            }
        }
    }
    
    return $user_avoirs;
}

// Email de notification d'émission d'avoir
function notifier_client_emission_avoir($code_avoir, $montant, $user_email, $retour) {
    $user = get_user_by('email', $user_email);
    $user_name = $user ? $user->display_name : 'Cher client';
    
    $subject = '💰 Vous avez reçu un bon d\'avoir - ' . $code_avoir;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 3px; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 5px; margin: 20px 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .montant { font-size: 36px; color: #4CAF50; font-weight: bold; margin: 20px 0; text-align: center; }
            .info-box { background: #fff; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; }
            .button { display: inline-block; background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; color: #999; margin-top: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>💰 Bon d'Avoir Émis !</h1>
                <p>Bonjour " . esc_html($user_name) . ",</p>
            </div>
            <div class='content'>
                <p>Suite à votre demande de retour <strong>#" . $retour->numero_retour . "</strong>, nous avons le plaisir de vous informer qu'un bon d'avoir a été émis.</p>
                
                <div class='montant'>" . wc_price($montant) . "</div>
                
                <div class='info-box'>
                    <p><strong>Code d'avoir :</strong></p>
                    <div class='code'>" . $code_avoir . "</div>
                </div>
                
                <p><strong>📋 Comment utiliser votre bon d'avoir ?</strong></p>
                <ol>
                    <li>Connectez-vous à votre compte</li>
                    <li>Ajoutez vos produits au panier</li>
                    <li>Lors du paiement, entrez le code d'avoir</li>
                    <li>Le montant sera automatiquement déduit</li>
                </ol>
                
                <div class='info-box'>
                    <p><strong>⏰ Validité :</strong> Ce bon d'avoir est valable 1 an</p>
                    <p><strong>🎯 Utilisation :</strong> Unique - Ne peut être utilisé qu'une seule fois</p>
                </div>
                
                <p style='text-align: center;'>
                    <a href='" . esc_url(wc_get_page_permalink('shop')) . "' class='button'>Commencer mes achats</a>
                </p>
                
                <p>Vous retrouverez ce bon d'avoir dans votre espace Mon Compte, section \"Mes Avoirs\".</p>
                
                <p>Cordialement,<br>L'équipe Doc-Headshok</p>
            </div>
            <div class='footer'>
                <p>Merci pour votre confiance !</p>
                <p>Doc-Headshok - Spécialiste Cannondale</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($user_email, $subject, $message, $headers);
}

// AJAX : Créer une demande de retour
add_action('wp_ajax_creer_demande_retour', 'creer_demande_retour_ajax');
function creer_demande_retour_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'retour_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    // Vérifier que l'utilisateur est connecté
    if (!is_user_logged_in()) {
        wp_send_json_error('Vous devez être connecté');
        return;
    }
    
    $user_id = get_current_user_id();
    $order_id = intval($_POST['order_id']);
    
    // Vérifier que la commande appartient à l'utilisateur
    $order = wc_get_order($order_id);
    if (!$order || $order->get_customer_id() != $user_id) {
        wp_send_json_error('Commande non valide');
        return;
    }
    
    // Vérifier que la commande est éligible au retour (completed, moins de 30 jours)
    if (!$order->has_status('completed')) {
        wp_send_json_error('Cette commande n\'est pas encore livrée');
        return;
    }
    
    $date_commande = $order->get_date_completed();
    if (!$date_commande) {
        $date_commande = $order->get_date_created();
    }
    
    $jours_depuis_commande = (time() - $date_commande->getTimestamp()) / (60 * 60 * 24);
    if ($jours_depuis_commande > 30) {
        wp_send_json_error('Le délai de retour (30 jours) est dépassé');
        return;
    }
    
    // Préparer les données
    $motif = sanitize_text_field($_POST['motif']);
    $description = sanitize_textarea_field($_POST['description']);
    $produits_ids = isset($_POST['produits']) ? $_POST['produits'] : array();
    
    if (empty($produits_ids)) {
        wp_send_json_error('Veuillez sélectionner au moins un produit');
        return;
    }
    
    // Calculer le montant total des produits concernés
    $montant_total = 0;
    $produits_details = array();
    
    foreach ($order->get_items() as $item_id => $item) {
        if (in_array($item_id, $produits_ids)) {
            $montant_total += $item->get_total();
            $produits_details[] = array(
                'item_id' => $item_id,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total()
            );
        }
    }
    
    $data = array(
        'user_id' => $user_id,
        'order_id' => $order_id,
        'motif' => $motif,
        'description' => $description,
        'produits_concernes' => json_encode($produits_details),
        'montant_total' => $montant_total
    );
    
    $numero_retour = creer_demande_retour($data);
    
    if ($numero_retour) {
        wp_send_json_success(array(
            'message' => 'Demande de retour créée avec succès',
            'numero_retour' => $numero_retour
        ));
    } else {
        wp_send_json_error('Erreur lors de la création de la demande');
    }
}

// AJAX : Mettre à jour le statut (admin uniquement)
add_action('wp_ajax_maj_statut_retour', 'maj_statut_retour_ajax');
function maj_statut_retour_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'admin_retour_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    $retour_id = intval($_POST['retour_id']);
    $statut = sanitize_text_field($_POST['statut']);
    $notes = sanitize_textarea_field($_POST['notes']);
    
    $result = mettre_a_jour_statut_retour($retour_id, $statut, $notes);
    
    if ($result) {
        wp_send_json_success('Statut mis à jour avec succès');
    } else {
        wp_send_json_error('Erreur lors de la mise à jour');
    }
}

// AJAX : Générer un bon d'avoir (admin uniquement)
add_action('wp_ajax_generer_bon_avoir', 'generer_bon_avoir_ajax');
function generer_bon_avoir_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'admin_retour_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    $retour_id = intval($_POST['retour_id']);
    $montant = floatval($_POST['montant']);
    $user_email = sanitize_email($_POST['user_email']);
    $notes = sanitize_textarea_field($_POST['notes']);
    
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error('Email invalide');
        return;
    }
    
    if ($montant <= 0) {
        wp_send_json_error('Montant invalide');
        return;
    }
    
    $code_avoir = creer_bon_avoir($retour_id, $montant, $user_email, $notes);
    
    if ($code_avoir) {
        wp_send_json_success(array(
            'message' => 'Bon d\'avoir généré avec succès',
            'code' => $code_avoir,
            'montant' => $montant
        ));
    } else {
        wp_send_json_error('Erreur lors de la génération de l\'avoir');
    }
}

// Email de confirmation au client
function envoyer_email_confirmation_retour($numero_retour, $user_id) {
    global $wpdb;
    
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    $retour = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE numero_retour = %s",
        $numero_retour
    ));
    
    if (!$retour) return;
    
    $order = wc_get_order($retour->order_id);
    
    $subject = '📦 Demande de retour enregistrée - ' . $numero_retour;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF3F22; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; }
            .numero { font-size: 24px; font-weight: bold; color: #FF3F22; margin: 20px 0; }
            .info { margin: 10px 0; }
            .label { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Demande de retour enregistrée</h1>
            </div>
            <div class='content'>
                <p>Bonjour " . esc_html($user->first_name) . ",</p>
                
                <p>Nous avons bien reçu votre demande de retour.</p>
                
                <div class='numero'>N° de retour : " . $numero_retour . "</div>
                
                <div class='info'>
                    <span class='label'>Commande concernée :</span> #" . $order->get_order_number() . "
                </div>
                
                <div class='info'>
                    <span class='label'>Motif :</span> " . esc_html($retour->motif) . "
                </div>
                
                <div class='info'>
                    <span class='label'>Montant :</span> " . wc_price($retour->montant_total) . "
                </div>
                
                <p><strong>Prochaines étapes :</strong></p>
                <ol>
                    <li>Notre équipe va examiner votre demande</li>
                    <li>Vous recevrez un email avec les instructions de retour</li>
                    <li>Une fois le colis reçu, nous procéderons au remboursement</li>
                </ol>
                
                <p>Vous pouvez suivre l'état de votre demande dans votre espace Mon Compte.</p>
                
                <p>Cordialement,<br>L'équipe Doc-Headshok</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($user->user_email, $subject, $message, $headers);
}

// Notification admin
function notifier_admin_nouveau_retour($numero_retour) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_retours';
    $retour = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE numero_retour = %s",
        $numero_retour
    ));
    
    if (!$retour) return;
    
    $user = get_userdata($retour->user_id);
    $order = wc_get_order($retour->order_id);
    
    $admin_email = get_option('admin_email');
    $subject = '🔄 Nouvelle demande de retour - ' . $numero_retour;
    
    $message = "
    Nouvelle demande de retour reçue
    
    N° de retour : $numero_retour
    Client : {$user->display_name} ({$user->user_email})
    Commande : #{$order->get_order_number()}
    Motif : {$retour->motif}
    Montant : " . wc_price($retour->montant_total) . "
    
    Gérer cette demande dans le back-office :
    " . admin_url('admin.php?page=gestion-retours') . "
    ";
    
    wp_mail($admin_email, $subject, $message);
}

// Notification client changement de statut
function notifier_client_changement_statut($retour) {
    $user = get_userdata($retour->user_id);
    if (!$user) return;
    
    $statuts_labels = array(
        'en_attente' => 'En attente d\'examen',
        'approuve' => 'Approuvé',
        'refuse' => 'Refusé',
        'en_cours' => 'Retour en cours',
        'recu' => 'Colis reçu',
        'rembourse' => 'Remboursé',
        'termine' => 'Terminé'
    );
    
    $statut_label = $statuts_labels[$retour->statut] ?? $retour->statut;
    
    $subject = '📦 Mise à jour de votre demande de retour - ' . $retour->numero_retour;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF3F22; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; }
            .statut { font-size: 20px; font-weight: bold; color: #FF3F22; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Mise à jour de votre retour</h1>
            </div>
            <div class='content'>
                <p>Bonjour " . esc_html($user->first_name) . ",</p>
                
                <p>Le statut de votre demande de retour a été mis à jour.</p>
                
                <p><strong>N° de retour :</strong> " . $retour->numero_retour . "</p>
                
                <div class='statut'>Nouveau statut : " . $statut_label . "</div>
                
                " . ($retour->notes_admin ? "<p><strong>Notes :</strong><br>" . nl2br(esc_html($retour->notes_admin)) . "</p>" : "") . "
                
                <p>Vous pouvez consulter les détails dans votre espace Mon Compte.</p>
                
                <p>Cordialement,<br>L'équipe Doc-Headshok</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($user->user_email, $subject, $message, $headers);
}

// Ajouter une page d'administration dans le menu WordPress
add_action('admin_menu', 'ajouter_menu_gestion_retours');
function ajouter_menu_gestion_retours() {
    add_menu_page(
        'Gestion des Retours',
        'Retours',
        'manage_woocommerce',
        'gestion-retours',
        'afficher_page_gestion_retours',
        'dashicons-undo',
        56
    );
}

// Page d'administration
function afficher_page_gestion_retours() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Vous n\'avez pas les permissions nécessaires');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'demandes_retours';
    
    // Traitement des actions
    if (isset($_POST['action']) && $_POST['action'] === 'update_status' && wp_verify_nonce($_POST['_wpnonce'], 'update_retour_status')) {
        $retour_id = intval($_POST['retour_id']);
        $statut = sanitize_text_field($_POST['statut']);
        $notes = sanitize_textarea_field($_POST['notes_admin']);
        $numero_suivi = sanitize_text_field($_POST['numero_suivi_retour']);
        $remboursement = isset($_POST['remboursement_effectue']) ? 1 : 0;
        
        $wpdb->update(
            $table_name,
            array(
                'statut' => $statut,
                'notes_admin' => $notes,
                'numero_suivi_retour' => $numero_suivi,
                'remboursement_effectue' => $remboursement
            ),
            array('id' => $retour_id),
            array('%s', '%s', '%s', '%d'),
            array('%d')
        );
        
        // Notifier le client
        $retour = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $retour_id));
        if ($retour) {
            notifier_client_changement_statut($retour);
        }
        
        echo '<div class="notice notice-success"><p>Statut mis à jour avec succès !</p></div>';
    }
    
    // Filtres
    $statut_filtre = isset($_GET['statut']) ? sanitize_text_field($_GET['statut']) : '';
    $retours = obtenir_tous_retours($statut_filtre);
    
    // Statistiques
    $total_retours = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $en_attente = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE statut = 'en_attente'");
    $approuves = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE statut = 'approuve'");
    $termines = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE statut = 'termine'");
    $avoirs_generes = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE avoir_genere IS NOT NULL AND avoir_genere != ''");
    
    ?>
    <div class="wrap">
        <h1>Gestion des Retours de Commande</h1>
        
        <div class="retours-stats" style="display: flex; gap: 20px; margin: 20px 0;">
            <div style="background: #fff; padding: 20px; border-left: 4px solid #FF3F22; flex: 1;">
                <h3 style="margin: 0; color: #FF3F22;"><?php echo $total_retours; ?></h3>
                <p style="margin: 5px 0 0 0;">Total retours</p>
            </div>
            <div style="background: #fff; padding: 20px; border-left: 4px solid #FFA500; flex: 1;">
                <h3 style="margin: 0; color: #FFA500;"><?php echo $en_attente; ?></h3>
                <p style="margin: 5px 0 0 0;">En attente</p>
            </div>
            <div style="background: #fff; padding: 20px; border-left: 4px solid #4CAF50; flex: 1;">
                <h3 style="margin: 0; color: #4CAF50;"><?php echo $approuves; ?></h3>
                <p style="margin: 5px 0 0 0;">Approuvés</p>
            </div>
            <div style="background: #fff; padding: 20px; border-left: 4px solid #888; flex: 1;">
                <h3 style="margin: 0; color: #888;"><?php echo $termines; ?></h3>
                <p style="margin: 5px 0 0 0;">Terminés</p>
            </div>
            <div style="background: #fff; padding: 20px; border-left: 4px solid #2196F3; flex: 1;">
                <h3 style="margin: 0; color: #2196F3;"><?php echo $avoirs_generes; ?></h3>
                <p style="margin: 5px 0 0 0;">Avoirs générés</p>
            </div>
        </div>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="statut_filtre" id="statut_filtre" onchange="window.location.href='?page=gestion-retours&statut=' + this.value">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?php selected($statut_filtre, 'en_attente'); ?>>En attente</option>
                    <option value="approuve" <?php selected($statut_filtre, 'approuve'); ?>>Approuvé</option>
                    <option value="refuse" <?php selected($statut_filtre, 'refuse'); ?>>Refusé</option>
                    <option value="en_cours" <?php selected($statut_filtre, 'en_cours'); ?>>En cours</option>
                    <option value="recu" <?php selected($statut_filtre, 'recu'); ?>>Reçu</option>
                    <option value="rembourse" <?php selected($statut_filtre, 'rembourse'); ?>>Remboursé</option>
                    <option value="termine" <?php selected($statut_filtre, 'termine'); ?>>Terminé</option>
                </select>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>N° Retour</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Commande</th>
                    <th>Motif</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($retours)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Aucun retour trouvé</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($retours as $retour): 
                        $user = get_userdata($retour->user_id);
                        $order = wc_get_order($retour->order_id);
                        
                        $statut_colors = array(
                            'en_attente' => '#FFA500',
                            'approuve' => '#4CAF50',
                            'refuse' => '#F44336',
                            'en_cours' => '#2196F3',
                            'recu' => '#9C27B0',
                            'rembourse' => '#4CAF50',
                            'termine' => '#888'
                        );
                        
                        $statut_color = $statut_colors[$retour->statut] ?? '#888';
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($retour->numero_retour); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($retour->date_demande)); ?></td>
                            <td>
                                <?php echo esc_html($user->display_name); ?><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $retour->order_id . '&action=edit'); ?>" target="_blank">
                                    #<?php echo $order->get_order_number(); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($retour->motif); ?></td>
                            <td><?php echo wc_price($retour->montant_total); ?></td>
                            <td>
                                <span style="background: <?php echo $statut_color; ?>; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px;">
                                    <?php echo esc_html($retour->statut); ?>
                                </span>
                                <?php if ($retour->remboursement_effectue): ?>
                                    <br><small style="color: green;">✓ Remboursé</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="button button-small" onclick="openRetourModal(<?php echo $retour->id; ?>)">
                                    Gérer
                                </button>
                                <button class="button button-small" onclick="viewRetourDetails(<?php echo $retour->id; ?>)">
                                    Détails
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal de gestion -->
    <div id="retour-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="background: white; max-width: 600px; margin: 50px auto; padding: 30px; border-radius: 8px; max-height: 80vh; overflow-y: auto;">
            <h2>Gérer le retour</h2>
            <div id="retour-modal-content"></div>
        </div>
    </div>
    
    <!-- Modal de génération d'avoir -->
    <div id="avoir-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="background: white; max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 8px;">
            <h2>💰 Générer un Bon d'Avoir</h2>
            <div id="avoir-modal-content">
                <form id="avoir-form">
                    <input type="hidden" id="avoir_retour_id" name="retour_id">
                    <input type="hidden" id="avoir_user_email" name="user_email">
                    
                    <p>
                        <label for="avoir_montant" style="font-weight: bold;">Montant de l'avoir (€) *</label><br>
                        <input type="number" id="avoir_montant" name="montant" step="0.01" min="0.01" required style="width: 100%; padding: 8px; margin-top: 5px;">
                    </p>
                    
                    <p>
                        <label for="avoir_notes" style="font-weight: bold;">Notes (optionnel)</label><br>
                        <textarea id="avoir_notes" name="notes" rows="4" style="width: 100%; padding: 8px; margin-top: 5px;" placeholder="Raison de l'émission de l'avoir..."></textarea>
                    </p>
                    
                    <p style="background: #e7f3fe; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0;">
                        <strong>ℹ️ Information :</strong><br>
                        • L'avoir sera valable 1 an<br>
                        • Utilisation unique<br>
                        • Un email sera automatiquement envoyé au client
                    </p>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" class="button" onclick="closeAvoirModal()">Annuler</button>
                        <button type="submit" class="button button-primary">Générer l'Avoir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function openRetourModal(retourId) {
        // Récupérer les détails via AJAX
        jQuery.post(ajaxurl, {
            action: 'get_retour_details',
            retour_id: retourId,
            nonce: '<?php echo wp_create_nonce('admin_retour_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                var retour = response.data;
                var html = '<form method="post" action="">';
                html += '<?php wp_nonce_field('update_retour_status'); ?>';
                html += '<input type="hidden" name="action" value="update_status">';
                html += '<input type="hidden" name="retour_id" value="' + retourId + '">';
                
                html += '<p><strong>N° Retour:</strong> ' + retour.numero_retour + '</p>';
                html += '<p><strong>Client:</strong> ' + retour.user_name + '</p>';
                html += '<p><strong>Commande:</strong> #' + retour.order_number + '</p>';
                html += '<p><strong>Motif:</strong> ' + retour.motif + '</p>';
                html += '<p><strong>Description:</strong><br>' + (retour.description || 'Aucune') + '</p>';
                
                html += '<hr><h3>Produits concernés:</h3>';
                var produits = JSON.parse(retour.produits_concernes);
                produits.forEach(function(p) {
                    html += '<p>• ' + p.name + ' (x' + p.quantity + ') - ' + p.total + ' €</p>';
                });
                
                html += '<hr><h3>Mise à jour</h3>';
                html += '<p><label>Statut:<br>';
                html += '<select name="statut" class="widefat">';
                html += '<option value="en_attente"' + (retour.statut === 'en_attente' ? ' selected' : '') + '>En attente</option>';
                html += '<option value="approuve"' + (retour.statut === 'approuve' ? ' selected' : '') + '>Approuvé</option>';
                html += '<option value="refuse"' + (retour.statut === 'refuse' ? ' selected' : '') + '>Refusé</option>';
                html += '<option value="en_cours"' + (retour.statut === 'en_cours' ? ' selected' : '') + '>En cours</option>';
                html += '<option value="recu"' + (retour.statut === 'recu' ? ' selected' : '') + '>Reçu</option>';
                html += '<option value="rembourse"' + (retour.statut === 'rembourse' ? ' selected' : '') + '>Remboursé</option>';
                html += '<option value="termine"' + (retour.statut === 'termine' ? ' selected' : '') + '>Terminé</option>';
                html += '</select></label></p>';
                
                html += '<p><label>N° de suivi retour:<br>';
                html += '<input type="text" name="numero_suivi_retour" class="widefat" value="' + (retour.numero_suivi_retour || '') + '"></label></p>';
                
                html += '<p><label>';
                html += '<input type="checkbox" name="remboursement_effectue" value="1"' + (retour.remboursement_effectue == '1' ? ' checked' : '') + '>';
                html += ' Remboursement effectué</label></p>';
                
                html += '<p><label>Notes admin:<br>';
                html += '<textarea name="notes_admin" class="widefat" rows="4">' + (retour.notes_admin || '') + '</textarea></label></p>';
                
                html += '<p>';
                html += '<button type="submit" class="button button-primary">Enregistrer</button> ';
                html += '<button type="button" class="button" onclick="closeRetourModal()">Annuler</button>';
                html += '</p>';
                
                html += '</form>';
                
                document.getElementById('retour-modal-content').innerHTML = html;
                document.getElementById('retour-modal').style.display = 'block';
            }
        });
    }
    
    function closeRetourModal() {
        document.getElementById('retour-modal').style.display = 'none';
    }
    
    function viewRetourDetails(retourId) {
        // Ouvrir dans une nouvelle fenêtre ou modal
        alert('Détails du retour #' + retourId);
    }
    
    // Fermer le modal en cliquant en dehors
    document.getElementById('retour-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRetourModal();
        }
    });
    </script>
    
    <style>
    .retours-stats h3 { font-size: 32px; }
    .wp-list-table th { font-weight: 600; }
    .wp-list-table td { vertical-align: middle; }
    </style>
    <?php
}

// AJAX pour récupérer les détails d'un retour (admin)
add_action('wp_ajax_get_retour_details', 'get_retour_details_ajax');
function get_retour_details_ajax() {
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'admin_retour_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'demandes_retours';
    $retour_id = intval($_POST['retour_id']);
    
    $retour = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $retour_id
    ));
    
    if (!$retour) {
        wp_send_json_error('Retour non trouvé');
        return;
    }
    
    $user = get_userdata($retour->user_id);
    $order = wc_get_order($retour->order_id);
    
    $data = array(
        'id' => $retour->id,
        'numero_retour' => $retour->numero_retour,
        'user_name' => $user->display_name,
        'order_number' => $order->get_order_number(),
        'motif' => $retour->motif,
        'description' => $retour->description,
        'produits_concernes' => $retour->produits_concernes,
        'montant_total' => $retour->montant_total,
        'statut' => $retour->statut,
        'notes_admin' => $retour->notes_admin,
        'numero_suivi_retour' => $retour->numero_suivi_retour,
        'remboursement_effectue' => $retour->remboursement_effectue
    );
    
    wp_send_json_success($data);
}
