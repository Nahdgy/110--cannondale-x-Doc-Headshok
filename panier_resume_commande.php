
/**
 * Shortcode [panier_resume_commande]
 * Affiche un résumé de la commande stylisé
 */

function panier_resume_commande_shortcode() {
    ob_start();

    // Récupérer le panier WooCommerce
    if ( ! WC()->cart ) {
        return '<p>Le panier est vide.</p>';
    }

    $cart_subtotal = WC()->cart->get_cart_subtotal();
    $cart_total = WC()->cart->get_total();

    ?>
    <div class="panier-resume-commande">

        <!-- Titre -->
        <h2 class="resume-titre">Résumé de la commande</h2>

        <!-- Séparateur -->
        <hr class="separator">

        <!-- Total -->
        <div class="resume-line">
            <span class="resume-total">Total</span>
            <span class="resume-total-value"><?php echo $cart_subtotal; ?></span>
        </div>

        <!-- Bouton Paiement -->
        <?php if (is_user_logged_in()) { ?>
            <a href="https://cannonbale.com/commander/" class="paiement-btn">Paiement</a>
        <?php } else { ?>
            <a href="/login" class="paiement-btn">Connexion pour payer</a>
        <?php } ?>

        <!-- Paiement sécurisé -->
        <div class="paiement-securise">
            <img src="https://cannonbale.com/wp-content/uploads/2025/07/Vector-1.png" alt="Paiement sécurisé" class="secure-icon">
            <span class="secure-text">Paiement sécurisé</span>
        </div>

    </div>

    <style>
        .resume-titre {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 24px;
            color: #000000;
            text-align: center;
        }
        .separator {
            border: 1px solid #FF3F22;
            height: 1px;
            background-color: #FF3F22 !important;
            margin: 10px 0;
        }
        .resume-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 5px 0;
        }
        .resume-text {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
        }
        .resume-total {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 24px;
            color: #000000;
        }
        .resume-total-value {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 24px;
            color: #000000;
        }
		.resume-value .woocommerce-Price-amount bdi,
		.resume-total-value .woocommerce-Price-amount bdi {
			width: auto;
		}
        .paiement-btn:hover {
            background-color: #151515;
            color: #F9F9F9;
        }
        .paiement-btn:focus {
            background-color: #151515;
            color: #F9F9F9;
        }
        .paiement-btn {
            display: block;
            text-align: center;
            text-decoration: none;
            width: 100%;
            padding: 12px;
            background-color: #151515;
            color: #F9F9F9 !important;
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            margin-top: 15px;
            cursor: pointer;
        }
        .paiement-securise {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }
        .secure-icon {
            height: auto;
        }
        .secure-text {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 12px;
            color: #000000;
        }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('panier_resume_commande', 'panier_resume_commande_shortcode');
