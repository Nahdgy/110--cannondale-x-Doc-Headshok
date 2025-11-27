/**
 * Shortcode : [carousel_bestseller]
 * Carrousel horizontal de produits recommandés
 */
function shortcode_carousel_bestseller() {
    ob_start(); ?>

    <style>
        .carousel-wrapper {
            position: relative;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: hidden;
        }
        .carousel-container {
            position: relative;
        }
        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: #F7F7F7;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #151515;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            z-index: 10;
        }
		.carousel-btn:hover{
			background-color: #FF3F22;
		}
        .carousel-btn.prev { left: 1px; }
        .carousel-btn.next { right: 1px; }

        .carousel-track {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 10px 40px;
        }

        .carousel-item {
            background-color: #F7F7F7;
            border: 1px solid #eee;
            padding: 10px;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            height: 360px;
            box-sizing: border-box;
        }
        .carousel-item img {
            height: 180px;
            object-fit: contain;
        }
        .carousel-item-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }
        .carousel-item-title {
            font-size: 16px;
            color: #000000 !important;
            margin: 0;
        }
        .carousel-item-price {
            font-weight: bold;
            display: flex;
        }
        .carousel-item-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }
        .add-to-cart-ajax {
            background: #000;
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
		.add-to-cart-ajax:hover{
			background: #FF3F22;
		}
        @media screen and (max-width: 768px) {
            .carousel-item {
                height: 300px;
            }
            .carousel-item img {
                height: 140px;
            }
            .add-to-cart-ajax {
                font-size: 12px;
            }
        }
    </style>

    <div class="carousel-wrapper">
        <div class="carousel-container">
            <!-- Bouton gauche -->
            <button id="prevBtn" class="carousel-btn prev">‹</button>

            <!-- Carrousel -->
            <div id="carousel" class="carousel-track">
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 10,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_visibility',
                            'field'    => 'name',
                            'terms'    => array('featured'), // produits "mis en avant" (étoile)
                            'operator' => 'IN',
                        ),
                    ),
                );
                $produits = new WP_Query($args);
                if ($produits->have_posts()) :
                    while ($produits->have_posts()) : $produits->the_post(); global $product;
                        $img_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        if (!$img_url) {
                            $img_url = "https://doc-headshok.com/wp-content/uploads/2023/10/2186-Garde-boue-arriere-Tesoro-Neo-K11078-Cannondale.jpg";
                        }
                ?>
                    <div class="carousel-item">
                        <a href="<?php the_permalink(); ?>">
                            <div style="width: 100%; display: flex; justify-content: center;">
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title(); ?>">
                            </div>
                        </a>

                        <div class="carousel-item-content">
                            <h3 class="carousel-item-title"><?php the_title(); ?></h3>
                            <span class="carousel-item-price"><?php echo $product->get_price_html(); ?></span>
                        </div>

                        <div class="carousel-item-footer">
                            <button class="add-to-cart-ajax" data-product-id="<?php echo $product->get_id(); ?>">Ajouter au panier</button>
                        </div>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>

            <!-- Bouton droite -->
            <button id="nextBtn" class="carousel-btn next">›</button>
        </div>
    </div>

    <script>
    (function(){
        const carousel = document.getElementById('carousel');
        const scrollAmount = 220;

        document.getElementById('prevBtn')?.addEventListener('click', () => {
            carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        document.getElementById('nextBtn')?.addEventListener('click', () => {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        // ✅ Ajouter au panier en AJAX sans redirection
        document.querySelectorAll(".add-to-cart-ajax").forEach(btn => {
            btn.addEventListener("click", function() {
                const productId = this.dataset.productId;

                fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: "action=add_to_cart_ajax&product_id=" + productId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("✅ Produit ajouté au panier !");
						location.reload();
                    } else {
                        alert("❌ Erreur : " + data.message);
                    }
                });
            });
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('carousel_bestseller', 'shortcode_carousel_bestseller');


// ✅ AJAX Add to Cart
add_action('wp_ajax_add_to_cart_ajax', 'add_to_cart_ajax_callback');
add_action('wp_ajax_nopriv_add_to_cart_ajax', 'add_to_cart_ajax_callback');
function add_to_cart_ajax_callback() {
    $product_id = intval($_POST['product_id']);
    $quantity = 1;

    if (WC()->cart) {
        WC()->cart->add_to_cart($product_id, $quantity);
        WC()->cart->calculate_totals();
        wp_send_json(["success" => true, "cart_total" => WC()->cart->get_total()]);
    } else {
        wp_send_json(["success" => false, "message" => "Panier introuvable"]);
    }
}
