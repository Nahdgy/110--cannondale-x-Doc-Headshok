<?php
get_header();

// Récupération de la requête de recherche
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$args = [
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    's' => $search,
];
$query = new WP_Query($args);
?>

<style>
ul.liste-produits {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media screen and (max-width: 1023px) {
    ul.liste-produits {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media screen and (max-width: 766px) {
    ul.liste-produits {
        grid-template-columns: repeat(1, 1fr);
    }
}
ul.liste-produits li {
    background-color: #F7F7F7;
    border-radius: 6px;
    padding: 15px;
    box-sizing: border-box;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    min-height: 350px;
    transition: transform 0.3s ease;
}
ul.liste-produits li:hover {
    transform: translateY(-4px);
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
ul.liste-produits li a {
    color: inherit;
    text-decoration: none;
    display: block;
    width: 100%;
}
ul.liste-produits li img {
    max-width: 100%;
    height: auto;
    margin-bottom: 15px;
    object-fit: contain;
    max-height: 180px;
}
ul.liste-produits li .titre-produit {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: #000000;
    text-align: left;
    margin-top: auto;
    margin-bottom: 10px;
}
ul.liste-produits li .info-produit {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    height: 100%;
    flex-grow: 1;
    width: 100%;
}
ul.liste-produits li .prix-produit {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-weight: 600;
    font-size: 16px;
    color: #444;
    margin-bottom: 12px;
    width: 100%;
}
ul.liste-produits li .bouton-ajouter-panier {
    background-color: #000000 !important;
    color: #ffffff !important;
    width: 100%;
    border-radius: 6px;
    padding: 10px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    margin-top: 10px;
    transition: background-color 0.3s ease;
    font-family: 'DIN Next LT Pro', sans-serif;
    border: none;
    cursor: pointer;
}
ul.liste-produits li .bouton-ajouter-panier:hover {
    background-color: #FF3F22 !important;
    color: #ffffff !important;
}
</style>

<div class="search-results-container" style="max-width:1450px;margin:2rem auto;">
    <h2 style="font-family:'DIN Next LT Pro',sans-serif;font-weight:700;font-size:2rem;margin-bottom:1rem;">
        Résultats pour : "<?php echo esc_html($search); ?>"
    </h2>
    <?php if ($query->have_posts()) : ?>
        <ul class="liste-produits">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $product = wc_get_product(get_the_ID());
                $product_id = $product->get_id();
                $product_sku = $product->get_sku();
            ?>
                <li>
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php else : ?>
                            <img src="<?php echo wc_placeholder_img_src('medium'); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </a>
                    <div class="info-produit">
                        <div class="titre-produit"><?php the_title(); ?></div>
                        <div class="prix-produit"><?php echo $product->get_price_html(); ?></div>
                        <a href="<?php echo esc_url('?add-to-cart=' . $product_id); ?>" 
                           data-quantity="1" 
                           class="bouton-ajouter-panier add_to_cart_button ajax_add_to_cart" 
                           data-product_id="<?php echo esc_attr($product_id); ?>" 
                           data-product_sku="<?php echo esc_attr($product_sku); ?>" 
                           aria-label="Ajouter '<?php echo esc_attr(get_the_title()); ?>' au panier" 
                           rel="nofollow">Ajouter au panier</a>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>Aucun produit trouvé pour votre recherche.</p>
    <?php endif; ?>
</div>

<?php
wp_reset_postdata();
get_footer();
?>