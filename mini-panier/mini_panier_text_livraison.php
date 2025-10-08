<?php

/*/*

Plugin Name: Panier Text LivraisonPlugin Name: Panier Text Livraison

Description: Un shortcode [mini_panier_text_livraison] qui affiche un message de livraison gratuite avec une barre de progression.Description: Un shortcode [panier_text_livraison] qui affiche un message de livraison gratuite avec une barre de progression.

Version: 1.7Version: 1.6

*/*/



function mini_panier_text_livraison_shortcode() {function mini_panier_text_livraison_shortcode() {

    if ( ! function_exists( 'WC' ) ) return ''; // sécurité si WooCommerce n'est pas activé    if ( ! function_exists( 'WC' ) ) return ''; // sécurité si WooCommerce n'est pas activé

        

    ob_start(); ?>    ob_start(); ?>

        

    <style>    <style>

        .mini-panier-livraison-container {        .mini-panier-livraison-container {

            background-color: #F7F7F7;            background-color: #F7F7F7;

            padding: 15px;            padding: 15px;

            border-radius: 8px;            border-radius: 8px;

            width: 100%;            width: 100%;

            margin: 20px auto;            margin: 20px auto;

        }        }

        .mini-panier-livraison-text {        .mini-panier-livraison-text {

            font-family: 'din-next-lt-pro', sans-serif;            font-family: 'din-next-lt-pro', sans-serif;

            font-weight: 300;            font-weight: 300;

            font-size: 16px;            font-size: 16px;

            color: #000000;            color: #000000;

            margin-bottom: 10px;            margin-bottom: 10px;

            display: flex;              /* ✅ en row */            display: flex;              /* ✅ en row */

            flex-wrap: wrap;            /* permet retour ligne si trop long */            flex-wrap: wrap;            /* permet retour ligne si trop long */

            gap: 5px;                   /* espace entre les éléments */            gap: 5px;                   /* espace entre les éléments */

            align-items: center;        /* alignement vertical */            align-items: center;        /* alignement vertical */

        }        }

        .mini-panier-livraison-text .amount {        .mini-panier-livraison-text .amount {

            font-weight: 700;            font-weight: 700;

        }        }

        .mini-panier-livraison-text .amount .woocommerce-Price-amount bdi {        .mini-panier-livraison-text .amount .woocommerce-Price-amount bdi {

            width: auto;            width: auto;

        }        }

        .mini-panier-livraison-text .highlight {        .mini-panier-livraison-text .highlight {

            font-weight: 700;            font-weight: 700;

        }        }

        .mini-panier-livraison-progress-bg {        .mini-panier-livraison-progress-bg {

            background-color: #DDDDDD;            width: 100%;

            border-radius: 10px;            height: 16px;

            width: 100%;            background-color: #E9E9E9;

            height: 10px;            border-radius: 10px;

            position: relative;            overflow: hidden;

            overflow: hidden;        }

        }        .mini-panier-livraison-progress-fill {

        .mini-panier-livraison-progress-fill {            height: 100%;

            background-color: #FF4A17;            background-color: #FF3F22;

            height: 100%;            border-radius: 10px;

            border-radius: 10px;            transition: width 0.5s ease;

            transition: width 0.5s ease;        }

            position: absolute;    </style>

            top: 0;

            left: 0;    <div class="mini-panier-livraison-container" id="mini-panier-livraison">

        }        <p class="mini-panier-livraison-text"></p>

    </style>        <div class="mini-panier-livraison-progress-bg">

            <div class="mini-panier-livraison-progress-fill"></div>

    <div id="mini-panier-livraison" class="mini-panier-livraison-container">        </div>

        <div class="mini-panier-livraison-text">    </div>

            Chargement...

        </div>    <script type="text/javascript">

        <div class="mini-panier-livraison-progress-bg">        function refreshPanierLivraison() {

            <div class="mini-panier-livraison-progress-fill" style="width: 0%;"></div>            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {

        </div>                action: 'get_panier_livraison',

    </div>                nonce: '<?php echo wp_create_nonce('panier_livraison_nonce'); ?>'

            }, function(response) {

    <script type="text/javascript">                if (response.success) {

        function refreshPanierLivraison() {                    jQuery('#mini-panier-livraison .mini-panier-livraison-text').html(response.data.message);

            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {                    jQuery('#mini-panier-livraison .mini-panier-livraison-progress-fill').css('width', response.data.percentage + '%');

                action: 'get_panier_livraison',                }

                nonce: '<?php echo wp_create_nonce('panier_livraison_nonce'); ?>'            }).fail(function(xhr, status, error) {

            }, function(response) {                console.log('Erreur AJAX panier livraison:', error);

                if (response.success) {                console.log('Status:', status);

                    jQuery('#mini-panier-livraison .mini-panier-livraison-text').html(response.data.message);                console.log('Response:', xhr.responseText);

                    jQuery('#mini-panier-livraison .mini-panier-livraison-progress-fill').css('width', response.data.percentage + '%');            });

                }        }

            }).fail(function(xhr, status, error) {

                console.log('Erreur AJAX panier livraison:', error);    refreshPanierLivraison(); // premier chargement

                console.log('Status:', status);    // La requête AJAX n'est plus lancée en continu, mais uniquement lors d'une action utilisateur (ex: mise à jour panier)

                console.log('Response:', xhr.responseText);    </script>

            });    

        }    <?php

    return ob_get_clean();

    refreshPanierLivraison(); // premier chargementadd_shortcode('mini_panier_text_livraison', 'mini_panier_text_livraison_shortcode');

    // La requête AJAX n'est plus lancée en continu, mais uniquement lors d'une action utilisateur (ex: mise à jour panier)

    </script>

    // AJAX handler pour récupérer le total panier

    <?phpfunction ajax_get_mini_panier_livraison() {

    return ob_get_clean();    // Vérifier le nonce de sécurité

}    if (!wp_verify_nonce($_POST['nonce'], 'panier_livraison_nonce')) {

add_shortcode('mini_panier_text_livraison', 'mini_panier_text_livraison_shortcode');        wp_send_json_error('Nonce de sécurité invalide');

        return;

// AJAX handler pour récupérer le total panier    }

function ajax_get_mini_panier_livraison() {    

    // Vérifier le nonce de sécurité    if ( ! function_exists( 'WC' ) ) {

    if (!wp_verify_nonce($_POST['nonce'], 'panier_livraison_nonce')) {        wp_send_json_error('WooCommerce non disponible');

        wp_send_json_error('Nonce de sécurité invalide');        return;

        return;    }

    }

        $free_shipping_threshold = 110;

    if ( ! function_exists( 'WC' ) ) {    $cart_total = WC()->cart->get_total('edit'); // total TTC numérique

        wp_send_json_error('WooCommerce non disponible');

        return;    $percentage = min(100, ($cart_total / $free_shipping_threshold) * 100);

    }

    if ($cart_total >= $free_shipping_threshold) {

    $free_shipping_threshold = 110;        $message = "Félicitations 🎉 Vous bénéficiez de la <span class='highlight'>livraison gratuite à domicile</span> ou en point relais en France Métropolitaine !";

    $cart_total = WC()->cart->get_total('edit'); // total TTC numérique    } else {

        $remaining = $free_shipping_threshold - $cart_total;

    $percentage = min(100, ($cart_total / $free_shipping_threshold) * 100);

        // ✅ on découpe en spans pour flex aligné en row

    if ($cart_total >= $free_shipping_threshold) {        $message = '

        $message = "Félicitations 🎉 Vous bénéficiez de la <span class='highlight'>livraison gratuite à domicile</span> ou en point relais en France Métropolitaine !";            <span>Dépensez encore</span>

    } else {            <span class="amount">' . wc_price($remaining) . '</span>

        $remaining = $free_shipping_threshold - $cart_total;            <span>pour la <span class="highlight">livraison gratuite à domicile</span> ou en point relais en France Métropolitaine.</span>

        ';

        // ✅ on découpe en spans pour flex aligné en row    }

        $message = '

            <span>Dépensez encore</span>    wp_send_json_success([

            <span class="amount">' . wc_price($remaining) . '</span>        'message' => $message,

            <span>pour la <span class="highlight">livraison gratuite à domicile</span> ou en point relais en France Métropolitaine.</span>        'percentage' => $percentage

        ';    ]);

    }add_action('wp_ajax_get_panier_livraison', 'ajax_get_mini_panier_livraison');

add_action('wp_ajax_nopriv_get_panier_livraison', 'ajax_get_mini_panier_livraison');

    wp_send_json_success([
        'message' => $message,
        'percentage' => $percentage
    ]);
}
add_action('wp_ajax_get_panier_livraison', 'ajax_get_mini_panier_livraison');
add_action('wp_ajax_nopriv_get_panier_livraison', 'ajax_get_mini_panier_livraison');
?>