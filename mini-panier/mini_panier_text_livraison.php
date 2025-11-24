
/*
Plugin Name: Panier Text Livraison
Description: Un shortcode [panier_text_livraison] qui affiche un message de livraison gratuite avec une barre de progression.
Version: 1.6
*/

function mini_panier_text_livraison_shortcode() {
    if ( ! function_exists( 'WC' ) ) return ''; // sécurité si WooCommerce n'est pas activé
    
    ob_start(); ?>
    
    <style>
        .mini-panier-livraison-container {
            background-color: #F7F7F7;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            margin: 20px auto;
        }
        .mini-panier-livraison-text {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
            margin-bottom: 10px;
            display: flex;              /* ✅ en row */
            flex-wrap: wrap;            /* permet retour ligne si trop long */
            gap: 5px;                   /* espace entre les éléments */
            align-items: center;        /* alignement vertical */
        }
        .mini-panier-livraison-text .amount {
            font-weight: 700;
        }
        .mini-panier-livraison-text .amount .woocommerce-Price-amount bdi {
            width: auto;
        }
        .mini-panier-livraison-text .highlight {
            font-weight: 700;
        }
        .mini-panier-livraison-progress-bg {
            width: 100%;
            height: 16px;
            background-color: #E9E9E9;
            border-radius: 10px;
            overflow: hidden;
        }
        .mini-panier-livraison-progress-fill {
            height: 100%;
            background-color: #FF3F22;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>

    <div class="mini-panier-livraison-container" id="mini-panier-livraison">
        <p class="mini-panier-livraison-text"></p>
        <div class="mini-panier-livraison-progress-bg">
            <div class="mini-panier-livraison-progress-fill"></div>
        </div>
    </div>

    <script type="text/javascript">
        function refreshPanierLivraison() {
            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'get_panier_livraison'
            }, function(response) {
                if (response.success) {
                    jQuery('#mini-panier-livraison .mini-panier-livraison-text').html(response.data.message);
                    jQuery('#mini-panier-livraison .mini-panier-livraison-progress-fill').css('width', response.data.percentage + '%');
                }
            });
        }

        refreshPanierLivraison(); // premier chargement
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('mini_panier_text_livraison', 'mini_panier_text_livraison_shortcode');


// AJAX handler pour récupérer le total panier
function ajax_get_mini_panier_livraison() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        wp_send_json_error();
        return;
    }

    $free_shipping_threshold = 110;
    $cart_total = WC()->cart->get_subtotal(); // total HT numérique (sans frais de port)

    $percentage = min(100, ($cart_total / $free_shipping_threshold) * 100);

    if ($cart_total >= $free_shipping_threshold) {
        $message = "Félicitations 🎉 Vous bénéficiez de la <span class='highlight'>livraison gratuite à domicile</span> ou en point relais en France Métropolitaine !";
    } else {
        $remaining = $free_shipping_threshold - $cart_total;

        // ✅ on découpe en spans pour flex aligné en row
        $message = '
            <span>Dépensez encore</span>
            <span class="amount">' . wc_price($remaining) . '</span>
            <span>pour la <span class="highlight">livraison gratuite à domicile</span> ou en point relais en France Métropolitaine.</span>
        ';
    }

    wp_send_json_success([
        'message' => $message,
        'percentage' => $percentage
    ]);
}
add_action('wp_ajax_get_panier_livraison', 'ajax_get_mini_panier_livraison');
add_action('wp_ajax_nopriv_get_panier_livraison', 'ajax_get_mini_panier_livraison');
