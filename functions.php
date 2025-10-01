<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.4.4' );
define( 'EHP_THEME_SLUG', 'hello-elementor' );

define( 'HELLO_THEME_PATH', get_template_directory() );
define( 'HELLO_THEME_URL', get_template_directory_uri() );
define( 'HELLO_THEME_ASSETS_PATH', HELLO_THEME_PATH . '/assets/' );
define( 'HELLO_THEME_ASSETS_URL', HELLO_THEME_URL . '/assets/' );
define( 'HELLO_THEME_SCRIPTS_PATH', HELLO_THEME_ASSETS_PATH . 'js/' );
define( 'HELLO_THEME_SCRIPTS_URL', HELLO_THEME_ASSETS_URL . 'js/' );
define( 'HELLO_THEME_STYLE_PATH', HELLO_THEME_ASSETS_PATH . 'css/' );
define( 'HELLO_THEME_STYLE_URL', HELLO_THEME_ASSETS_URL . 'css/' );
define( 'HELLO_THEME_IMAGES_PATH', HELLO_THEME_ASSETS_PATH . 'images/' );
define( 'HELLO_THEME_IMAGES_URL', HELLO_THEME_ASSETS_URL . 'images/' );

// Configuration optimisée pour snippets Elementor avec code inline
// Pas besoin d'enqueue de fichier externe, juste les fonctions utilitaires

// Fonction utilitaire pour générer les variables AJAX dans les snippets
function get_ajax_prestations_vars() {
    if (!is_user_logged_in()) {
        return array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => '',
            'user_logged_in' => false
        );
    }
    
    return array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sauvegarder_prestation_nonce'),
        'user_logged_in' => true
    );
}

// Fonction pour afficher les variables AJAX en JavaScript (à utiliser dans les snippets)
function render_ajax_prestations_vars() {
    $vars = get_ajax_prestations_vars();
    ?>
    <script>
    window.ajax_object = window.ajax_object || {
        ajax_url: '<?php echo esc_js($vars['ajax_url']); ?>',
        nonce: '<?php echo esc_js($vars['nonce']); ?>',
        user_logged_in: <?php echo $vars['user_logged_in'] ? 'true' : 'false'; ?>
    };
    </script>
    <?php
}

// Handler AJAX pour sauvegarder une prestation (compatible avec my_account.php)
add_action('wp_ajax_sauvegarder_prestation_ajax', 'sauvegarder_prestation_ajax_handler');
add_action('wp_ajax_nopriv_sauvegarder_prestation_ajax', 'sauvegarder_prestation_ajax_handler');

function sauvegarder_prestation_ajax_handler() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'sauvegarder_prestation_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Utilisateur non connecté');
        return;
    }
    
    // Créer la table si nécessaire
    global $wpdb;
    $table_name = $wpdb->prefix . 'demandes_prestations';
    
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        type_prestation varchar(255) NOT NULL,
        type_fourche varchar(255),
        description text,
        modele_velo varchar(255),
        annee_velo varchar(50),
        statut varchar(50) DEFAULT 'attente',
        date_creation datetime DEFAULT CURRENT_TIMESTAMP,
        prix_total decimal(10,2) DEFAULT 0,
        numero_suivi varchar(50),
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Validation des données obligatoires
    $type_prestation = isset($_POST['type_prestation']) ? sanitize_text_field($_POST['type_prestation']) : '';
    if (empty($type_prestation)) {
        wp_send_json_error('Le type de prestation est obligatoire');
        return;
    }
    
    // Préparer les données
    // Utiliser le numéro de commande existant comme numéro de suivi
    $numero_suivi = isset($_POST['numero_suivi']) ? sanitize_text_field($_POST['numero_suivi']) : '';
    
    $data = array(
        'user_id' => $user_id,
        'type_prestation' => $type_prestation,
        'type_fourche' => isset($_POST['type_fourche']) ? sanitize_text_field($_POST['type_fourche']) : '',
        'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
        'modele_velo' => isset($_POST['modele_velo']) ? sanitize_text_field($_POST['modele_velo']) : '',
        'annee_velo' => isset($_POST['annee_velo']) ? sanitize_text_field($_POST['annee_velo']) : '',
        'statut' => isset($_POST['statut']) ? sanitize_text_field($_POST['statut']) : 'attente',
        'prix_total' => isset($_POST['prix_total']) ? floatval($_POST['prix_total']) : 0,
        'numero_suivi' => $numero_suivi,
        'date_creation' => current_time('mysql')
    );
    
    // Insérer dans la base de données
    $result = $wpdb->insert($table_name, $data);
    
    if ($result !== false) {
        $prestation_id = $wpdb->insert_id;
        wp_send_json_success(array(
            'message' => 'Prestation sauvegardée avec succès',
            'prestation_id' => $prestation_id,
            'numero_suivi' => $numero_suivi
        ));
    } else {
        // Diagnostic d'erreur plus détaillé
        $error_message = 'Erreur lors de la sauvegarde en base de données';
        if ($wpdb->last_error) {
            $error_message .= ': ' . $wpdb->last_error;
        }
        wp_send_json_error($error_message);
    }
}

// ====================================
// ADMINISTRATION DES PRESTATIONS
// ====================================

// Ajouter le menu d'administration des prestations
add_action('admin_menu', 'ajouter_menu_prestations_admin');

function ajouter_menu_prestations_admin() {
    add_menu_page(
        'Gestion des Prestations',           // Titre de la page
        'Prestations',                       // Titre du menu
        'manage_options',                    // Capacité requise
        'gestion-prestations',               // Slug de la page
        'afficher_page_prestations_admin',   // Fonction callback
        'dashicons-tools',                   // Icône
        30                                   // Position dans le menu
    );
}

// Afficher la page d'administration des prestations
function afficher_page_prestations_admin() {
    global $wpdb;
    $table_prestations = $wpdb->prefix . 'demandes_prestations';
    
    // Traitement des actions
    if (isset($_POST['action']) && $_POST['action'] === 'changer_statut') {
        $prestation_id = intval($_POST['prestation_id']);
        $nouveau_statut = sanitize_text_field($_POST['nouveau_statut']);
        
        $result = $wpdb->update(
            $table_prestations,
            array('statut' => $nouveau_statut),
            array('id' => $prestation_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            echo '<div class="notice notice-success"><p>Statut mis à jour avec succès !</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Erreur lors de la mise à jour du statut.</p></div>';
        }
    }
    
    // Filtres
    $filtre_statut = isset($_GET['statut']) ? sanitize_text_field($_GET['statut']) : '';
    $filtre_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
    $filtre_fourche = isset($_GET['fourche']) ? sanitize_text_field($_GET['fourche']) : '';
    $recherche = isset($_GET['recherche']) ? sanitize_text_field($_GET['recherche']) : '';
    
    // Construire la requête avec filtres
    $where_conditions = array();
    $where_values = array();
    
    if (!empty($filtre_statut)) {
        $where_conditions[] = "statut = %s";
        $where_values[] = $filtre_statut;
    }
    
    if (!empty($filtre_type)) {
        $where_conditions[] = "type_prestation = %s";
        $where_values[] = $filtre_type;
    }
    
    if (!empty($filtre_fourche)) {
        $where_conditions[] = "type_fourche = %s";
        $where_values[] = $filtre_fourche;
    }
    
    if (!empty($recherche)) {
        $where_conditions[] = "(modele_velo LIKE %s OR description LIKE %s OR type_fourche LIKE %s)";
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
    }
    
    $query = "SELECT * FROM $table_prestations" . $where_clause . " ORDER BY date_creation DESC";
    
    if (!empty($where_values)) {
        $prestations = $wpdb->get_results($wpdb->prepare($query, $where_values));
    } else {
        $prestations = $wpdb->get_results($query);
    }
    
    // Récupérer les types de prestations et fourches pour les filtres
    $types_prestations = $wpdb->get_col("SELECT DISTINCT type_prestation FROM $table_prestations ORDER BY type_prestation");
    $types_fourches = $wpdb->get_col("SELECT DISTINCT type_fourche FROM $table_prestations WHERE type_fourche IS NOT NULL AND type_fourche != '' ORDER BY type_fourche");
    
    ?>
    <div class="wrap">
        <h1>Gestion des Prestations</h1>
        
        <!-- Filtres -->
        <div class="tablenav top">
            <form method="get" style="display: inline-block;">
                <input type="hidden" name="page" value="gestion-prestations">
                
                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="attente" <?php selected($filtre_statut, 'attente'); ?>>En attente</option>
                    <option value="en_cours" <?php selected($filtre_statut, 'en_cours'); ?>>En cours</option>
                    <option value="terminee" <?php selected($filtre_statut, 'terminee'); ?>>Terminée</option>
                </select>
                
                <select name="type">
                    <option value="">Tous les types</option>
                    <?php foreach ($types_prestations as $type) : ?>
                        <option value="<?php echo esc_attr($type); ?>" <?php selected($filtre_type, $type); ?>>
                            <?php echo esc_html($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="fourche">
                    <option value="">Toutes les fourches</option>
                    <?php foreach ($types_fourches as $fourche) : ?>
                        <option value="<?php echo esc_attr($fourche); ?>" <?php selected($filtre_fourche, $fourche); ?>>
                            <?php echo esc_html($fourche); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="recherche" placeholder="Rechercher..." value="<?php echo esc_attr($recherche); ?>">
                
                <input type="submit" class="button" value="Filtrer">
                
                <?php if (!empty($filtre_statut) || !empty($filtre_type) || !empty($filtre_fourche) || !empty($recherche)) : ?>
                    <a href="?page=gestion-prestations" class="button">Effacer les filtres</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tableau des prestations -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>Type de pièce</th>
                    <th>Modèle précis de pièce</th>
                    <th>Modèle et année du vélo</th>
                    <th>N° de suivi</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prestations)) : ?>
                    <?php foreach ($prestations as $prestation) : ?>
                        <?php
                        $user = get_userdata($prestation->user_id);
                        $user_name = $user ? $user->display_name . ' (' . $user->user_email . ')' : 'Utilisateur supprimé';
                        
                        $statut_colors = array(
                            'attente' => '#f39c12',
                            'en_cours' => '#3498db',
                            'terminee' => '#27ae60'
                        );
                        
                        $statut_color = isset($statut_colors[$prestation->statut]) ? $statut_colors[$prestation->statut] : '#95a5a6';
                        ?>
                        <tr>
                            <td><strong>#<?php echo $prestation->id; ?></strong></td>
                            <td><?php echo esc_html($user_name); ?></td>
                            <td><?php echo esc_html($prestation->type_prestation); ?></td>
                            <td><?php echo esc_html($prestation->type_fourche ?: 'Non spécifié'); ?></td>
                            <td><?php echo esc_html($prestation->modele_velo . ' (' . $prestation->annee_velo . ')'); ?></td>
                            <td>
                                <?php if ($prestation->numero_suivi): ?>
                                    <strong style="color: #FF3F22;"><?php echo esc_html($prestation->numero_suivi); ?></strong>
                                <?php else: ?>
                                    <em style="color: #999;">Non généré</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo esc_html(substr($prestation->description, 0, 100)); ?>
                                    <?php if (strlen($prestation->description) > 100) echo '...'; ?>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($prestation->date_creation)); ?></td>
                            <td>
                                <span style="color: <?php echo $statut_color; ?>; font-weight: bold;">
                                    <?php 
                                    $statut_labels = array(
                                        'attente' => 'En attente',
                                        'en_cours' => 'En cours',
                                        'terminee' => 'Terminée'
                                    );
                                    echo $statut_labels[$prestation->statut] ?? ucfirst($prestation->statut);
                                    ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="action" value="changer_statut">
                                    <input type="hidden" name="prestation_id" value="<?php echo $prestation->id; ?>">
                                    
                                    <select name="nouveau_statut" onchange="this.form.submit()">
                                        <option value="">Changer statut</option>
                                        <option value="attente" <?php echo $prestation->statut === 'attente' ? 'disabled' : ''; ?>>
                                            En attente
                                        </option>
                                        <option value="en_cours" <?php echo $prestation->statut === 'en_cours' ? 'disabled' : ''; ?>>
                                            En cours
                                        </option>
                                        <option value="terminee" <?php echo $prestation->statut === 'terminee' ? 'disabled' : ''; ?>>
                                            Terminée
                                        </option>
                                    </select>
                                </form>
                                
                                <button class="button button-small" onclick="voirDetails(<?php echo $prestation->id; ?>)">
                                    Détails
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">
                            Aucune prestation trouvée.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p><strong>Total : <?php echo count($prestations); ?> prestation(s)</strong></p>
    </div>
    
    <!-- Modal pour les détails -->
    <div id="modal-details" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 600px; width: 90%;">
            <h3>Détails de la prestation</h3>
            <div id="contenu-details"></div>
            <button onclick="fermerModal()" class="button">Fermer</button>
        </div>
    </div>
    
    <script>
    function voirDetails(prestationId) {
        // Récupérer les détails via AJAX
        jQuery.post(ajaxurl, {
            action: 'obtenir_details_prestation',
            prestation_id: prestationId,
            nonce: '<?php echo wp_create_nonce('details_prestation_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                document.getElementById('contenu-details').innerHTML = response.data;
                document.getElementById('modal-details').style.display = 'block';
            } else {
                alert('Erreur lors du chargement des détails');
            }
        });
    }
    
    function fermerModal() {
        document.getElementById('modal-details').style.display = 'none';
    }
    
    // Fermer le modal en cliquant en dehors
    document.getElementById('modal-details').onclick = function(e) {
        if (e.target === this) {
            fermerModal();
        }
    }
    </script>
    
    <style>
    .wrap h1 {
        margin-bottom: 20px;
    }
    
    .tablenav {
        margin: 10px 0;
        padding: 10px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .tablenav select, .tablenav input[type="text"] {
        margin-right: 10px;
    }
    
    .wp-list-table th {
        font-weight: bold;
    }
    
    .wp-list-table td {
        vertical-align: middle;
    }
    
    .button-small {
        font-size: 11px;
        padding: 3px 8px;
        height: auto;
        margin-left: 5px;
    }
    </style>
    <?php
}

// Action AJAX pour obtenir les détails d'une prestation
add_action('wp_ajax_obtenir_details_prestation', 'obtenir_details_prestation_ajax');

function obtenir_details_prestation_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'details_prestation_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    
    global $wpdb;
    $table_prestations = $wpdb->prefix . 'demandes_prestations';
    $prestation_id = intval($_POST['prestation_id']);
    
    $prestation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_prestations WHERE id = %d",
        $prestation_id
    ));
    
    if (!$prestation) {
        wp_send_json_error('Prestation non trouvée');
        return;
    }
    
    $user = get_userdata($prestation->user_id);
    $user_info = $user ? $user->display_name . ' (' . $user->user_email . ')' : 'Utilisateur supprimé';
    
    $details = '<table style="width: 100%; border-collapse: collapse;">';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">ID:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">#' . $prestation->id . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Utilisateur:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($user_info) . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Type:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->type_prestation) . '</td></tr>';
    
    // Affichage différencié pour type de fourche et modèle
    if (!empty($prestation->type_fourche)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Type de fourche:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->type_fourche) . '</td></tr>';
    }
    
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Modèle vélo:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->modele_velo) . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Année vélo:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->annee_velo) . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Statut:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->statut) . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Date création:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . date('d/m/Y H:i:s', strtotime($prestation->date_creation)) . '</td></tr>';
    
    if ($prestation->description) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Description:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . nl2br(esc_html($prestation->description)) . '</td></tr>';
    }
    
    if ($prestation->prix_total > 0) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Prix total:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . wc_price($prestation->prix_total) . '</td></tr>';
    }
    
    if ($prestation->numero_suivi) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">N° suivi:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->numero_suivi) . '</td></tr>';
    }
    
    $details .= '</table>';
    
    wp_send_json_success($details);
}

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				HELLO_THEME_STYLE_URL . 'reset.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				HELLO_THEME_STYLE_URL . 'theme.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				HELLO_THEME_STYLE_URL . 'header-footer.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}

add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_filter( 'woocommerce_order_item_display_meta_key', '__return_false' );


add_action('wp_ajax_envoyer_demande_remarque', 'envoyer_demande_remarque');
add_action('wp_ajax_nopriv_envoyer_demande_remarque', 'envoyer_demande_remarque');
function envoyer_demande_remarque() {
    $remarque = sanitize_text_field($_POST['remarque']);
    $admin_email = get_option('admin_email');
    $attachments = array();

    if (!empty($_FILES['piece_jointe']['name'][0])) {
        foreach ($_FILES['piece_jointe']['tmp_name'] as $key => $tmp_name) {
            $file = $_FILES['piece_jointe'];
            $uploaded = wp_upload_bits($file['name'][$key], null, file_get_contents($tmp_name));
            if (!$uploaded['error']) {
                $attachments[] = $uploaded['file'];
            }
        }
    }

	// Ajout de l'utilisateur en copie (CC) et infos dans l'objet
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
	$headers = '';
	if ($user_email) {
		$headers = array('Cc: ' . $user_email);
	}
	// Ajout infos utilisateur dans l'objet
	$subject = 'Nouvelle demande via formulaire';
	$infos = [];
	if ($user_email) $infos[] = 'Email: ' . $user_email;
	if ($user_nom) $infos[] = 'Nom: ' . $user_nom;
	if ($user_prenom) $infos[] = 'Prénom: ' . $user_prenom;
	if ($user_tel) $infos[] = 'Tel: ' . $user_tel;
	if (!empty($infos)) $subject .= ' | ' . implode(' | ', $infos);

	wp_mail($admin_email, $subject, $remarque, $headers, $attachments);
	wp_send_json_success();
}
add_action('wp_ajax_envoyer_form_fox', 'envoyer_form_fox');
add_action('wp_ajax_nopriv_envoyer_form_fox', 'envoyer_form_fox');

//Formulaire d'amortisseur/tige selle
function envoyer_form_fox() {
	// Récupération des données
	$url = sanitize_text_field($_POST['url']);
	$prestation = sanitize_text_field($_POST['option1']);
	$date_revision = sanitize_text_field($_POST['date_revision']);
	$poids_pilote = sanitize_text_field($_POST['poids_pilote']);
	$modele_annee = sanitize_text_field($_POST['modele_annee']);
	$remarques = sanitize_textarea_field($_POST['remarques']);
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

	// Construction du message
	$message = "Numéro de commande : $order_number\n";
	$message .= "Prestation : $prestation\n";
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

    // Envoi à l'admin avec l'utilisateur en copie (CC)
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
	} elseif (strpos($current_url, 'tige-de-selle-oneup') !== false) {
		$subject .= ' - tige-de-selle-oneup';
	} else {
		$subject .= $current_url;
	}
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
	wp_send_json_success(['message' => 'Formulaire envoyé avec succès !', 'order_number' => $order_number]);
}
add_action('wp_ajax_envoyer_form_fourche', 'envoyer_form_fourche');
add_action('wp_ajax_nopriv_envoyer_form_fourche', 'envoyer_form_fourche');

//Formulaire de fourche
function envoyer_form_fourche() {
	// Récupération des données
	$url = isset($_POST['url']) ? sanitize_text_field($_POST['url']) : '';
	$type_fourche = isset($_POST['type_fourche']) ? sanitize_text_field($_POST['type_fourche']) : '';
	$prestation = isset($_POST['prestation']) ? sanitize_text_field($_POST['prestation']) : '';
	$prestations = isset($_POST['prestations']) ? $_POST['prestations'] : [];
	$type_prestation = isset($_POST['type_prestation']) ? sanitize_text_field($_POST['type_prestation']) : '';
	$pratique = isset($_POST['pratique']) ? sanitize_text_field($_POST['pratique']) : '';
	$symptomes = isset($_POST['symptomes']) ? $_POST['symptomes'] : [];
	$usages = isset($_POST['usage']) ? $_POST['usage'] : [];
	$date_revision = isset($_POST['date_revision']) ? sanitize_text_field($_POST['date_revision']) : '';
	$poids_pilote = isset($_POST['poids_pilote']) ? sanitize_text_field($_POST['poids_pilote']) : '';
	$modele_annee = isset($_POST['modele_annee']) ? sanitize_text_field($_POST['modele_annee']) : '';
	$remarques = isset($_POST['remarques']) ? sanitize_textarea_field($_POST['remarques']) : '';
	$prix_total = isset($_POST['prix_total']) ? floatval($_POST['prix_total']) : 0;
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
	$message .= "=== INFORMATIONS FOURCHE ===\n";
	$message .= "Modèle de fourche : $type_fourche\n";
	$message .= "\n=== INFORMATIONS VÉLO ===\n";
	$message .= "Modèle et année du vélo : $modele_annee\n";
	$message .= "\n=== PRESTATIONS DEMANDÉES ===\n";
	
	// Prestations (nouveau format avec array)
	if (!empty($prestations) && is_array($prestations)) {
		$message .= "Prestations : " . implode(', ', array_map('sanitize_text_field', $prestations)) . "\n";
	} elseif (!empty($prestation)) {
		$message .= "Prestation : $prestation\n";
	}
	
	// Nouveaux champs
	$message .= "Type de prestation : $type_prestation\n";
	$message .= "Options supplémentaires : ".(is_array($usages) ? implode(', ', array_map('sanitize_text_field', $usages)) : '')."\n";
	$message .= "Pratique : $pratique\n";
	
	// Symptômes
	if (!empty($symptomes) && is_array($symptomes)) {
		$message .= "Symptômes : " . implode(', ', array_map('sanitize_text_field', $symptomes)) . "\n";
	}
	
	$message .= "\n=== INFORMATIONS TECHNIQUES ===\n";
	$message .= "Date de la dernière révision : $date_revision\n";
	$message .= "Poids du pilote : $poids_pilote kg\n";
	
	if (!empty($remarques)) {
		$message .= "\n=== REMARQUES ===\n";
		$message .= "$remarques\n";
	}
	
	// Prix total
	if ($prix_total > 0) {
		$message .= "\n=== PRIX ===\n";
		$message .= "Prix total estimé : " . number_format($prix_total, 2, ',', ' ') . " € TTC\n";
	}

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

    // Envoi à l'admin avec l'utilisateur en copie (CC)
	$subject = 'Nouvelle demande de prestation fourche';
	
	// Ajout du modèle de fourche dans le sujet
	if (!empty($type_fourche)) {
		$subject .= " - $type_fourche";
	}
	
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
	wp_send_json_success(['message' => 'Formulaire envoyé avec succès !', 'order_number' => $order_number]);
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
// //JS du fil d'ariane dans le header
// add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );
// function ajouter_fil_ariane_js() {
//     wp_enqueue_script(
//         'fil-d-arriane-js',
//         get_template_directory_uri() . '/assets/js/fil-d-arriane.js',
//         array(), // dépendances éventuelles
//         null, // version
//         false // false = dans le header, true = dans le footer
//     );
// }
// add_action('wp_enqueue_scripts', 'ajouter_fil_ariane_js');

//Photo par défeaut
add_filter( 'woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src' );
function custom_woocommerce_placeholder_img_src( $src ) {
    return 'https://doc-headshok.com/wp-content/uploads/2025/09/Photo-indisponible.png';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
 
function woo_custom_cart_button_text() {
return __('+', 'woocommerce');
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();