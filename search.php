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
    align-items: center;
    text-align: center;
    min-height: 300px;
    transition: transform 0.3s ease;
}
ul.liste-produits li:hover {
    transform: translateY(-4px);
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
    font-size: 18px;
    color: #000000;
    text-align: center;
    margin-top: auto;
}
</style>

<div class="search-results-container" style="max-width:1450px;margin:2rem auto;">
    <h2 style="font-family:'DIN Next LT Pro',sans-serif;font-weight:700;font-size:2rem;margin-bottom:1rem;">
        Résultats pour : "<?php echo esc_html($search); ?>"
    </h2>
    <?php if ($query->have_posts()) : ?>
        <ul class="liste-produits">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php else : ?>
                            <img src="<?php echo wc_placeholder_img_src('medium'); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="titre-produit"><?php the_title(); ?></div>
                    </a>
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