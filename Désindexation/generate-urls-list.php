<?php
/**
 * Script pour générer la liste de toutes les URLs du site Prestashop
 * À placer à la racine de Prestashop et exécuter via navigateur ou CLI
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Augmenter la limite de mémoire et le temps d'exécution
ini_set('memory_limit', '512M');
set_time_limit(300);

// Vérifier que le fichier de configuration existe
$config_file = dirname(__FILE__).'/config/config.inc.php';
if (!file_exists($config_file)) {
    die("ERREUR : Le fichier de configuration Prestashop n'a pas été trouvé à : " . $config_file . "<br>Assurez-vous que ce script est placé à la racine de votre installation Prestashop.");
}

// Inclure le fichier de configuration de Prestashop
try {
    require_once($config_file);
} catch (Exception $e) {
    die("ERREUR lors du chargement de la configuration : " . $e->getMessage());
}

// Définir le contexte
try {
    $context = Context::getContext();
    $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $id_shop = (int)Configuration::get('PS_SHOP_DEFAULT');
    
    echo "Configuration chargée avec succès<br>";
    echo "Langue par défaut : " . $id_lang . "<br>";
    echo "Boutique par défaut : " . $id_shop . "<br><br>";
} catch (Exception $e) {
    die("ERREUR lors de l'initialisation : " . $e->getMessage());
}

$urls = array();
$link = new Link();

// URL de base du site
try {
    $base_url = _PS_BASE_URL_.__PS_BASE_URI__;
    $urls[] = $base_url;
    echo "URL de base : " . $base_url . "<br><br>";
} catch (Exception $e) {
    echo "ERREUR URL de base : " . $e->getMessage() . "<br>";
}

try {
    // 1. Récupérer toutes les catégories actives
    echo "Récupération des catégories...<br>";
    flush();
    
    $sql = 'SELECT c.id_category 
            FROM '._DB_PREFIX_.'category c
            WHERE c.active = 1 AND c.id_category != 1';
    
    $categories = Db::getInstance()->executeS($sql);
    
    if ($categories) {
        foreach ($categories as $category) {
            try {
                $cat_obj = new Category($category['id_category'], $id_lang);
                $url = $link->getCategoryLink($cat_obj);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur catégorie " . $category['id_category'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($categories) . " catégories récupérées<br>";
    }
} catch (Exception $e) {
    echo "ERREUR catégories : " . $e->getMessage() . "<br>";
}

try {
    // 2. Récupérer tous les produits actifs
    echo "Récupération des produits...<br>";
    flush();
    
    $sql = 'SELECT p.id_product 
            FROM '._DB_PREFIX_.'product p
            WHERE p.active = 1';
    
    $products = Db::getInstance()->executeS($sql);
    
    if ($products) {
        foreach ($products as $product) {
            try {
                $prod_obj = new Product($product['id_product'], false, $id_lang);
                $url = $link->getProductLink($prod_obj);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur produit " . $product['id_product'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($products) . " produits récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR produits : " . $e->getMessage() . "<br>";
}

try {
    // 3. Récupérer toutes les pages CMS actives
    echo "Récupération des pages CMS...<br>";
    flush();
    
    $sql = 'SELECT id_cms FROM '._DB_PREFIX_.'cms WHERE active = 1';
    $cms_pages = Db::getInstance()->executeS($sql);
    
    if ($cms_pages) {
        foreach ($cms_pages as $cms) {
            try {
                $url = $link->getCMSLink($cms['id_cms']);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur CMS " . $cms['id_cms'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($cms_pages) . " pages CMS récupérées<br>";
    }
} catch (Exception $e) {
    echo "ERREUR CMS : " . $e->getMessage() . "<br>";
}

try {
    // 4. Récupérer les catégories CMS
    echo "Récupération des catégories CMS...<br>";
    flush();
    
    $sql = 'SELECT id_cms_category FROM '._DB_PREFIX_.'cms_category WHERE active = 1';
    $cms_categories = Db::getInstance()->executeS($sql);
    
    if ($cms_categories) {
        foreach ($cms_categories as $cms_cat) {
            try {
                $url = $link->getCMSCategoryLink($cms_cat['id_cms_category']);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur catégorie CMS " . $cms_cat['id_cms_category'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($cms_categories) . " catégories CMS récupérées<br>";
    }
} catch (Exception $e) {
    echo "ERREUR catégories CMS : " . $e->getMessage() . "<br>";
}

try {
    // 5. Récupérer les fournisseurs (suppliers)
    echo "Récupération des fournisseurs...<br>";
    flush();
    
    $sql = 'SELECT id_supplier FROM '._DB_PREFIX_.'supplier WHERE active = 1';
    $suppliers = Db::getInstance()->executeS($sql);
    
    if ($suppliers) {
        foreach ($suppliers as $supplier) {
            try {
                $url = $link->getSupplierLink($supplier['id_supplier']);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur fournisseur " . $supplier['id_supplier'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($suppliers) . " fournisseurs récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR fournisseurs : " . $e->getMessage() . "<br>";
}

try {
    // 6. Récupérer les fabricants (manufacturers)
    echo "Récupération des fabricants...<br>";
    flush();
    
    $sql = 'SELECT id_manufacturer FROM '._DB_PREFIX_.'manufacturer WHERE active = 1';
    $manufacturers = Db::getInstance()->executeS($sql);
    
    if ($manufacturers) {
        foreach ($manufacturers as $manufacturer) {
            try {
                $url = $link->getManufacturerLink($manufacturer['id_manufacturer']);
                $urls[] = $url;
            } catch (Exception $e) {
                echo "Erreur fabricant " . $manufacturer['id_manufacturer'] . " : " . $e->getMessage() . "<br>";
            }
        }
        echo "✓ " . count($manufacturers) . " fabricants récupérés<br>";
    }
} catch (Exception $e) {
    echo "ERREUR fabricants : " . $e->getMessage() . "<br>";
}

try {
    // 7. Pages standards
    echo "Ajout des pages standards...<br>";
    flush();
    
    $standard_pages = array(
        'contact' => $link->getPageLink('contact'),
        'stores' => $link->getPageLink('stores'),
        'sitemap' => $link->getPageLink('sitemap'),
        'new-products' => $link->getPageLink('new-products'),
        'best-sales' => $link->getPageLink('best-sales'),
        'prices-drop' => $link->getPageLink('prices-drop'),
        'my-account' => $link->getPageLink('my-account'),
    );
    
    foreach ($standard_pages as $page_url) {
        if (!empty($page_url)) {
            $urls[] = $page_url;
        }
    }
    echo "✓ Pages standards ajoutées<br>";
} catch (Exception $e) {
    echo "ERREUR pages standards : " . $e->getMessage() . "<br>";
}

// Supprimer les doublons et trier
$urls = array_unique($urls);
sort($urls);

echo "<br>Génération du fichier...<br>";

// Générer le fichier texte
$filename = 'urls-list-' . date('Y-m-d-H-i-s') . '.txt';
$filepath = dirname(__FILE__) . '/' . $filename;

$content = "Liste des URLs du site - Généré le " . date('d/m/Y à H:i:s') . "\n";
$content .= "Nombre total d'URLs : " . count($urls) . "\n";
$content .= str_repeat("=", 80) . "\n\n";

foreach ($urls as $url) {
    $content .= $url . "\n";
}

try {
    if (file_put_contents($filepath, $content)) {
        echo "<br>✓ Fichier généré avec succès : <strong>" . $filename . "</strong><br>";
        echo "Total d'URLs : <strong>" . count($urls) . "</strong><br>";
        echo "<br><a href='" . $filename . "' download style='padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;'>Télécharger le fichier</a><br>";
    } else {
        echo "<br>✗ Erreur lors de la création du fichier.<br>";
        echo "Vérifiez les permissions d'écriture du répertoire.<br>";
    }
} catch (Exception $e) {
    echo "ERREUR lors de la création du fichier : " . $e->getMessage() . "<br>";
}

} catch (Exception $e) {
    echo "ERREUR lors de la création du fichier : " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Génération terminée</h2>";
echo "<p><strong>Nombre total d'URLs :</strong> " . count($urls) . "</p>";
?>
