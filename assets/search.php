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
$results_count = (int) $query->found_posts;
?>

<style>
.search-page {
    --search-bg: linear-gradient(180deg, #f4f1eb 0%, #ffffff 28%);
    --search-card: #f7f7f7;
    --search-card-hover: #ffffff;
    --search-text: #111111;
    --search-muted: #6d6d6d;
    --search-accent: #ff3f22;
    --search-border: rgba(17, 17, 17, 0.08);
    --search-shadow: 0 18px 45px rgba(0, 0, 0, 0.08);
    background: var(--search-bg);
    padding: 30vh 15px 80px;
}
.search-results-shell {
    max-width: 1450px;
    margin: 0 auto;
}
.search-results-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(280px, 0.7fr);
    gap: 28px;
    align-items: stretch;
    margin-bottom: 34px;
}
.search-results-summary,
.search-results-meta {
    background: rgba(255, 255, 255, 0.78);
    backdrop-filter: blur(10px);
    border: 1px solid var(--search-border);
    border-radius: 22px;
    box-shadow: var(--search-shadow);
}
.search-results-summary {
    padding: 34px;
}
.search-results-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--search-accent);
    margin-bottom: 14px;
}
.search-results-summary h1 {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: clamp(2rem, 2vw, 3.6rem);
    line-height: 1;
    letter-spacing: -0.04em;
    color: var(--search-text);
    margin: 0 0 16px;
}
.search-results-summary p {
    margin: 0;
    max-width: 62ch;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--search-muted);
}
.search-results-meta {
    padding: 28px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 18px;
}
.search-meta-block span {
    display: block;
    font-family: 'DIN Next LT Pro', sans-serif;
}
.search-meta-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--search-muted);
    margin-bottom: 8px;
}
.search-meta-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--search-text);
}
.search-meta-pill {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 10px 14px;
    border-radius: 999px;
    background: #111111;
    color: #ffffff;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 13px;
    font-weight: 700;
}
.search-results-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}
.search-results-count {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--search-text);
}
.search-results-hint {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 14px;
    color: var(--search-muted);
}
ul.liste-produits {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
    width: 100%;
    box-sizing: border-box;
}
ul.liste-produits li {
    background: var(--search-card);
    border: 1px solid var(--search-border);
    border-radius: 22px;
    padding: 18px;
    box-sizing: border-box;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    min-height: 350px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
}
ul.liste-produits li:hover {
    transform: translateY(-6px);
    box-shadow: var(--search-shadow);
    background: var(--search-card-hover);
}
ul.liste-produits li a:first-child {
    color: inherit;
    text-decoration: none;
    display: block;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 220px;
    margin-bottom: 6px;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f1f1f1 100%);
}
ul.liste-produits li img {
    max-width: 100%;
    height: auto;
    object-fit: contain;
    max-height: 180px;
}
ul.liste-produits li .titre-produit {
    font-family: 'DIN Next LT Pro', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: var(--search-text);
    text-align: left;
    margin-top: 0;
    margin-bottom: 12px;
    line-height: 1.25;
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
    font-size: 18px;
    color: #303030;
    margin-bottom: 16px;
    width: 100%;
}
ul.liste-produits li .bouton-ajouter-panier {
    background-color: #111111 !important;
    color: #ffffff !important;
    width: 100%;
    border-radius: 12px;
    padding: 13px 14px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    margin-top: 10px;
    transition: background-color 0.25s ease, transform 0.25s ease;
    font-family: 'DIN Next LT Pro', sans-serif;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
ul.liste-produits li .bouton-ajouter-panier:hover {
    background-color: var(--search-accent) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}
.search-empty-state {
    padding: 44px 28px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid var(--search-border);
    box-shadow: var(--search-shadow);
    text-align: center;
}
.search-empty-state h2 {
    margin: 0 0 12px;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 32px;
    color: var(--search-text);
}
.search-empty-state p {
    margin: 0 auto 24px;
    max-width: 56ch;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: var(--search-muted);
}
.search-empty-state a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 22px;
    border-radius: 999px;
    background: #111111;
    color: #ffffff;
    font-family: 'DIN Next LT Pro', sans-serif;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}
.search-empty-state a:hover {
    background: var(--search-accent);
}
@media screen and (max-width: 1023px) {
    .search-page {
        padding-top: 150px;
    }
    .search-results-hero {
        grid-template-columns: 1fr;
    }
    ul.liste-produits {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media screen and (max-width: 766px) {
    .search-page {
        padding: 130px 10px 56px;
    }
    .search-results-summary,
    .search-results-meta {
        padding: 22px;
        border-radius: 18px;
    }
    .search-results-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }
    ul.liste-produits {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    ul.liste-produits li {
        min-height: 300px;
        padding: 14px;
        border-radius: 18px;
    }
    ul.liste-produits li a:first-child {
        min-height: 170px;
    }
    ul.liste-produits li img {
        max-height: 145px;
    }
    ul.liste-produits li .titre-produit,
    ul.liste-produits li .prix-produit {
        font-size: 15px;
    }
    .search-empty-state h2 {
        font-size: 26px;
    }
}
</style>

<div class="search-page">
    <div class="search-results-shell">
        <section class="search-results-hero">
            <div class="search-results-summary">
                <span class="search-results-eyebrow">Recherche produits</span>
                <h1><?php echo $results_count > 0 ? 'Vos résultats' : 'Aucun résultat'; ?></h1>
                <p>
                    <?php if ( $search ) : ?>
                        <?php echo $results_count > 0
                            ? 'Voici les produits correspondant a votre recherche pour : "' . esc_html( $search ) . '".'
                            : 'Nous n\'avons trouve aucun produit pour : "' . esc_html( $search ) . '". Essayez un terme plus court, une reference ou un nom de marque.'; ?>
                    <?php else : ?>
                        Saisissez un terme, une marque ou une reference pour afficher les produits correspondants.
                    <?php endif; ?>
                </p>
            </div>
            <aside class="search-results-meta">
                <div class="search-meta-block">
                    <span class="search-meta-label">Recherche</span>
                    <span class="search-meta-value"><?php echo $search ? esc_html( $search ) : 'Aucune saisie'; ?></span>
                </div>
                <div class="search-meta-block">
                    <span class="search-meta-label">Produits trouves</span>
                    <span class="search-meta-pill"><?php echo esc_html( $results_count ); ?> resultat<?php echo $results_count > 1 ? 's' : ''; ?></span>
                </div>
            </aside>
        </section>

        <?php if ($query->have_posts()) : ?>
            <div class="search-results-toolbar">
                <div class="search-results-count"><?php echo esc_html( $results_count ); ?> produit<?php echo $results_count > 1 ? 's' : ''; ?> affiche<?php echo $results_count > 1 ? 's' : ''; ?></div>
                <div class="search-results-hint">Selection produits WooCommerce correspondant a votre recherche.</div>
            </div>

            <ul class="liste-produits">
                <?php while ($query->have_posts()) : $query->the_post();
                    $product = wc_get_product(get_the_ID());

                    if ( ! $product ) {
                        continue;
                    }

                    $product_id = $product->get_id();
                    $product_sku = $product->get_sku();
                ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( wc_placeholder_img_src('medium') ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                            <?php endif; ?>
                        </a>
                        <div class="info-produit">
                            <div class="titre-produit"><?php the_title(); ?></div>
                            <div class="prix-produit"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
                            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
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
            <div class="search-empty-state">
                <h2>Pas de produit trouve</h2>
                <p>Vous pouvez essayer avec une autre orthographe, une reference produit, ou revenir a la boutique pour parcourir les categories disponibles.</p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Retour a la boutique</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
wp_reset_postdata();
get_footer();
?>