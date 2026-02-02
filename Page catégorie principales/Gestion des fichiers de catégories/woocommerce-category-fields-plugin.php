<?php
/**
 * Plugin Name: WooCommerce Category Custom Fields
 * Description: Plugin complet pour ajouter des champs personnalisés aux catégories WooCommerce - Galerie d'images, PDF et tableau personnalisé
 * Version: 1.1.0
 * Author: Custom Development
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Text Domain: wccf
 */

// Sécurité : empêcher l'accès direct
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

// === VOTRE CODE EXISTANT ICI ===
// (Copiez tout le contenu de category-custom-fields.php sans les balises <?php d'ouverture)

// Ajouter les champs à la page d'ajout de catégorie
add_action('product_cat_add_form_fields', 'add_category_custom_fields');
function add_category_custom_fields() {
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
    $gallery_ids = get_term_meta($term->term_id, 'category_gallery', true);
    $pdf_id = get_term_meta($term->term_id, 'category_pdf', true);
    $table_data = get_term_meta($term->term_id, 'category_table', true);
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
    <?php
}

// Sauvegarder les champs personnalisés
add_action('created_product_cat', 'save_category_custom_fields');
add_action('edited_product_cat', 'save_category_custom_fields');
function save_category_custom_fields($term_id) {
    if (isset($_POST['category_gallery'])) {
        update_term_meta($term_id, 'category_gallery', sanitize_text_field($_POST['category_gallery']));
    }
    
    if (isset($_POST['category_pdf'])) {
        update_term_meta($term_id, 'category_pdf', absint($_POST['category_pdf']));
    }
    
    if (isset($_POST['category_table'])) {
        update_term_meta($term_id, 'category_table', sanitize_textarea_field($_POST['category_table']));
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
            $(document).on('input blur', '.editable-cell', function() {
                updateTableData();
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
            }
            
            // Initialiser les cellules éditables si le tableau existe déjà
            if ($('.category-custom-table').length > 0) {
                $('.editable-cell').attr('contenteditable', 'true');
            }
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

// Hook d'activation du plugin (optionnel)
register_activation_hook(__FILE__, 'wccf_activate');
function wccf_activate() {
    // Actions à effectuer lors de l'activation du plugin
    flush_rewrite_rules();
}

// Hook de désactivation du plugin (optionnel)
register_deactivation_hook(__FILE__, 'wccf_deactivate');
function wccf_deactivate() {
    // Actions à effectuer lors de la désactivation du plugin
    flush_rewrite_rules();
}
?>