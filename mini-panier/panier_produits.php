
/*
Plugin Name: Panier Produits Shortcode
Description: Shortcode [panier_produits] pour afficher les produits du panier WooCommerce avec image, stock, quantité, suppression et prix dynamique.
Version: 1.6
*/

// Shortcode
function panier_produits_shortcode() {
    $cart_items = WC()->cart ? WC()->cart->get_cart() : [];
    if (!$cart_items || empty($cart_items)) {
        return '<p>Votre panier est vide.</p>';
    }

    ob_start();
    ?>
    <style>
        .panier-produits {
            overflow-y: hidden; /* scroll caché par défaut */
            padding-right: 10px;
            transition: max-height 0.3s;
        }
        .panier-grid {
            display: grid;
            grid-template-columns: 100px 1fr auto; /* image | infos | prix */
            gap: 20px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .panier-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .panier-nom {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #000000;
        }
        .panier-stock {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
        }
        .panier-quantite-wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
        }
        .panier-quantite {
            border: 1px solid #656565;
            border-radius: 10px;
            padding: 5px 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            width: fit-content;
        }
        .panier-quantite button {
            padding: 5px 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: #151515;
        }
        .panier-quantite input {
            width: 64px;
            text-align: center;
            border: none;
            background: transparent;
            font-size: 14px;
        }
        .panier-supprimer img {
            cursor: pointer;
        }
        .panier-image {
            text-align: center;
        }
        .panier-image img {
            max-width: 80px;
        }
        .panier-prix {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #000000;
            text-align: right;
            white-space: nowrap;
        }

	  @media screen and (max-width: 530px) {
		  
	}
    </style>

    <div class="panier-produits">
        <?php foreach ($cart_items as $cart_item_key => $cart_item) :
            $product = $cart_item['data'];
            $qty = $cart_item['quantity'];
            $stock = $product->get_stock_quantity();
            $subtotal = $cart_item['line_total'];
        ?>
            <div class="panier-grid" data-key="<?php echo esc_attr($cart_item_key); ?>">
                <!-- Colonne gauche : image -->
                <div class="panier-image">
                    <?php 
					$image_url = $product->get_image_id() 
						? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') 
						: 'https://cannonbale.com/wp-content/uploads/2023/10/2186-Garde-boue-arriere-Tesoro-Neo-K11078-Cannondale.jpg';
					?>
					<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                </div>

                <!-- Colonne centre : infos -->
                <div class="panier-info">
                    <div class="panier-nom"><?php echo esc_html($product->get_name()); ?></div>
                    <div class="panier-stock">Stocks Disponibles: <?php echo $stock ? $stock : 'Rupture'; ?></div>
                    <div class="panier-quantite-wrapper">
                        <div class="panier-quantite">
                            <button class="moins">-</button>
                            <input type="number" min="1" value="<?php echo esc_attr($qty); ?>" readonly>
                            <button class="plus">+</button>
                        </div>
                        <div class="panier-supprimer">
                            <img src="https://cannonbale.com/wp-content/uploads/2025/07/Vector.png" 
                                 alt="Supprimer" class="supprimer-btn">
                        </div>
                    </div>
                </div>

                <!-- Colonne droite : prix -->
                <div class="panier-prix"><?php echo wc_price($subtotal); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ajout de l'animation spinner
        const style = document.createElement('style');
        style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
        const panierContainer = document.querySelector(".panier-produits");

        function updateScroll() {
            const rows = panierContainer.querySelectorAll(".panier-grid").length;
            if (rows > 3) {
                panierContainer.style.maxHeight = "400px";
                panierContainer.style.overflowY = "auto";
            } else {
                panierContainer.style.maxHeight = "none";
                panierContainer.style.overflowY = "hidden";
            }
        }

        document.querySelectorAll(".panier-grid").forEach(function(row) {
            let moinsBtn = row.querySelector(".moins");
            let plusBtn = row.querySelector(".plus");
            let input = row.querySelector("input");
            let prixDiv = row.querySelector(".panier-prix");
            let supBtn = row.querySelector(".supprimer-btn");

            moinsBtn.addEventListener("click", function() {
                let q = parseInt(input.value);
                if (q > 1) {
                    q--;
                    input.value = q;
                    majPanier(row.dataset.key, q, prixDiv);
                }
            });

            plusBtn.addEventListener("click", function() {
                let q = parseInt(input.value);
                q++;
                input.value = q;
                majPanier(row.dataset.key, q, prixDiv);
            });

            supBtn.addEventListener("click", function() {
                // Remplace l'icône poubelle par un spinner
                supBtn.src = "https://cannonbale.com/wp-content/uploads/2025/07/spinner.svg";
                supBtn.style.animation = "spin 1s linear infinite";
                majPanier(row.dataset.key, 0, prixDiv, row);
            });
    // Ajout de l'animation spinner
    const style = document.createElement('style');
    style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
    document.head.appendChild(style);
        });

        function majPanier(cartKey, qty, prixDiv, row = null) {
            fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=maj_panier&key=" + cartKey + "&qty=" + qty
            })
            .then(res => res.json())
            .then(data => {
				// Mise à jour du prix de la ligne
				if (qty === 0 && row) {
					row.remove();

					// Vérifier si le panier est vide après suppression
					if (document.querySelectorAll(".panier-grid").length === 0) {
						document.querySelector(".panier-produits").innerHTML = "<p>Votre panier est vide.</p>";
					}
				} else if (data.line_subtotal && prixDiv) {
					prixDiv.innerHTML = data.line_subtotal;
				}
				// Mise à jour du résumé de commande
				let resumeSousTotal = document.querySelector(".panier-resume-commande .resume-value");
				let resumeTotal = document.querySelector(".panier-resume-commande .resume-total-value");

				if (resumeSousTotal && data.cart_subtotal) {
					resumeSousTotal.innerHTML = data.cart_subtotal;
				}
				if (resumeTotal && data.cart_total) {
					resumeTotal.innerHTML = data.cart_total;
				}
                // Mise à jour du scroll après chaque modification
                updateScroll();
            });
        }

    // Initialisation du scroll
    updateScroll();
    // La requête AJAX n'est plus lancée en continu, mais uniquement lors d'une action utilisateur (ex: mise à jour panier)
    });

	document.addEventListener("DOMContentLoaded", function() {
		function deplacerPrixMobile() {
			if (window.innerWidth <= 530) {
				document.querySelectorAll(".panier-grid").forEach(function(row) {
					const info = row.querySelector(".panier-info");
					const prix = row.querySelector(".panier-prix");
					if (info && prix && !prix.classList.contains("moved")) {
						// Déplacer juste après le nom du produit
						const nom = row.querySelector(".panier-nom");
						if (nom) {
							nom.insertAdjacentElement("afterend", prix);
							prix.classList.add("moved"); // éviter de le bouger plusieurs fois
							prix.style.textAlign = "left"; // adapter l'affichage mobile
							prix.style.marginTop = "5px";
						}
					}
				});
			}
		}

		// Exécuter au chargement
		deplacerPrixMobile();

		// Réexécuter si on redimensionne
		window.addEventListener("resize", deplacerPrixMobile);
	});
        // Ajout de l'animation spinner
        (function(){
            const style = document.createElement('style');
            style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
            document.head.appendChild(style);
        })();
        </script>
    <?php
    return ob_get_clean();
}
add_shortcode('panier_produits', 'panier_produits_shortcode');

// AJAX
add_action('wp_ajax_maj_panier', 'maj_panier_ajax');
add_action('wp_ajax_nopriv_maj_panier', 'maj_panier_ajax');
function maj_panier_ajax() {
    $cart = WC()->cart;
    $key = sanitize_text_field($_POST['key']);
    $qty = intval($_POST['qty']);

    if ($qty > 0) {
        $cart->set_quantity($key, $qty, true);
    } else {
        $cart->remove_cart_item($key);
    }

    WC()->cart->calculate_totals();

    wp_send_json([
        "line_subtotal" => wc_price($cart->get_cart_item($key)['line_total'] ?? 0),
        "cart_subtotal" => WC()->cart->get_cart_subtotal(),
        "cart_total"    => WC()->cart->get_total(),
    ]);
}
