<?php
/**
 * Script pour générer la liste de toutes les URLs du site WordPress
 * À placer à la racine de WordPress et exécuter via navigateur ou CLI
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Augmenter la limite de mémoire et le temps d'exécution
ini_set('memory_limit', '512M');
set_time_limit(300);

// Vérifier que le fichier de chargement WordPress existe
$wp_load = dirname(__FILE__).'/wp-load.php';
if (!file_exists($wp_load)) {
    die("ERREUR : Le fichier wp-load.php n'a pas été trouvé à : " . $wp_load . "<br>Assurez-vous que ce script est placé à la racine de votre installation WordPress.");
}

// Charger WordPress
try {
    require_once($wp_load);
} catch (Exception $e) {
    die("ERREUR lors du chargement de WordPress : " . $e->getMessage());
}

// Vérifier que WordPress est bien chargé
if (!function_exists('get_option')) {
    die("ERREUR : WordPress n'a pas été chargé correctement.");
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Génération URLs WordPress</title></head><body>";
echo "<h1>Génération de la liste des URLs</h1>";

try {
    $site_url = get_site_url();
    echo "Configuration chargée avec succès<br>";
    echo "URL du site : " . $site_url . "<br><br>";
} catch (Exception $e) {
    die("ERREUR lors de l'initialisation : " . $e->getMessage());
}

$urls = array();

// URL de base du site
try {
    $urls[] = home_url('/');
    echo "URL de base ajoutée<br><br>";
} catch (Exception $e) {
    echo "ERREUR URL de base : " . $e->getMessage() . "<br>";
}

// 1. Récupérer toutes les pages publiées
try {
    echo "Récupération des pages...<br>";
    flush();
    
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    $pages = get_posts($args);
    
    if ($pages) {
        foreach ($pages as $page) {
            try {
                $url = get_permalink($page->ID);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur page " . $page->ID . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($pages) . " pages récupérées<br>";
    }
    wp_reset_postdata();
} catch (Exception $e) {
    echo "ERREUR pages : " . $e->getMessage() . "<br>";
}

// 2. Récupérer tous les articles (posts) publiés
try {
    echo "Récupération des articles...<br>";
    flush();
    
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    $posts = get_posts($args);
    
    if ($posts) {
        foreach ($posts as $post) {
            try {
                $url = get_permalink($post->ID);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur article " . $post->ID . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($posts) . " articles récupérés<br>";
    }
    wp_reset_postdata();
} catch (Exception $e) {
    echo "ERREUR articles : " . $e->getMessage() . "<br>";
}

// 3. Récupérer toutes les catégories
try {
    echo "Récupération des catégories...<br>";
    flush();
    
    $categories = get_categories(array(
        'hide_empty' => false,
        'orderby' => 'id',
        'order' => 'ASC'
    ));
    
    if ($categories) {
        foreach ($categories as $category) {
            try {
                $url = get_category_link($category->term_id);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur catégorie " . $category->term_id . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($categories) . " catégories récupérées<br>";
    }
} catch (Exception $e) {
    echo "ERREUR catégories : " . $e->getMessage() . "<br>";
}

// 4. Récupérer tous les tags
try {
    echo "Récupération des tags...<br>";
    flush();
    
    $tags = get_tags(array(
        'hide_empty' => false,
        'orderby' => 'id',
        'order' => 'ASC'
    ));
    
    if ($tags) {
        foreach ($tags as $tag) {
            try {
                $url = get_tag_link($tag->term_id);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur tag " . $tag->term_id . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($tags) . " tags récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR tags : " . $e->getMessage() . "<br>";
}

// 5. Récupérer les custom post types publics
try {
    echo "Récupération des custom post types...<br>";
    flush();
    
    $custom_post_types = get_post_types(array(
        'public' => true,
        '_builtin' => false
    ), 'names');
    
    $cpt_count = 0;
    
    if ($custom_post_types) {
        foreach ($custom_post_types as $cpt) {
            try {
                $args = array(
                    'post_type' => $cpt,
                    'post_status' => 'publish',
                    'posts_per_page' => -1
                );
                
                $cpt_posts = get_posts($args);
                
                if ($cpt_posts) {
                    foreach ($cpt_posts as $cpt_post) {
                        try {
                            $url = get_permalink($cpt_post->ID);
                            if ($url) {
                                $urls[] = $url;
                                $cpt_count++;
                            }
                        } catch (Exception $e) {
                            echo "Erreur custom post " . $cpt_post->ID . " : " . $e->getMessage() . "<br>";
                        }
                    }
                }
                wp_reset_postdata();
            } catch (Exception $e) {
                echo "Erreur custom post type " . $cpt . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . $cpt_count . " custom post types récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR custom post types : " . $e->getMessage() . "<br>";
}

// 6. Récupérer les archives par date (dernières années)
try {
    echo "Récupération des archives mensuelles...<br>";
    flush();
    
    global $wpdb;
    $archives = $wpdb->get_results("
        SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month
        FROM $wpdb->posts
        WHERE post_status = 'publish'
        AND post_type = 'post'
        ORDER BY post_date DESC
    ");
    
    if ($archives) {
        foreach ($archives as $archive) {
            try {
                $url = get_month_link($archive->year, $archive->month);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur archive " . $archive->year . "-" . $archive->month . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($archives) . " archives mensuelles récupérées<br>";
    }
} catch (Exception $e) {
    echo "ERREUR archives : " . $e->getMessage() . "<br>";
}

// 7. Récupérer les auteurs
try {
    echo "Récupération des auteurs...<br>";
    flush();
    
    $authors = get_users(array(
        'who' => 'authors',
        'has_published_posts' => true,
        'orderby' => 'ID',
        'order' => 'ASC'
    ));
    
    if ($authors) {
        foreach ($authors as $author) {
            try {
                $url = get_author_posts_url($author->ID);
                if ($url) {
                    $urls[] = $url;
                }
            } catch (Exception $e) {
                echo "Erreur auteur " . $author->ID . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($authors) . " auteurs récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR auteurs : " . $e->getMessage() . "<br>";
}

// 8. Pages WooCommerce (si installé)
if (class_exists('WooCommerce')) {
    try {
        echo "Récupération des produits WooCommerce...<br>";
        flush();
        
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        
        $products = get_posts($args);
        
        if ($products) {
            foreach ($products as $product) {
                try {
                    $url = get_permalink($product->ID);
                    if ($url) {
                        $urls[] = $url;
                    }
                } catch (Exception $e) {
                    echo "Erreur produit " . $product->ID . " : " . $e->getMessage() . "<br>";
                }
            }
            echo "✓ " . count($products) . " produits WooCommerce récupérés<br>";
        }
        wp_reset_postdata();
        
        // Catégories de produits
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        
        if ($product_categories && !is_wp_error($product_categories)) {
            foreach ($product_categories as $cat) {
                try {
                    $url = get_term_link($cat->term_id, 'product_cat');
                    if ($url && !is_wp_error($url)) {
                        $urls[] = $url;
                    }
                } catch (Exception $e) {
                    echo "Erreur catégorie produit " . $cat->term_id . " : " . $e->getMessage() . "<br>";
                }
            }
            echo "✓ " . count($product_categories) . " catégories de produits récupérées<br>";
        }
        
        // Pages standards WooCommerce
        $woo_pages = array(
            'shop' => wc_get_page_id('shop'),
            'cart' => wc_get_page_id('cart'),
            'checkout' => wc_get_page_id('checkout'),
            'myaccount' => wc_get_page_id('myaccount')
        );
        
        foreach ($woo_pages as $page_id) {
            if ($page_id > 0) {
                $url = get_permalink($page_id);
                if ($url) {
                    $urls[] = $url;
                }
            }
        }
        echo "✓ Pages WooCommerce standards ajoutées<br>";
        
    } catch (Exception $e) {
        echo "ERREUR WooCommerce : " . $e->getMessage() . "<br>";
    }
}

// 9. Ajouter les taxonomies personnalisées
try {
    echo "Récupération des taxonomies personnalisées...<br>";
    flush();
    
    $taxonomies = get_taxonomies(array(
        'public' => true,
        '_builtin' => false
    ), 'names');
    
    $tax_count = 0;
    
    if ($taxonomies) {
        foreach ($taxonomies as $taxonomy) {
            try {
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false
                ));
                
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        try {
                            $url = get_term_link($term->term_id, $taxonomy);
                            if ($url && !is_wp_error($url)) {
                                $urls[] = $url;
                                $tax_count++;
                            }
                        } catch (Exception $e) {
                            echo "Erreur terme " . $term->term_id . " : " . $e->getMessage() . "<br>";
                        }
                    }
                }
            } catch (Exception $e) {
                echo "Erreur taxonomie " . $taxonomy . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . $tax_count . " termes de taxonomies personnalisées récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR taxonomies : " . $e->getMessage() . "<br>";
}

// Supprimer les doublons et trier
$urls = array_unique($urls);
$urls = array_filter($urls); // Supprimer les valeurs vides
sort($urls);

echo "<br>Génération du fichier...<br>";

// Générer le fichier texte
$filename = 'urls-list-wordpress-' . date('Y-m-d-H-i-s') . '.txt';
$filepath = dirname(__FILE__) . '/' . $filename;

$content = "Liste des URLs du site WordPress - Généré le " . date('d/m/Y à H:i:s') . "\n";
$content .= "Site : " . get_site_url() . "\n";
$content .= "Nombre total d'URLs : " . count($urls) . "\n";
$content .= str_repeat("=", 80) . "\n\n";

foreach ($urls as $url) {
    $content .= $url . "\n";
}

try {
    if (file_put_contents($filepath, $content)) {
        echo "<br>✓ Fichier généré avec succès : <strong>" . $filename . "</strong><br>";
        echo "Total d'URLs : <strong>" . count($urls) . "</strong><br>";
        echo "<br><a href='" . $filename . "' download style='padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px; display:inline-block; margin-top:10px;'>Télécharger le fichier</a><br>";
    } else {
        echo "<br>✗ Erreur lors de la création du fichier.<br>";
        echo "Vérifiez les permissions d'écriture du répertoire.<br>";
    }
} catch (Exception $e) {
    echo "ERREUR lors de la création du fichier : " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Génération terminée</h2>";
echo "<p><strong>Nombre total d'URLs :</strong> " . count($urls) . "</p>";
echo "<p style='color:#666; font-size:12px;'>⚠️ N'oubliez pas de supprimer ce fichier après utilisation pour des raisons de sécurité.</p>";
echo "</body></html>";
?>
