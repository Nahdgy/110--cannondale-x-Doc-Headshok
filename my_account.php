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
        update_user_meta($user_id, 'type_compte', sanitize_text_field($_POST['type_compte']));

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
    $type_compte = get_user_meta($user->ID, 'type_compte', true);
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
        'status'      => 'any'
    ]);

	$en_attente_paiement = 0;
    $en_cours = 0;
    $passees = 0;
    foreach ($customer_orders as $order_id) {
        $order = wc_get_order($order_id);
        if ($order->has_status('pending') || $order->has_status('wc-pending')) {
            $en_attente_paiement++;
        } elseif ($order->has_status(['processing', 'on-hold', 'wc-processing', 'wc-on-hold', 'wc-livraison', 'wc-livraison-colissi', 'wc-lpc_anomaly', 'wc-lpc_delivered', 'wc-lpc_ready_to_ship', 'wc-lpc_transit'])) {
            $en_cours++;
        } elseif ($order->has_status('completed') || $order->has_status('wc-completed')) {
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

        /* Styles pour les codes promo */
        .codes-promo-container {
            margin-top: 20px;
        }

        .codes-promo-liste {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .code-promo-item {
            background: linear-gradient(135deg, #ff3f22 0%, #db6e78 100%);
            border-radius: 12px;
            padding: 25px;
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .code-promo-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .code-promo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .code-promo-code {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 2px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px dashed rgba(255, 255, 255, 0.5);
        }

        .code-promo-statut {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .statut-actif {
            background: rgba(34, 197, 94, 0.9);
            color: #fff;
        }

        .statut-expired {
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
        }

        .code-promo-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .code-promo-info {
            flex: 1;
        }

        .code-promo-info p {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .code-promo-reduction {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 18px !important;
            margin-bottom: 12px !important;
        }

        .code-promo-expiry {
            font-weight: 600;
            opacity: 0.9;
        }

        .code-promo-minimum,
        .code-promo-usage {
            font-size: 13px !important;
            opacity: 0.85;
        }

        .code-promo-description {
            margin-top: 12px !important;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-style: italic;
            opacity: 0.9;
        }

        .code-promo-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .bouton-code-promo {
            background: #fff;
            color: #667eea !important;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            transition: all 0.2s ease;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bouton-code-promo:hover {
            background: #f0f0f0 !important;
            color: #667eea !important;
            text-decoration: none !important;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .bouton-utiliser {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            border: 2px solid #fff;
        }

        .bouton-utiliser:hover {
            background: rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }

        .codes-promo-vide {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
        }

        .codes-promo-vide p {
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 16px;
            color: #6c757d;
            margin: 10px 0;
        }

        .codes-promo-info {
            font-size: 14px !important;
            color: #adb5bd !important;
        }

        #codes-promo::before {
            content: "";
            display: block;
            width: 100%;
            height: 4px;
            background-color: #FF3F22;
            margin-bottom: 15px;
        }

        /* Styles pour les avoirs */
        .avoirs-container {
            margin-top: 20px;
        }

        .avoirs-liste {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .avoir-item {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border-radius: 12px;
            padding: 25px;
            color: #fff;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .avoir-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5);
        }

        .avoir-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .avoir-code {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .avoir-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.9;
        }

        .avoir-code-text {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 22px;
            letter-spacing: 2px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px dashed rgba(255, 255, 255, 0.5);
            display: inline-block;
        }

        .avoir-statut {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .statut-disponible {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            border: 2px solid #fff;
        }

        .statut-utilise {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        .avoir-details {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: start;
        }

        .avoir-montant {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 36px;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
        }

        .avoir-info {
            flex: 1;
        }

        .avoir-info p {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .avoir-description {
            margin-top: 12px !important;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-style: italic;
            opacity: 0.9;
        }

        .avoir-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .bouton-avoir {
            background: #fff;
            color: #4CAF50 !important;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            transition: all 0.2s ease;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bouton-avoir:hover {
            background: #f0f0f0 !important;
            color: #4CAF50 !important;
            text-decoration: none !important;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .bouton-avoir.bouton-utiliser {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            border: 2px solid #fff;
        }

        .bouton-avoir.bouton-utiliser:hover {
            background: rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }

        .avoir-aide {
            grid-column: 1 / -1;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .avoir-aide p {
            margin: 0;
            opacity: 0.85;
            font-size: 13px;
        }

        .avoirs-vide {
            background: #e8f5e9;
            border: 2px dashed #4CAF50;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
        }

        .avoirs-vide p {
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 16px;
            color: #2e7d32;
            margin: 10px 0;
        }

        .avoirs-info {
            font-size: 14px !important;
            color: #66bb6a !important;
        }

        #avoirs::before {
            content: "";
            display: block;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
            margin-bottom: 15px;
        }

        /* Styles pour les retours */
        .retours-container {
            margin-top: 20px;
        }

        .retours-liste {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        .retour-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: box-shadow 0.2s ease;
        }

        .retour-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .retour-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .retour-numero {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: #FF3F22;
        }

        .retour-date {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 400;
            font-size: 14px;
            color: #6c757d;
        }

        .retour-statut {
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

        .statut-approuve {
            background: #4CAF50;
            color: white;
        }

        .statut-refuse {
            background: #F44336;
            color: white;
        }

        .statut-recu {
            background: #9C27B0;
            color: white;
        }

        .statut-rembourse {
            background: #22C55E;
            color: white;
        }

        .retour-details {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .retour-info {
            flex: 1;
        }

        .retour-info p {
            margin: 8px 0;
            font-size: 14px;
            color: #333;
        }

        .retour-produits {
            margin: 10px 0;
            padding-left: 20px;
        }

        .retour-produits li {
            font-size: 13px;
            color: #555;
            margin: 5px 0;
        }

        .retour-notes-admin {
            background: #fff3cd;
            border-left: 4px solid #FFA500;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .retour-notes-admin p {
            margin: 5px 0;
        }

        .retour-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .retour-montant {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: #FF3F22;
        }

        .retour-rembourse {
            background: #22C55E;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .nouvelle-demande-retour {
            background: #f0f0f0;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-top: 20px;
        }

        .nouvelle-demande-retour h3 {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .nouvelle-demande-retour p {
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .no-retours {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        /* Modal de demande de retour */
        .modal-retour {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            overflow-y: auto;
        }

        .modal-retour-content {
            background: white;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            position: relative;
        }

        .modal-retour-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #FF3F22;
            padding-bottom: 15px;
        }

        .modal-retour-header h2 {
            margin: 0;
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 24px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: #999;
            line-height: 1;
        }

        .modal-close:hover {
            color: #333;
        }

        .form-retour {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-retour label {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }

        .form-retour select,
        .form-retour textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 14px;
        }

        .form-retour textarea {
            min-height: 100px;
            resize: vertical;
        }

        .produits-selection {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .produit-checkbox {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .produit-checkbox:last-child {
            border-bottom: none;
        }

        .produit-checkbox input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .produit-checkbox label {
            margin: 0;
            font-weight: 400;
            cursor: pointer;
            flex: 1;
        }

        .message-retour-succes {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .message-retour-succes h3 {
            margin-top: 0;
            color: #155724;
        }

        .numero-retour-display {
            font-size: 24px;
            font-weight: bold;
            color: #FF3F22;
            margin: 15px 0;
        }

        #retours::before {
            content: "";
            display: block;
            width: 100%;
            height: 4px;
            background-color: #FF3F22;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .code-promo-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .code-promo-code {
                font-size: 18px;
            }

            .code-promo-details {
                flex-direction: column;
            }

            .code-promo-actions {
                align-items: flex-start;
                width: 100%;
            }

            .bouton-code-promo {
                width: 100%;
            }

            .retour-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .retour-details {
                flex-direction: column;
            }

            .retour-actions {
                align-items: flex-start;
                width: 100%;
            }

            .modal-retour-content {
                margin: 20px;
                padding: 20px;
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

        // Fonction pour copier le code promo dans le presse-papier
        window.copierCodePromo = function(code) {
            // Créer un élément temporaire pour copier le texte
            const tempInput = document.createElement('input');
            tempInput.value = code;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // Pour mobile
            
            try {
                document.execCommand('copy');
                
                // Afficher un message de confirmation
                const originalText = event.target.textContent;
                event.target.textContent = '✓ CODE COPIÉ !';
                event.target.style.background = '#22C55E';
                event.target.style.color = '#fff';
                
                setTimeout(function() {
                    event.target.textContent = originalText;
                    event.target.style.background = '';
                    event.target.style.color = '';
                }, 2000);
            } catch (err) {
                alert('Impossible de copier le code. Veuillez le copier manuellement : ' + code);
            }
            
            document.body.removeChild(tempInput);
        };

        // Fonction pour ouvrir le modal de demande de retour
        window.ouvrirModalRetour = function() {
            // Créer le modal
            const modal = document.createElement('div');
            modal.className = 'modal-retour';
            modal.id = 'modal-demande-retour';
            
            modal.innerHTML = `
                <div class="modal-retour-content">
                    <div class="modal-retour-header">
                        <h2>Demande de retour</h2>
                        <button class="modal-close" onclick="fermerModalRetour()">&times;</button>
                    </div>
                    <div id="retour-form-container">
                        <form class="form-retour" id="form-demande-retour">
                            <div>
                                <label for="commande-select">Sélectionnez la commande *</label>
                                <select id="commande-select" name="order_id" required>
                                    <option value="">Choisir une commande...</option>
                                    <?php
                                    // Récupérer toutes les commandes (sauf annulées et échouées)
                                    $eligible_orders = wc_get_orders(array(
                                        'customer_id' => $user->ID,
                                        'status' => array('completed', 'processing', 'on-hold', 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-livraison', 'wc-livraison-colissi', 'wc-lpc_delivered', 'wc-lpc_ready_to_ship', 'wc-lpc_transit'),
                                        'limit' => -1,
                                        'orderby' => 'date',
                                        'order' => 'DESC'
                                    ));
                                    
                                    foreach ($eligible_orders as $order) {
                                        $date_completed = $order->get_date_completed();
                                        if (!$date_completed) continue;
                                        
                                        echo '<option value="' . $order->get_id() . '" data-order-data=\'' . json_encode(array(
                                            'id' => $order->get_id(),
                                            'number' => $order->get_order_number(),
                                            'items' => array_map(function($item) {
                                                return array(
                                                    'id' => $item->get_id(),
                                                    'name' => $item->get_name(),
                                                    'quantity' => $item->get_quantity(),
                                                    'total' => $item->get_total()
                                                );
                                            }, $order->get_items())
                                        )) . '\'>';
                                        echo 'Commande #' . $order->get_order_number() . ' - ' . $date_completed->date('d/m/Y');
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div id="produits-container" style="display: none;">
                                <label>Produits à retourner *</label>
                                <div class="produits-selection" id="produits-selection">
                                    <!-- Les produits seront chargés dynamiquement -->
                                </div>
                            </div>
                            
                            <div>
                                <label for="motif-retour">Motif du retour *</label>
                                <select id="motif-retour" name="motif" required>
                                    <option value="">Sélectionner un motif...</option>
                                    <option value="Produit défectueux">Produit défectueux</option>
                                    <option value="Produit non conforme">Produit non conforme à la description</option>
                                    <option value="Mauvaise taille">Mauvaise taille/modèle</option>
                                    <option value="Changement d'avis">Changement d'avis</option>
                                    <option value="Article endommagé">Article endommagé lors de la livraison</option>
                                    <option value="Autre">Autre raison</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="description-retour">Description détaillée</label>
                                <textarea id="description-retour" name="description" placeholder="Expliquez en détail la raison de votre retour..."></textarea>
                            </div>
                            
                            <div style="text-align: center; margin-top: 20px;">
                                <button type="submit" class="bouton-commande" id="btn-submit-retour">
                                    ENVOYER LA DEMANDE
                                </button>
                                <button type="button" class="bouton-commande" onclick="fermerModalRetour()" style="background: #999; margin-left: 10px;">
                                    ANNULER
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Afficher le modal
            setTimeout(() => {
                modal.style.display = 'block';
            }, 100);
            
            // Gérer la sélection de commande
            const commandeSelect = document.getElementById('commande-select');
            const produitsContainer = document.getElementById('produits-container');
            const produitsSelection = document.getElementById('produits-selection');
            
            commandeSelect.addEventListener('change', function() {
                if (this.value) {
                    const option = this.options[this.selectedIndex];
                    const orderData = JSON.parse(option.getAttribute('data-order-data'));
                    
                    // Afficher les produits
                    produitsSelection.innerHTML = '';
                    orderData.items.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'produit-checkbox';
                        div.innerHTML = `
                            <input type="checkbox" id="produit-${item.id}" name="produits[]" value="${item.id}">
                            <label for="produit-${item.id}">
                                ${item.name} (x${item.quantity}) - ${item.total} €
                            </label>
                        `;
                        produitsSelection.appendChild(div);
                    });
                    
                    produitsContainer.style.display = 'block';
                } else {
                    produitsContainer.style.display = 'none';
                }
            });
            
            // Gérer la soumission du formulaire
            const form = document.getElementById('form-demande-retour');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const orderId = document.getElementById('commande-select').value;
                const motif = document.getElementById('motif-retour').value;
                const description = document.getElementById('description-retour').value;
                
                // Récupérer les produits sélectionnés
                const produitsChecked = document.querySelectorAll('#produits-selection input[type="checkbox"]:checked');
                const produits = Array.from(produitsChecked).map(cb => cb.value);
                
                if (!orderId || !motif || produits.length === 0) {
                    alert('Veuillez remplir tous les champs obligatoires et sélectionner au moins un produit.');
                    return;
                }
                
                const btnSubmit = document.getElementById('btn-submit-retour');
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'ENVOI EN COURS...';
                
                // Envoyer la demande via AJAX
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Afficher le message de succès
                                    document.getElementById('retour-form-container').innerHTML = `
                                        <div class="message-retour-succes">
                                            <h3>✓ Demande de retour enregistrée !</h3>
                                            <p>Votre numéro de retour :</p>
                                            <div class="numero-retour-display">${response.data.numero_retour}</div>
                                            <p>Vous allez recevoir un email avec les instructions pour retourner votre colis.</p>
                                            <p>Vous pouvez suivre l'état de votre demande dans cette page.</p>
                                            <button class="bouton-commande" onclick="window.location.reload()">
                                                OK
                                            </button>
                                        </div>
                                    `;
                                } else {
                                    alert('Erreur : ' + response.data);
                                    btnSubmit.disabled = false;
                                    btnSubmit.textContent = 'ENVOYER LA DEMANDE';
                                }
                            } catch (e) {
                                alert('Erreur lors du traitement de la demande.');
                                btnSubmit.disabled = false;
                                btnSubmit.textContent = 'ENVOYER LA DEMANDE';
                            }
                        }
                    }
                };
                
                const formData = 'action=creer_demande_retour' +
                    '&nonce=<?php echo wp_create_nonce('retour_nonce'); ?>' +
                    '&order_id=' + encodeURIComponent(orderId) +
                    '&motif=' + encodeURIComponent(motif) +
                    '&description=' + encodeURIComponent(description) +
                    '&produits=' + encodeURIComponent(JSON.stringify(produits));
                
                xhr.send(formData);
            });
        };
        
        window.fermerModalRetour = function() {
            const modal = document.getElementById('modal-demande-retour');
            if (modal) {
                modal.remove();
            }
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
                <a href="#retours">Mes retours</a>
                <a href="#prestations">Historique des prestations</a>
                <a href="#codes-promo">Mes codes promo</a>
                <a href="#avoirs">Mes avoirs</a>
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
                        <label for="type_compte">Type de compte</label>
                        <select id="type_compte" name="type_compte" class="disabled-input" disabled>
                            <option value="particulier" <?php selected($type_compte, 'particulier'); ?>>Particulier</option>
                            <option value="magasin" <?php selected($type_compte, 'magasin'); ?>>Magasin</option>
                        </select>
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
                            
                            // Récupérer le statut exact depuis WooCommerce
                            $order_status = $order->get_status();
                            $statut_text = wc_get_order_status_name($order_status);
                            $statut_class = 'statut-' . $order_status;
                            
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

            <!-- Section Mes Retours -->
            <div id="retours" class="section-compte">
                <h2>Mes demandes de retour</h2>
                <div class="retours-container">
                    <?php
                    $retours = obtenir_retours_utilisateur($user->ID);
                    
                    $statuts_labels_retour = array(
                        'en_attente' => 'En attente d\'examen',
                        'approuve' => 'Approuvé - Renvoyez votre colis',
                        'refuse' => 'Refusé',
                        'en_cours' => 'Retour en cours',
                        'recu' => 'Colis reçu',
                        'rembourse' => 'Remboursé',
                        'termine' => 'Terminé'
                    );
                    
                    if (!empty($retours)) {
                        echo '<div class="retours-liste">';
                        
                        foreach ($retours as $retour) {
                            $order = wc_get_order($retour->order_id);
                            
                            $statut_class = '';
                            switch($retour->statut) {
                                case 'en_attente':
                                    $statut_class = 'statut-attente';
                                    break;
                                case 'approuve':
                                    $statut_class = 'statut-approuve';
                                    break;
                                case 'refuse':
                                    $statut_class = 'statut-refuse';
                                    break;
                                case 'en_cours':
                                    $statut_class = 'statut-cours';
                                    break;
                                case 'recu':
                                    $statut_class = 'statut-recu';
                                    break;
                                case 'rembourse':
                                    $statut_class = 'statut-rembourse';
                                    break;
                                case 'termine':
                                    $statut_class = 'statut-terminee';
                                    break;
                                default:
                                    $statut_class = 'statut-attente';
                            }
                            
                            $statut_text = $statuts_labels_retour[$retour->statut] ?? ucfirst($retour->statut);
                            
                            echo '<div class="retour-item">';
                            echo '<div class="retour-header">';
                            echo '<div class="retour-numero">Retour #' . esc_html($retour->numero_retour) . '</div>';
                            echo '<div class="retour-date">' . date('d/m/Y H:i', strtotime($retour->date_demande)) . '</div>';
                            echo '<div class="retour-statut ' . $statut_class . '">' . $statut_text . '</div>';
                            echo '</div>';
                            
                            echo '<div class="retour-details">';
                            echo '<div class="retour-info">';
                            echo '<p><strong>Commande concernée :</strong> <a href="#commandes">#' . $order->get_order_number() . '</a></p>';
                            echo '<p><strong>Motif :</strong> ' . esc_html($retour->motif) . '</p>';
                            
                            if ($retour->description) {
                                echo '<p><strong>Description :</strong> ' . nl2br(esc_html($retour->description)) . '</p>';
                            }
                            
                            // Afficher les produits concernés
                            $produits = json_decode($retour->produits_concernes, true);
                            if ($produits && is_array($produits)) {
                                echo '<p><strong>Produits :</strong></p>';
                                echo '<ul class="retour-produits">';
                                foreach ($produits as $produit) {
                                    echo '<li>' . esc_html($produit['name']) . ' (x' . $produit['quantity'] . ')</li>';
                                }
                                echo '</ul>';
                            }
                            
                            if ($retour->numero_suivi_retour) {
                                echo '<p><strong>N° de suivi retour :</strong> ' . esc_html($retour->numero_suivi_retour) . '</p>';
                            }
                            
                            if ($retour->notes_admin) {
                                echo '<div class="retour-notes-admin">';
                                echo '<p><strong>📝 Message de notre équipe :</strong></p>';
                                echo '<p>' . nl2br(esc_html($retour->notes_admin)) . '</p>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            
                            echo '<div class="retour-actions">';
                            echo '<div class="retour-montant">' . wc_price($retour->montant_total) . '</div>';
                            
                            if ($retour->remboursement_effectue) {
                                echo '<div class="retour-rembourse">✓ Remboursé</div>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<p class="no-retours">Aucune demande de retour effectuée.</p>';
                    }
                    ?>
                    
                    <div class="nouvelle-demande-retour">
                        <h3>Demander un retour</h3>
                        <p>Vous souhaitez retourner un ou plusieurs produits d'une commande ? Sélectionnez la commande ci-dessous.</p>
                        
                        <button class="bouton-commande" onclick="ouvrirModalRetour()">+ NOUVELLE DEMANDE DE RETOUR</button>
                    </div>
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

            <!-- Section Codes Promo -->
            <div id="codes-promo" class="section-compte">
                <h2>Mes codes promo</h2>
                <div class="codes-promo-container">
                    <?php
                    // Récupérer tous les coupons WooCommerce assignés à cet utilisateur
                    $user_email = $user->user_email;
                    $user_coupons = obtenir_coupons_utilisateur($user_email);
                    
                    if (!empty($user_coupons)) {
                        echo '<div class="codes-promo-liste">';
                        
                        foreach ($user_coupons as $coupon_code) {
                            $coupon = new WC_Coupon($coupon_code);
                            
                            // Vérifier que le coupon existe et est valide
                            if (!$coupon->get_id()) {
                                continue;
                            }
                            
                            // Déterminer le type de réduction
                            $discount_type = $coupon->get_discount_type();
                            $amount = $coupon->get_amount();
                            
                            $discount_text = '';
                            switch($discount_type) {
                                case 'percent':
                                    $discount_text = $amount . '% de réduction';
                                    break;
                                case 'fixed_cart':
                                    $discount_text = wc_price($amount) . ' de réduction sur le panier';
                                    break;
                                case 'fixed_product':
                                    $discount_text = wc_price($amount) . ' de réduction par produit';
                                    break;
                                default:
                                    $discount_text = 'Réduction spéciale';
                            }
                            
                            // Date d'expiration
                            $expiry_date = $coupon->get_date_expires();
                            $expiry_text = $expiry_date ? 'Expire le ' . $expiry_date->date('d/m/Y') : 'Pas de date d\'expiration';
                            
                            // Montant minimum
                            $minimum_amount = $coupon->get_minimum_amount();
                            $minimum_text = $minimum_amount ? 'Minimum d\'achat : ' . wc_price($minimum_amount) : '';
                            
                            // Nombre d'utilisations
                            $usage_limit = $coupon->get_usage_limit();
                            $usage_count = $coupon->get_usage_count();
                            $usage_text = '';
                            if ($usage_limit) {
                                $remaining = $usage_limit - $usage_count;
                                $usage_text = 'Utilisations restantes : ' . $remaining . '/' . $usage_limit;
                            }
                            
                            // Vérifier si le coupon est expiré
                            $is_expired = $expiry_date && $expiry_date->getTimestamp() < time();
                            $statut_class = $is_expired ? 'statut-expired' : 'statut-actif';
                            $statut_text = $is_expired ? 'Expiré' : 'Actif';
                            
                            echo '<div class="code-promo-item">';
                            echo '<div class="code-promo-header">';
                            echo '<div class="code-promo-code">' . esc_html($coupon_code) . '</div>';
                            echo '<div class="code-promo-statut ' . $statut_class . '">' . $statut_text . '</div>';
                            echo '</div>';
                            
                            echo '<div class="code-promo-details">';
                            echo '<div class="code-promo-info">';
                            echo '<p class="code-promo-reduction">' . $discount_text . '</p>';
                            echo '<p class="code-promo-expiry">' . $expiry_text . '</p>';
                            
                            if ($minimum_text) {
                                echo '<p class="code-promo-minimum">' . $minimum_text . '</p>';
                            }
                            
                            if ($usage_text) {
                                echo '<p class="code-promo-usage">' . $usage_text . '</p>';
                            }
                            
                            if ($coupon->get_description()) {
                                echo '<p class="code-promo-description">' . esc_html($coupon->get_description()) . '</p>';
                            }
                            
                            echo '</div>';
                            
                            if (!$is_expired) {
                                echo '<div class="code-promo-actions">';
                                echo '<button class="bouton-code-promo" onclick="copierCodePromo(\'' . esc_js($coupon_code) . '\')">📋 COPIER LE CODE</button>';
                                echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="bouton-code-promo bouton-utiliser">UTILISER MAINTENANT</a>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<div class="codes-promo-vide">';
                        echo '<p>Vous n\'avez pas encore de codes promo assignés.</p>';
                        echo '<p class="codes-promo-info">Les codes promo vous seront attribués par notre équipe pour des offres spéciales ou des récompenses de fidélité.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Section Avoirs -->
            <div id="avoirs" class="section-compte">
                <h2>Mes avoirs</h2>
                <div class="avoirs-container">
                    <?php
                    // Récupérer tous les avoirs de l'utilisateur
                    $user_email = $user->user_email;
                    $user_avoirs = obtenir_avoirs_utilisateur($user_email);
                    
                    if (!empty($user_avoirs)) {
                        echo '<div class="avoirs-liste">';
                        
                        foreach ($user_avoirs as $avoir) {
                            $is_expired = $avoir['date_expiration'] && $avoir['date_expiration']->getTimestamp() < time();
                            $statut_class = $is_expired ? 'statut-expired' : ($avoir['utilise'] ? 'statut-utilise' : 'statut-disponible');
                            $statut_text = $is_expired ? 'Expiré' : ($avoir['utilise'] ? 'Utilisé' : 'Disponible');
                            
                            echo '<div class="avoir-item">';
                            echo '<div class="avoir-header">';
                            echo '<div class="avoir-code">';
                            echo '<span class="avoir-label">💰 BON D\'AVOIR</span>';
                            echo '<span class="avoir-code-text">' . esc_html($avoir['code']) . '</span>';
                            echo '</div>';
                            echo '<div class="avoir-statut ' . $statut_class . '">' . $statut_text . '</div>';
                            echo '</div>';
                            
                            echo '<div class="avoir-details">';
                            echo '<div class="avoir-montant">' . wc_price($avoir['montant']) . '</div>';
                            
                            echo '<div class="avoir-info">';
                            
                            if ($avoir['date_emission']) {
                                echo '<p><strong>📅 Émis le :</strong> ' . date('d/m/Y', strtotime($avoir['date_emission'])) . '</p>';
                            }
                            
                            if ($avoir['date_expiration']) {
                                $expiry_date = $avoir['date_expiration']->date('d/m/Y');
                                $days_remaining = ceil(($avoir['date_expiration']->getTimestamp() - time()) / (60 * 60 * 24));
                                
                                if ($days_remaining > 0 && $days_remaining <= 30 && !$avoir['utilise']) {
                                    echo '<p><strong>⏰ Expire le :</strong> <span style="color: #FF3F22;">' . $expiry_date . ' (dans ' . $days_remaining . ' jours)</span></p>';
                                } else {
                                    echo '<p><strong>⏰ Expire le :</strong> ' . $expiry_date . '</p>';
                                }
                            }
                            
                            if ($avoir['retour_id']) {
                                global $wpdb;
                                $table_retours = $wpdb->prefix . 'demandes_retours';
                                $retour = $wpdb->get_row($wpdb->prepare("SELECT numero_retour FROM $table_retours WHERE id = %d", $avoir['retour_id']));
                                if ($retour) {
                                    echo '<p><strong>🔄 Suite au retour :</strong> #' . esc_html($retour->numero_retour) . '</p>';
                                }
                            }
                            
                            if ($avoir['description']) {
                                echo '<p class="avoir-description">' . esc_html($avoir['description']) . '</p>';
                            }
                            
                            echo '</div>';
                            
                            if (!$is_expired && !$avoir['utilise']) {
                                echo '<div class="avoir-actions">';
                                echo '<button class="bouton-avoir" onclick="copierCodePromo(\'' . esc_js($avoir['code']) . '\')">📋 COPIER LE CODE</button>';
                                echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="bouton-avoir bouton-utiliser">🛒 UTILISER MAINTENANT</a>';
                                echo '</div>';
                                
                                echo '<div class="avoir-aide">';
                                echo '<p><small><strong>Comment utiliser ?</strong><br>';
                                echo 'Ajoutez vos produits au panier, puis saisissez ce code dans le champ "Code promo" lors du paiement. Le montant sera automatiquement déduit de votre commande.</small></p>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<div class="avoirs-vide">';
                        echo '<p>Vous n\'avez pas d\'avoir disponible actuellement.</p>';
                        echo '<p class="avoirs-info">Les avoirs sont générés suite à des retours de commande ou des remboursements partiels. Ils apparaîtront automatiquement ici une fois émis par notre équipe.</p>';
                        echo '</div>';
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
