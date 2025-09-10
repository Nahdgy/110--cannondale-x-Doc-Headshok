/*
Plugin Name: Panier Produits Shortcode
Description: Shortcode [mini_panier_produits] pour afficher les produits du panier WooCommerce avec image, stock, quantité, suppression et prix dynamique.
Version: 1.7
*/

// Shortcode
function mini_panier_produits_shortcode() {
    $cart_items = WC()->cart ? WC()->cart->get_cart() : [];
    if (!$cart_items || empty($cart_items)) {
        return '<p>Votre panier est vide.</p>';
    }

    ob_start();
    ?>
    <style>
        .mini-panier-produits {
            overflow-y: hidden;
            padding-right: 10px;
            transition: max-height 0.3s;
        }
        .mini-panier-grid {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .mini-panier-info { display: flex; flex-direction: column; gap: 8px; }
        .mini-panier-nom {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700; font-size: 16px; color: #000000;
        }
        .mini-panier-stock {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 300; font-size: 16px; color: #000000;
        }
        .mini-panier-quantite-wrapper {
            display: flex; justify-content: flex-start; align-items: center; gap: 10px;
        }
        .mini-panier-quantite {
            border: 1px solid #656565; border-radius: 10px; padding: 5px 8px;
            display: inline-flex; align-items: center; gap: 5px; width: fit-content;
        }
        .mini-panier-quantite button {
            padding: 5px 10px; border: none; background: transparent; cursor: pointer;
            font-size: 16px; font-weight: bold; color: #151515;
        }
        .mini-panier-quantite input {
            width: 64px; text-align: center; border: none; background: transparent; font-size: 14px;
        }
        .mini-panier-supprimer img { cursor: pointer; }
        .mini-panier-image { text-align: center; }
        .mini-panier-image img { max-width: 80px; }
        .mini-panier-prix {
            font-family: 'din-next-lt-pro', sans-serif;
            font-weight: 700; font-size: 16px; color: #000000; text-align: right; white-space: nowrap;
        }
    </style>

    <div class="mini-panier-produits"></div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ajout de l'animation spinner
        const style = document.createElement('style');
        style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
        const panierContainer = document.querySelector(".mini-panier-produits");

        function updateScroll() {
            const rows = panierContainer.querySelectorAll(".mini-panier-grid").length;
            if (rows > 3) {
                panierContainer.style.maxHeight = "400px";
                panierContainer.style.overflowY = "auto";
            } else {
                panierContainer.style.maxHeight = "none";
                panierContainer.style.overflowY = "hidden";
            }
        }

        function bindEvents() {
            panierContainer.querySelectorAll(".mini-panier-grid").forEach(function(row) {
                let moinsBtn = row.querySelector(".mini-moins");
                let plusBtn = row.querySelector(".mini-plus");
                let input = row.querySelector("input");
                let prixDiv = row.querySelector(".mini-panier-prix");
                let supBtn = row.querySelector(".mini-supprimer-btn");

                moinsBtn.addEventListener("click", function() {
                    let q = parseInt(input.value);
                    if (q > 1) {
                        q--;
                        input.value = q;
                        majMiniPanier(row.dataset.key, q, prixDiv);
                    }
                });

                plusBtn.addEventListener("click", function() {
                    let q = parseInt(input.value);
                    q++;
                    input.value = q;
                    majMiniPanier(row.dataset.key, q, prixDiv);
                });

                supBtn.addEventListener("click", function() {
                    // Remplace l'icône poubelle par un spinner
                    supBtn.src = "https://cannonbale.com/wp-content/uploads/2025/07/spinner.svg";
                    supBtn.style.animation = "spin 1s linear infinite";
                    majMiniPanier(row.dataset.key, 0, prixDiv, row);
                });
    // Ajout de l'animation spinner
    const style = document.createElement('style');
    style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
    document.head.appendChild(style);
            });
        }

        function majMiniPanier(cartKey, qty, prixDiv, row = null) {
            fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "action=maj_panier&key=" + cartKey + "&qty=" + qty
            })
            .then(res => res.json())
            .then(data => {
                if (qty === 0 && row) {
                    row.remove();
                    if (document.querySelectorAll(".mini-panier-grid").length === 0) {
                        panierContainer.innerHTML = "<p>Votre panier est vide.</p>";
                    }
                } else if (data.line_subtotal && prixDiv) {
                    prixDiv.innerHTML = data.line_subtotal;
                }

                let resumeSousTotal = document.querySelector(".panier-resume-commande .resume-value");
                let resumeTotal = document.querySelector(".panier-resume-commande .resume-total-value");

                if (resumeSousTotal && data.cart_subtotal) resumeSousTotal.innerHTML = data.cart_subtotal;
                if (resumeTotal && data.cart_total) resumeTotal.innerHTML = data.cart_total;

                updateScroll();
            });
        }

        // 🔄 Fonction pour récupérer le panier et reconstruire le HTML
        function fetchMiniPanier() {
            fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=get_mini_panier")
            .then(res => res.json())
            .then(data => {
                if (!data.items || data.items.length === 0) {
                    panierContainer.innerHTML = "<p>Votre panier est vide.</p>";
                    return;
                }

                let html = "";
                data.items.forEach(item => {
                    html += `
                        <div class="mini-panier-grid" data-key="${item.key}">
                            <div class="mini-panier-image">
                                <img src="${item.image}" alt="${item.name}">
                            </div>
                            <div class="mini-panier-info">
                                <div class="mini-panier-nom">${item.name}</div>
                                <div class="mini-panier-stock">Stocks Disponibles: ${item.stock !== null ? item.stock : 'Rupture'}</div>
                                <div class="mini-panier-quantite-wrapper">
                                    <div class="mini-panier-quantite">
                                        <button class="mini-moins">-</button>
                                        <input type="number" min="1" value="${item.qty}" readonly>
                                        <button class="mini-plus">+</button>
                                    </div>
                                    <div class="mini-panier-supprimer">
                                        <img src="https://cannonbale.com/wp-content/uploads/2025/07/Vector.png" 
                                            alt="Supprimer" class="mini-supprimer-btn">
                                    </div>
                                </div>
                            </div>
                            <div class="mini-panier-prix">${item.subtotal}</div>
                        </div>
                    `;
                });

                panierContainer.innerHTML = html;
                bindEvents();
                updateScroll();
            });
        }

    // Premier chargement
    fetchMiniPanier();
    // La requête AJAX n'est plus lancée en continu, mais uniquement lors d'une action utilisateur (ex: mise à jour panier)
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
add_shortcode('mini_panier_produits', 'mini_panier_produits_shortcode');

// =====================
// ✅ AJAX GET Panier Complet
// =====================
add_action('wp_ajax_get_mini_panier', 'get_mini_panier_ajax');
add_action('wp_ajax_nopriv_get_mini_panier', 'get_mini_panier_ajax');
function get_mini_panier_ajax() {
    $cart = WC()->cart;
    $items = [];

    if ($cart && !empty($cart->get_cart())) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $items[] = [
                'key'       => $cart_item_key,
                'id'        => $product->get_id(),
                'name'      => $product->get_name(),
                'qty'       => $cart_item['quantity'],
                'price'     => wc_price($product->get_price()),
                'subtotal'  => wc_price($cart_item['line_total']),
				'image'     => $product->get_image_id() 
										? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') 
										: 'https://cannonbale.com/wp-content/uploads/2023/10/2186-Garde-boue-arriere-Tesoro-Neo-K11078-Cannondale.jpg',
                'permalink' => get_permalink($product->get_id()),
				'stock'     => $product->get_stock_quantity(), // 👈 ajout ici
            ];
        }
    }

    wp_send_json([
        "success" => true,
        "items"   => $items,
        "cart_subtotal" => $cart ? $cart->get_cart_subtotal() : 0,
        "cart_total"    => $cart ? $cart->get_total() : 0,
    ]);
}

// =====================
// ✅ AJAX MAJ Panier
// =====================
add_action('wp_ajax_maj_panier', 'maj_mini_panier_ajax');
add_action('wp_ajax_nopriv_maj_panier', 'maj_mini_panier_ajax');
function maj_mini_panier_ajax() {
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
