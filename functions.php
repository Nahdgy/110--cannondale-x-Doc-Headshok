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
	$usages = isset($_POST['usage']) ? $_POST['usage'] : [];
	$date_revision = isset($_POST['date_revision']) ? sanitize_text_field($_POST['date_revision']) : '';
	$poids_pilote = isset($_POST['poids_pilote']) ? sanitize_text_field($_POST['poids_pilote']) : '';
	$modele_annee = isset($_POST['modele_annee']) ? sanitize_text_field($_POST['modele_annee']) : '';
	$remarques = isset($_POST['remarques']) ? sanitize_textarea_field($_POST['remarques']) : '';
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
	$message .= "Type de fourche : $type_fourche\n";
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

    // Envoi à l'admin avec l'utilisateur en copie (CC)
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
