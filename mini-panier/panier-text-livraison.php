
/*
Plugin Name: Panier Text Livraison
Description: Un shortcode [panier_text_livraison] qui affiche un message de livraison gratuite avec une barre de progression.
Version: 1.6
*/

function panier_text_livraison_shortcode() {
    if ( ! function_exists( 'WC' ) ) return ''; // sécurité si WooCommerce n'est pas activé
    
    ob_start(); ?>
    
    <style>
        .panier-livraison-container {
            background-color: #F7F7F7;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            margin: 20px auto;
        }
        .panier-livraison-text {
            font-family: "din-next-lt-pro", sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
            margin-bottom: 10px;
            display: flex;              /* ✅ en row */
            flex-wrap: wrap;            /* permet retour ligne si trop long */
            gap: 5px;                   /* espace entre les éléments */
            align-items: center;        /* alignement vertical */
        }
        .panier-livraison-text .amount {
            font-weight: 700;
        }
        .panier-livraison-text .amount .woocommerce-Price-amount bdi {
            width: auto;
        }
        .panier-livraison-text .highlight {
            font-weight: 700;
        }
        .panier-livraison-progress-bg {
            width: 100%;
            height: 16px;
            background-color: #E9E9E9;
            border-radius: 10px;
            overflow: hidden;
        }
        .panier-livraison-progress-fill {
            height: 100%;
            background-color: #FF3F22;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>

    <div class="panier-livraison-container" id="panier-livraison">
        <p class="panier-livraison-text"></p>
        <div class="panier-livraison-progress-bg">
            <div class="panier-livraison-progress-fill"></div>
        </div>
    </div>

    <script type="text/javascript">
        function refreshPanierLivraison() {
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'get_panier_livraison'
            }, function(response) {
                if (response.success) {
                    jQuery('#panier-livraison .panier-livraison-text').html(response.data.message);
                    jQuery('#panier-livraison .panier-livraison-progress-fill').css('width', response.data.percentage + '%');
                }
            });
        }

        refreshPanierLivraison(); // premier chargement
        setInterval(refreshPanierLivraison, 400);
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('panier_text_livraison', 'panier_text_livraison_shortcode');


// AJAX handler pour récupérer le total panier
function ajax_get_panier_livraison() {
    if ( ! function_exists( 'WC' ) ) wp_send_json_error();

    $free_shipping_threshold = 149;
    $cart_subtotal = WC()->cart->get_cart_subtotal(); // total TTC numérique

    $percentage = min(100, ($cart_subtotal / $free_shipping_threshold) * 100);
     var_dump($cart_subtotal);   
    if ($cart_subtotal >= $free_shipping_threshold) {
        $message = "Félicitations 🎉 Vous bénéficiez de la <span class='highlight'>livraison gratuite à domicile</span> ou en point relais en France Métropolitaine !";
    } else {
        $remaining = $free_shipping_threshold - $cart_subtotal;

        // ✅ on découpe en spans pour flex aligné en row
        $message = '
            <span>Dépensez encore</span>
            <span class="amount">' . ($remaining) . '</span>
            <span>pour la <span class="highlight">livraison gratuite à domicile</span> ou en point relais en France Métropolitaine.</span>
        ';
    }

    wp_send_json_success([
        'message' => $message,
        'percentage' => $percentage
    ]);
}
add_action('wp_ajax_get_panier_livraison', 'ajax_get_panier_livraison');
add_action('wp_ajax_nopriv_get_panier_livraison', 'ajax_get_panier_livraison');
