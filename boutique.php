// Shortcode : [produits_boutique]
add_shortcode('produits_boutique', 'afficher_produits_boutique');

function afficher_produits_boutique() {
	ob_start();

	// Récupère toutes les catégories principales (hors "uncategorized")
	$categories = get_terms([
		'taxonomy' => 'product_cat',
		'parent' => 0,
		'hide_empty' => false,
		'exclude' => [get_option('default_product_cat')],
	]);

	// Récupère tous les produits
	$args = [
		'status' => 'publish',
		'limit' => -1,
	];
	// Filtrage par catégorie via GET
	if (isset($_GET['filtre']) && is_array($_GET['filtre']) && count($_GET['filtre']) > 0) {
		$args['category'] = array_map('sanitize_text_field', $_GET['filtre']);
	}
	$produits = wc_get_products($args);

	// Préparation des filtres dynamiques (catégories et sous-catégories)
	$html_filtres = '<form id="form-filtres">';
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-categories">Catégories <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-categories" style="display:none;">';
	foreach ($categories as $cat) {
		$html_filtres .= '<label style="display:block;margin-bottom:8px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($cat->slug).'" '.(isset($_GET['filtre']) && in_array($cat->slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= esc_html($cat->name);
		$html_filtres .= '</label>';
		// Sous-catégories
		$souscats = get_terms([
			'taxonomy' => 'product_cat',
			'parent' => $cat->term_id,
			'hide_empty' => false,
		]);
		foreach ($souscats as $scat) {
			$html_filtres .= '<label style="display:block;margin-left:20px;margin-bottom:6px;">';
			$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($scat->slug).'" '.(isset($_GET['filtre']) && in_array($scat->slug, $_GET['filtre']) ? 'checked' : '').'> ';
			$html_filtres .= esc_html($scat->name);
			$html_filtres .= '</label>';
		}
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	$html_filtres .= '<button type="submit" style="margin-top:10px;background:#FF3F22;color:#fff;padding:6px 16px;border:none;border-radius:6px;">Filtrer</button>';
	$html_filtres .= '<span id="reset" style="cursor:pointer; color:#FF3F22; margin-left:20px;">Tout effacer</span>';
	$html_filtres .= '</form>';

	// Affichage produits
	$limite_affichage = 18;
	$total = count($produits);
	$all_produits = [];
	foreach ($produits as $product) {
		$image_url = $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : wc_placeholder_img_src('medium');
		$all_produits[] = [
			'id' => $product->get_id(),
			'name' => $product->get_name(),
			'image' => $image_url,
			'price' => $product->get_price_html(),
			'permalink' => $product->get_permalink(),
		];
	}
	   ?>
       <div class="barre-tri">
            <div style="display: flex; align-items: center;">
                <div id="nombre-produits"><?php echo count($produits); ?> résultats</div>
                <div id="cacher-filtres">Cacher les filtres</div>
            </div>
            <div class="tri-container">
                <label for="tri-sous-categories">Trier par :</label>
                <select id="tri-sous-categories">
                    <option value="default">Tri par défaut</option>
                    <option value="alpha">Ordre alphabétique A-Z</option>
                    <option value="alpha-desc">Ordre alphabétique Z-A</option>
                </select>
            </div>
        </div>
        <hr class="separator-red">
	   <div id="produits-filtrables-container" style="display:flex; flex-direction:row; gap:40px; align-items:flex-start;">
        
		   <div id="filtres-colonne" style="min-width:280px;max-width:320px;">
			   <?php echo $html_filtres; ?>
		   </div>
		   <div style="flex:1;">
			   <hr class="separator-red">
			   <ul class="liste-categories" id="liste-produits">
				   <?php for ($i = 0; $i < min($limite_affichage, $total); $i++): $prod = $all_produits[$i]; ?>
					   <li>
						   <a href="<?php echo esc_url($prod['permalink']); ?>">
							   <img src="<?php echo esc_url($prod['image']); ?>" alt="<?php echo esc_attr($prod['name']); ?>">
							   <div class="titre-categorie"><?php echo esc_html($prod['name']); ?></div>
							   <div class="prix-produit"><?php echo $prod['price']; ?></div>
						   </a>
						   <button class="btn-ajouter" data-product-id="<?php echo esc_attr($prod['id']); ?>">Ajouter au panier</button>
					   </li>
				   <?php endfor; ?>
			   </ul>
			   <?php if ($total > $limite_affichage): ?>
				   <div style="display:flex;justify-content:center;margin-top:30px;">
					   <button id="voir-plus" class="btn-voir-plus">Voir plus</button>
				   </div>
			   <?php endif; ?>
			   <!-- Plus de popup custom, alert navigateur classique -->
		   </div>
	   </div>

	   <style>
		   #produits-filtrables-container {
			   display: flex;
			   flex-direction: row;
			   gap: 40px;
			   align-items: flex-start;
		   }
		   #filtres-colonne {
			   min-width: 280px;
			   max-width: 320px;
		   }
           #nombre-produits {
                font-family: 'din-next-lt-pro', sans-serif;
                font-weight: 300;
                font-size: 24px;
                color: black;
                margin-right: 2rem;
            }
            #tri-sous-categories {
                width: 160px;
                padding: 6px 8px;
                font-family: 'din-next-lt-pro', sans-serif;
                font-size: 14px;
            }
		   ul.liste-categories {
			   list-style: none;
			   padding: 0;
			   margin: 0;
			   display: grid;
			   grid-template-columns: repeat(3, 1fr);
			   gap: 20px;
		   }
		   @media screen and (max-width: 1023px) {
			   ul.liste-categories {
				   grid-template-columns: repeat(2, 1fr);
			   }
			   #produits-filtrables-container {
				   flex-direction: column;
			   }
			   #filtres-colonne {
				   max-width: 100%;
				   margin-bottom: 30px;
			   }
		   }
		   @media screen and (max-width: 766px) {
			   ul.liste-categories {
				   grid-template-columns: 1fr;
			   }
		   }
		   ul.liste-categories li {
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
		   ul.liste-categories li:hover {
			   transform: scale(1.03);
			   box-shadow: 0 2px 12px rgba(0,0,0,0.08);
		   }
		   ul.liste-categories li a {
			   text-decoration: none;
			   color: inherit;
			   display: block;
			   width: 100%;
			   height: 100%;
		   }
		   ul.liste-categories li img {
		        margin-bottom: 18px;
			   border-radius: 6px;
			   background: #fff;
			   box-shadow: 0 1px 6px rgba(0,0,0,0.07);
		   }
		   ul.liste-categories li .titre-categorie {
			   font-size: 1.1rem;
			   font-weight: 600;
			   margin-bottom: 10px;
			   margin-top: 10px;
		   }
		   .prix-produit {
			   font-size: 1.1rem;
			   color: #000000;
			   font-weight: bold;
			   margin-bottom: 12px;
		   }
		   .btn-ajouter {
			   background: #000000;
			   color: #fff;
			   border: none;
			   border-radius: 6px;
			   padding: 8px 18px;
			   font-size: 20px;
               font-weight: bold;
			   cursor: pointer;
			   margin-top: 10px;
			   transition: background 0.2s;
               width: 100%;
		    }
		   .btn-ajouter:hover {
			   background: #FF3F22;
		    }
		   .separator-red {
			   border: none;
			   border-top: 2px solid #FF3F22;
			   margin: 18px 0;
		    }
            .barre-tri {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 5px;
            }
            .tri-container {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .tri-container label {
                font-family: 'din-next-lt-pro', sans-serif;
                font-weight: 300;
                font-size: 16px;
                color: #000;
                margin-bottom: 0;
            }
		   .btn-voir-plus {
			   background: #000;
			   color: #fff;
               border: none;
			   border-radius: 6px;
			   padding: 10px 32px;
			   font-size: 1.1rem;
			   font-weight: 600;
			   cursor: pointer;
			   margin: 0 auto;
			   transition: background 0.2s, color 0.2s;
			   box-shadow: 0 1px 8px rgba(0,0,0,0.04);
			   display: block;
		   }
		   .btn-voir-plus:hover {
			   background: #FF3F22;
			   color: #fff;
		   }
		   .toggle-titre {
			   cursor: pointer;
			   font-weight: bold;
			   margin-bottom: 8px;
			   font-size: 1.1rem;
			   color: #222;
			   display: flex;
			   align-items: center;
			   gap: 8px;
		   }
		   #form-filtres {
			   margin-bottom: 30px;
			   background: #fff;
			   border-radius: 8px;
			   padding: 18px;
			   box-shadow: 0 1px 8px rgba(0,0,0,0.04);
			   max-width: 600px;
		   }
		   .filtre-groupe {
			   margin-bottom: 18px;
		   }
	   </style>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
            // Tri sous-catégories
            const triSelect = document.getElementById('tri-sous-categories');
            const liste = document.getElementById('liste-sous-categories');

            triSelect.addEventListener('change', function () {
            const items = Array.from(liste.querySelectorAll('li'));

                if (this.value === 'alpha') {
                    items.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
                } else if (this.value === 'alpha-desc') {
                    items.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
                } else {
                    return;
                }

            items.forEach(item => liste.appendChild(item));
            });
			// Toggle filtres
			document.querySelectorAll('.toggle-titre').forEach(function(titre) {
				titre.addEventListener('click', function() {
					var target = document.getElementById(titre.getAttribute('data-target'));
					if (target) {
						target.style.display = (target.style.display === 'none' || target.style.display === '') ? 'block' : 'none';
					}
				});
			});
			// Reset filtres
			var resetBtn = document.getElementById('reset');
			if (resetBtn) {
				resetBtn.addEventListener('click', function() {
					document.querySelectorAll('#form-filtres input[type=checkbox]').forEach(function(cb) {
						cb.checked = false;
					});
					document.getElementById('form-filtres').submit();
				});
			}
			// Voir plus
			   var voirPlusBtn = document.getElementById('voir-plus');
			   if (voirPlusBtn) {
				   var produits = <?php echo json_encode($all_produits); ?>;
				   var limite = <?php echo $limite_affichage; ?>;
				   var total = <?php echo $total; ?>;
				   var affiches = limite;
				   voirPlusBtn.addEventListener('click', function() {
					   var ul = document.getElementById('liste-produits');
					   for (var i = affiches; i < Math.min(affiches + limite, total); i++) {
						   var prod = produits[i];
						   var li = document.createElement('li');
						   li.innerHTML = '<a href="'+prod.permalink+'">'+
							   '<img src="'+prod.image+'" alt="'+prod.name+'">'+
							   '<div class="titre-categorie">'+prod.name+'</div>'+ 
							   '<div class="prix-produit">'+prod.price+'</div>'+ 
							   '</a>'+
							   '<button class="btn-ajouter" data-product-id="'+prod.id+'">Ajouter au panier</button>';
						   ul.appendChild(li);
					   }
					   affiches += limite;
					   if (affiches >= total) {
						   voirPlusBtn.style.display = 'none';
					   }
				   });
			   }

			   // Ajout au panier
			   function showPopupAjout() {
				   alert('Produit ajouté au panier !');
			   }
			   function addToCart(productId, btn) {
				   btn.disabled = true;
				   btn.textContent = 'Ajout...';
				   var xhr = new XMLHttpRequest();
				   xhr.open('POST', '/?wc-ajax=add_to_cart', true);
				   xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				   xhr.onload = function() {
					   btn.disabled = false;
					   btn.textContent = 'Ajouter au panier';
					   if (xhr.status === 200) {
						   showPopupAjout();
					   } else {
						   alert('Erreur lors de l\'ajout au panier');
					   }
				   };
				   xhr.send('product_id=' + encodeURIComponent(productId) + '&quantity=1');
			   }
			   // Délégation sur tous les boutons
			   document.addEventListener('click', function(e){
				   if(e.target.classList.contains('btn-ajouter') && e.target.hasAttribute('data-product-id')){
					   var productId = e.target.getAttribute('data-product-id');
					   addToCart(productId, e.target);
				   }
			   });
		});
	</script>
	<?php
	return ob_get_clean();
}
