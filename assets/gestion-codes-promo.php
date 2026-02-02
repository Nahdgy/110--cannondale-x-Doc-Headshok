<?php
/**
 * Système de gestion des codes promo assignés aux utilisateurs
 * 
 * Ce fichier permet d'assigner des codes promo WooCommerce à des utilisateurs spécifiques
 * via leur adresse email et d'afficher ces codes dans leur espace Mon Compte
 */

// Fonction pour obtenir tous les coupons assignés à un utilisateur via son email
function obtenir_coupons_utilisateur($user_email) {
    if (empty($user_email)) {
        return array();
    }
    
    $args = array(
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'desc',
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
    );
    
    $coupons = get_posts($args);
    $user_coupons = array();
    
    foreach ($coupons as $coupon_post) {
        $coupon = new WC_Coupon($coupon_post->ID);
        
        // Vérifier si ce coupon est restreint à certains emails
        $email_restrictions = $coupon->get_email_restrictions();
        
        if (!empty($email_restrictions)) {
            // Vérifier si l'email de l'utilisateur correspond
            foreach ($email_restrictions as $restricted_email) {
                // Support des wildcards dans WooCommerce (ex: *@exemple.com)
                $pattern = str_replace('*', '.*', preg_quote($restricted_email, '/'));
                if (preg_match('/^' . $pattern . '$/i', $user_email)) {
                    $user_coupons[] = $coupon_post->post_title;
                    break;
                }
            }
        }
    }
    
    return $user_coupons;
}

// Fonction pour assigner un code promo à un ou plusieurs utilisateurs
function assigner_code_promo_utilisateurs($coupon_code, $user_emails) {
    if (empty($coupon_code) || empty($user_emails)) {
        return false;
    }
    
    // Si c'est une seule adresse email en string, convertir en array
    if (!is_array($user_emails)) {
        $user_emails = array($user_emails);
    }
    
    // Récupérer le coupon
    $coupon = new WC_Coupon($coupon_code);
    
    if (!$coupon->get_id()) {
        return false;
    }
    
    // Récupérer les restrictions email actuelles
    $current_restrictions = $coupon->get_email_restrictions();
    
    // Fusionner avec les nouvelles adresses
    $new_restrictions = array_unique(array_merge($current_restrictions, $user_emails));
    
    // Mettre à jour le coupon
    $coupon->set_email_restrictions($new_restrictions);
    $coupon->save();
    
    return true;
}

// Fonction pour retirer un code promo d'un utilisateur
function retirer_code_promo_utilisateur($coupon_code, $user_email) {
    if (empty($coupon_code) || empty($user_email)) {
        return false;
    }
    
    $coupon = new WC_Coupon($coupon_code);
    
    if (!$coupon->get_id()) {
        return false;
    }
    
    // Récupérer les restrictions email actuelles
    $current_restrictions = $coupon->get_email_restrictions();
    
    // Retirer l'email de la liste
    $new_restrictions = array_diff($current_restrictions, array($user_email));
    
    // Mettre à jour le coupon
    $coupon->set_email_restrictions(array_values($new_restrictions));
    $coupon->save();
    
    return true;
}

// Action AJAX pour assigner un code promo à un utilisateur (admin uniquement)
add_action('wp_ajax_assigner_code_promo', 'assigner_code_promo_ajax');
function assigner_code_promo_ajax() {
    // Vérifier les permissions (admin uniquement)
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Vous n\'avez pas les permissions nécessaires.');
        return;
    }
    
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'assigner_code_promo_nonce')) {
        wp_send_json_error('Erreur de sécurité.');
        return;
    }
    
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    $user_emails_raw = sanitize_textarea_field($_POST['user_emails']);
    
    // Séparer les emails (par ligne, virgule ou point-virgule)
    $user_emails = preg_split('/[\r\n,;]+/', $user_emails_raw);
    $user_emails = array_map('trim', $user_emails);
    $user_emails = array_filter($user_emails); // Supprimer les entrées vides
    
    // Valider les emails
    $valid_emails = array();
    $invalid_emails = array();
    
    foreach ($user_emails as $email) {
        if (is_email($email)) {
            $valid_emails[] = $email;
        } else {
            $invalid_emails[] = $email;
        }
    }
    
    if (empty($valid_emails)) {
        wp_send_json_error('Aucune adresse email valide fournie.');
        return;
    }
    
    // Assigner le code promo
    $result = assigner_code_promo_utilisateurs($coupon_code, $valid_emails);
    
    if ($result) {
        $message = count($valid_emails) . ' utilisateur(s) assigné(s) au code promo ' . $coupon_code;
        if (!empty($invalid_emails)) {
            $message .= '. Emails invalides ignorés : ' . implode(', ', $invalid_emails);
        }
        wp_send_json_success($message);
    } else {
        wp_send_json_error('Erreur lors de l\'assignation. Vérifiez que le code promo existe.');
    }
}

// Ajouter une meta box dans l'écran d'édition des coupons pour faciliter l'assignation
add_action('add_meta_boxes', 'ajouter_metabox_assignation_coupon');
function ajouter_metabox_assignation_coupon() {
    add_meta_box(
        'assignation_coupon_users',
        'Assigner aux utilisateurs',
        'afficher_metabox_assignation_coupon',
        'shop_coupon',
        'side',
        'high'
    );
}

function afficher_metabox_assignation_coupon($post) {
    $coupon = new WC_Coupon($post->ID);
    $email_restrictions = $coupon->get_email_restrictions();
    
    ?>
    <div class="assignation-coupon-wrapper">
        <p>
            <label for="coupon_user_emails">Adresses email (une par ligne) :</label>
            <textarea 
                id="coupon_user_emails" 
                name="coupon_user_emails" 
                rows="5" 
                style="width:100%;"
                placeholder="email1@exemple.com&#10;email2@exemple.com"
            ><?php echo esc_textarea(implode("\n", $email_restrictions)); ?></textarea>
        </p>
        <p class="description">
            Les utilisateurs avec ces adresses email pourront voir et utiliser ce code promo dans leur espace Mon Compte.
            <br>Vous pouvez également utiliser des wildcards : *@exemple.com
        </p>
        
        <?php if (!empty($email_restrictions)): ?>
        <div class="emails-assignes">
            <strong>Actuellement assigné à :</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <?php foreach ($email_restrictions as $email): ?>
                    <li><?php echo esc_html($email); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
        .assignation-coupon-wrapper {
            padding: 10px 0;
        }
        .emails-assignes {
            background: #f0f0f1;
            border-left: 4px solid #2271b1;
            padding: 10px;
            margin-top: 10px;
        }
        .emails-assignes ul {
            font-size: 12px;
        }
    </style>
    <?php
}

// Sauvegarder les emails assignés lors de la mise à jour du coupon
add_action('woocommerce_coupon_options_save', 'sauvegarder_assignation_coupon', 10, 1);
function sauvegarder_assignation_coupon($post_id) {
    if (isset($_POST['coupon_user_emails'])) {
        $user_emails_raw = sanitize_textarea_field($_POST['coupon_user_emails']);
        
        // Séparer les emails
        $user_emails = preg_split('/[\r\n,;]+/', $user_emails_raw);
        $user_emails = array_map('trim', $user_emails);
        $user_emails = array_filter($user_emails);
        
        // Valider et nettoyer les emails
        $valid_emails = array();
        foreach ($user_emails as $email) {
            // Permettre les wildcards
            if (strpos($email, '*') !== false) {
                $valid_emails[] = $email;
            } elseif (is_email($email)) {
                $valid_emails[] = $email;
            }
        }
        
        // Mettre à jour les restrictions email du coupon
        $coupon = new WC_Coupon($post_id);
        $coupon->set_email_restrictions($valid_emails);
        $coupon->save();
    }
}

// Ajouter une colonne dans la liste des coupons pour voir rapidement les assignations
add_filter('manage_edit-shop_coupon_columns', 'ajouter_colonne_assignation_coupons');
function ajouter_colonne_assignation_coupons($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Ajouter après la colonne "type"
        if ($key === 'type') {
            $new_columns['assignations'] = 'Assigné à';
        }
    }
    
    return $new_columns;
}

add_action('manage_shop_coupon_posts_custom_column', 'afficher_colonne_assignation_coupons', 10, 2);
function afficher_colonne_assignation_coupons($column, $post_id) {
    if ($column === 'assignations') {
        $coupon = new WC_Coupon($post_id);
        $email_restrictions = $coupon->get_email_restrictions();
        
        if (!empty($email_restrictions)) {
            $count = count($email_restrictions);
            echo '<span title="' . esc_attr(implode(', ', $email_restrictions)) . '">';
            echo $count . ' utilisateur' . ($count > 1 ? 's' : '');
            echo '</span>';
        } else {
            echo '<span style="color: #999;">Non assigné</span>';
        }
    }
}

// Notification email aux utilisateurs lors de l'assignation d'un nouveau code promo
function notifier_utilisateur_nouveau_code_promo($user_email, $coupon_code) {
    $coupon = new WC_Coupon($coupon_code);
    
    if (!$coupon->get_id()) {
        return false;
    }
    
    // Obtenir les informations de l'utilisateur
    $user = get_user_by('email', $user_email);
    $user_name = $user ? $user->display_name : 'Cher client';
    
    // Détails du coupon
    $discount_type = $coupon->get_discount_type();
    $amount = $coupon->get_amount();
    
    $discount_text = '';
    switch($discount_type) {
        case 'percent':
            $discount_text = $amount . '% de réduction';
            break;
        case 'fixed_cart':
            $discount_text = number_format($amount, 2) . '€ de réduction sur votre panier';
            break;
        case 'fixed_product':
            $discount_text = number_format($amount, 2) . '€ de réduction par produit';
            break;
    }
    
    // Date d'expiration
    $expiry_date = $coupon->get_date_expires();
    $expiry_text = $expiry_date ? 'Valable jusqu\'au ' . $expiry_date->date('d/m/Y') : 'Sans date d\'expiration';
    
    // Construction de l'email
    $subject = '🎁 Vous avez reçu un nouveau code promo !';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .code { font-size: 32px; font-weight: bold; letter-spacing: 3px; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 5px; margin: 20px 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .discount { font-size: 24px; color: #667eea; font-weight: bold; margin: 20px 0; }
            .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; color: #999; margin-top: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎁 Nouveau Code Promo !</h1>
                <p>Bonjour $user_name,</p>
                <p>Nous avons le plaisir de vous offrir un code promo exclusif !</p>
            </div>
            <div class='content'>
                <div class='code'>$coupon_code</div>
                <div class='discount'>$discount_text</div>
                <p><strong>$expiry_text</strong></p>
                
                " . ($coupon->get_description() ? '<p>' . esc_html($coupon->get_description()) . '</p>' : '') . "
                
                <p>Ce code promo est disponible dans votre espace Mon Compte et sera automatiquement visible lors de vos prochaines commandes.</p>
                
                <a href='" . esc_url(wc_get_page_permalink('myaccount')) . "' class='button'>Voir mon compte</a>
                <a href='" . esc_url(wc_get_page_permalink('shop')) . "' class='button'>Commencer mes achats</a>
            </div>
            <div class='footer'>
                <p>Merci de votre fidélité !</p>
                <p>L'équipe Doc-Headshok</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    return wp_mail($user_email, $subject, $message, $headers);
}

// Hook pour envoyer automatiquement un email lors de l'ajout d'un email à un coupon
add_action('woocommerce_coupon_options_save', 'notifier_nouveaux_utilisateurs_codes_promo', 20, 1);
function notifier_nouveaux_utilisateurs_codes_promo($post_id) {
    // Récupérer les anciennes restrictions
    $coupon = new WC_Coupon($post_id);
    $old_restrictions = get_post_meta($post_id, '_email_restrictions_before_save', true);
    $new_restrictions = $coupon->get_email_restrictions();
    
    if (empty($new_restrictions)) {
        return;
    }
    
    // Trouver les nouveaux emails ajoutés
    $old_emails = is_array($old_restrictions) ? $old_restrictions : array();
    $new_emails = array_diff($new_restrictions, $old_emails);
    
    // Notifier chaque nouvel utilisateur
    foreach ($new_emails as $email) {
        notifier_utilisateur_nouveau_code_promo($email, $coupon->get_code());
    }
    
    // Sauvegarder les restrictions actuelles pour la prochaine comparaison
    update_post_meta($post_id, '_email_restrictions_before_save', $new_restrictions);
}

// Sauvegarder les restrictions avant la modification
add_action('woocommerce_coupon_options', 'sauvegarder_restrictions_avant_modification', 10, 2);
function sauvegarder_restrictions_avant_modification($coupon_id, $coupon) {
    if ($coupon && $coupon->get_id()) {
        $current_restrictions = $coupon->get_email_restrictions();
        update_post_meta($coupon_id, '_email_restrictions_before_save', $current_restrictions);
    }
}
