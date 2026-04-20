<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.4.7' );
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

            add_theme_support( 'editor-styles' );
            add_editor_style( 'assets/css/editor-styles.css' );

            if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
                add_theme_support( 'woocommerce' );
                add_theme_support( 'wc-product-gallery-zoom' );
                add_theme_support( 'wc-product-gallery-lightbox' );
                add_theme_support( 'wc-product-gallery-slider' );
            }
        }
    }
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
    $theme_version_option_name = 'hello_theme_version';
    $hello_theme_db_version = get_option( $theme_version_option_name );

    if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
        update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
    }
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
    function hello_elementor_display_header_footer() {
        $hello_elementor_header_footer = true;

        return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
    }
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
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
     * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
     */
    function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
        if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
            $elementor_theme_manager->register_all_core_location();
        }
    }
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
    function hello_elementor_content_width() {
        $GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
    }
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
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
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

$hello_settings_functions = get_template_directory() . '/includes/settings-functions.php';
if ( file_exists( $hello_settings_functions ) ) {
    require $hello_settings_functions;
}

$hello_elementor_functions = get_template_directory() . '/includes/elementor-functions.php';
if ( file_exists( $hello_elementor_functions ) ) {
    require $hello_elementor_functions;
}

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
    function hello_elementor_customizer() {
        if ( ! is_customize_preview() ) {
            return;
        }

        if ( ! hello_elementor_display_header_footer() ) {
            return;
        }

        $customizer_functions = get_template_directory() . '/includes/customizer-functions.php';
        if ( file_exists( $customizer_functions ) ) {
            require $customizer_functions;
        }
    }
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
    /**
     * @param bool $val default value.
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

if ( ! function_exists( 'hello_elementor_body_open' ) ) {
    function hello_elementor_body_open() {
        wp_body_open();
    }
}

add_filter( 'template_include', 'cannondale_use_custom_search_template', 99 );

function cannondale_use_custom_search_template( $template ) {
    if ( is_admin() || ! is_search() ) {
        return $template;
    }

    $custom_search_template = HELLO_THEME_ASSETS_PATH . 'search.php';

    if ( file_exists( $custom_search_template ) ) {
        return $custom_search_template;
    }

    return $template;
}

// Supprimer le robots natif WordPress
add_action( 'init', function() {
    remove_action( 'wp_head', 'wp_robots', 1 );
});

// Forcer noindex sur catégories et tags produits
add_filter( 'rank_math/frontend/robots', function( $robots ) {

    if ( is_tax( 'product_tag' ) || is_tax( 'product_cat' ) ) {
        $robots['index'] = 'noindex';
        $robots['follow'] = 'follow';
    }

    return $robots;
});
// Configuration optimisée pour snippets Elementor avec code inline
// Pas besoin d'enqueue de fichier externe, juste les fonctions utilitaires

// Cible les categories modele de velo (URLs natives WooCommerce)
function cannondale_is_velo_modele_category_context() {
    if (is_admin() || !is_tax('product_cat')) {
        return false;
    }

    $term = get_queried_object();
    if (!($term instanceof WP_Term) || $term->taxonomy !== 'product_cat') {
        return false;
    }

    $velo_term = get_term_by('slug', 'velo', 'product_cat');
    if (!$velo_term || is_wp_error($velo_term)) {
        return false;
    }

    $allowed_pratiques = array('vtt', 'vae', 'route', 'velos-urbains');
    $parent_term = !empty($term->parent) ? get_term((int) $term->parent, 'product_cat') : null;

    if (!$parent_term || is_wp_error($parent_term)) {
        return false;
    }

    $is_model_under_allowed_pratique = in_array($parent_term->slug, $allowed_pratiques, true)
        && term_is_ancestor_of($velo_term, $parent_term, 'product_cat');

    return (bool) $is_model_under_allowed_pratique;
}

// Rend le template custom directement depuis ce fichier pour eviter les conflits template_include.
function cannondale_render_velo_modele_template() {
    if (!cannondale_is_velo_modele_category_context()) {
        return;
    }

    global $wp_query;
    if (isset($wp_query->is_404) && $wp_query->is_404) {
        $wp_query->is_404 = false;
        status_header(200);
    }

    get_header();
    echo '<main id="primary" class="site-main">';
    echo afficher_produits_par_modele();
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'cannondale_render_velo_modele_template', 1);

//Fonction de détail modèle vélo
if ( ! function_exists( 'afficher_produits_par_modele' ) ) {
	function afficher_produits_par_modele() {

		ob_start();

		$modele   = get_query_var('modele');
		$pratique = get_query_var('pratique');

        // Fallback pour les URLs natives WooCommerce (/product-category/...)
        if (!$modele && is_tax('product_cat')) {
            $queried_term = get_queried_object();
            if ($queried_term instanceof WP_Term && $queried_term->taxonomy === 'product_cat') {
                $modele = $queried_term->slug;

                if (!empty($queried_term->parent)) {
                    $parent_term = get_term((int) $queried_term->parent, 'product_cat');
                    if ($parent_term && !is_wp_error($parent_term)) {
                        $pratique = $parent_term->slug;
                    }
                }
            }
        }

	

		if (!$modele) {
			echo '<p>Aucun modèle sélectionné.</p>';
			return ob_get_clean();
		}

		$slug = sanitize_text_field($modele);
		$modele_slug = $slug;
		$instance_id = 'pm_' . preg_replace('/[^A-Za-z0-9_]/', '', wp_generate_password(8, false, false));
		$tri_select_id = 'tri-sous-categories-' . $instance_id;
		$grille_id = 'grille-produits-modele-' . $instance_id;
		$voir_plus_id = 'voir-plus-' . $instance_id;
		$nombre_produits_id = 'nombre-produits-' . $instance_id;
		$modal_id = 'modalZoom-' . $instance_id;
		$image_zoom_id = 'imageZoomee-' . $instance_id;
		$zoom_instructions_id = 'zoom-instructions-' . $instance_id;
		$open_zoom_function = 'ouvrirImageZoom_' . $instance_id;
		$close_zoom_function = 'fermerImageZoom_' . $instance_id;
		$term = get_term_by('slug', $slug, 'product_cat');
		if ($term && $pratique) {
			$parent = get_term($term->parent, 'product_cat');

			if (!$parent || $parent->slug !== $pratique) {
				return '<p>Modèle invalide.</p>';
			}
		}
		if (!$term) {
			echo '<p>Modèle introuvable.</p>';
			return ob_get_clean();
		}

	// Récupération des champs personnalisés de la catégorie
	$chemin_pdf = '';
	$pdf_id = get_term_meta($term->term_id, 'category_pdf', true);
	if ($pdf_id) {
		$chemin_pdf = wp_get_attachment_url($pdf_id);
	}
	
	// Récupération de la galerie d'images de la catégorie
		$gallery_ids = get_term_meta($term->term_id, 'category_gallery', true);
		$category_images = [];
		if (!empty($gallery_ids)) {
			if (is_array($gallery_ids)) {
				$gallery_array = $gallery_ids;
			} else {
				$gallery_array = explode(',', (string) $gallery_ids);
			}

			$gallery_array = array_filter(array_map('absint', $gallery_array));

			foreach ($gallery_array as $image_id) {
				$image_url = wp_get_attachment_image_url($image_id, 'medium');
				$image_full_url = wp_get_attachment_image_url($image_id, 'full');
				if ($image_url) {
					$category_images[] = [
						'medium' => $image_url,
						'full' => $image_full_url ?: $image_url,
						'title' => get_the_title($image_id)
					];
				}
			}
		}
	
	// Récupération du tableau personnalisé de la catégorie
	$table_data = get_term_meta($term->term_id, 'category_table', true);
	$category_table = null;
	if ($table_data) {
		$category_table = json_decode($table_data, true);
	}

		$stored_product_order = get_term_meta($term->term_id, 'category_product_order', true);
		if (!is_array($stored_product_order)) {
			$decoded_product_order = json_decode((string) $stored_product_order, true);
			if (is_array($decoded_product_order)) {
				$stored_product_order = $decoded_product_order;
			} else {
				$stored_product_order = explode(',', (string) $stored_product_order);
			}
		}

		$stored_product_order = array_values(array_filter(array_map('absint', (array) $stored_product_order)));

		$all_product_ids_in_category = get_posts([
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'tax_query' => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $slug,
				],
			],
			'orderby' => [
				'menu_order' => 'ASC',
				'title' => 'ASC',
			],
		]);

		$ordered_product_ids = array_values(array_unique(array_merge(
			array_values(array_intersect($stored_product_order, $all_product_ids_in_category)),
			array_values(array_diff($all_product_ids_in_category, $stored_product_order))
		)));

	// 👉 D'abord exécuter la requête pour récupérer les produits
		// Récupération de tous les produits pour JS (pour bouton voir plus)
		$args_all = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $slug,
				],
			],
		];

		if (!empty($ordered_product_ids)) {
			$args_all['post__in'] = $ordered_product_ids;
			$args_all['orderby'] = 'post__in';
		} else {
			$args_all['orderby'] = [
				'menu_order' => 'ASC',
				'title' => 'ASC',
			];
		}
		$loop_all = new WP_Query($args_all);
		$all_produits = [];
		if ($loop_all->have_posts()) {
			while ($loop_all->have_posts()) {
				$loop_all->the_post();
				global $product;
				$thumbnail = get_the_post_thumbnail($product->get_id(), 'medium');
				if (!$thumbnail) {
					$thumbnail = '<img src="' . wc_placeholder_img_src('medium') . '" alt="Image par défaut" />';
				}
				$all_produits[] = [
					'permalink' => get_permalink(),
					'thumbnail' => $thumbnail,
					'title' => get_the_title(),
					'price_html' => $product->get_price_html(),
					'id' => $product->get_id(),
					'sku' => $product->get_sku(),
					'name' => $product->get_name(),
				];
			}
			wp_reset_postdata();
		}

		$limite_affichage = 18;
		$args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $limite_affichage,
			'tax_query' => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $slug,
				],
			],
		];

		if (!empty($ordered_product_ids)) {
			$args['post__in'] = $ordered_product_ids;
			$args['orderby'] = 'post__in';
		} else {
			$args['orderby'] = [
				'menu_order' => 'ASC',
				'title' => 'ASC',
			];
		}

		$loop = new WP_Query($args);
		$produits_total = $loop_all->found_posts;

	// 👉 Ensuite, afficher le HTML
	?>
	<style>
        .produits-par-modele-wrapper {
            display: block;
            width: 100%;
            padding: 30vh 20vw 40px;
            box-sizing: border-box;
            background: #ffffff;
            border: 1px solid #dcdcdc;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            margin: 0 0 24px;
        }

        #<?php echo esc_attr($voir_plus_id); ?> {
            display: block;
            margin: 20px auto 0;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            font-size: 14px;
            font-family: 'din-next-lt-pro', sans-serif;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #<?php echo esc_attr($voir_plus_id); ?>:hover {
            background-color: #FF3F22;
            color: #fff;
        }
		.grille-produits-modele {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 20px;
		}

		.produit-modele {
		background-color: #F7F7F7;
		padding: 15px;
		border-radius: 6px;
		text-align: center;
		position: relative;
		transition: transform 0.3s ease;
		display: flex;
		flex-direction: column;
		min-height: 400px;
		box-sizing: border-box;
		}

		.produit-modele:hover {
		transform: scale(1.03);
		box-shadow: 0 2px 12px rgba(0,0,0,0.08);
		}

		.produit-modele img {
		max-width: 100%;
		height: auto;
		margin-bottom: 18px;
		border-radius: 6px;
		background: #fff;
		box-shadow: 0 1px 6px rgba(0,0,0,0.07);
		object-fit: contain;
		}

		.info-produit {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		justify-content: space-between;
		height: 100%;
		flex-grow: 1;
		}

		.texte-produit h3 {
		font-size: 16px;
		font-weight: 600;
		margin: 10px 0;
		color: #000 !important;
		text-align: left;
		}

		.texte-produit p {
		font-size: 14px;
		color: #444;
		margin: 0;
		text-align: left;
		}

		.bouton-ajouter-panier {
		align-self: flex-end;
		background-color: #000000 !important;
		color: #ffffff !important;
		width: 100%;
		border-radius: 6px;
		padding: 10px;
		text-align: center;
		font-size: 16px;
		font-weight: 600;
		text-decoration: none;
		margin-top: 5px;
		transition: background-color 0.3s ease;
		}

		.bouton-ajouter-panier:hover {
		background-color: #000000 !important;
		color: #ffffff !important;
		}

		.barre-tri {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 5px;
		}
		#<?php echo esc_attr($nombre_produits_id); ?> {
		font-family: 'din-next-lt-pro', sans-serif;
		font-weight: 300;
		font-size: 24px;
		color: black;
		margin-right: 2rem;
		}
		.tri-container {
		display: flex;
		align-items: center;
		gap: 10px;
		}
		.tri-container label {
		font-family: 'din-next-lt-pro', sans-serif;
		font-weight: 300;
		font-size: 16px;
		color: #000;
		margin-bottom: 0;
		}
		#<?php echo esc_attr($tri_select_id); ?> {
		width: 160px;
		padding: 6px 8px;
		font-family: 'din-next-lt-pro', sans-serif;
		font-size: 14px;
		}
		.separator-red {
		border: none;
		border-top: 2px solid #FF3F22;
		margin: 18px 0;
		}

		.titre-et-manuel {
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-wrap: wrap;
		margin-bottom: 15px;
		gap: 10px;
		}

		.titre-modele {
		font-family: Helvetica, sans-serif;
		font-weight: 700;
		font-size: 42px;
		color: #000000;
		margin: 0;
		}

		.produits-affiches {
		font-family: 'DIN Next LT Pro', sans-serif;
		font-weight: 300;
		font-size: 24px;
		color: #656565;
		margin-left: 10px;
		}

		.bouton-manuel {
		background-color: #151515 !important;
		color: #F9F9F9 !important;
		padding: 10px 20px;
		border-radius: 6px;
		text-decoration: none;
		font-weight: 700;
		font-size: 16px;
		font-family: 'DIN Next LT Pro', sans-serif;
		white-space: nowrap;
		transition: background-color 0.3s ease;
		}

		.bouton-manuel:hover {
		background-color: #000 !important;
		}

		.images-et-essentiel {
		display: flex;
		gap: 30px;
		flex-wrap: wrap;
		margin-bottom: 30px;
		flex-direction: column;
		}

		.images-modele {
		display: flex;
		gap: 15px;
		flex-wrap: wrap;
		max-width: 70%;
		}

		.modele-image {
		max-width: 150px;
		height: auto;
		border: 1px solid #ccc;
		border-radius: 6px;
		cursor: pointer;
		transition: transform 0.2s ease;
		}

		.modele-image:hover {
		transform: scale(1.05);
		}

		.boite-essentiel {
		background-color: #F7F7F7;
		padding: 20px;
		border-radius: 8px;
		flex: 0 0 250px; /* ✅ réduit la largeur */
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start; /* ✅ pour aligner le titre en haut */
		min-height: 200px;
		}

		.titre-essentiel {
		font-family: 'DIN Next LT Pro', sans-serif;
		font-size: 20px;
		font-weight: 700;
		margin-bottom: 15px;
		text-align: center;
		width: 100%;
		}

		.contenu-essentiel {
		text-align: left;
		font-size: 14px;
		color: #444;
		width: 100%;
		}

		/* MODAL ZOOM IMAGE */
		#<?php echo esc_attr($modal_id); ?> {
		display: none;
		position: fixed;
		z-index: 9999;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background-color: rgba(0,0,0,0.9);
		padding: 0;
		}

		.modal-content-container {
		display: flex;
		justify-content: center;
		align-items: center;
		min-height: 100vh;
		padding: 20px;
		gap: 30px;
		}

		.main-image-container {
		display: flex;
		flex-direction: column;
		align-items: center;
		}

		#<?php echo esc_attr($modal_id); ?> img {
		max-height: 60vh;
		max-width: 50vw;
		height: auto;
		width: auto;
		cursor: zoom-in;
		transition: transform 0.3s ease;
		object-fit: contain;
		transform-origin: center center;
		}

		#<?php echo esc_attr($modal_id); ?> .close {
		position: absolute;
		top: 20px;
		right: 35px;
		color: #fff;
		font-size: 40px;
		font-weight: bold;
		cursor: pointer;
		transition: 0.3s;
		z-index: 10001;
		}

		#<?php echo esc_attr($zoom_instructions_id); ?> {
		position: absolute;
		bottom: 20px;
		left: 50%;
		transform: translateX(-50%);
		color: #fff;
		background: rgba(0, 0, 0, 0.7);
		padding: 10px 20px;
		border-radius: 5px;
		font-family: 'din-next-lt-pro', sans-serif;
		font-size: 14px;
		z-index: 10001;
		}

		/* STYLES POUR LE TABLEAU PERSONNALISÉ DE CATÉGORIE */
		.tableau-category {
		margin: 30px 0;
		background-color: #f9f9f9;
		padding: 20px;
		border-radius: 8px;
		}

		.titre-tableau {
		font-family: 'din-next-lt-pro', sans-serif;
		font-size: 24px;
		font-weight: 700;
		margin-bottom: 15px;
		text-align: center;
		color: #000;
		}

		.category-table-display {
		width: 100%;
		border-collapse: collapse;
		margin: 20px 0;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		background: white;
		}

		.category-table-display td {
		border: 1px solid #ddd;
		padding: 12px;
		background: #f9f9f9;
		transition: background 0.3s ease;
		font-family: 'din-next-lt-pro', sans-serif;
		font-size: 14px;
		}

		.category-table-display tr:nth-child(even) td {
		background: #f1f1f1;
		}

		.category-table-display tr:hover td {
		background: #e8f4f8;
		}

		.category-table-display tr:first-child td {
		font-weight: 600;
		background: #e0e0e0 !important;
		color: #000;
		}

		/* Responsive Design */
		@media screen and (max-width: 1129px) {
			.grille-produits-modele {
			grid-template-columns: repeat(2, 1fr);
			}
			.barre-tri {
			flex-direction: column;
			align-items: stretch;
			gap: 15px;
			}
			.tri-container {
			justify-content: center;
			flex-wrap: wrap;
			}
		}
		@media (max-width: 1024px) {
			.modal-content-container {
				flex-direction: column;
				gap: 20px;
			}
			
			#<?php echo esc_attr($modal_id); ?> img {
				max-width: 90vw;
				max-height: 70vh;
			}
		}
		
		@media screen and (max-width: 768px) {
				.produits-par-modele-wrapper {
					padding: 20vh 12px 32px;
				}

			.grille-produits-modele {
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
			}
			.produit-modele {
			min-height: 0px;
			}
			.texte-produit h3 {
			font-size: 14px;
			}
			.bouton-ajouter-panier {
			padding: 8px 12px;
			font-size: 14px;
			}
			.barre-tri {
			flex-direction: column;
			gap: 10px;
			}
			#<?php echo esc_attr($tri_select_id); ?> {
			width: 100px;
			}
		}
	</style>
		<div class="produits-par-modele-wrapper">
		<div class="titre-et-manuel">
		<h1 class="titre-modele">
			<?php echo esc_html($term->name); ?>
		</h1>

		<?php if ($chemin_pdf): ?>
			<a href="<?php echo esc_url($chemin_pdf); ?>" download class="bouton-manuel">TÉLÉCHARGER LE MANUEL</a>
		<?php else: ?>
			<p style="color: #777; font-size: 14px;">Aucun manuel PDF disponible.</p>
		<?php endif; ?>
		</div>

		<div class="images-et-essentiel">
		<div class="images-modele">
			<?php
			// Afficher d'abord les images de la galerie de catégorie
			if (!empty($category_images)) {
				foreach ($category_images as $img) {
				echo '<img src="' . esc_url($img['medium']) . '" alt="' . esc_attr($img['title']) . '" class="modele-image" onclick="' . esc_attr($open_zoom_function) . '(&quot;' . esc_url($img['full']) . '&quot;)">';
				}
			} else {
				// Si pas d'images dans la galerie de catégorie, utiliser l'ancienne méthode
				$images_args = [
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'post_status'    => 'inherit',
				's'              => $modele_slug
				];

				$images_query = new WP_Query($images_args);

				if ($images_query->have_posts()) {
				while ($images_query->have_posts()) {
					$images_query->the_post();
					$image_url = wp_get_attachment_image_src(get_the_ID(), 'medium')[0];
					$image_full_url = wp_get_attachment_image_src(get_the_ID(), 'full')[0];
					echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="modele-image" onclick="' . esc_attr($open_zoom_function) . '(&quot;' . esc_url($image_full_url) . '&quot;)">';
				}
				wp_reset_postdata();
				} else {
				echo '<p style="font-size: 14px; color: #777;">Aucune image disponible pour ce modèle.</p>';
				}
			}
			?>
		</div>

		<?php
		// Affichage du tableau personnalisé de la catégorie (si disponible)
		if ($category_table && isset($category_table['rows']) && isset($category_table['cols']) && isset($category_table['data'])) {
			echo '<div class="tableau-category">';
			echo '<h3 class="titre-tableau">Informations techniques</h3>';
			echo '<table class="category-table-display">';
			for ($i = 0; $i < $category_table['rows']; $i++) {
			echo '<tr>';
			for ($j = 0; $j < $category_table['cols']; $j++) {
				$cell_content = isset($category_table['data'][$i][$j]) ? $category_table['data'][$i][$j] : '';
				echo '<td>' . esc_html($cell_content) . '</td>';
			}
			echo '</tr>';
			}
			echo '</table>';
			echo '</div>';
		}
		?>
		</div>

	<div id="produits-filtrables-container" style="display:flex; flex-direction: column; gap:20px; margin-bottom: 2%;">
		<div class="barre-tri">
		<div style="display: flex; align-items: center;">
					<div id="<?php echo esc_attr($nombre_produits_id); ?>">
			<span class="produits-affiches"><?php echo $produits_total; ?> résultats</span>
			</div>
		</div>
		<div class="tri-container">
					<label for="<?php echo esc_attr($tri_select_id); ?>">Trier par :</label>
					<select id="<?php echo esc_attr($tri_select_id); ?>">
			<option value="default">Tri par défaut</option>
			<option value="alpha">Ordre alphabétique A-Z</option>
			<option value="alpha-desc">Ordre alphabétique Z-A</option>
			</select>
		</div>
		</div>
		<hr class="separator-red">
	</div>
	<?php

	// 👉 Puis afficher les produits
		if ($loop->have_posts()) {
			echo '<div class="grille-produits-modele" id="' . esc_attr($grille_id) . '">';
			$i = 0;
			while ($loop->have_posts() && $i < $limite_affichage) {
				$loop->the_post();
				global $product;
				$thumbnail = get_the_post_thumbnail($product->get_id(), 'medium');
				if (!$thumbnail) {
					$thumbnail = '<img src="' . wc_placeholder_img_src('medium') . '" alt="Image par défaut" />';
				}
				echo '<div class="produit-modele">';
				echo '<a href="' . get_permalink() . '">' . $thumbnail . '</a>';
				echo '<div class="info-produit">';
				echo '<div class="texte-produit">';
				echo '<h3>' . get_the_title() . '</h3>';
				echo '<p>' . $product->get_price_html() . '</p>';
				echo '</div>';
				echo '<a href="' . esc_url('?add-to-cart=' . $product->get_id()) . '" 
									data-quantity="1" 
									class="bouton-ajouter-panier add_to_cart_button ajax_add_to_cart" 
									data-product_id="' . esc_attr($product->get_id()) . '" 
									data-product_sku="' . esc_attr($product->get_sku()) . '" 
									aria-label="Ajouter “' . esc_attr($product->get_name()) . '” au panier" 
									rel="nofollow">Ajouter au panier</a>';
				echo '</div>';
				echo '</div>';
				$i++;
			}
			echo '</div>';
				if ($produits_total > $limite_affichage) {
					echo '<button id="' . esc_attr($voir_plus_id) . '">Voir plus</button>';
				}
			wp_reset_postdata();
		} else {
			echo '<p>Aucun produit trouvé pour ce modèle.</p>';
		}
        echo '</div>';
		if ($produits_total > $limite_affichage) : ?>
			<script>
				window.allProduitsByModel = window.allProduitsByModel || {};
				window.allProduitsByModel[<?php echo wp_json_encode($instance_id); ?>] = <?php echo wp_json_encode($all_produits); ?>;
			</script>
		<?php endif; ?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const instanceId = <?php echo wp_json_encode($instance_id); ?>;
				const triSelect = document.getElementById(<?php echo wp_json_encode($tri_select_id); ?>);
				const grille = document.getElementById(<?php echo wp_json_encode($grille_id); ?>);
				const voirPlusBtn = document.getElementById(<?php echo wp_json_encode($voir_plus_id); ?>);
				const allProduitsByModel = (window.allProduitsByModel && window.allProduitsByModel[instanceId]) ? window.allProduitsByModel[instanceId] : null;
				if (triSelect && grille) {
					triSelect.addEventListener('change', function () {
						const items = Array.from(grille.querySelectorAll('.produit-modele'));
						if (this.value === 'alpha') {
							items.sort((a, b) => a.querySelector('h3').textContent.localeCompare(b.querySelector('h3').textContent));
						} else if (this.value === 'alpha-desc') {
							items.sort((a, b) => b.querySelector('h3').textContent.localeCompare(a.querySelector('h3').textContent));
						} else {
							return;
						}
						items.forEach(item => grille.appendChild(item));
					});
				}
				if (voirPlusBtn && allProduitsByModel) {
					voirPlusBtn.addEventListener('click', function (e) {
						e.preventDefault();
						grille.innerHTML = '';
						allProduitsByModel.forEach(function(p) {
							const div = document.createElement('div');
							div.className = 'produit-modele';
							div.innerHTML = `
								<a href="${p.permalink}">${p.thumbnail}</a>
								<div class=\"info-produit\">
									<div class=\"texte-produit\">
										<h3>${p.title}</h3>
										<p>${p.price_html}</p>
									</div>
									<a href="<?php echo esc_url(home_url('/?add-to-cart=')); ?>${p.id}" data-quantity="1" class="bouton-ajouter-panier add_to_cart_button ajax_add_to_cart" data-product_id="${p.id}" data-product_sku="${p.sku}" aria-label="Ajouter “${p.name}” au panier" rel="nofollow">Ajouter au panier</a>
								</div>
							`;
							grille.appendChild(div);
						});
						voirPlusBtn.style.display = 'none';
					});
				}
			});
		</script>

		<div id="<?php echo esc_attr($modal_id); ?>" onclick="<?php echo esc_attr($close_zoom_function); ?>()">
		<span class="close">&times;</span>
		<div class="modal-content-container">
			<div class="main-image-container">
			<img id="<?php echo esc_attr($image_zoom_id); ?>" src="" onclick="event.stopPropagation();">
			</div>
		</div>
		<div id="<?php echo esc_attr($zoom_instructions_id); ?>">
			Cliquez sur l'image pour zoomer (Niveau 1/3)
		</div>
		</div>

		<script>
		// Encapsuler tout le code de zoom dans DOMContentLoaded
		document.addEventListener('DOMContentLoaded', function() {
			let zoomLevel = 0; // Niveau de zoom actuel (0, 1, 2, 3)
			const maxZoomLevels = 3; // Maximum 3 niveaux de zoom
			const zoomScales = [1, 2, 3.5, 5]; // Facteurs d'agrandissement pour chaque niveau

			// Vérifier si on est sur un écran d'ordinateur (largeur > 1024px)
			function isDesktop() {
			return window.innerWidth > 1024;
			}

			// Fonction globale pour ouvrir le zoom
			window[<?php echo wp_json_encode($open_zoom_function); ?>] = function(src) {
			console.log('Fonction ouvrirImageZoom appelée avec:', src);
			
			const modal = document.getElementById(<?php echo wp_json_encode($modal_id); ?>);
			const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
			
			console.log('Éléments trouvés:', { modal, img }); // Debug
			
			// Vérifier que les éléments existent
			if (!modal) {
				console.error('Modal non trouvé:', modal);
				return;
			}
			
			if (!img) {
				console.error('Image non trouvée:', img);
				return;
			}
			
			try {
				modal.style.display = "block";
				img.src = src;
				
				// Réinitialiser le zoom
				zoomLevel = 0;
				img.style.transform = 'scale(1)';
				img.style.transformOrigin = 'center center';
				img.style.cursor = 'zoom-in';
				
				// Mettre à jour les instructions initiales
				updateZoomInstructions();
				
				// Configurer le zoom par clic et mouvement
				setupClickZoom();
				setupMouseMove();
				
				console.log('Modal ouvert avec succès'); // Debug
			} catch (error) {
				console.error('Erreur lors de l\'ouverture du modal:', error);
			}
			}

			// Fonction globale pour fermer le zoom
			window[<?php echo wp_json_encode($close_zoom_function); ?>] = function() {
			const modal = document.getElementById(<?php echo wp_json_encode($modal_id); ?>);
			if (modal) {
				modal.style.display = "none";
				zoomLevel = 0;
			}
			}

			// Fonction globale pour gérer le clic sur l'image
			function handleImageClick(e) {
			e.stopPropagation();
			
			const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
			
			// Vérifier que l'image existe
			if (!img) {
				console.error('Image non trouvée dans handleImageClick');
				return;
			}
			
			// Calculer la position du clic par rapport à l'image
			const rect = img.getBoundingClientRect();
			const x = ((e.clientX - rect.left) / rect.width) * 100;
			const y = ((e.clientY - rect.top) / rect.height) * 100;
			
			// Passer au niveau de zoom suivant
			zoomLevel++;
			
			// Si on dépasse le maximum, revenir au niveau 0
			if (zoomLevel > maxZoomLevels) {
				zoomLevel = 0;
				img.style.transformOrigin = 'center center';
			} else {
				// Définir le point d'origine du zoom à l'endroit du clic
				img.style.transformOrigin = `${x}% ${y}%`;
			}
			
			// Appliquer le nouveau niveau de zoom
			const scale = zoomScales[zoomLevel];
			img.style.transform = `scale(${scale})`;
			img.style.transition = 'transform 0.3s ease';
			
			// Mettre à jour le curseur selon le niveau de zoom
			if (zoomLevel === 0) {
				img.style.cursor = 'zoom-in';
			} else if (zoomLevel === maxZoomLevels) {
				img.style.cursor = 'zoom-out';
			} else {
				img.style.cursor = 'zoom-in';
			}
			
			// Mettre à jour les instructions
			updateZoomInstructions();
			
			console.log('Zoom level:', zoomLevel, 'Scale:', scale, 'Origin:', `${x}% ${y}%`); // Debug
			}

			// Gérer le mouvement de la souris pour déplacer le point de zoom
			function handleMouseMove(e) {
			const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
			
			if (!img || zoomLevel === 0) {
				return;
			}
			
			// Calculer la position de la souris par rapport à l'image
			const rect = img.getBoundingClientRect();
			const x = ((e.clientX - rect.left) / rect.width) * 100;
			const y = ((e.clientY - rect.top) / rect.height) * 100;
			
			// Mettre à jour le point d'origine du zoom en temps réel
			img.style.transformOrigin = `${x}% ${y}%`;
			}

			function setupClickZoom() {
			const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
			
			// Vérifier que l'image existe
			if (!img) {
				console.error('Image non trouvée pour setupClickZoom');
				return;
			}
			
			// Nettoyer les anciens event listeners
			img.removeEventListener('click', handleImageClick);
			
			// Ajouter le nouvel event listener pour le clic
			img.addEventListener('click', handleImageClick);
			
			console.log('Event listener ajouté pour le zoom'); // Debug
			}

			function setupMouseMove() {
			const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
			
			// Vérifier que l'image existe
			if (!img) {
				console.error('Image non trouvée pour setupMouseMove');
				return;
			}
			
			// Nettoyer les anciens event listeners
			img.removeEventListener('mousemove', handleMouseMove);
			
			// Ajouter le nouvel event listener pour le mouvement de la souris
			img.addEventListener('mousemove', handleMouseMove);
			
			console.log('Event listener ajouté pour le mouvement de la souris'); // Debug
			}

			function updateZoomInstructions() {
			const instructions = document.getElementById(<?php echo wp_json_encode($zoom_instructions_id); ?>);
			
			// Vérifier que l'élément existe
			if (!instructions) {
				console.error('Instructions non trouvées');
				return;
			}
			
			if (zoomLevel === 0) {
				instructions.textContent = 'Cliquez sur une partie de l\'image pour zoomer (Niveau 1/3)';
			} else if (zoomLevel === maxZoomLevels) {
				instructions.textContent = 'Cliquez pour revenir à la taille normale • Bougez la souris pour explorer';
			} else {
				instructions.textContent = `Zoom niveau ${zoomLevel}/3 • Cliquez pour zoomer davantage • Bougez la souris pour explorer`;
			}
			}

			// Gestion responsive
			window.addEventListener('resize', function() {
			if (!isDesktop()) {
				// Sur mobile/tablette, réinitialiser le zoom
				const img = document.getElementById(<?php echo wp_json_encode($image_zoom_id); ?>);
				if (img) {
				zoomLevel = 0;
				img.style.transform = 'scale(1)';
				img.style.transformOrigin = 'center center';
				img.style.cursor = 'default';
				}
			}
			});
			
			console.log('Script de zoom initialisé avec succès');
		});
		</script>

	<?php

		if (current_user_can('administrator') && isset($_POST['sauver_contenu_essentiel'])) {
		$contenu_sauvegarde = wp_kses_post($_POST['contenu_essentiel']);
		update_option('contenu_essentiel_' . $modele_slug, $contenu_sauvegarde);

		// Redirection propre vers la même URL en GET (PRG pattern)
		$url_actuelle = esc_url_raw(add_query_arg(null, null));
		wp_redirect($url_actuelle);
		exit;
		}

	return ob_get_clean();
	}
}


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
    // Vérifier le nonce seulement s'il est fourni (pour compatibilité avec Elementor)
    if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
        if (!wp_verify_nonce($_POST['nonce'], 'sauvegarder_prestation_nonce')) {
            wp_send_json_error('Erreur de sécurité');
            return;
        }
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
        prestations_choisies text,
        options_choisies text,
        type_prestation_choisie varchar(255),
        date_derniere_revision varchar(50),
        poids_pilote varchar(50),
        remarques text,
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
        'prestations_choisies' => isset($_POST['prestations_choisies']) ? sanitize_text_field($_POST['prestations_choisies']) : '',
        'options_choisies' => isset($_POST['options_choisies']) ? sanitize_text_field($_POST['options_choisies']) : '',
        'type_prestation_choisie' => isset($_POST['type_prestation_choisie']) ? sanitize_text_field($_POST['type_prestation_choisie']) : '',
        'date_derniere_revision' => isset($_POST['date_derniere_revision']) ? sanitize_text_field($_POST['date_derniere_revision']) : '',
        'poids_pilote' => isset($_POST['poids_pilote']) ? sanitize_text_field($_POST['poids_pilote']) : '',
        'remarques' => isset($_POST['remarques']) ? sanitize_textarea_field($_POST['remarques']) : '',
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
// //Accessibilité shopimind
// function add_cors_headers() {
//   header("Access-Control-Allow-Origin: https://my.shopimind.com");
//   header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
//   header("Access-Control-Allow-Headers: Content-Type, Authorization");
// }
// add_action('init', 'add_cors_headers');

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
        $where_conditions[] = "(p.modele_velo LIKE %s OR p.description LIKE %s OR p.type_fourche LIKE %s OR p.numero_suivi LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s)";
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
        $where_values[] = '%' . $recherche . '%';
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
    }
    
    $query = "SELECT p.* FROM $table_prestations p LEFT JOIN {$wpdb->users} u ON u.ID = p.user_id" . $where_clause . " ORDER BY p.date_creation DESC";
    
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
    <div id="modal-details" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: hidden;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
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
                // Bloquer le scroll de la page
                document.body.style.overflow = 'hidden';
            } else {
                alert('Erreur lors du chargement des détails');
            }
        });
    }
    
    function fermerModal() {
        document.getElementById('modal-details').style.display = 'none';
        // Rétablir le scroll de la page
        document.body.style.overflow = '';
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
    
    if ($prestation->numero_suivi) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">N° suivi:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong style="color:#FF3F22;">' . esc_html($prestation->numero_suivi) . '</strong></td></tr>';
    }
    
    $details .= '<tr><td colspan="2" style="padding: 12px 8px; background: #f0f0f0; font-weight: bold; border-bottom: 1px solid #ddd;">INFORMATIONS PRESTATION</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Type:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->type_prestation) . '</td></tr>';
    
    // Type de fourche
    if (!empty($prestation->type_fourche)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Modèle de fourche:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->type_fourche) . '</td></tr>';
    }
    
    // Prestations choisies
    if (!empty($prestation->prestations_choisies)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Prestations choisies:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;"><span style="color:#0073aa; font-weight:600;">' . esc_html($prestation->prestations_choisies) . '</span></td></tr>';
    }
    
    // Options choisies
    if (!empty($prestation->options_choisies)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Options choisies:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;"><span style="color:#0073aa;">' . esc_html($prestation->options_choisies) . '</span></td></tr>';
    }
    
    // Type de prestation (Express/Standard)
    if (!empty($prestation->type_prestation_choisie)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Type de prestation:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->type_prestation_choisie) . '</td></tr>';
    }
    
    $details .= '<tr><td colspan="2" style="padding: 12px 8px; background: #f0f0f0; font-weight: bold; border-bottom: 1px solid #ddd;">INFORMATIONS VÉLO</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Modèle vélo:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->modele_velo) . '</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Année vélo:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->annee_velo) . '</td></tr>';
    
    $details .= '<tr><td colspan="2" style="padding: 12px 8px; background: #f0f0f0; font-weight: bold; border-bottom: 1px solid #ddd;">INFORMATIONS TECHNIQUES</td></tr>';
    
    // Date de dernière révision
    if (!empty($prestation->date_derniere_revision)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Date dernière révision:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . date('d/m/Y', strtotime($prestation->date_derniere_revision)) . '</td></tr>';
    }
    
    // Poids du pilote
    if (!empty($prestation->poids_pilote)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Poids du pilote:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . esc_html($prestation->poids_pilote) . ' kg</td></tr>';
    }
    
    // Remarques
    if (!empty($prestation->remarques)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Remarques client:</td><td style="padding: 8px; border-bottom: 1px solid #ddd; background: #fffbcc;">' . nl2br(esc_html($prestation->remarques)) . '</td></tr>';
    }
    
    // Description
    if (!empty($prestation->description)) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Description:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . nl2br(esc_html($prestation->description)) . '</td></tr>';
    }
    
    $details .= '<tr><td colspan="2" style="padding: 12px 8px; background: #f0f0f0; font-weight: bold; border-bottom: 1px solid #ddd;">GESTION</td></tr>';
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Statut:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">';
    
    $statut_colors = array(
        'attente' => '#f39c12',
        'en_cours' => '#3498db',
        'terminee' => '#27ae60'
    );
    $statut_color = isset($statut_colors[$prestation->statut]) ? $statut_colors[$prestation->statut] : '#95a5a6';
    $statut_labels = array(
        'attente' => 'En attente',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée'
    );
    $details .= '<span style="color:' . $statut_color . '; font-weight:bold;">' . ($statut_labels[$prestation->statut] ?? ucfirst($prestation->statut)) . '</span>';
    $details .= '</td></tr>';
    
    $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Date création:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;">' . date('d/m/Y H:i:s', strtotime($prestation->date_creation)) . '</td></tr>';
    
    if ($prestation->prix_total > 0) {
        $details .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">Prix total:</td><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong style="color:#27ae60; font-size:18px;">' . wc_price($prestation->prix_total) . '</strong></td></tr>';
    }
    
    $details .= '</table>';
    
    wp_send_json_success($details);
}

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
	$order_number = 'PRST-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);

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
	$order_number = 'PRST-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);

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
	$order_number = 'PRST-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);

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

add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
 
function woo_custom_cart_button_text() {
return __('+', 'woocommerce');
}

// Afficher les pratiques dans le profil utilisateur admin
add_action('show_user_profile', 'afficher_pratiques_profil');
add_action('edit_user_profile', 'afficher_pratiques_profil');

function afficher_pratiques_profil($user) {
    $pratiques = get_user_meta($user->ID, 'pratique', true);
    $civilite = get_user_meta($user->ID, 'civilite', true);
    $telephone = get_user_meta($user->ID, 'telephone', true);
    $dob = get_user_meta($user->ID, 'dob', true);
    $adresse = get_user_meta($user->ID, 'adresse', true);
    
    if (!is_array($pratiques)) {
        $pratiques = $pratiques ? [$pratiques] : [];
    }
    ?>
    <h3>Informations personnalisées</h3>
    <table class="form-table">
        <tr>
            <th><label>Civilité</label></th>
            <td>
                <?php echo !empty($civilite) ? esc_html($civilite) : '<em>Non renseignée</em>'; ?>
            </td>
        </tr>
        <tr>
            <th><label>Pratiques sportives</label></th>
            <td>
                <?php 
                if (!empty($pratiques)) {
                    echo '<div style="display: flex; gap: 5px; flex-wrap: wrap;">';
                    foreach ($pratiques as $pratique) {
                        echo '<span style="background: #0073aa; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">' . esc_html($pratique) . '</span>';
                    }
                    echo '</div>';
                } else {
                    echo '<em>Aucune pratique sélectionnée</em>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><label>Téléphone</label></th>
            <td>
                <?php echo !empty($telephone) ? esc_html($telephone) : '<em>Non renseigné</em>'; ?>
            </td>
        </tr>
        <tr>
            <th><label>Date de naissance</label></th>
            <td>
                <?php echo !empty($dob) ? esc_html(date('d/m/Y', strtotime($dob))) : '<em>Non renseignée</em>'; ?>
            </td>
        </tr>
        <tr>
            <th><label>Adresse de livraison</label></th>
            <td>
                <?php echo !empty($adresse) ? nl2br(esc_html($adresse)) : '<em>Non renseignée</em>'; ?>
            </td>
        </tr>
    </table>
    
    <h3>Historique des prestations</h3>
    <table class="form-table">
        <tr>
            <td colspan="2">
                <?php
                // Afficher l'historique des prestations de cet utilisateur
                global $wpdb;
                $table_prestations = $wpdb->prefix . 'demandes_prestations';
                
                $prestations = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $table_prestations WHERE user_id = %d ORDER BY date_creation DESC LIMIT 10",
                    $user->ID
                ));
                
                if (!empty($prestations)) {
                    echo '<table style="width: 100%; border-collapse: collapse;">';
                    echo '<thead>';
                    echo '<tr style="background: #f9f9f9;">';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Type</th>';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Modèle</th>';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">N° suivi</th>';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Statut</th>';
                    echo '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Prix</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($prestations as $prestation) {
                        echo '<tr>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . date('d/m/Y', strtotime($prestation->date_creation)) . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($prestation->type_prestation) . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($prestation->modele_velo . ' (' . $prestation->annee_velo . ')') . '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">';
                        if ($prestation->numero_suivi) {
                            echo '<strong style="color: #FF3F22;">' . esc_html($prestation->numero_suivi) . '</strong>';
                        } else {
                            echo '<em>Non généré</em>';
                        }
                        echo '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">';
                        
                        $statut_colors = array(
                            'attente' => '#f39c12',
                            'en_cours' => '#3498db',
                            'terminee' => '#27ae60'
                        );
                        
                        $statut_color = isset($statut_colors[$prestation->statut]) ? $statut_colors[$prestation->statut] : '#95a5a6';
                        
                        echo '<span style="color: ' . $statut_color . '; font-weight: bold;">';
                        $statut_labels = array(
                            'attente' => 'En attente',
                            'en_cours' => 'En cours',
                            'terminee' => 'Terminée'
                        );
                        echo $statut_labels[$prestation->statut] ?? ucfirst($prestation->statut);
                        echo '</span>';
                        echo '</td>';
                        echo '<td style="border: 1px solid #ddd; padding: 8px;">';
                        if ($prestation->prix_total > 0) {
                            echo wc_price($prestation->prix_total);
                        } else {
                            echo '<em>N/A</em>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    
                    // Lien vers la gestion complète
                    echo '<p style="margin-top: 10px;">';
                    echo '<a href="' . admin_url('admin.php?page=gestion-prestations&recherche=' . urlencode($user->user_email)) . '" class="button button-secondary">';
                    echo 'Voir toutes les prestations de cet utilisateur';
                    echo '</a>';
                    echo '</p>';
                    
                } else {
                    echo '<p><em>Aucune prestation demandée par cet utilisateur.</em></p>';
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
}

// Sécurité - empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier que WooCommerce est actif
add_action('plugins_loaded', 'wccf_check_woocommerce');
function wccf_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wccf_woocommerce_missing_notice');
        return;
    }
}

function wccf_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('WooCommerce Category Custom Fields nécessite WooCommerce pour fonctionner.', 'wccf'); ?></p>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', 'cannondale_enqueue_category_order_admin_assets');
function cannondale_enqueue_category_order_admin_assets($hook) {
    if (($hook === 'term.php' || $hook === 'edit-tags.php') && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_cat') {
        wp_enqueue_script('jquery-ui-sortable');
    }
}

// Ajouter les champs à la page d'ajout de catégorie
add_action('product_cat_add_form_fields', 'add_category_custom_fields');
function add_category_custom_fields() {
    // Ajouter un nonce pour la sécurité
    wp_nonce_field('category_custom_fields_nonce', 'category_custom_fields_nonce');
    ?>
    <div class="form-field">
        <label for="category_gallery"><?php _e('Galerie d\'images', 'woocommerce'); ?></label>
        <div id="category_gallery_container">
            <ul class="category_gallery_images" style="display: flex; flex-wrap: wrap; gap: 10px; list-style: none; padding: 0;">
                <!-- Les images sélectionnées apparaîtront ici -->
            </ul>
        </div>
        <input type="hidden" id="category_gallery" name="category_gallery" value="" />
        <button type="button" class="upload_gallery_button button"><?php _e('Ajouter des images', 'woocommerce'); ?></button>
        <button type="button" class="remove_gallery_button button" style="display:none;"><?php _e('Supprimer toutes les images', 'woocommerce'); ?></button>
        <p class="description"><?php _e('Sélectionnez plusieurs images pour la galerie de cette catégorie.', 'woocommerce'); ?></p>
    </div>

    <div class="form-field">
        <label for="category_pdf"><?php _e('Fichier PDF', 'woocommerce'); ?></label>
        <div id="category_pdf_container">
            <div class="category_pdf_preview" style="margin-bottom: 10px;"></div>
        </div>
        <input type="hidden" id="category_pdf" name="category_pdf" value="" />
        <button type="button" class="upload_pdf_button button"><?php _e('Sélectionner un PDF', 'woocommerce'); ?></button>
        <button type="button" class="remove_pdf_button button" style="display:none;"><?php _e('Supprimer le PDF', 'woocommerce'); ?></button>
        <p class="description"><?php _e('Sélectionnez un fichier PDF à associer à cette catégorie.', 'woocommerce'); ?></p>
    </div>

    <div class="form-field">
        <label for="category_table"><?php _e('Tableau personnalisé', 'woocommerce'); ?></label>
        <div id="category_table_container">
            <div class="table-controls" style="margin-bottom: 15px;">
                <label for="table_rows"><?php _e('Nombre de lignes:', 'woocommerce'); ?></label>
                <input type="number" id="table_rows" min="1" max="20" value="3" style="width: 60px; margin-right: 15px;" />
                
                <label for="table_cols"><?php _e('Nombre de colonnes:', 'woocommerce'); ?></label>
                <input type="number" id="table_cols" min="1" max="10" value="3" style="width: 60px; margin-right: 15px;" />
                
                <button type="button" class="generate_table_button button"><?php _e('Générer le tableau', 'woocommerce'); ?></button>
                <button type="button" class="clear_table_button button" style="margin-left: 10px;"><?php _e('Vider le tableau', 'woocommerce'); ?></button>
            </div>
            <div class="table-editor" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; min-height: 100px;">
                <p style="color: #666; text-align: center; margin: 40px 0;"><?php _e('Cliquez sur "Générer le tableau" pour créer votre tableau personnalisé.', 'woocommerce'); ?></p>
            </div>
        </div>
        <input type="hidden" id="category_table" name="category_table" value="" />
        <p class="description"><?php _e('Créez un tableau personnalisé pour cette catégorie. Vous pouvez ajuster le nombre de lignes et colonnes, puis cliquer dans chaque cellule pour ajouter du contenu.', 'woocommerce'); ?></p>
    </div>
    <?php
}

// Ajouter les champs à la page d'édition de catégorie
add_action('product_cat_edit_form_fields', 'edit_category_custom_fields');
function edit_category_custom_fields($term) {
    // Ajouter un nonce pour la sécurité
    wp_nonce_field('category_custom_fields_nonce', 'category_custom_fields_nonce');
    
    $gallery_ids = get_term_meta($term->term_id, 'category_gallery', true);
    $pdf_id = get_term_meta($term->term_id, 'category_pdf', true);
    $table_data = get_term_meta($term->term_id, 'category_table', true);
    $stored_product_order = get_term_meta($term->term_id, 'category_product_order', true);

    if (!is_array($stored_product_order)) {
        $decoded_order = json_decode((string) $stored_product_order, true);
        if (is_array($decoded_order)) {
            $stored_product_order = $decoded_order;
        } else {
            $stored_product_order = array_filter(array_map('absint', explode(',', (string) $stored_product_order)));
        }
    }

    $stored_product_order = array_values(array_filter(array_map('absint', (array) $stored_product_order)));

    $product_ids_in_category = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $term->term_id,
            ),
        ),
        'orderby' => array(
            'menu_order' => 'ASC',
            'title' => 'ASC',
        ),
    ));

    $ordered_product_ids = array_values(array_unique(array_merge(
        array_values(array_intersect($stored_product_order, $product_ids_in_category)),
        array_values(array_diff($product_ids_in_category, $stored_product_order))
    )));
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_gallery"><?php _e('Galerie d\'images', 'woocommerce'); ?></label>
        </th>
        <td>
            <div id="category_gallery_container">
                <ul class="category_gallery_images" style="display: flex; flex-wrap: wrap; gap: 10px; list-style: none; padding: 0;">
                    <?php
                    if ($gallery_ids) {
                        $gallery_array = explode(',', $gallery_ids);
                        foreach ($gallery_array as $image_id) {
                            if ($image_id) {
                                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                if ($image_url) {
                                    echo '<li data-attachment_id="' . esc_attr($image_id) . '" style="position: relative; display: inline-block;">';
                                    echo '<img src="' . esc_url($image_url) . '" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;" />';
                                    echo '<a href="#" class="delete_gallery_image" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 18px; text-decoration: none; font-size: 12px;">&times;</a>';
                                    echo '</li>';
                                }
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
            <input type="hidden" id="category_gallery" name="category_gallery" value="<?php echo esc_attr($gallery_ids); ?>" />
            <button type="button" class="upload_gallery_button button"><?php _e('Ajouter des images', 'woocommerce'); ?></button>
            <button type="button" class="remove_gallery_button button" style="<?php echo $gallery_ids ? '' : 'display:none;'; ?>"><?php _e('Supprimer toutes les images', 'woocommerce'); ?></button>
            <p class="description"><?php _e('Sélectionnez plusieurs images pour la galerie de cette catégorie.', 'woocommerce'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_pdf"><?php _e('Fichier PDF', 'woocommerce'); ?></label>
        </th>
        <td>
            <div id="category_pdf_container">
                <div class="category_pdf_preview" style="margin-bottom: 10px;">
                    <?php
                    if ($pdf_id) {
                        $pdf_url = wp_get_attachment_url($pdf_id);
                        $pdf_filename = basename(get_attached_file($pdf_id));
                        if ($pdf_url) {
                            echo '<div style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; display: inline-block;">';
                            echo '<span style="font-size: 20px; margin-right: 8px;">📄</span>';
                            echo '<strong>' . esc_html($pdf_filename) . '</strong>';
                            echo '<a href="' . esc_url($pdf_url) . '" target="_blank" style="margin-left: 10px;">Voir le PDF</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <input type="hidden" id="category_pdf" name="category_pdf" value="<?php echo esc_attr($pdf_id); ?>" />
            <button type="button" class="upload_pdf_button button"><?php _e('Sélectionner un PDF', 'woocommerce'); ?></button>
            <button type="button" class="remove_pdf_button button" style="<?php echo $pdf_id ? '' : 'display:none;'; ?>"><?php _e('Supprimer le PDF', 'woocommerce'); ?></button>
            <p class="description"><?php _e('Sélectionnez un fichier PDF à associer à cette catégorie.', 'woocommerce'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_table"><?php _e('Tableau personnalisé', 'woocommerce'); ?></label>
        </th>
        <td>
            <div id="category_table_container">
                <div class="table-controls" style="margin-bottom: 15px;">
                    <label for="table_rows"><?php _e('Nombre de lignes:', 'woocommerce'); ?></label>
                    <input type="number" id="table_rows" min="1" max="20" value="3" style="width: 60px; margin-right: 15px;" />
                    
                    <label for="table_cols"><?php _e('Nombre de colonnes:', 'woocommerce'); ?></label>
                    <input type="number" id="table_cols" min="1" max="10" value="3" style="width: 60px; margin-right: 15px;" />
                    
                    <button type="button" class="generate_table_button button"><?php _e('Générer le tableau', 'woocommerce'); ?></button>
                    <button type="button" class="clear_table_button button" style="margin-left: 10px;"><?php _e('Vider le tableau', 'woocommerce'); ?></button>
                </div>
                <div class="table-editor" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9; min-height: 100px;">
                    <?php
                    if ($table_data) {
                        $table_obj = json_decode($table_data, true);
                        if ($table_obj && isset($table_obj['rows']) && isset($table_obj['cols']) && isset($table_obj['data'])) {
                            echo '<table class="category-custom-table" style="width: 100%; border-collapse: collapse;">';
                            for ($i = 0; $i < $table_obj['rows']; $i++) {
                                echo '<tr>';
                                for ($j = 0; $j < $table_obj['cols']; $j++) {
                                    $cell_content = isset($table_obj['data'][$i][$j]) ? $table_obj['data'][$i][$j] : '';
                                    echo '<td class="editable-cell" data-row="' . $i . '" data-col="' . $j . '" style="border: 1px solid #ddd; padding: 8px; min-height: 30px; background: white; cursor: text;">';
                                    echo esc_html($cell_content);
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                            echo '<script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    document.getElementById("table_rows").value = ' . $table_obj['rows'] . ';
                                    document.getElementById("table_cols").value = ' . $table_obj['cols'] . ';
                                });
                            </script>';
                        }
                    } else {
                        echo '<p style="color: #666; text-align: center; margin: 40px 0;">' . __('Cliquez sur "Générer le tableau" pour créer votre tableau personnalisé.', 'woocommerce') . '</p>';
                    }
                    ?>
                </div>
            </div>
            <input type="hidden" id="category_table" name="category_table" value="<?php echo esc_attr($table_data); ?>" />
            <p class="description"><?php _e('Créez un tableau personnalisé pour cette catégorie. Vous pouvez ajuster le nombre de lignes et colonnes, puis cliquer dans chaque cellule pour ajouter du contenu.', 'woocommerce'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_product_order"><?php _e('Ordre des produits (drag-and-drop)', 'woocommerce'); ?></label>
        </th>
        <td>
            <p><?php _e('Glissez-deposez les produits pour definir leur ordre d\'affichage pour cette categorie.', 'woocommerce'); ?></p>
            <input type="text" class="category-product-order-search regular-text" placeholder="Rechercher un produit..." style="margin-bottom:10px;" />

            <?php if (!empty($ordered_product_ids)) : ?>
                <ul class="category-product-order-list">
                    <?php foreach ($ordered_product_ids as $product_id) :
                        $product = wc_get_product($product_id);
                        if (!$product) {
                            continue;
                        }
                        $sku = $product->get_sku();
                        ?>
                        <li data-product-id="<?php echo esc_attr($product_id); ?>">
                            <span class="drag-handle">&#9776;</span>
                            <span class="product-order-title"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="product-order-meta">#<?php echo esc_html($product_id); ?><?php echo $sku ? ' - SKU: ' . esc_html($sku) : ''; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php _e('Aucun produit publié dans cette catégorie.', 'woocommerce'); ?></p>
            <?php endif; ?>

            <input type="hidden" id="category_product_order" name="category_product_order" value="<?php echo esc_attr(wp_json_encode($ordered_product_ids)); ?>" />
            <p class="description"><?php _e('Cet ordre est stocké pour cette catégorie uniquement.', 'woocommerce'); ?></p>
        </td>
    </tr>
    <?php
}

// Sauvegarder les champs personnalisés
add_action('created_product_cat', 'save_category_custom_fields');
add_action('edited_product_cat', 'save_category_custom_fields');
function save_category_custom_fields($term_id) {
    // Vérifier le nonce pour la sécurité
    if (!isset($_POST['category_custom_fields_nonce']) || !wp_verify_nonce($_POST['category_custom_fields_nonce'], 'category_custom_fields_nonce')) {
        return;
    }
    
    // Debug : Vérifier les données reçues
    error_log('Saving category fields for term ID: ' . $term_id);
    error_log('POST data: ' . print_r($_POST, true));
    
    if (isset($_POST['category_gallery'])) {
        $gallery_value = sanitize_text_field($_POST['category_gallery']);
        update_term_meta($term_id, 'category_gallery', $gallery_value);
        error_log('Gallery saved: ' . $gallery_value);
    }
    
    if (isset($_POST['category_pdf'])) {
        $pdf_value = absint($_POST['category_pdf']);
        update_term_meta($term_id, 'category_pdf', $pdf_value);
        error_log('PDF saved: ' . $pdf_value);
    }
    
    if (isset($_POST['category_table'])) {
        $table_value = stripslashes($_POST['category_table']);
        
        // Vérifier que c'est du JSON valide
        $decoded = json_decode($table_value, true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            // Re-encoder pour s'assurer du format
            $table_value = json_encode($decoded);
            $result = update_term_meta($term_id, 'category_table', $table_value);
            error_log('Table saved successfully: ' . $table_value);
            error_log('Update result: ' . ($result ? 'true' : 'false'));
        } else {
            error_log('Invalid JSON for table data: ' . $table_value);
            error_log('JSON error: ' . json_last_error_msg());
        }
    }

    if (isset($_POST['category_product_order'])) {
        $decoded_order = json_decode(wp_unslash($_POST['category_product_order']), true);

        if (is_array($decoded_order)) {
            $sanitized_order = array_values(array_filter(array_map('absint', $decoded_order)));
            update_term_meta($term_id, 'category_product_order', $sanitized_order);
        }
    }
}

// Ajouter les scripts JavaScript pour la gestion des médias
add_action('admin_footer', 'category_custom_fields_scripts');
function category_custom_fields_scripts() {
    global $pagenow;
    
    if ($pagenow == 'edit-tags.php' || $pagenow == 'term.php') {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var gallery_frame;
            var pdf_frame;
            
            // Gestion de la galerie d'images
            $(document).on('click', '.upload_gallery_button', function(e) {
                e.preventDefault();
                
                if (gallery_frame) {
                    gallery_frame.open();
                    return;
                }
                
                gallery_frame = wp.media({
                    title: 'Sélectionner des images pour la galerie',
                    button: {
                        text: 'Ajouter à la galerie'
                    },
                    multiple: true,
                    library: {
                        type: 'image'
                    }
                });
                
                gallery_frame.on('select', function() {
                    var selection = gallery_frame.state().get('selection');
                    var gallery_ids = [];
                    var existing_ids = $('#category_gallery').val();
                    
                    if (existing_ids) {
                        gallery_ids = existing_ids.split(',');
                    }
                    
                    selection.map(function(attachment) {
                        attachment = attachment.toJSON();
                        gallery_ids.push(attachment.id);
                        
                        // Ajouter l'image à la liste
                        $('.category_gallery_images').append(
                            '<li data-attachment_id="' + attachment.id + '" style="position: relative; display: inline-block;">' +
                            '<img src="' + attachment.sizes.thumbnail.url + '" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;" />' +
                            '<a href="#" class="delete_gallery_image" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 18px; text-decoration: none; font-size: 12px;">&times;</a>' +
                            '</li>'
                        );
                    });
                    
                    $('#category_gallery').val(gallery_ids.join(','));
                    $('.remove_gallery_button').show();
                });
                
                gallery_frame.open();
            });
            
            // Supprimer une image de la galerie
            $(document).on('click', '.delete_gallery_image', function(e) {
                e.preventDefault();
                var attachment_id = $(this).closest('li').data('attachment_id');
                $(this).closest('li').remove();
                
                var gallery_ids = $('#category_gallery').val().split(',');
                gallery_ids = gallery_ids.filter(function(id) {
                    return id != attachment_id;
                });
                
                $('#category_gallery').val(gallery_ids.join(','));
                
                if (gallery_ids.length === 0 || (gallery_ids.length === 1 && gallery_ids[0] === '')) {
                    $('.remove_gallery_button').hide();
                }
            });
            
            // Supprimer toute la galerie
            $(document).on('click', '.remove_gallery_button', function(e) {
                e.preventDefault();
                $('.category_gallery_images').empty();
                $('#category_gallery').val('');
                $(this).hide();
            });
            
            // Gestion du PDF
            $(document).on('click', '.upload_pdf_button', function(e) {
                e.preventDefault();
                
                if (pdf_frame) {
                    pdf_frame.open();
                    return;
                }
                
                pdf_frame = wp.media({
                    title: 'Sélectionner un fichier PDF',
                    button: {
                        text: 'Sélectionner ce PDF'
                    },
                    multiple: false,
                    library: {
                        type: 'application/pdf'
                    }
                });
                
                pdf_frame.on('select', function() {
                    var attachment = pdf_frame.state().get('selection').first().toJSON();
                    
                    $('#category_pdf').val(attachment.id);
                    
                    $('.category_pdf_preview').html(
                        '<div style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; display: inline-block;">' +
                        '<span style="font-size: 20px; margin-right: 8px;">📄</span>' +
                        '<strong>' + attachment.filename + '</strong>' +
                        '<a href="' + attachment.url + '" target="_blank" style="margin-left: 10px;">Voir le PDF</a>' +
                        '</div>'
                    );
                    
                    $('.remove_pdf_button').show();
                });
                
                pdf_frame.open();
            });
            
            // Supprimer le PDF
            $(document).on('click', '.remove_pdf_button', function(e) {
                e.preventDefault();
                $('#category_pdf').val('');
                $('.category_pdf_preview').empty();
                $(this).hide();
            });
            
            // ========== GESTION DU TABLEAU PERSONNALISÉ ==========
            
            // Générer le tableau
            $(document).on('click', '.generate_table_button', function(e) {
                e.preventDefault();
                var rows = parseInt($('#table_rows').val()) || 3;
                var cols = parseInt($('#table_cols').val()) || 3;
                
                // Limiter les valeurs
                rows = Math.min(Math.max(rows, 1), 20);
                cols = Math.min(Math.max(cols, 1), 10);
                
                var tableHtml = '<table class="category-custom-table" style="width: 100%; border-collapse: collapse;">';
                
                for (var i = 0; i < rows; i++) {
                    tableHtml += '<tr>';
                    for (var j = 0; j < cols; j++) {
                        tableHtml += '<td class="editable-cell" data-row="' + i + '" data-col="' + j + '" ';
                        tableHtml += 'style="border: 1px solid #ddd; padding: 8px; min-height: 30px; background: white; cursor: text;" ';
                        tableHtml += 'contenteditable="true"></td>';
                    }
                    tableHtml += '</tr>';
                }
                tableHtml += '</table>';
                
                $('.table-editor').html(tableHtml);
                updateTableData();
            });
            
            // Vider le tableau
            $(document).on('click', '.clear_table_button', function(e) {
                e.preventDefault();
                $('.table-editor').html('<p style="color: #666; text-align: center; margin: 40px 0;">Cliquez sur "Générer le tableau" pour créer votre tableau personnalisé.</p>');
                $('#category_table').val('');
            });
            
            // Mettre à jour les données du tableau quand on modifie une cellule
            $(document).on('input blur keyup', '.editable-cell', function() {
                updateTableData();
            });
            
            // Mettre à jour lors du changement de dimensions
            $(document).on('change', '#table_rows, #table_cols', function() {
                if ($('.category-custom-table').length > 0) {
                    updateTableData();
                }
            });
            
            // Fonction pour mettre à jour les données JSON du tableau
            function updateTableData() {
                var tableData = {
                    rows: parseInt($('#table_rows').val()) || 3,
                    cols: parseInt($('#table_cols').val()) || 3,
                    data: []
                };
                
                $('.category-custom-table tr').each(function(rowIndex) {
                    tableData.data[rowIndex] = [];
                    $(this).find('td').each(function(colIndex) {
                        tableData.data[rowIndex][colIndex] = $(this).text().trim();
                    });
                });
                
                $('#category_table').val(JSON.stringify(tableData));
                console.log('Table data updated:', JSON.stringify(tableData)); // Debug
            }
            
            // Initialiser les cellules éditables si le tableau existe déjà
            if ($('.category-custom-table').length > 0) {
                $('.editable-cell').attr('contenteditable', 'true');
                updateTableData(); // S'assurer que les données sont synchronisées
            }
            
            // Forcer la mise à jour avant soumission du formulaire
            $('form').on('submit', function() {
                if ($('.category-custom-table').length > 0) {
                    updateTableData();
                    console.log('Form submitted with table data:', $('#category_table').val()); // Debug
                }
            });
            
            // Mise à jour périodique pour s'assurer que les données sont toujours synchronisées
            setInterval(function() {
                if ($('.category-custom-table').length > 0) {
                    updateTableData();
                }
            }, 2000);

            function refreshCategoryProductOrderInput() {
                var orderedIds = [];
                $('.category-product-order-list li').each(function() {
                    orderedIds.push(parseInt($(this).attr('data-product-id'), 10));
                });

                $('#category_product_order').val(JSON.stringify(orderedIds.filter(function(id) {
                    return !isNaN(id) && id > 0;
                })));
            }

            if ($('.category-product-order-list').length > 0) {
                $('.category-product-order-list').sortable({
                    axis: 'y',
                    placeholder: 'category-product-order-placeholder',
                    update: function() {
                        refreshCategoryProductOrderInput();
                    }
                });

                refreshCategoryProductOrderInput();
            }

            $(document).on('input', '.category-product-order-search', function() {
                var needle = ($(this).val() || '').toLowerCase().trim();
                $('.category-product-order-list li').each(function() {
                    var haystack = $(this).text().toLowerCase();
                    $(this).toggle(haystack.indexOf(needle) !== -1);
                });
            });

            $('form').on('submit', function() {
                refreshCategoryProductOrderInput();
            });
        });
        </script>
        <?php
    }
}

// Fonctions pour récupérer les données (utilisables dans les templates)
function get_category_gallery($term_id) {
    $gallery_ids = get_term_meta($term_id, 'category_gallery', true);
    if ($gallery_ids) {
        return explode(',', $gallery_ids);
    }
    return array();
}

function get_category_pdf($term_id) {
    return get_term_meta($term_id, 'category_pdf', true);
}

function get_category_pdf_url($term_id) {
    $pdf_id = get_term_meta($term_id, 'category_pdf', true);
    if ($pdf_id) {
        return wp_get_attachment_url($pdf_id);
    }
    return false;
}

// Fonction pour récupérer les données du tableau
function get_category_table($term_id) {
    $table_data = get_term_meta($term_id, 'category_table', true);
    if ($table_data) {
        return json_decode($table_data, true);
    }
    return false;
}

// Support pour Elementor Dynamic Tags
add_action('elementor/dynamic_tags/register', 'register_category_dynamic_tags');
function register_category_dynamic_tags($dynamic_tags) {
    // Vérifier si les classes nécessaires existent
    if (!class_exists('Elementor\Core\DynamicTags\Tag')) {
        return;
    }
    
    // Enregistrer les tags dynamiques
    $dynamic_tags->register_group('woocommerce_category', [
        'title' => 'Catégorie WooCommerce'
    ]);
    
    // Tag pour la galerie
    $dynamic_tags->register_tag('Category_Gallery_Tag');
    
    // Tag pour le PDF
    $dynamic_tags->register_tag('Category_PDF_Tag');
}

// Classe pour le tag dynamique de la galerie
if (class_exists('Elementor\Core\DynamicTags\Tag')) {
    class Category_Gallery_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_name() {
            return 'category-gallery';
        }
        
        public function get_title() {
            return 'Galerie de la catégorie';
        }
        
        public function get_group() {
            return 'woocommerce_category';
        }
        
        public function get_categories() {
            return [\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY];
        }
        
        public function render() {
            $term_id = get_queried_object_id();
            if (is_product_category()) {
                $gallery_ids = get_category_gallery($term_id);
                if (!empty($gallery_ids)) {
                    $gallery = array();
                    foreach ($gallery_ids as $image_id) {
                        if ($image_id) {
                            $gallery[] = array(
                                'id' => $image_id
                            );
                        }
                    }
                    return $gallery;
                }
            }
            return array();
        }
    }
    
    // Classe pour le tag dynamique du PDF
    class Category_PDF_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_name() {
            return 'category-pdf';
        }
        
        public function get_title() {
            return 'PDF de la catégorie';
        }
        
        public function get_group() {
            return 'woocommerce_category';
        }
        
        public function get_categories() {
            return [\Elementor\Modules\DynamicTags\Module::URL_CATEGORY];
        }
        
        public function render() {
            $term_id = get_queried_object_id();
            if (is_product_category()) {
                $pdf_url = get_category_pdf_url($term_id);
                if ($pdf_url) {
                    echo esc_url($pdf_url);
                }
            }
        }
    }
}

// Shortcode pour afficher la galerie de catégorie
add_shortcode('category_gallery', 'category_gallery_shortcode');
function category_gallery_shortcode($atts) {
    $atts = shortcode_atts(array(
        'term_id' => 0,
        'size' => 'medium',
        'columns' => 3
    ), $atts);
    
    $term_id = $atts['term_id'] ? $atts['term_id'] : get_queried_object_id();
    $gallery_ids = get_category_gallery($term_id);
    
    if (empty($gallery_ids)) {
        return '';
    }
    
    $output = '<div class="category-gallery columns-' . esc_attr($atts['columns']) . '">';
    
    foreach ($gallery_ids as $image_id) {
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, $atts['size']);
            $image_full_url = wp_get_attachment_image_url($image_id, 'full');
            if ($image_url) {
                $output .= '<div class="gallery-item">';
                $output .= '<a href="' . esc_url($image_full_url) . '" data-lightbox="category-gallery">';
                $output .= '<img src="' . esc_url($image_url) . '" alt="" />';
                $output .= '</a>';
                $output .= '</div>';
            }
        }
    }
    
    $output .= '</div>';
    
    return $output;
}

// Shortcode pour afficher le lien PDF de catégorie
add_shortcode('category_pdf', 'category_pdf_shortcode');
function category_pdf_shortcode($atts) {
    $atts = shortcode_atts(array(
        'term_id' => 0,
        'text' => 'Télécharger le PDF',
        'class' => 'category-pdf-link'
    ), $atts);
    
    $term_id = $atts['term_id'] ? $atts['term_id'] : get_queried_object_id();
    $pdf_url = get_category_pdf_url($term_id);
    
    if (!$pdf_url) {
        return '';
    }
    
    return '<a href="' . esc_url($pdf_url) . '" class="' . esc_attr($atts['class']) . '" target="_blank" download>' . esc_html($atts['text']) . '</a>';
}

// Shortcode pour afficher le tableau de catégorie
add_shortcode('category_table', 'category_table_shortcode');
function category_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'term_id' => 0,
        'class' => 'category-table'
    ), $atts);
    
    $term_id = $atts['term_id'] ? $atts['term_id'] : get_queried_object_id();
    $table_data = get_category_table($term_id);
    
    if (!$table_data || !isset($table_data['rows']) || !isset($table_data['cols']) || !isset($table_data['data'])) {
        return '';
    }
    
    $output = '<div class="' . esc_attr($atts['class']) . '">';
    $output .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
    
    for ($i = 0; $i < $table_data['rows']; $i++) {
        $output .= '<tr>';
        for ($j = 0; $j < $table_data['cols']; $j++) {
            $cell_content = isset($table_data['data'][$i][$j]) ? $table_data['data'][$i][$j] : '';
            $output .= '<td style="border: 1px solid #ddd; padding: 12px; background: #f9f9f9;">';
            $output .= esc_html($cell_content);
            $output .= '</td>';
        }
        $output .= '</tr>';
    }
    
    $output .= '</table>';
    $output .= '</div>';
    
    return $output;
}

// CSS pour l'admin
add_action('admin_head', 'category_custom_fields_css');
function category_custom_fields_css() {
    global $pagenow;
    
    if ($pagenow == 'edit-tags.php' || $pagenow == 'term.php') {
        ?>
        <style>
        .category-gallery {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }
        .category-gallery.columns-2 { grid-template-columns: repeat(2, 1fr); }
        .category-gallery.columns-3 { grid-template-columns: repeat(3, 1fr); }
        .category-gallery.columns-4 { grid-template-columns: repeat(4, 1fr); }
        
        .gallery-item img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .category-pdf-link {
            display: inline-block;
            padding: 10px 20px;
            background: #FF3F22;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        
        .category-pdf-link:hover {
            background: #e03419;
            color: white;
        }
        
        /* Styles pour le tableau personnalisé */
        .category-custom-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .category-custom-table td {
            border: 1px solid #ddd;
            padding: 8px;
            min-height: 30px;
            background: white;
            transition: background 0.2s ease;
        }
        
        .category-custom-table td:hover {
            background: #f0f8ff;
        }
        
        .category-custom-table td:focus {
            outline: 2px solid #0073aa;
            background: #fff;
        }
        
        .table-controls {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .table-controls label {
            font-weight: 600;
            margin-right: 8px;
        }
        
        .table-controls input[type="number"] {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .table-editor {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
            min-height: 100px;
            border-radius: 4px;
        }
        
        /* Styles pour l'affichage frontend */
        .category-table table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .category-table td {
            border: 1px solid #ddd;
            padding: 12px;
            background: #f9f9f9;
            transition: background 0.3s ease;
        }
        
        .category-table tr:nth-child(even) td {
            background: #f1f1f1;
        }
        
        .category-table tr:hover td {
            background: #e8f4f8;
        }
        </style>
        <?php
    }
}
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

// Inclure le système de gestion des retours
$gestion_retours_paths = array(
    get_template_directory() . '/gestion-retours.php',
    get_template_directory() . '/assets/gestion-retours.php',
);

foreach ( $gestion_retours_paths as $gestion_retours_path ) {
    if ( file_exists( $gestion_retours_path ) ) {
        require_once $gestion_retours_path;
        break;
    }
}

// Activation du module de carte interactive avec liste de lieux
$carte_revendeurs_paths = array(
    get_template_directory() . '/carte-revendeurs.php',
    get_template_directory() . '/système de carte interactive/carte-revendeurs.php',
    get_template_directory() . '/systeme de carte interactive/carte-revendeurs.php',
    get_template_directory() . '/assets/carte-revendeurs.php',
);

foreach ( $carte_revendeurs_paths as $carte_revendeurs_path ) {
    if ( file_exists( $carte_revendeurs_path ) ) {
        require_once $carte_revendeurs_path;
        break;
    }
}

// ========== GESTION DU TYPE DE COMPTE (PARTICULIER / MAGASIN) ==========

// Ajouter une colonne "Type de compte" dans la liste des utilisateurs
add_filter('manage_users_columns', 'ajouter_colonne_type_compte');
function ajouter_colonne_type_compte($columns) {
    $columns['type_compte'] = 'Type de compte';
    return $columns;
}

// Afficher le contenu de la colonne "Type de compte"
add_filter('manage_users_custom_column', 'afficher_colonne_type_compte', 10, 3);
function afficher_colonne_type_compte($value, $column_name, $user_id) {
    if ($column_name == 'type_compte') {
        $type_compte = get_user_meta($user_id, 'type_compte', true);
        if ($type_compte == 'magasin') {
            return '<span style="color: #2271b1; font-weight: bold;">🏪 Magasin</span>';
        } elseif ($type_compte == 'particulier') {
            return '<span style="color: #50575e;">👤 Particulier</span>';
        } else {
            return '<span style="color: #999;">Non renseigné</span>';
        }
    }
    return $value;
}

// Ajouter un filtre dropdown dans la liste des utilisateurs
add_action('restrict_manage_users', 'ajouter_filtre_type_compte');
function ajouter_filtre_type_compte() {
    $type_compte = isset($_GET['type_compte']) ? $_GET['type_compte'] : '';
    ?>
    <select name="type_compte" style="float: none; margin-left: 10px;">
        <option value="">Tous les types de compte</option>
        <option value="particulier" <?php selected($type_compte, 'particulier'); ?>>Particulier</option>
        <option value="magasin" <?php selected($type_compte, 'magasin'); ?>>Magasin</option>
    </select>
    <?php
}

// Filtrer les utilisateurs par type de compte
add_filter('pre_get_users', 'filtrer_utilisateurs_par_type_compte');
function filtrer_utilisateurs_par_type_compte($query) {
    global $pagenow;
    
    if (is_admin() && $pagenow == 'users.php' && isset($_GET['type_compte']) && $_GET['type_compte'] != '') {
        $type_compte = $_GET['type_compte'];
        $meta_query = array(
            array(
                'key' => 'type_compte',
                'value' => $type_compte,
                'compare' => '='
            )
        );
        $query->set('meta_query', $meta_query);
    }
}

// Afficher le champ "Type de compte" dans la page de détails utilisateur
add_action('show_user_profile', 'afficher_type_compte_profil_admin');
add_action('edit_user_profile', 'afficher_type_compte_profil_admin');
function afficher_type_compte_profil_admin($user) {
    $type_compte = get_user_meta($user->ID, 'type_compte', true);
    $raison_sociale = get_user_meta($user->ID, 'raison_sociale', true);
    ?>
    <h3>Type de compte</h3>
    <table class="form-table">
        <tr>
            <th><label for="type_compte">Type de compte</label></th>
            <td>
                <select name="type_compte" id="type_compte">
                    <option value="">Sélectionner...</option>
                    <option value="particulier" <?php selected($type_compte, 'particulier'); ?>>Particulier</option>
                    <option value="magasin" <?php selected($type_compte, 'magasin'); ?>>Magasin</option>
                </select>
                <p class="description">Sélectionnez le type de compte de l'utilisateur.</p>
            </td>
        </tr>
        <tr>
            <th><label for="raison_sociale">Raison sociale</label></th>
            <td>
                <input type="text" name="raison_sociale" id="raison_sociale" value="<?php echo esc_attr($raison_sociale); ?>" class="regular-text" placeholder="Nom de l'entreprise (facultatif)">
                <p class="description">Raison sociale de l'utilisateur (facultatif).</p>
            </td>
        </tr>
    </table>
    <?php
}

// Sauvegarder le champ "Type de compte"
add_action('personal_options_update', 'sauvegarder_type_compte_profil_admin');
add_action('edit_user_profile_update', 'sauvegarder_type_compte_profil_admin');
function sauvegarder_type_compte_profil_admin($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['type_compte'])) {
        update_user_meta($user_id, 'type_compte', sanitize_text_field($_POST['type_compte']));
    }
    
    if (isset($_POST['raison_sociale'])) {
        $raison_sociale = sanitize_text_field($_POST['raison_sociale']);
        if (!empty($raison_sociale)) {
            update_user_meta($user_id, 'raison_sociale', $raison_sociale);
        } else {
            delete_user_meta($user_id, 'raison_sociale');
        }
    }
}

// ========== NAVIGATION ENTRE COMMANDES WOOCOMMERCE (ADMIN) ==========

// Affiche des flèches précédent/suivant sur la page de détail d'une commande.
add_action('woocommerce_admin_order_data_after_order_details', 'cannondale_render_order_navigation_arrows_admin');
add_action('admin_footer', 'cannondale_position_order_navigation_near_title_action');
add_action('admin_head', 'cannondale_order_quantity_highlight_styles');
add_action('admin_footer', 'cannondale_order_quantity_highlight_script');

function cannondale_render_order_navigation_arrows_admin($order) {
    if (!is_a($order, 'WC_Order')) {
        return;
    }

    if (!current_user_can('edit_shop_orders') && !current_user_can('manage_woocommerce')) {
        return;
    }

    $previous_order_id = cannondale_get_adjacent_order_id($order, 'previous');
    $next_order_id = cannondale_get_adjacent_order_id($order, 'next');

    if (!$previous_order_id && !$next_order_id) {
        return;
    }

    $previous_url = $previous_order_id ? cannondale_get_order_admin_edit_url($previous_order_id) : '';
    $next_url = $next_order_id ? cannondale_get_order_admin_edit_url($next_order_id) : '';

    echo '<div id="cannondale-order-nav-admin" style="margin: 12px 0 0; display: inline-flex; align-items: center; gap: 6px;">';

    if ($previous_url) {
        echo '<a href="' . esc_url($previous_url) . '" class="page-title-action" aria-label="Commande précédente" title="Commande précédente">← Précédent</a>';
    } else {
        echo '<span class="page-title-action disabled" aria-hidden="true" style="opacity:.55; cursor:not-allowed;">← Précédent</span>';
    }

    if ($next_url) {
        echo '<a href="' . esc_url($next_url) . '" class="page-title-action" aria-label="Commande suivante" title="Commande suivante">Suivant →</a>';
    } else {
        echo '<span class="page-title-action disabled" aria-hidden="true" style="opacity:.55; cursor:not-allowed;">Suivant →</span>';
    }

    echo '</div>';
}

function cannondale_position_order_navigation_near_title_action() {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return;
    }

    $is_order_screen = in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'), true)
        || (isset($screen->post_type) && $screen->post_type === 'shop_order');

    if (!$is_order_screen) {
        return;
    }

    ?>
    <script>
    (function () {
        var nav = document.getElementById('cannondale-order-nav-admin');
        if (!nav) {
            return;
        }

        var titleAction = document.querySelector('.wrap .page-title-action');
        var heading = document.querySelector('.wrap h1.wp-heading-inline, .wrap h1');

        nav.style.margin = '0 0 0 6px';

        if (titleAction && titleAction.parentNode) {
            titleAction.insertAdjacentElement('afterend', nav);
            return;
        }

        if (heading && heading.parentNode) {
            heading.insertAdjacentElement('afterend', nav);
        }
    })();
    </script>
    <?php
}

function cannondale_order_quantity_highlight_styles() {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return;
    }

    $is_order_screen = in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'), true)
        || (isset($screen->post_type) && $screen->post_type === 'shop_order');

    if (!$is_order_screen) {
        return;
    }
    ?>
    <style>
        .woocommerce_order_items td.item_quantity .view.cannondale-qty-highlight,
        .woocommerce_order_items td.quantity .view.cannondale-qty-highlight,
        #woocommerce-order-items td.item_quantity .view.cannondale-qty-highlight,
        #woocommerce-order-items td.quantity .view.cannondale-qty-highlight {
            display: inline-block;
            background: #ff6a00;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .woocommerce_order_items td.item_quantity .view.cannondale-qty-highlight .times,
        .woocommerce_order_items td.quantity .view.cannondale-qty-highlight .times,
        #woocommerce-order-items td.item_quantity .view.cannondale-qty-highlight .times,
        #woocommerce-order-items td.quantity .view.cannondale-qty-highlight .times {
            color: #fff;
            opacity: 0.95;
        }
    </style>
    <?php
}

function cannondale_order_quantity_highlight_script() {
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return;
    }

    $is_order_screen = in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'), true)
        || (isset($screen->post_type) && $screen->post_type === 'shop_order');

    if (!$is_order_screen) {
        return;
    }
    ?>
    <script>
    (function () {
        function extractQty(text) {
            if (!text) {
                return 0;
            }

            var clean = String(text).replace(/\s+/g, ' ').trim();
            var match = clean.match(/(\d+(?:[\.,]\d+)?)/);
            if (!match) {
                return 0;
            }

            return parseFloat(match[1].replace(',', '.')) || 0;
        }

        function applyQtyHighlight() {
            var nodes = document.querySelectorAll(
                '.woocommerce_order_items td.item_quantity .view,' +
                '.woocommerce_order_items td.quantity .view,' +
                '#woocommerce-order-items td.item_quantity .view,' +
                '#woocommerce-order-items td.quantity .view'
            );

            nodes.forEach(function (node) {
                node.classList.remove('cannondale-qty-highlight');
                var hasTimesMarker = !!node.querySelector('.times');
                if (!hasTimesMarker) {
                    return;
                }

                var qty = extractQty(node.textContent);

                if (qty > 1) {
                    node.classList.add('cannondale-qty-highlight');
                }
            });
        }

        applyQtyHighlight();
        setTimeout(applyQtyHighlight, 250);
        setTimeout(applyQtyHighlight, 800);

        var orderItemsRoot = document.getElementById('woocommerce-order-items') || document.querySelector('.woocommerce_order_items');
        if (orderItemsRoot && 'MutationObserver' in window) {
            var observer = new MutationObserver(function () {
                applyQtyHighlight();
            });

            observer.observe(orderItemsRoot, { childList: true, subtree: true, characterData: true });
        }

        document.addEventListener('wc_backbone_modal_loaded', applyQtyHighlight);
        document.addEventListener('updated_wc_div', applyQtyHighlight);
    })();
    </script>
    <?php
}

function cannondale_get_adjacent_order_id($current_order, $direction = 'previous') {
    if (!is_a($current_order, 'WC_Order')) {
        return 0;
    }

    $current_order_id = (int) $current_order->get_id();
    if ($current_order_id <= 0) {
        return 0;
    }

    global $wpdb;

    $status_keys = array_keys(wc_get_order_statuses());

    if (empty($status_keys)) {
        return 0;
    }

    $is_next = ($direction === 'next');
    $date_operator = $is_next ? '>' : '<';
    $id_operator = $is_next ? '>' : '<';
    $sort_direction = $is_next ? 'ASC' : 'DESC';
    $status_placeholders = implode(', ', array_fill(0, count($status_keys), '%s'));

    if (class_exists('Automattic\\WooCommerce\\Utilities\\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
        $table = $wpdb->prefix . 'wc_orders';

        $current_created_gmt = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT date_created_gmt FROM {$table} WHERE id = %d LIMIT 1",
                $current_order_id
            )
        );

        if (empty($current_created_gmt)) {
            return 0;
        }

        $query = "SELECT id
                  FROM {$table}
                  WHERE type = %s
                    AND status IN ({$status_placeholders})
                    AND (
                        date_created_gmt {$date_operator} %s
                        OR (date_created_gmt = %s AND id {$id_operator} %d)
                    )
                  ORDER BY date_created_gmt {$sort_direction}, id {$sort_direction}
                  LIMIT 1";

        $params = array_merge(
            array($current_order->get_type()),
            $status_keys,
            array($current_created_gmt, $current_created_gmt, $current_order_id)
        );
        $order_id = (int) $wpdb->get_var($wpdb->prepare($query, $params));

        return $order_id > 0 ? $order_id : 0;
    }

    $table = $wpdb->posts;

    $current_created = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COALESCE(NULLIF(post_date_gmt, '0000-00-00 00:00:00'), post_date)
             FROM {$table}
             WHERE ID = %d
             LIMIT 1",
            $current_order_id
        )
    );

    if (empty($current_created)) {
        return 0;
    }

    $query = "SELECT ID
              FROM {$table}
              WHERE post_type = %s
                AND post_status IN ({$status_placeholders})
                AND (
                    COALESCE(NULLIF(post_date_gmt, '0000-00-00 00:00:00'), post_date) {$date_operator} %s
                    OR (
                        COALESCE(NULLIF(post_date_gmt, '0000-00-00 00:00:00'), post_date) = %s
                        AND ID {$id_operator} %d
                    )
                )
              ORDER BY COALESCE(NULLIF(post_date_gmt, '0000-00-00 00:00:00'), post_date) {$sort_direction}, ID {$sort_direction}
              LIMIT 1";

    $params = array_merge(
        array($current_order->get_type()),
        $status_keys,
        array($current_created, $current_created, $current_order_id)
    );
    $order_id = (int) $wpdb->get_var($wpdb->prepare($query, $params));

    return $order_id > 0 ? $order_id : 0;
}

function cannondale_get_order_admin_edit_url($order_id) {
    if (class_exists('Automattic\\WooCommerce\\Utilities\\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
        return admin_url('admin.php?page=wc-orders&action=edit&id=' . absint($order_id));
    }

    return admin_url('post.php?post=' . absint($order_id) . '&action=edit');
}

// ========== AJOUT COLONNE STOCK DANS ADMIN COMMANDE ==========

/**
 * Ajouter l'entête de colonne "Stock" dans le tableau des articles de commande
 */
add_action('woocommerce_admin_order_item_headers', 'ajouter_entete_colonne_stock');
function ajouter_entete_colonne_stock() {
    echo '<th class="item_stock sortable" data-sort="int">Stock</th>';
}

/**
 * Afficher le stock du produit dans la colonne "Stock"
 */
add_action('woocommerce_admin_order_item_values', 'afficher_stock_commande', 10, 3);
function afficher_stock_commande($product, $item, $item_id) {
    if (!is_a($item, 'WC_Order_Item_Product')) {
        echo '<td class="item_stock" width="1%"><span style="color: #999;">-</span></td>';
        return;
    }

    $order_product = $item->get_product();
    if (!$order_product) {
        echo '<td class="item_stock" width="1%"><span style="color: #999;">-</span></td>';
        return;
    }

    // Pour les variations, WooCommerce peut gérer le stock sur le parent.
    $stock_owner_id = method_exists($order_product, 'get_stock_managed_by_id')
        ? (int) $order_product->get_stock_managed_by_id()
        : (int) $order_product->get_id();

    $stock_product = wc_get_product($stock_owner_id);
    if (!$stock_product) {
        $stock_product = $order_product;
    }

    if (!$stock_product->managing_stock()) {
        $stock_text = $stock_product->is_in_stock()
            ? '<span style="color: #2271b1;">En stock</span>'
            : '<span style="color: #dc3545; font-weight: bold;">Rupture</span>';
    } else {
        $stock_quantity = $stock_product->get_stock_quantity();
        $stock_quantity = is_numeric($stock_quantity) ? (int) wc_stock_amount($stock_quantity) : 0;

        if ($stock_quantity <= 0) {
            $stock_text = '<span style="color: #dc3545; font-weight: bold;">' . $stock_quantity . '</span>';
        } elseif ($stock_quantity <= 5) {
            $stock_text = '<span style="color: #ff6c00; font-weight: bold;">' . $stock_quantity . '</span>';
        } else {
            $stock_text = '<span style="color: #28a745;">' . $stock_quantity . '</span>';
        }
    }

    echo '<td class="item_stock" width="1%">';
    echo '<div class="view">';
    echo $stock_text;
    echo '</div>';
    echo '</td>';
}

/**
 * Ajouter du CSS pour styliser la colonne de stock
 */
add_action('admin_head', 'ajouter_css_colonne_stock');
function ajouter_css_colonne_stock() {
    if (!is_admin()) return;
    
    // Vérifier si nous sommes sur la page de commande
    global $post_type;
    if ('shop_order' !== $post_type) return;
    
    echo '<style>
    .woocommerce_order_items .item_stock {
        text-align: center;
        font-weight: 500;
    }
    .woocommerce_order_items th.item_stock {
        background-color: #f9f9f9;
    }
    </style>';
}

/**
 * Ajout de la fonctionnalité de bouton d'affichage des commandes client dans le détails client
 */
add_action('show_user_profile', 'add_view_orders_button_to_user_profile');
add_action('edit_user_profile', 'add_view_orders_button_to_user_profile');
add_action('admin_footer-profile.php', 'cannondale_move_orders_button_before_personal_options');
add_action('admin_footer-user-edit.php', 'cannondale_move_orders_button_before_personal_options');

function cannondale_get_customer_orders_admin_url($user) {
    $user_id = isset($user->ID) ? absint($user->ID) : 0;
    if (!$user_id) {
        return admin_url('edit.php?post_type=shop_order');
    }

    if (class_exists('Automattic\\WooCommerce\\Utilities\\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
        $search_value = '';

        if (!empty($user->user_email)) {
            $search_value = $user->user_email;
        } elseif (!empty($user->user_login)) {
            $search_value = $user->user_login;
        } else {
            $search_value = (string) $user_id;
        }

        return add_query_arg(
            array(
                'page' => 'wc-orders',
                's' => $search_value,
                'search-filter' => 'customers',
                'action' => '-1',
                'm' => '0',
                '_created_via' => '',
                '_customer_user' => '',
                'paged' => '1',
                'action2' => '-1',
            ),
            admin_url('admin.php')
        );
    }

    return add_query_arg(
        array(
            'post_type' => 'shop_order',
            '_customer_user' => $user_id,
        ),
        admin_url('edit.php')
    );
}

function add_view_orders_button_to_user_profile($user) {
    $orders_url = cannondale_get_customer_orders_admin_url($user);

    echo '<div id="cannondale-customer-orders-box">';
    echo '<h3>Commandes client</h3>';
    echo '<a href="' . esc_url($orders_url) . '" class="button button-primary">Voir les commandes</a>';
    echo '</div>';
}

function cannondale_move_orders_button_before_personal_options() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return;
    }

    if (!in_array($screen->id, array('profile', 'user-edit'), true)) {
        return;
    }
    ?>
    <script>
    (function () {
        var box = document.getElementById('cannondale-customer-orders-box');
        if (!box) {
            return;
        }

        var heading = document.querySelector('.wrap h2');
        if (!heading || !heading.parentNode) {
            return;
        }

        box.style.marginBottom = '18px';
        heading.parentNode.insertBefore(box, heading);
    })();
    </script>
    <?php
}
add_filter('woocommerce_checkout_fields', 'cannondale_checkout_fields_maquette');
function cannondale_checkout_fields_maquette($fields) {
    if (function_exists('is_checkout') && !is_checkout()) {
        return $fields;
    }

    // Force optional company fields for both address blocks, regardless of locale defaults.
    if (!isset($fields['billing']['billing_company'])) {
        $fields['billing']['billing_company'] = array(
            'type' => 'text',
            'label' => 'Raison sociale (facultatif)',
            'required' => false,
            'class' => array('form-row-wide'),
            'priority' => 20,
            'autocomplete' => 'organization',
        );
    }

    if (!isset($fields['shipping']['shipping_company'])) {
        $fields['shipping']['shipping_company'] = array(
            'type' => 'text',
            'label' => 'Raison sociale (facultatif)',
            'required' => false,
            'class' => array('form-row-wide'),
            'priority' => 20,
            'autocomplete' => 'organization',
        );
    }

    $billing_layout = array(
        'billing_country' => array('priority' => 10, 'class' => array('form-row-wide')),
        'billing_company' => array('priority' => 20, 'required' => false, 'class' => array('form-row-wide')),
        'billing_first_name' => array('priority' => 30, 'class' => array('form-row-first')),
        'billing_last_name' => array('priority' => 40, 'class' => array('form-row-last')),
        'billing_address_1' => array('priority' => 50, 'class' => array('form-row-wide')),
        'billing_address_2' => array('priority' => 60, 'required' => false, 'class' => array('form-row-wide')),
        'billing_postcode' => array('priority' => 70, 'class' => array('form-row-first')),
        'billing_city' => array('priority' => 80, 'class' => array('form-row-last')),
        'billing_phone' => array('priority' => 90, 'required' => false, 'class' => array('form-row-wide')),
        'billing_email' => array('priority' => 100, 'class' => array('form-row-wide')),
    );

    $shipping_layout = array(
        'shipping_country' => array('priority' => 10, 'class' => array('form-row-wide')),
        'shipping_company' => array('priority' => 20, 'required' => false, 'class' => array('form-row-wide')),
        'shipping_first_name' => array('priority' => 30, 'class' => array('form-row-first')),
        'shipping_last_name' => array('priority' => 40, 'class' => array('form-row-last')),
        'shipping_address_1' => array('priority' => 50, 'class' => array('form-row-wide')),
        'shipping_address_2' => array('priority' => 60, 'required' => false, 'class' => array('form-row-wide')),
        'shipping_postcode' => array('priority' => 70, 'class' => array('form-row-first')),
        'shipping_city' => array('priority' => 80, 'class' => array('form-row-last')),
    );

    foreach ($billing_layout as $field_key => $config) {
        if (!isset($fields['billing'][$field_key])) {
            continue;
        }

        foreach ($config as $prop => $value) {
            $fields['billing'][$field_key][$prop] = $value;
        }
    }

    foreach ($shipping_layout as $field_key => $config) {
        if (!isset($fields['shipping'][$field_key])) {
            continue;
        }

        foreach ($config as $prop => $value) {
            $fields['shipping'][$field_key][$prop] = $value;
        }
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['placeholder'] = 'Ajouter une note a votre commande';
        $fields['order']['order_comments']['class'] = array('form-row-wide');
    }

    // Prefill company fields from the custom account field when available.
    $current_user_id = get_current_user_id();
    if ($current_user_id > 0) {
        $company_name = trim((string) get_user_meta($current_user_id, 'raison_sociale', true));

        if ($company_name !== '') {
            if (isset($fields['billing']['billing_company'])) {
                $fields['billing']['billing_company']['default'] = $company_name;
            }

            if (isset($fields['shipping']['shipping_company'])) {
                $fields['shipping']['shipping_company']['default'] = $company_name;
            }
        }
    }

    return $fields;
}

add_filter('woocommerce_ship_to_different_address_checked', '__return_true');

function cannondale_is_checkout_request_context() {
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return false;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (!empty($_REQUEST['wc-ajax'])) {
        $wc_ajax_action = sanitize_key(wp_unslash($_REQUEST['wc-ajax']));
        return in_array($wc_ajax_action, array('update_order_review', 'apply_coupon', 'remove_coupon'), true);
    }

    return false;
}

add_filter('woocommerce_cart_item_name', 'cannondale_checkout_review_item_with_image', 20, 3);
function cannondale_checkout_review_item_with_image($item_name, $cart_item, $cart_item_key) {
    if (!cannondale_is_checkout_request_context()) {
        return $item_name;
    }

    if (empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) {
        return $item_name;
    }

    $product = $cart_item['data'];
    $image = $product->get_image(
        array(48, 48),
        array(
            'class' => 'checkout-review-item__image',
            'loading' => 'lazy',
        )
    );

    if (empty($image)) {
        return $item_name;
    }

    return '<span class="checkout-review-item">' . $image . '<span class="checkout-review-item__title">' . $item_name . '</span></span>';
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();
