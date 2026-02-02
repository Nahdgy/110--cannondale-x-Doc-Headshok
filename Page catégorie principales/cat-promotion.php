add_shortcode('produits_promotion', 'afficher_produits_promotion');

function afficher_produits_promotion() {
  ob_start();

  // Essayer d'abord 'promotions' puis 'promotion' comme fallback
  $parent = get_term_by('slug', 'promotions', 'product_cat');
  if (!$parent) {
    $parent = get_term_by('slug', 'promotion', 'product_cat');
  }
  
  $category_slug = $parent ? $parent->slug : 'promotions';
  
  // Récupération des filtres depuis l'URL (GET)
  $filtres = isset($_GET['filtre']) ? (array)$_GET['filtre'] : [];

  // Construction de la requête pour les produits
  $args = [
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'tax_query' => [
      'relation' => 'AND',
      [
        'taxonomy' => 'product_cat',
        'field'    => 'slug',
        'terms'    => $category_slug,
      ],
    ],
  ];

  // Appliquer les filtres si présents
  if (!empty($filtres)) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_cat',
      'field'    => 'slug',
      'terms'    => $filtres,
      'operator' => 'IN',
    ];
  }
  
  // Debug: afficher les arguments de la requête (à retirer après test)
  // error_log('Args WP_Query promotions: ' . print_r($args, true));

  $produits = new WP_Query($args);

  $html_filtres = afficher_filtres_promotion($filtres);

  if ($produits->have_posts()) {
    ?>
    <style>
      ul.liste-produits-promo {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
      }

      ul.liste-produits-promo li {
        background-color: #F7F7F7;
        border-radius: 6px;
        padding: 15px;
        box-sizing: border-box;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 400px;
        transition: transform 0.3s ease;
      }

      ul.liste-produits-promo li:hover {
        transform: scale(1.03);
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      }

      ul.liste-produits-promo li a {
        text-decoration: none;
        color: inherit;
        display: block;
        width: 100%;
      }

      ul.liste-produits-promo li img {
        margin-bottom: 18px;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        max-width: 100%;
        height: auto;
        object-fit: contain;
      }

      .info-produit {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
        height: 100%;
        flex-grow: 1;
      }

      .texte-produit {
        width: 100%;
      }

      ul.liste-produits-promo li .titre-produit {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        margin-top: 10px;
        color: #000 !important;
        text-align: left;
      }

      ul.liste-produits-promo li .prix-produit {
        font-size: 14px;
        color: #FF3F22 !important;
        margin: 0;
        text-align: left;
      }

      ul.liste-produits-promo li:hover .titre-produit {
        color: #000 !important;
      }

      .bouton-ajouter-panier {
        align-self: flex-end;
        background-color: #000000 !important;
        color: #ffffff !important;
        width: 100%;
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        margin-top: 5px;
        transition: background-color 0.3s ease;
        font-family: 'din-next-lt-pro', sans-serif;
      }

      .bouton-ajouter-panier:hover {
        background-color: #000000 !important;
        color: #ffffff !important;
      }

      .barre-tri {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
      }
      #nombre-produits {
        font-family: 'din-next-lt-pro', sans-serif;
        font-weight: 300;
        font-size: 24px;
        color: black;
        margin-right: 2rem;
      }

      .toggle-titre {
        cursor: pointer;
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 16px;
        color: #222;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      #form-filtres {
        height: 600px;
        overflow-y: scroll;
        width: 100%;
        scrollbar-color: black #F8F8F8;
      }

      .filtre-groupe {
        margin-right: 1rem;
        min-width: 200px;
      }
		
      .tri-container {
        display: flex;
        align-items: center;
        gap: 10px;
      }
		
      .tri-container label {
        font-family: 'DIN Next LT Pro', sans-serif;
        font-weight: 300;
        font-size: 16px;
        color: #000;
        margin-bottom: 0;
      }
		
      #tri-produits-promo {
        width: 160px;
        padding: 6px 8px;
        font-family: 'DIN Next LT Pro', sans-serif;
        font-size: 14px;
      }
		
      .separator-red {
        border: none;
        border-top: 2px solid #FF3F22;
        margin: 18px 0;
      }

      #filtres-appliques{
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 10px;
      }

      #voir-plus {
        background-color: #000;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 14px;
        border-radius: 4px;
        cursor: pointer;
        margin: 20px auto 0;
        display: block;
      }
      
      /* Dropdown filters styles */
      .btn-toggle-filtres {
        background: #FF3F22;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-family: 'din-next-lt-pro', sans-serif;
      }
      
      .btn-toggle-filtres:hover {
        background: #e6381e;
        transform: translateY(-1px);
      }
      
      .section-filtres-collapsed {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.4s ease;
        background: #f9f9f9;
        border-radius: 8px;
        margin-bottom: 20px;
      }
      
      .section-filtres-expanded {
        max-height: 800px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: 1px solid #eee;
      }
      
      #filtres-colonne {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      
      /* Responsive Design */
      @media screen and (max-width: 1129px) {
        .barre-tri {
          flex-direction: column;
          align-items: stretch;
          gap: 15px;
        }
        
        .barre-tri > div:first-child {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
        }
        
        .tri-container {
          justify-content: center;
          flex-wrap: wrap;
        }
        
        #zone-filtres {
          width: 100% !important;
          min-width: auto !important;
          margin-bottom: 20px;
        }
        
        #form-filtres {
          display: flex !important;
          flex-direction: column;
          flex-wrap: wrap;
          gap: 15px;
          overflow-x: auto;
          padding: 15px;
          background: #fff;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .filtre-groupe {
          min-width: 200px;
          flex-shrink: 0;
          margin-bottom: 0;
          background: #fff;
          border-radius: 6px;
          padding: 12px;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .toggle-titre {
          font-size: 14px;
          margin-bottom: 8px;
          color: #FF3F22;
          font-weight: 600;
        }
        
        .filtre-options {
          max-height: 150px;
          overflow-y: auto;
        }
        
        .separator-red {
          display: none;
        }

        ul.liste-produits-promo {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      
      @media screen and (max-width: 768px) {
        #form-filtres {
          flex-direction: column !important;
          overflow-x: visible;
        }
        
        .filtre-groupe {
          min-width: auto !important;
          width: 100%;
        }
        
        .btn-toggle-filtres {
          width: 100%;
          justify-content: center;
        }
        
        .tri-container label {
          text-align: center;
        }
        
        .section-filtres-expanded {
          max-height: 500px;
          overflow-y: auto;
        }

        .barre-tri {
          flex-direction: column;
          gap: 10px;
        }

        #tri-produits-promo {
          width: 100px;
        }

        ul.liste-produits-promo {
          grid-template-columns: repeat(2, 1fr);
          gap: 10px;
        }
        ul.liste-produits-promo li {
          min-height: 0px;
        }
        ul.liste-produits-promo li .titre-produit{
          font-size: 14px;
        }
        
        .bouton-ajouter-panier {
          padding: 8px 12px;
          font-size: 14px;
        }
      }
    </style>

    <div id="produits-filtrables-container" style="display:flex; flex-direction: column; gap:20px;">
      <div class="barre-tri">
        <div>
          <div id="nombre-produits"><?php echo $produits->found_posts; ?> résultats</div>
          <button id="toggle-filtres" class="btn-toggle-filtres">
            <span id="filtres-icon">🔽</span> Filtres
          </button>
        </div>
        <div class="tri-container">
          <label for="tri-produits-promo">Trier par :</label>
          <select id="tri-produits-promo">
            <option value="default">Tri par défaut</option>
            <option value="alpha">Ordre alphabétique A-Z</option>
            <option value="alpha-desc">Ordre alphabétique Z-A</option>
            <option value="prix-asc">Prix croissant</option>
            <option value="prix-desc">Prix décroissant</option>
          </select>
        </div>
      </div>
      <hr class="separator-red">

      <!-- Section filtres déroulante -->
      <div id="section-filtres" class="section-filtres-collapsed">
        <div id="filtres-colonne" style="width:100%;">
          <?php echo $html_filtres; ?>
        </div>
      </div>
      
      <div class="contenu-principal">
        <ul class="liste-produits-promo" id="liste-produits-promo">
            <?php while ($produits->have_posts()) : $produits->the_post();
              global $product;
              $thumbnail = get_the_post_thumbnail($product->get_id(), 'medium');
              if (!$thumbnail) {
                $thumbnail = '<img src="' . wc_placeholder_img_src('medium') . '" alt="Image par défaut" />';
              }
              ?>
              <li data-name="<?php echo esc_attr(get_the_title()); ?>" data-price="<?php echo esc_attr($product->get_price()); ?>">
                <a href="<?php echo get_permalink(); ?>">
                  <?php echo $thumbnail; ?>
                </a>
                <div class="info-produit">
                  <div class="texte-produit">
                    <h3 class="titre-produit"><?php echo get_the_title(); ?></h3>
                    <p class="prix-produit"><?php echo $product->get_price_html(); ?></p>
                  </div>
                  <a href="<?php echo esc_url('?add-to-cart=' . $product->get_id()); ?>" 
                     data-quantity="1" 
                     class="bouton-ajouter-panier add_to_cart_button ajax_add_to_cart" 
                     data-product_id="<?php echo esc_attr($product->get_id()); ?>" 
                     data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" 
                     aria-label="Ajouter '<?php echo esc_attr(get_the_title()); ?>' au panier" 
                     rel="nofollow">Ajouter au panier</a>
                </div>
              </li>
            <?php endwhile; ?>
          </ul>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const triSelect = document.getElementById('tri-produits-promo');
      const liste = document.getElementById('liste-produits-promo');

      triSelect.addEventListener('change', function () {
        const items = Array.from(liste.querySelectorAll('li'));

        if (this.value === 'alpha') {
          items.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
        } else if (this.value === 'alpha-desc') {
          items.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
        } else if (this.value === 'prix-asc') {
          items.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (this.value === 'prix-desc') {
          items.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        } else {
          return; // Tri par défaut : pas de tri
        }

        items.forEach(item => liste.appendChild(item));
      });

      // Dropdown filters functionality
      const toggleBtn = document.getElementById('toggle-filtres');
      const filtresSection = document.getElementById('section-filtres');
      const filtresIcon = document.getElementById('filtres-icon');
      
      if (toggleBtn && filtresSection) {
        toggleBtn.addEventListener('click', function() {
          const isCollapsed = filtresSection.classList.contains('section-filtres-collapsed');
          
          if (isCollapsed) {
            filtresSection.classList.remove('section-filtres-collapsed');
            filtresSection.classList.add('section-filtres-expanded');
            filtresIcon.textContent = '🔼';
            toggleBtn.innerHTML = '<span id="filtres-icon">🔼</span> Masquer les filtres';
          } else {
            filtresSection.classList.remove('section-filtres-expanded');
            filtresSection.classList.add('section-filtres-collapsed');
            filtresIcon.textContent = '🔽';
            toggleBtn.innerHTML = '<span id="filtres-icon">🔽</span> Filtres';
          }
        });
      }

      // Gestion des filtres (affichage/repli)
      document.querySelectorAll(".toggle-titre").forEach((title) => {
        title.addEventListener("click", () => {
          const section = document.getElementById(title.dataset.target);
          const arrow = title.querySelector("span");
          const isOpen = section.style.display === "block";
          section.style.display = isOpen ? "none" : "block";
          arrow.textContent = isOpen ? "˅" : "˄";
        });
      });
    });
    </script>

    <?php
    wp_reset_postdata();
  } else {
    echo '<p>Aucun produit trouvé en promotion.</p>';
  }

  return ob_get_clean();
}

function afficher_filtres_promotion($filtres_actuels = []) {
  // Récupérer toutes les catégories parentes principales
  // Exclure la catégorie promotion/promotions des filtres
  $promo_term = get_term_by('slug', 'promotions', 'product_cat');
  if (!$promo_term) {
    $promo_term = get_term_by('slug', 'promotion', 'product_cat');
  }
  $exclude_id = $promo_term ? $promo_term->term_id : 0;
  
  // Récupérer les IDs des catégories à exclure
  $slugs_to_exclude = ['acceuil', 'canyon', 'uncategorized', 'velo'];
  $exclude_ids = [$exclude_id];
  
  foreach ($slugs_to_exclude as $slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if ($term) {
      $exclude_ids[] = $term->term_id;
    }
  }
  
  $categories_principales = get_terms([
    'taxonomy' => 'product_cat',
    'parent' => 0,
    'hide_empty' => false,
    'exclude' => $exclude_ids,
  ]);

  $sections = [
    'Filtres appliqués' => [],
    'Disponibilité' => ['en-stock'],
    'Catégories' => $categories_principales,
  ];

  $html = '<form id="form-filtres" method="get">';

  foreach ($sections as $titre => $options) {
    $id = 'section-' . sanitize_title($titre);
    $html .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
    $html .= '<h4 class="toggle-titre" data-target="' . $id . '">' . esc_html($titre) . ' <span>˅</span></h4>';
    $html .= '<div class="filtre-options" id="' . $id . '" style="display:none;">';

    if ($titre === 'Filtres appliqués') {
      $html .= '<div id="filtres-appliques" style="margin-bottom: 5px;">';
      if (!empty($filtres_actuels)) {
        foreach ($filtres_actuels as $slug) {
          $html .= '<span style="background:#FF3F22;color:#fff;padding:2px 8px;border-radius:4px;margin-right:4px;">' . esc_html($slug) . '</span>';
        }
      }
      $html .= '</div>';
      $html .= '<span id="reset" style="cursor:pointer; color:#FF3F22;">Tout effacer</span>';
    } else {
      foreach ($options as $opt) {
        if (is_object($opt)) {
          $slug = $opt->slug;
          $label = $opt->name;
        } else {
          $slug = sanitize_title($opt);
          $label = ucfirst($opt);
        }
        $checked = in_array($slug, $filtres_actuels) ? 'checked' : '';
        $html .= '<label style="display:block; margin-bottom: 5px; font-family: DIN Next LT Pro, sans-serif; font-weight: 400; font-size: 14px; color: #000;">';
        $html .= '<input type="checkbox" name="filtre[]" value="' . esc_attr($slug) . '" style="margin-right: 6px;" ' . $checked . '>'; 
        $html .= esc_html($label) . '</label>';
      }
    }

    $html .= '</div><hr class="separator-red"></div>';
  }

  $html .= '<button type="submit" style="background:#FF3F22;color:#fff;padding:6px 16px;border:none;border-radius:6px;">Filtrer</button>';
  $html .= '</form>';

  $html .= "<script>
    document.addEventListener('DOMContentLoaded', function() {
      const resetBtn = document.getElementById('reset');
      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          document.querySelectorAll('#form-filtres input[type=checkbox]').forEach(cb => cb.checked = false);
          document.getElementById('form-filtres').submit();
        });
      }
    });
  </script>";

  return $html;
}
