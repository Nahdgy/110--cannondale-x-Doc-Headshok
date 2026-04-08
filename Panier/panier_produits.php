
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
        .panier-table-head {
            display: grid;
            grid-template-columns: minmax(280px, 2.2fr) 170px 1.2fr 140px 42px;
            gap: 20px;
            align-items: center;
            min-width: 812px;
            padding: 0 15px 12px 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 8px;
            box-sizing: border-box;
        }
        .panier-table-head > div {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 500;
            font-size: 18px;
            color: #4a4a4a;
        }
        .panier-table-head > div:nth-child(1) {
            min-width: 280px;
        }
        .panier-table-head > div:nth-child(2) {
            min-width: 170px;
        }
        .panier-table-head > div:nth-child(3) {
            min-width: 180px;
        }
        .panier-table-head > div:nth-child(4) {
            min-width: 140px;
        }
        .panier-table-head > div:nth-child(5) {
            min-width: 42px;
        }
        .panier-produits {
            overflow-x: auto;
            overflow-y: hidden; /* scroll caché par défaut */
            padding-right: 10px;
            transition: max-height 0.3s;
        }
        .panier-grid {
            display: grid;
            grid-template-columns: minmax(280px, 2.2fr) 170px 1.2fr 140px 42px;
            gap: 20px;
            align-items: center;
            min-width: 812px;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            box-sizing: border-box;
        }
        .panier-col-produit {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 280px;
        }
        .panier-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }
        .panier-nom {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #000000;
        }
        .panier-description {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 400;
            font-size: 15px;
            color: #000000;
        }
        .panier-stock {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000000;
        }
        .panier-col-quantite {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            min-width: 170px;
        }
        .panier-quantite-wrapper {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 0;
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
        .panier-col-caracteristiques {
            font-family: 'din-next-lt-pro', sans-serif;
            font-size: 16px;
            color: #000000;
            min-width: 180px;
        }
        .panier-caracteristique-ligne {
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .panier-caracteristique-ligne:last-child {
            margin-bottom: 0;
        }
        .panier-caracteristique-ligne strong {
            font-weight: 700;
        }
        .panier-col-action {
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 42px;
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
            min-width: 140px;
            text-align: right;
            white-space: nowrap;
        }

      @media screen and (max-width: 860px) {
          .panier-table-head {
              display: none;
          }
          .panier-grid {
              grid-template-columns: 1fr auto;
              grid-template-areas:
                  "produit produit"
                  "caracteristiques caracteristiques"
                  "quantite prix"
                  "action action";
              gap: 12px;
              min-width: 0;
              padding: 14px 10px;
          }
          .panier-col-produit {
              grid-area: produit;
              min-width: 0;
          }
          .panier-col-quantite {
              grid-area: quantite;
              min-width: 0;
          }
          .panier-col-caracteristiques {
              grid-area: caracteristiques;
              font-size: 14px;
              min-width: 0;
          }
          .panier-prix {
              grid-area: prix;
              font-size: 15px;
              min-width: 0;
              text-align: right;
          }
          .panier-col-action {
              grid-area: action;
              justify-content: flex-end;
              min-width: 0;
          }
          .panier-image img {
              max-width: 70px;
          }
          .panier-nom {
              font-size: 14px;
          }
          .panier-description,
          .panier-stock {
              font-size: 14px;
          }
          .panier-quantite {
              padding: 4px 6px;
          }
          .panier-quantite button {
              padding: 3px 8px;
              font-size: 14px;
          }
          .panier-quantite input {
              width: 50px;
              font-size: 13px;
          }
          .panier-supprimer img {
              width: 24px;
              height: 24px;
              min-width: 24px;
              min-height: 24px;
          }
      }
    </style>

    <div class="panier-produits">
        <div class="panier-table-head">
        <div>Produit</div>
        <div>Quantité</div>
        <div>Caractéristique</div>
        <div class="panier-head-prix">Prix total</div>
        <div></div>
    </div>
        <?php foreach ($cart_items as $cart_item_key => $cart_item) :
            $product = $cart_item['data'];
            $qty = $cart_item['quantity'];
            $stock = $product->get_stock_quantity();
            $subtotal = $cart_item['line_total'] + $cart_item['line_tax'];
            $short_description = wp_strip_all_tags($product->get_short_description());
            $variation_data = [];

            if (!empty($cart_item['variation']) && is_array($cart_item['variation'])) {
                foreach ($cart_item['variation'] as $attribute_name => $attribute_value) {
                    if ($attribute_value === '') {
                        continue;
                    }

                    $taxonomy = str_replace('attribute_', '', $attribute_name);
                    $label = wc_attribute_label($taxonomy);
                    $display_value = $attribute_value;

                    if (taxonomy_exists($taxonomy)) {
                        $term = get_term_by('slug', $attribute_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $display_value = $term->name;
                        }
                    }

                    $variation_data[] = [
                        'label' => $label,
                        'value' => $display_value,
                    ];
                }
            }
        ?>
            <div class="panier-grid" data-key="<?php echo esc_attr($cart_item_key); ?>">
                
                <div class="panier-col-produit">
                    <div class="panier-image">
                        <?php 
						$image_url = $product->get_image_id() 
							? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') 
							: 'https://cannonbale.com/wp-content/uploads/2023/10/2186-Garde-boue-arriere-Tesoro-Neo-K11078-Cannondale.jpg';
						?>
						<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>">
                    </div>

                    <div class="panier-info">
                        <div class="panier-nom"><?php echo wp_kses_post($product->get_name()); ?></div>
                        <?php if (!empty($short_description)) : ?>
                            <div class="panier-description"><?php echo esc_html($short_description); ?></div>
                        <?php endif; ?>
                        <div class="panier-stock">Stocks disponibles : <?php echo $stock ? esc_html($stock) : 'Rupture'; ?></div>
                    </div>
                </div>

                <div class="panier-col-quantite">
                    <div class="panier-quantite-wrapper">
                        <div class="panier-quantite">
                            <button class="moins">-</button>
                            <input type="number" min="1" value="<?php echo esc_attr($qty); ?>" readonly>
                            <button class="plus">+</button>
                        </div>
                    </div>
                </div>

                <div class="panier-col-caracteristiques">
                    <?php if (!empty($variation_data)) : ?>
                        <?php foreach ($variation_data as $variation_item) : ?>
                            <div class="panier-caracteristique-ligne">
                                <strong><?php echo esc_html($variation_item['label']); ?> :</strong> <?php echo esc_html($variation_item['value']); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="panier-caracteristique-ligne">-</div>
                    <?php endif; ?>
                </div>

                <div class="panier-prix"><?php echo wc_price($subtotal); ?></div>

                <div class="panier-col-action panier-supprimer">
                    <img src="https://doc-headshok.com/wp-content/uploads/2025/07/Vector.png" 
                         alt="Supprimer" class="supprimer-btn">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    (function() {
        // Empêcher l'exécution multiple si déjà initialisé
        if (window.panierProduitsInitialized) return;
        window.panierProduitsInitialized = true;

        document.addEventListener("DOMContentLoaded", function() {
            // Ajout de l'animation spinner
            if (!document.getElementById('panier-spinner-style')) {
                const style = document.createElement('style');
                style.id = 'panier-spinner-style';
                style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
                document.head.appendChild(style);
            }
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
            // Éviter d'attacher les événements plusieurs fois
            if (row.dataset.eventsAttached === 'true') return;
            row.dataset.eventsAttached = 'true';
            
            let moinsBtn = row.querySelector(".moins");
            let plusBtn = row.querySelector(".plus");
            let input = row.querySelector("input");
            let prixDiv = row.querySelector(".panier-prix");
            let supBtn = row.querySelector(".supprimer-btn");
            let isUpdating = false; // Flag pour éviter les clics multiples

            moinsBtn.addEventListener("click", function() {
                if (isUpdating) return; // Ignorer si une mise à jour est en cours
                
                let q = parseInt(input.value);
                if (q > 1) {
                    isUpdating = true;
                    moinsBtn.disabled = true;
                    plusBtn.disabled = true;
                    moinsBtn.style.opacity = "0.5";
                    plusBtn.style.opacity = "0.5";
                    q--;
                    input.value = q;
                    
                    majPanier(row.dataset.key, q, prixDiv, null, function() {
                        isUpdating = false;
                        moinsBtn.disabled = false;
                        plusBtn.disabled = false;
                        moinsBtn.style.opacity = "1";
                        plusBtn.style.opacity = "1";
                    });
                }
            });

            plusBtn.addEventListener("click", function() {
                if (isUpdating) return; // Ignorer si une mise à jour est en cours
                
                isUpdating = true;
                moinsBtn.disabled = true;
                plusBtn.disabled = true;
                moinsBtn.style.opacity = "0.5";
                plusBtn.style.opacity = "0.5";
                let q = parseInt(input.value);
                q++;
                input.value = q;
                
                majPanier(row.dataset.key, q, prixDiv, null, function() {
                    isUpdating = false;
                    moinsBtn.disabled = false;
                    plusBtn.disabled = false;
                    moinsBtn.style.opacity = "1";
                    plusBtn.style.opacity = "1";
                });
            });

            supBtn.addEventListener("click", function() {
                if (isUpdating) return; // Ignorer si une mise à jour est en cours
                
                isUpdating = true;
                // Remplace l'icône poubelle par un spinner
                supBtn.src = "https://doc-headshok.com/wp-content/uploads/2025/07/spinner.svg";
                supBtn.style.animation = "spin 1s linear infinite";
                majPanier(row.dataset.key, 0, prixDiv, row);
            });
            });

            function majPanier(cartKey, qty, prixDiv, row = null, callback = null) {
            fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=maj_panier_full&key=" + cartKey + "&qty=" + qty
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
				
				// Déclencher l'événement WooCommerce pour synchroniser la barre de livraison
				jQuery(document.body).trigger('wc_fragment_refresh');
				
                // Mise à jour du scroll après chaque modification
                updateScroll();
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour du panier:', error);
            })
            .finally(() => {
                // Toujours réactiver les boutons après l'AJAX (succès ou erreur)
                if (callback) callback();
            });
        }

            // Initialisation du scroll
            updateScroll();
            // La requête AJAX n'est plus lancée en continu, mais uniquement lors d'une action utilisateur (ex: mise à jour panier)
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('panier_produits', 'panier_produits_shortcode');

// =====================
// ✅ AJAX MAJ Panier Full
// =====================
add_action('wp_ajax_maj_panier_full', 'maj_panier_ajax');
add_action('wp_ajax_nopriv_maj_panier_full', 'maj_panier_ajax');
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

    $cart_item = $cart->get_cart_item($key);
    $line_total_with_tax = 0;
    if ($cart_item) {
        $line_total_with_tax = $cart_item['line_total'] + $cart_item['line_tax'];
    }

    wp_send_json([
        "line_subtotal" => wc_price($line_total_with_tax),
        "cart_subtotal" => WC()->cart->get_cart_subtotal(),
        "cart_total"    => WC()->cart->get_total(),
    ]);
}
