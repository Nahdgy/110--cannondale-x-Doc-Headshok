// Fonction pour sauvegarder l'historique des prestations
function sauvegarder_demande_prestation($user_id, $data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_prestations';
    
    // Créer la table si elle n'existe pas
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        type_prestation varchar(255) NOT NULL,
        prestations text,
        options_supplementaires text,
        date_revision date,
        poids_pilote int(11),
        modele_velo varchar(255),
        annee_velo varchar(50),
        remarques text,
        prix_total decimal(10,2),
        fichier_joint varchar(255),
        numero_suivi varchar(50),
        date_creation datetime DEFAULT CURRENT_TIMESTAMP,
        statut varchar(50) DEFAULT 'en_attente',
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insérer la nouvelle demande
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'type_prestation' => $data['type_prestation'] ?? '',
            'prestations' => is_array($data['prestations']) ? implode(', ', $data['prestations']) : $data['prestations'],
            'options_supplementaires' => is_array($data['options']) ? implode(', ', $data['options']) : $data['options'],
            'date_revision' => $data['date_revision'] ?? null,
            'poids_pilote' => $data['poids_pilote'] ?? null,
            'modele_velo' => $data['modele_velo'] ?? '',
            'annee_velo' => $data['annee_velo'] ?? '',
            'remarques' => $data['remarques'] ?? '',
            'prix_total' => $data['prix_total'] ?? 0,
            'fichier_joint' => $data['fichier_joint'] ?? '',
            'numero_suivi' => $data['numero_suivi'] ?? '',
            'statut' => 'en_attente'
        ),
        array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s')
    );
    
    return $wpdb->insert_id;
}

// Fonction pour récupérer l'historique des prestations d'un utilisateur
function obtenir_historique_prestations($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'demandes_prestations';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date_creation DESC",
        $user_id
    ));
    
    return $results;
}

// Action AJAX pour sauvegarder une demande de prestation
add_action('wp_ajax_sauvegarder_prestation', 'sauvegarder_prestation_ajax');
add_action('wp_ajax_nopriv_sauvegarder_prestation', 'sauvegarder_prestation_ajax');

function sauvegarder_prestation_ajax() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'prestation_nonce')) {
        wp_die('Erreur de sécurité');
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_die('Utilisateur non connecté');
    }
    
    $data = array(
        'type_prestation' => sanitize_text_field($_POST['type_prestation']),
        'prestations' => $_POST['prestations'],
        'options' => $_POST['options'] ?? array(),
        'date_revision' => sanitize_text_field($_POST['date_revision']),
        'poids_pilote' => intval($_POST['poids_pilote']),
        'modele_velo' => sanitize_text_field($_POST['modele_velo']),
        'annee_velo' => sanitize_text_field($_POST['annee_velo']),
        'remarques' => sanitize_textarea_field($_POST['remarques']),
        'prix_total' => floatval($_POST['prix_total']),
        'numero_suivi' => sanitize_text_field($_POST['numero_suivi'])
    );
    
    $demande_id = sauvegarder_demande_prestation($user_id, $data);
    
    if ($demande_id) {
        wp_send_json_success(array('demande_id' => $demande_id));
    } else {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }
}

// Fonction pour générer un PDF de facture pour les prestations
function generer_pdf_facture($order_id) {
    // Vérifier que l'utilisateur a le droit d'accéder à cette commande
    if (!current_user_can('administrator') && !wc_customer_bought_product(get_current_user_id(), $order_id, '')) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_customer_id() !== get_current_user_id()) {
            wp_die('Accès non autorisé');
        }
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_die('Commande non trouvée');
    }
    
    // Vérifier si TCPDF est disponible
    if (!class_exists('TCPDF')) {
        // Essayer d'inclure TCPDF depuis WordPress ou un plugin
        $tcpdf_path = ABSPATH . 'wp-content/plugins/woocommerce-pdf-invoices-packing-slips/lib/tcpdf/tcpdf.php';
        if (file_exists($tcpdf_path)) {
            require_once($tcpdf_path);
        } else {
            // Alternative : utiliser une bibliothèque PDF simple
            return generer_pdf_simple($order);
        }
    }
    
    // Créer le PDF avec TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Informations du document
    $pdf->SetCreator('Doc-Headshok');
    $pdf->SetAuthor('Doc-Headshok');
    $pdf->SetTitle('Facture #' . $order->get_order_number());
    $pdf->SetSubject('Facture de commande');
    
    // Supprimer header/footer par défaut
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Marges
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Ajouter une page
    $pdf->AddPage();
    
    // Contenu de la facture
    $html = generer_contenu_facture($order);
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Générer le PDF
    $filename = 'Facture-' . $order->get_order_number() . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' pour téléchargement direct
    exit;
}

// Fonction pour générer le contenu HTML de la facture
function generer_contenu_facture($order) {
    $billing_address = $order->get_formatted_billing_address();
    $shipping_address = $order->get_formatted_shipping_address();
    $order_date = $order->get_date_created()->date('d/m/Y');
    
    $html = '
    <style>
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #FF3F22; }
        .facture-titre { font-size: 20px; margin: 20px 0; }
        .infos-commande { margin: 20px 0; }
        .adresses { display: flex; justify-content: space-between; margin: 20px 0; }
        .adresse { width: 45%; }
        .products-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .products-table th, .products-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .products-table th { background-color: #f5f5f5; }
        .total-section { margin-top: 20px; text-align: right; }
    </style>
    
    <div class="header">
        <div class="logo">DOC-HEADSHOK</div>
        <p>Spécialiste Cannondale & Fourches Lefty</p>
    </div>
    
    <h2 class="facture-titre">FACTURE #' . $order->get_order_number() . '</h2>
    
    <div class="infos-commande">
        <p><strong>Date de commande :</strong> ' . $order_date . '</p>
        <p><strong>Statut :</strong> ' . wc_get_order_status_name($order->get_status()) . '</p>
    </div>
    
    <table style="width: 100%; margin: 20px 0;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <h3>Adresse de facturation</h3>
                <p>' . str_replace('<br/>', '<br>', $billing_address) . '</p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <h3>Adresse de livraison</h3>
                <p>' . ($shipping_address ? str_replace('<br/>', '<br>', $shipping_address) : 'Identique à l\'adresse de facturation') . '</p>
            </td>
        </tr>
    </table>
    
    <table class="products-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';
    
    // Ajouter les produits
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $html .= '
            <tr>
                <td>' . $item->get_name() . '</td>
                <td>' . $item->get_quantity() . '</td>
                <td>' . wc_price($product->get_price()) . '</td>
                <td>' . wc_price($item->get_total()) . '</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
    </table>
    
    <div class="total-section">
        <table style="width: 300px; margin-left: auto;">
            <tr>
                <td><strong>Sous-total :</strong></td>
                <td style="text-align: right;"><strong>' . wc_price($order->get_subtotal()) . '</strong></td>
            </tr>';
    
    // Frais de livraison
    if ($order->get_shipping_total() > 0) {
        $html .= '
            <tr>
                <td>Livraison :</td>
                <td style="text-align: right;">' . wc_price($order->get_shipping_total()) . '</td>
            </tr>';
    }
    
    // Taxes
    if ($order->get_total_tax() > 0) {
        $html .= '
            <tr>
                <td>TVA :</td>
                <td style="text-align: right;">' . wc_price($order->get_total_tax()) . '</td>
            </tr>';
    }
    
    $html .= '
            <tr style="border-top: 2px solid #000;">
                <td><strong>Total :</strong></td>
                <td style="text-align: right;"><strong>' . wc_price($order->get_total()) . '</strong></td>
            </tr>
        </table>
    </div>
    
    <div style="margin-top: 40px; text-align: center; color: #666;">
        <p>Merci pour votre commande !</p>
        <p>Doc-Headshok - Spécialiste Cannondale</p>
    </div>';
    
    return $html;
}

// Fonction alternative pour générer un PDF simple sans TCPDF
function generer_pdf_simple($order) {
    // Générer le contenu HTML de la facture
    $html_content = generer_contenu_facture($order);
    
    // Alternative simple avec HTML pour téléchargement
    $full_html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Facture #' . $order->get_order_number() . '</title>
        <style>
            @page { margin: 20mm; }
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px;
                font-size: 12px;
            }
            .print-button { 
                position: fixed; 
                top: 10px; 
                right: 10px; 
                padding: 10px 20px; 
                background: #FF3F22; 
                color: white; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                font-size: 14px;
                z-index: 1000;
            }
            @media print { 
                .print-button { display: none; }
                body { margin: 0; padding: 0; }
            }
        </style>
    </head>
    <body>
        <button class="print-button" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>
        ' . $html_content . '
    </body>
    </html>';
    
    // Définir les headers pour forcer le téléchargement du fichier HTML
    $filename = 'Facture-' . $order->get_order_number() . '.html';
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $full_html;
    exit;
}

// Action AJAX pour télécharger la facture
add_action('wp_ajax_telecharger_facture', 'telecharger_facture_ajax');
add_action('wp_ajax_nopriv_telecharger_facture', 'telecharger_facture_ajax');

function telecharger_facture_ajax() {
    // Vérifier le nonce pour la sécurité (GET ou POST)
    $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : (isset($_POST['nonce']) ? $_POST['nonce'] : '');
    if (!wp_verify_nonce($nonce, 'telecharger_facture_nonce')) {
        wp_die('Erreur de sécurité');
    }
    
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);
    generer_pdf_facture($order_id);
}

function mon_compte_personnalise_shortcode() {
    if (!is_user_logged_in()) {
        return do_shortcode('[mon_formulaire_connexion]');
    }

    $user = wp_get_current_user();

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_infos'])) {
        $user_id = $user->ID;
        $updated_data = [];

        if (!empty($_POST['nom'])) {
            $updated_data['last_name'] = sanitize_text_field($_POST['nom']);
        }
        if (!empty($_POST['prenom'])) {
            $updated_data['first_name'] = sanitize_text_field($_POST['prenom']);
        }
        if (!empty($_POST['email']) && is_email($_POST['email'])) {
            $updated_data['user_email'] = sanitize_email($_POST['email']);
        }
        if (!empty($_POST['password'])) {
            wp_set_password($_POST['password'], $user_id);
        }

        $updated_data['ID'] = $user_id;
        wp_update_user($updated_data);

        update_user_meta($user_id, 'civilite', sanitize_text_field($_POST['civilite']));

        if (!empty($_POST['pratique']) && is_array($_POST['pratique'])) {
            $pratiques_sanitized = array_map('sanitize_text_field', $_POST['pratique']);
            update_user_meta($user_id, 'pratique', $pratiques_sanitized);
        } else {
            delete_user_meta($user_id, 'pratique');
        }

        update_user_meta($user_id, 'telephone', sanitize_text_field($_POST['telephone']));
        update_user_meta($user_id, 'dob', sanitize_text_field($_POST['dob']));
        
        // Traitement des champs d'adresse séparés
        $adresse_ligne1 = sanitize_text_field($_POST['adresse_ligne1']);
        $adresse_ligne2 = sanitize_text_field($_POST['adresse_ligne2']);
        $ville = sanitize_text_field($_POST['ville']);
        $code_postal = sanitize_text_field($_POST['code_postal']);
        $pays = sanitize_text_field($_POST['pays']);
        
        // Sauvegarder dans les métadonnées WordPress/WooCommerce standard
        update_user_meta($user_id, 'billing_address_1', $adresse_ligne1);
        update_user_meta($user_id, 'billing_address_2', $adresse_ligne2);
        update_user_meta($user_id, 'billing_city', $ville);
        update_user_meta($user_id, 'billing_postcode', $code_postal);
        update_user_meta($user_id, 'billing_country', $pays);
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['telephone']));
        
        // Copier pour l'adresse de livraison par défaut
        update_user_meta($user_id, 'shipping_address_1', $adresse_ligne1);
        update_user_meta($user_id, 'shipping_address_2', $adresse_ligne2);
        update_user_meta($user_id, 'shipping_city', $ville);
        update_user_meta($user_id, 'shipping_postcode', $code_postal);
        update_user_meta($user_id, 'shipping_country', $pays);
        
        // Conserver le champ adresse combiné pour compatibilité
        $adresse_complete = trim($adresse_ligne1 . "\n" . $adresse_ligne2 . "\n" . $ville . " " . $code_postal . "\n" . $pays);
        update_user_meta($user_id, 'adresse', $adresse_complete);
        $user = get_userdata($user_id);
    }

    // Récupération infos utilisateur
    $civilite = get_user_meta($user->ID, 'civilite', true);
    $pratique = get_user_meta($user->ID, 'pratique', true);
    if (!is_array($pratique)) {
        $pratique = $pratique ? [$pratique] : [];
    }
    $telephone = get_user_meta($user->ID, 'telephone', true);
    $dob = get_user_meta($user->ID, 'dob', true);
    
    // Récupération des champs d'adresse séparés
    $adresse_ligne1 = get_user_meta($user->ID, 'billing_address_1', true);
    $adresse_ligne2 = get_user_meta($user->ID, 'billing_address_2', true);
    $ville = get_user_meta($user->ID, 'billing_city', true);
    $code_postal = get_user_meta($user->ID, 'billing_postcode', true);
    $pays = get_user_meta($user->ID, 'billing_country', true);
    
    // Conserver le champ adresse combiné pour compatibilité
    $adresse = get_user_meta($user->ID, 'adresse', true);
    
    $pratiques_options = ['VTT', 'VAE', 'Route', 'Vélos Urbains'];
    
    // Liste des pays (principaux pays européens et autres)
    $pays_options = [
        'FR' => 'France',
        'BE' => 'Belgique',
        'CH' => 'Suisse',
        'LU' => 'Luxembourg',
        'DE' => 'Allemagne',
        'IT' => 'Italie',
        'ES' => 'Espagne',
        'PT' => 'Portugal',
        'NL' => 'Pays-Bas',
        'GB' => 'Royaume-Uni',
        'IE' => 'Irlande',
        'AT' => 'Autriche',
        'DK' => 'Danemark',
        'SE' => 'Suède',
        'NO' => 'Norvège',
        'FI' => 'Finlande',
        'PL' => 'Pologne',
        'CZ' => 'République tchèque',
        'HU' => 'Hongrie',
        'SK' => 'Slovaquie',
        'SI' => 'Slovénie',
        'HR' => 'Croatie',
        'US' => 'États-Unis',
        'CA' => 'Canada',
        'AU' => 'Australie',
        'JP' => 'Japon'
    ];

    // Compteur de commandes WooCommerce
    $customer_orders = wc_get_orders([
        'customer_id' => $user->ID,
        'return'      => 'ids',
                'status'      => ['pending', 'processing', 'on-hold', 'completed']
    ]);

	$en_attente_paiement = 0;
    $en_cours = 0;
    $passees = 0;
    foreach ($customer_orders as $order_id) {
        $order = wc_get_order($order_id);
        if ($order->has_status('pending')) {
            $en_attente_paiement++;
        } elseif ($order->has_status(['processing', 'on-hold'])) {
            $en_cours++;
        } elseif ($order->has_status('completed')) {
            $passees++;
        }
    }

    ob_start();
    ?>
    <style>
        .compte-wrapper {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            display: flex;
            gap: 40px;
        }

        .menu-sidebar {
            flex: 0 0 250px;
            display: flex;
            flex-direction: column;
        }

        .menu-sidebar h1 {
            font-family: Helvetica, sans-serif;
            font-weight: 700;
            font-size: 50px;
            margin-bottom: 20px;
        }

        .menu-compte {
            display: flex;
            flex-direction: column;
        }

        .menu-compte a {
            border-top: 1px solid #000;
            background-color: #fff;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            text-align: center;
        }

        .menu-compte a:hover {
            background-color: initial !important;
            color: initial !important;
            text-decoration: none !important;
            cursor: default;
        }

        .menu-compte a.active {
            background-color: #F7F7F7;
        }

        .menu-compte a.deconnexion {
            font-size: 10px;
            text-decoration: underline;
        }

        .sections-content {
            flex: 1;
        }

        .section-compte {
            margin-bottom: 60px;
            scroll-margin-top: 100px;
        }

        .section-compte h2 {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 24px;
            color: #000000;
            margin-bottom: 20px;
        }

        #commandes::before,
        #parametres::before {
            content: "";
            display: block;
            width: 100%;
            height: 4px;
            background-color: #FF3F22;
            margin-bottom: 15px;
        }

        .form-infos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-infos label {
            display: block;
            margin-bottom: 5px;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
        }

        .form-infos input,
        .form-infos select,
        .form-infos textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select#civilite {
            height: 38px;
        }

        .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 14px;
        }

        .form-infos .full-width {
            grid-column: span 2;
        }

        .form-row-password {
            width: 100%;
        }

        .btn-modifier {
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-modifier:hover {
            background-color: #000 !important;
            cursor: pointer;
        }

        .disabled-input {
            background-color: #eee;
            pointer-events: none;
        }

        .supprimer-compte {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #FF2A2D;
            text-decoration: underline;
            cursor: pointer;
        }

        .texte-newsletter {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
            margin-bottom: 10px;
        }

		.commande-compteur {
			font-family: 'din-next-lt-pro', sans-serif;
			font-weight: 300;
			font-size: 16px;
			color: #000000;
			margin-bottom: 20px;
		}

		.produit-item {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-top: 10px;
			flex-wrap: wrap;
		}

		.produit-nom {
			font-family: 'din-next-lt-pro', sans-serif;
			font-weight: 300;
			font-size: 16px;
			color: #000000;
			flex: 1 1 100%;
		}

		.produit-prix {
			font-family: 'din-next-lt-pro', sans-serif;
			font-weight: 700;
			font-size: 24px;
			color: #000000;
		}

		.commande-numero {
			font-family: 'din-next-lt-pro', sans-serif;
			font-weight: 700;
			font-size: 16px;
			color: #FF3F22;
			margin-bottom: 10px;
		}

		.produit-item {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin-top: 10px;
			flex-wrap: wrap;
		}

		.produit-details {
			display: flex;
			align-items: center;
			gap: 10px;
			flex: 1;
		}

		.produit-texte {
			display: flex;
			flex-direction: column;
		}

		.boutons-actions {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		.bouton-commande {
			font-family: 'din-next-lt-pro', sans-serif;
			font-weight: 700;
			font-size: 16px;
			color: #F9F9F9 !important; /* couleur du texte */
			background-color: #151515;
			padding: 8px 14px;
			border: none;
			border-radius: 10px;
			cursor: pointer;
			text-align: center;
			text-decoration: none;
		}

		.bouton-commande:hover {
			color: #F9F9F9 !important; /* empêche le hover de modifier la couleur */
			background-color: #151515 !important; /* empêche le hover de modifier le fond */
			text-decoration: none !important;
			cursor: default;
		}

        @media screen and (max-width: 768px) {
            .compte-wrapper {
                flex-direction: column;
            }
            .form-infos {
                grid-template-columns: 1fr;
            }
            .form-row-password {
                width: 100%;
            }
        }

        /* Styles pour les prestations et commandes */
        .prestations-container,
        .commandes-container {
            margin-top: 20px;
        }

        .prestations-liste,
        .commandes-liste {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .prestation-item,
        .commande-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: box-shadow 0.2s ease;
        }

        .prestation-item:hover,
        .commande-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .prestation-header,
        .commande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .prestation-type,
        .commande-numero-header {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: #000;
        }

        .prestation-date,
        .commande-date {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 400;
            font-size: 14px;
            color: #6c757d;
        }

        .prestation-statut,
        .commande-statut {
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

		/* Statuts pour prestations */

        .statut-attente {
            background: #fff3cd;
            color: #856404;
        }

        .statut-cours {
            background: #d1ecf1;
            color: #0c5460;
        }

        .statut-terminee {
            background: #d4edda;
            color: #155724;
        }

		/* Statuts pour commandes */
        .statut-pending {
            background: #fff3cd;
            color: #856404;
        }

        .statut-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .statut-on-hold {
            background: #ffeaa7;
            color: #d63031;
        }

        .statut-completed {
            background: #d4edda;
            color: #155724;
        }

        .statut-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .statut-refunded {
            background: #e2e3e5;
            color: #383d41;
        }

        .statut-failed {
            background: #f5c6cb;
            color: #721c24;
        }

        .statut-autre {
            background: #e2e3e5;
            color: #495057;
        }

        .prestation-details,
        .commande-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
  

        .prestation-info,
        .commande-info {
            flex: 1;
        }

        .prestation-info p,
        .commande-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .commande-produits {
            margin-bottom: 10px;
        }

        .produit-ligne {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .produit-ligne:last-child {
            border-bottom: none;
        }

        .produit-nom {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 400;
            font-size: 14px;
            color: #333;
            flex: 1;
        }

        .produit-quantite {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 600;
            font-size: 12px;
            color: #666;
            margin-left: 10px;
        }

        .commande-livraison {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }

        .prestation-actions,
        .commande-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .prestation-prix,
        .commande-prix {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: #FF3F22;
        }

        .bouton-prestation,
        .bouton-commande {
            background: #000;
            color: #fff !important;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            transition: background 0.2s ease;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
        }

        .bouton-prestation:hover,
        .bouton-commande:hover {
            background: #333 !important;
            color: #fff !important;
            text-decoration: none !important;
        }

        .bouton-payment {
            background: #e67e00 !important;
        }

        .bouton-payment:hover {
            background: #d4730a !important;
        }

        .bouton-modifier {
            background: #666 !important;
        }

        .bouton-modifier:hover {
            background: #555 !important;
        }

        .bouton-suivi {
            background: #17a2b8 !important;
        }

        .bouton-suivi:hover {
            background: #138496 !important;
        }

        @media (max-width: 768px) {
            .prestation-header,
            .commande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .prestation-details,
            .commande-details {
                flex-direction: column;
            }

            .prestation-actions,
            .commande-actions {
                align-items: flex-start;
                width: 100%;
            }

            .bouton-prestation,
            .bouton-commande {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-infos');
        const modifierBtn = document.getElementById('btn-modifier');
        const inputs = form.querySelectorAll('input, select, textarea:not(#pratique_affichage)');
        const pratiqueCheckboxes = form.querySelectorAll('#pratique-group input[type="checkbox"]');
        const pratiqueGroup = document.getElementById('pratique-group');
        const pratiqueAffichage = document.getElementById('pratique_affichage');
        let editing = false;

        modifierBtn.addEventListener('click', function (e) {
            if (!editing) {
                e.preventDefault();
                inputs.forEach(input => {
                    input.classList.remove('disabled-input');
                    input.removeAttribute('disabled');
                });
                pratiqueGroup.style.display = 'block';
                pratiqueAffichage.style.display = 'none';
                modifierBtn.textContent = 'Enregistrer';
                editing = true;
            }
        });

        pratiqueCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const selected = Array.from(pratiqueCheckboxes)
                    .filter(box => box.checked)
                    .map(box => box.value)
                    .join(', ');
                pratiqueAffichage.value = selected;
            });
        });

        // Fonction pour télécharger la facture
        window.telechargerFacture = function(orderId) {
            // Créer un lien temporaire pour télécharger la facture
            const link = document.createElement('a');
            link.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=telecharger_facture&order_id=' + orderId + '&nonce=<?php echo wp_create_nonce('telecharger_facture_nonce'); ?>';
            link.style.display = 'none';
            link.target = '_blank';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        // Fonction pour télécharger la facture de prestation
        window.telechargerFacturePrestation = function(prestationId) {
            const link = document.createElement('a');
            link.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=telecharger_facture_prestation&prestation_id=' + prestationId + '&nonce=<?php echo wp_create_nonce('telecharger_facture_prestation_nonce'); ?>';
            link.style.display = 'none';
            link.target = '_blank';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
		
		
        // Fonction pour le suivi des livraisons
        window.suivreLivraison = function(orderId) {
            // Récupérer les informations de suivi pour la commande
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                if (response.data.tracking_url) {
                                    // Ouvrir l'URL de suivi dans une nouvelle fenêtre
                                    window.open(response.data.tracking_url, '_blank');
                                } else {
                                    alert('Informations de suivi : ' + response.data.message);
                                }
                            } else {
                                alert('Erreur : ' + response.data.message);
                            }
                        } catch (e) {
                            alert('Erreur lors de la récupération des informations de suivi.');
                        }
                    } else {
                        alert('Erreur de communication avec le serveur.');
                    }
                }
            };
            
            xhr.send('action=get_order_tracking&order_id=' + orderId + '&nonce=<?php echo wp_create_nonce('get_order_tracking_nonce'); ?>');
        };
    });
    </script>

    <div class="compte-wrapper">
        <div class="menu-sidebar">
            <h1>Mon compte</h1>
            <div class="menu-compte">
                <a href="#infos" class="active">Mes informations</a>
                <a href="#commandes">Mes commandes</a>
                <a href="#prestations">Historique des prestations</a>
                <a href="#parametres">Paramètres</a>
               <a href="<?php echo esc_url(wp_logout_url('https://doc-headshok.com/login/')); ?>" class="deconnexion">Se déconnecter</a>
            </div>
        </div>

        <div class="sections-content">
            <div id="infos" class="section-compte">
                <h2>Mes informations personnelles</h2>
                <form class="form-infos" method="post" action="" id="form-infos">
                    <div>
                        <label for="civilite">Civilité</label>
                        <select id="civilite" name="civilite" class="disabled-input" disabled>
                            <option value="M." <?php selected($civilite, 'M.'); ?>>M.</option>
                            <option value="Mme" <?php selected($civilite, 'Mme'); ?>>Mme</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pratique">Pratique</label>
                        <input type="text" id="pratique_affichage" class="disabled-input" disabled
                            value="<?php echo esc_attr(implode(', ', $pratique)); ?>">
                        <div class="checkbox-group" id="pratique-group" style="display: none; margin-top: 10px;">
                            <?php foreach ($pratiques_options as $option): ?>
                                <label>
                                    <input type="checkbox" name="pratique[]" value="<?php echo esc_attr($option); ?>"
                                        <?php echo in_array($option, $pratique) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($option); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo esc_attr($user->last_name); ?>" class="disabled-input" disabled>
                    </div>

                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo esc_attr($user->first_name); ?>" class="disabled-input" disabled>
                    </div>

                    <div>
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?php echo esc_attr($telephone); ?>" class="disabled-input" disabled>
                    </div>

                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" class="disabled-input" disabled>
                    </div>

                    <div>
                        <label for="dob">Date de naissance</label>
                        <input type="date" id="dob" name="dob" value="<?php echo esc_attr($dob); ?>" class="disabled-input" disabled>
                    </div>

                    <div class="full-width">
                        <label for="adresse_ligne1">Adresse ligne 1</label>
                        <input type="text" id="adresse_ligne1" name="adresse_ligne1" value="<?php echo esc_attr($adresse_ligne1); ?>" class="disabled-input" disabled placeholder="Numéro et nom de rue">
                    </div>

                    <div class="full-width">
                        <label for="adresse_ligne2">Adresse ligne 2</label>
                        <input type="text" id="adresse_ligne2" name="adresse_ligne2" value="<?php echo esc_attr($adresse_ligne2); ?>" class="disabled-input" disabled placeholder="Complément d'adresse (optionnel)">
                    </div>

                    <div>
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo esc_attr($ville); ?>" class="disabled-input" disabled placeholder="Votre ville">
                    </div>

                    <div>
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" value="<?php echo esc_attr($code_postal); ?>" class="disabled-input" disabled placeholder="Code postal">
                    </div>

                    <div class="full-width">
                        <label for="pays">Pays</label>
                        <select id="pays" name="pays" class="disabled-input" disabled>
                            <option value="">Sélectionner un pays</option>
                            <?php foreach ($pays_options as $code => $nom): ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($pays, $code); ?>>
                                    <?php echo esc_html($nom); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row-password">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="disabled-input" disabled style="width: 100%;">
                    </div>

                    <div class="full-width" style="text-align: right;">
                        <button type="submit" id="btn-modifier" name="update_infos" class="btn-modifier">Modifier</button>
                    </div>
                </form>
            </div>

            <div id="commandes" class="section-compte">
                <h2>Mes commandes</h2>
                <div class="commandes-container">
                    <?php
                    if (!empty($customer_orders)) {
                        echo '<div class="commandes-liste">';
                        
                        foreach ($customer_orders as $order_id) {
                            $order = wc_get_order($order_id);
                            
                            // Déterminer le statut et la classe CSS
                            $statut_class = '';
                            $statut_text = '';
                            
                            switch($order->get_status()) {
                                case 'pending':
                                    $statut_class = 'statut-pending';
                                    $statut_text = 'En attente de paiement';
                                    break;
                                case 'processing':
                                    $statut_class = 'statut-processing';
                                    $statut_text = 'En cours de traitement';
                                    break;
                                case 'on-hold':
                                    $statut_class = 'statut-on-hold';
                                    $statut_text = 'En attente';
                                    break;
                                case 'completed':
                                    $statut_class = 'statut-completed';
                                    $statut_text = 'Terminée';
                                    break;
                                case 'cancelled':
                                    $statut_class = 'statut-cancelled';
                                    $statut_text = 'Annulée';
                                    break;
                                case 'refunded':
                                    $statut_class = 'statut-refunded';
                                    $statut_text = 'Remboursée';
                                    break;
                                case 'failed':
                                    $statut_class = 'statut-failed';
                                    $statut_text = 'Échouée';
                                    break;
                                default:
                                    $statut_class = 'statut-autre';
                                    $statut_text = ucfirst($order->get_status());
                            }
                            
                            // Récupérer le mode de livraison
                            $shipping_methods = $order->get_shipping_methods();
                            $shipping_method_name = '';
                            if (!empty($shipping_methods)) {
                                $shipping_method = reset($shipping_methods);
                                $shipping_method_name = $shipping_method->get_method_title();
                            }
                            
                            echo '<div class="commande-item">';
                            echo '<div class="commande-header">';
                            echo '<div class="commande-numero-header">Commande #' . $order->get_order_number() . '</div>';
                            echo '<div class="commande-date">' . $order->get_date_created()->date('d/m/Y') . '</div>';
                            echo '<div class="commande-statut ' . $statut_class . '">' . $statut_text . '</div>';
                            echo '</div>';
                            
                            echo '<div class="commande-details">';
                            echo '<div class="commande-info">';
                            
                            // Afficher les produits de la commande
                            $items = $order->get_items();
                            if (!empty($items)) {
                                echo '<div class="commande-produits">';
                                foreach ($items as $item) {
                                    $product = $item->get_product();
                                    if ($product) {
                                        echo '<div class="produit-ligne">';
                                        echo '<span class="produit-nom">' . esc_html($item->get_name()) . '</span>';
                                        echo '<span class="produit-quantite">x' . $item->get_quantity() . '</span>';
                                        echo '</div>';
                                    }
                                }
                                echo '</div>';
                            }
                            
                            if ($shipping_method_name) {
                                echo '<p class="commande-livraison"><strong>Livraison :</strong> ' . esc_html($shipping_method_name) . '</p>';
                            }
                            
                            echo '</div>';
                            
                            echo '<div class="commande-actions">';
                            echo '<div class="commande-prix">' . wc_price($order->get_total()) . '</div>';
                            
                            // Actions selon le statut
                            if ($order->has_status('pending')) {
                                echo '<a href="' . esc_url($order->get_checkout_payment_url()) . '" class="bouton-commande bouton-payment">FINALISER LE PAIEMENT</a>';
                                echo '<a href="' . esc_url(wc_get_cart_url()) . '" class="bouton-commande bouton-modifier">MODIFIER LA COMMANDE</a>';
                            } else {
                                echo '<button class="bouton-commande" onclick="telechargerFacture(' . $order_id . ')">TÉLÉCHARGER LA FACTURE</button>';
                                if ($order->has_status(['processing', 'on-hold', 'completed'])) {
                                    echo '<button class="bouton-commande bouton-suivi" onclick="suivreLivraison(\'' . $order->get_order_number() . '\')">SUIVRE LA LIVRAISON</button>';
                                }
                            }
                            
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<p>Aucune commande trouvée.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Section Historique des prestations -->
            <div id="prestations" class="section-compte">
                <h2>Historique des prestations</h2>
                <div class="prestations-container">
                    <?php
                    $prestations = obtenir_historique_prestations($user->ID);
                    
                    if (!empty($prestations)) {
                        echo '<div class="prestations-liste">';
                        
                        foreach ($prestations as $prestation) {
                            $statut_class = '';
                            $statut_text = '';
                            
                            switch($prestation->statut) {
                                case 'en_attente':
                                    $statut_class = 'statut-attente';
                                    $statut_text = 'En attente';
                                    break;
                                case 'en_cours':
                                    $statut_class = 'statut-cours';
                                    $statut_text = 'En cours';
                                    break;
                                case 'terminee':
                                    $statut_class = 'statut-terminee';
                                    $statut_text = 'Terminée';
                                    break;
                                default:
                                    $statut_class = 'statut-attente';
                                    $statut_text = 'En attente';
                            }
                            
                            echo '<div class="prestation-item">';
                            echo '<div class="prestation-header">';
                            echo '<div class="prestation-type">' . esc_html($prestation->type_prestation) . '</div>';
                            echo '<div class="prestation-date">' . date('d/m/Y', strtotime($prestation->date_creation)) . '</div>';
                            echo '<div class="prestation-statut ' . $statut_class . '">' . $statut_text . '</div>';
                            echo '</div>';
                            
                            echo '<div class="prestation-details">';
                            echo '<div class="prestation-info">';
                            
                            if ($prestation->prestations) {
                                echo '<p><strong>Prestations :</strong> ' . esc_html($prestation->prestations) . '</p>';
                            }
                            
                            if ($prestation->options_supplementaires) {
                                echo '<p><strong>Options :</strong> ' . esc_html($prestation->options_supplementaires) . '</p>';
                            }
                            
                            if ($prestation->modele_velo || $prestation->annee_velo) {
                                $modele_annee = trim($prestation->modele_velo . ' (' . $prestation->annee_velo . ')');
                                echo '<p><strong>Vélo :</strong> ' . esc_html($modele_annee) . '</p>';
                            }
                            
                            if ($prestation->numero_suivi) {
                                echo '<p><strong>N° de suivi :</strong> ' . esc_html($prestation->numero_suivi) . '</p>';
                            }
                            
                            echo '</div>';
                            
                            echo '<div class="prestation-actions">';
                            if ($prestation->prix_total > 0) {
                                echo '<div class="prestation-prix">' . wc_price($prestation->prix_total) . '</div>';
                            }
                            
                            if ($prestation->statut == 'terminee') {
                                echo '<button class="bouton-prestation" onclick="telechargerFacturePrestation(' . $prestation->id . ')">TÉLÉCHARGER LA FACTURE</button>';
                            }
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<p>Aucune prestation demandée.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div id="parametres" class="section-compte">
                <h2>Paramètres</h2>
                <p class="texte-newsletter">S’inscrire à la newsletter</p>
                <?php echo do_shortcode('[sibwp_form id=1]'); ?>
                <p class="supprimer-compte">Supprimer mon compte</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mon_compte_personnalise', 'mon_compte_personnalise_shortcode');

// Handler AJAX pour télécharger les factures de prestations
add_action('wp_ajax_telecharger_facture_prestation', 'telecharger_facture_prestation');
add_action('wp_ajax_nopriv_telecharger_facture_prestation', 'telecharger_facture_prestation');

function telecharger_facture_prestation() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_GET['nonce'], 'telecharger_facture_prestation_nonce')) {
        wp_die('Accès non autorisé');
    }

    // Vérifier que l'utilisateur est connecté
    if (!is_user_logged_in()) {
        wp_die('Vous devez être connecté');
    }

    $prestation_id = intval($_GET['prestation_id']);
    $user_id = get_current_user_id();

    // Récupérer la prestation
    global $wpdb;
    $table_prestations = $wpdb->prefix . 'demandes_prestations';
    
    $prestation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_prestations WHERE id = %d AND user_id = %d AND statut = 'terminee'",
        $prestation_id,
        $user_id
    ));

    if (!$prestation) {
        wp_die('Prestation non trouvée ou non accessible');
    }

    // Générer le PDF
    generer_pdf_facture_prestation($prestation);
}

function generer_pdf_facture_prestation($prestation) {
    // Vérifier si TCPDF est disponible
    if (!class_exists('TCPDF')) {
        require_once(ABSPATH . 'wp-content/plugins/woocommerce/packages/woocommerce-admin/vendor/tecnickcom/tcpdf/tcpdf.php');
    }

    // Créer une nouvelle instance TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurer le document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Cannondale Service');
    $pdf->SetTitle('Facture Prestation #' . $prestation->id);
    $pdf->SetSubject('Facture de prestation');

    // Supprimer l'en-tête et le pied de page par défaut
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Ajouter une page
    $pdf->AddPage();

    // Définir la police
    $pdf->SetFont('helvetica', '', 12);

    // Contenu de la facture
    $html = '
    <style>
        .header { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; }
        .total { font-size: 16px; font-weight: bold; color: #FF3F22; }
    </style>
    
    <div class="header">
        FACTURE DE PRESTATION #' . $prestation->id . '
    </div>
    
    <div class="section">
        <div class="label">Date de demande :</div>
        ' . date('d/m/Y', strtotime($prestation->date_demande)) . '
    </div>
    
    <div class="section">
        <div class="label">Type de prestation :</div>
        ' . esc_html($prestation->type_prestation) . '
    </div>
    
    <div class="section">
        <div class="label">Modèle du vélo :</div>
        ' . esc_html($prestation->modele_velo) . '
    </div>
    
    <div class="section">
        <div class="label">Année du vélo :</div>
        ' . esc_html($prestation->annee_velo) . '
    </div>';

    if ($prestation->description) {
        $html .= '
        <div class="section">
            <div class="label">Description :</div>
            ' . nl2br(esc_html($prestation->description)) . '
        </div>';
    }

    if ($prestation->numero_suivi) {
        $html .= '
        <div class="section">
            <div class="label">Numéro de suivi :</div>
            ' . esc_html($prestation->numero_suivi) . '
        </div>';
    }

    $html .= '
    <div class="section">
        <div class="label">Statut :</div>
        ' . ucfirst($prestation->statut) . '
    </div>';

    if ($prestation->prix_total > 0) {
        $html .= '
        <hr>
        <div class="section total">
            TOTAL : ' . number_format($prestation->prix_total, 2) . ' €
        </div>';
    }

    // Écrire le HTML dans le PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Générer le nom du fichier
    $filename = 'facture_prestation_' . $prestation->id . '_' . date('Y-m-d') . '.pdf';

    // Sortir le PDF
    $pdf->Output($filename, 'D');
    exit;
}
