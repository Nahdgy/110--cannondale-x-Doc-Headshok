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
	// Note: Le filtrage par prix sera géré côté client JavaScript
	$produits = wc_get_products($args);

	// Préparation des filtres structurés
	$html_filtres = '<form id="form-filtres">';
	
	// 1. PRATIQUE
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-pratique">Pratique <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-pratique" style="display:none;">';
	$pratiques = ['vtt' => 'VTT', 'vae-2' => 'VAE', 'route' => 'Route', 'urbain' => 'Urbain'];
	foreach ($pratiques as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:8px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 2. MODÈLES DE VÉLOS VTT
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-modeles-vtt">Modèles VTT <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-modeles-vtt" style="display:none;">';
	$modeles_vtt = [
		'scalpel-se-2021-2023' => 'Scalpel', 'jekyll-29-2021-2024' => 'Jekyll', 'habit-29-2019-2022' => 'Habit', 
		'trail-29-2015-2021' => 'Trail', 'trigger-29-aluminium-carbone-2013-2015' => 'Trigger', 'f-si-2015-2018' => 'F-Si',
		'rush-2005-2009' => 'Rush', 'prophet-prophet-sl-2005-2008' => 'Prophet', 'flash-carbone-26-29-2010-2012' => 'Flash',
		'claymore-2011-2013' => 'Claymore', 'beast-of-the-east-2016-2019' => 'Beast of the East'
	];
	foreach ($modeles_vtt as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 3. MODÈLES DE VÉLOS ROUTE
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-modeles-route">Modèles Route <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-modeles-route" style="display:none;">';
	$modeles_route = [
		'supersix-evo-4-2023-2025' => 'SuperSix EVO', 'synapse-carbon-2022' => 'Synapse', 
		'caad13-2020-2025' => 'CAAD', 'systemsix-2019-2024' => 'SystemSix', 'topstone-2025' => 'TopStone',
		'slice-rs-2013-2014' => 'Slice', 'super-slice-2019-2022' => 'SuperSlice', 'superx-2025' => 'SuperX'
	];
	foreach ($modeles_route as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 4. FOURCHES
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-fourches">Fourches <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-fourches" style="display:none;">';
	$fourches = ['lefty' => 'Lefty', 'fatty' => 'Fatty/Super Fatty', 'Olaf' => 'Olaf', 'Occasion' => 'Fourche occasion'];
	foreach ($fourches as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 5. TRANSMISSION
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-transmission">Transmission <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-transmission" style="display:none;">';
	$transmission = ['cassettes' => 'Cassettes', 'chaine' => 'Chaînes', 'plateaux' => 'Plateaux', 'pédalier' => 'Pédaliers', 'boitier-de-pedalier' => 'Boitier de pédalier', 'manivelles' => 'Manivelles', 'cable' => 'Câbles/Gaines'];
	foreach ($transmission as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 6. FREINAGE
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-freinage">Freinage <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-freinage" style="display:none;">';
	$freinage = ['disque' => 'Frein à disque', 'plaquettes' => 'Plaquettes'];
	foreach ($freinage as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 7. PIÈCES DE CADRE
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-pieces-cadre">Pièces de cadre <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-pieces-cadre" style="display:none;">';
	$pieces_cadre = ['guide-cable' => 'Guide-câble', 'jeu-direction' => 'Jeu de direction', 'patte-derailleur' => 'Patte de dérailleur', 'protection-cadres-et-fourches-cannondale' => 'Protections', 'emboutspasse-durites' => 'Embouts - passe-durite', 'roulements' => 'Roulements'];
	foreach ($pieces_cadre as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 8. ROUE / PNEU
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-roue-pneu">Roue / Pneu <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-roue-pneu" style="display:none;">';
	$roue_pneu = ['roues' => 'Roues', 'pneus' => 'Pneus', 'chambres' => 'Chambre à air', 'roues-libres' => 'Roue Libre', 'roulements-roue' => 'Roulements', 'axes-serrage' => 'Axes et serrage', 'moyeux' => 'Moyeux'];
	foreach ($roue_pneu as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 9. COMPOSANTS PÉRIPHÉRIQUES
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-composants">Composants périphériques <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-composants" style="display:none;">';
	$composants = ['selles' => 'Selles', 'tiges_de_selle' => 'Collier de selle / Tige de selle', 'cintres' => 'Cintre / Serrage / Guidoline', 'amortisseurs_arrieres' => 'Amortisseurs arrières', 'electrique' => 'Composants électriques', 'direction' => 'Jeu de direction', 'potences' => 'Potences'];
	foreach ($composants as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 10. ÉQUIPEMENTS
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-equipements">Équipements <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-equipements" style="display:none;">';
	$equipements = ['casques' => 'Casques', 'vetements' => 'Vêtements', 'bidons' => 'Bidon/Porte bidon', 'multioutil' => 'Multi-outils', 'entretien' => 'Entretien', 'bequilles' => 'Béquilles', 'poignees' => 'Poignées', 'produits_connectes' => 'Produits connectés', 'pompes' => 'Pompes', 'bagagerie' => 'Bagagerie'];
	foreach ($equipements as $slug => $nom) {
		$html_filtres .= '<label style="display:block;margin-bottom:6px;">';
		$html_filtres .= '<input type="checkbox" name="filtre[]" value="'.esc_attr($slug).'" '.(isset($_GET['filtre']) && in_array($slug, $_GET['filtre']) ? 'checked' : '').'> ';
		$html_filtres .= $nom;
		$html_filtres .= '</label>';
	}
	$html_filtres .= '</div><hr class="separator-red"></div>';
	
	// 11. FILTRE PAR PRIX
	$html_filtres .= '<div class="filtre-groupe" style="margin-bottom: 20px;">';
	$html_filtres .= '<h4 class="toggle-titre" data-target="section-prix">Prix <span>˅</span></h4>';
	$html_filtres .= '<div class="filtre-options" id="section-prix" style="display:none;">';
	$html_filtres .= '<div style="margin-bottom:15px;">';
	$html_filtres .= '<label style="display:block;margin-bottom:8px;">Prix maximum :</label>';
	$html_filtres .= '<input type="number" id="prix_max" min="0" max="3000" style="width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;" placeholder="3000 €">';
	$html_filtres .= '</div>';
	$html_filtres .= '<button type="button" id="appliquer-prix" style="background:#FF3F22;color:#fff;padding:6px 16px;border:none;border-radius:6px;width:100%;">Appliquer le filtre prix</button>';
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
		$prix_numerique = floatval($product->get_price()); // Prix numérique pour le filtrage
		$all_produits[] = [
			'id' => $product->get_id(),
			'name' => $product->get_name(),
			'image' => $image_url,
			'price' => $product->get_price_html(),
			'price_numeric' => $prix_numerique, // Prix numérique ajouté
			'permalink' => $product->get_permalink(),
		];
	}
	   ?>
<style>
	/* Section filtres déroulante */
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

	#filtres-icon {
		transition: transform 0.3s ease;
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

	#produits-filtrables-container {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	#produits-container {
		width: 100%;
	}

	#filtres-colonne {
		width: 100%;
		background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
		display: flex;
    	flex-direction: column;
    	justify-content: end;
	}
	ul.liste-categories li img {
		margin-bottom: 18px;
		border-radius: 6px;
		background: #fff;
		box-shadow: 0 1px 6px rgba(0,0,0,0.07);
	}
	ul.liste-categories li .titre-categorie {
		font-size: 16px;
		font-weight: 600;
		margin-bottom: 10px;
		margin-top: 10px;
		color: #000 !important;
	}
	ul.liste-categories li:hover .titre-categorie {
		color: #000 !important;
	}

	.prix-produit {
		font-size: 18px;
		color: #000000A1;
		font-weight: 400;
		margin-bottom: 12px;
	}

	.btn-ajouter {
		background: #000000;
		color: #fff;
		border: none;
		border-radius: 6px;
		padding: 8px 18px;
		font-size: 16px;
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
		font-size: 16px;
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
	.filtre-options {
		max-height: 300px;
		overflow-y: auto;
	}
	.filtre-options::-webkit-scrollbar {
		width: 6px;
	}
	.filtre-options::-webkit-scrollbar-track {
		background: #f1f1f1;
		border-radius: 3px;
	}
	.filtre-options::-webkit-scrollbar-thumb {
		background: #FF3F22;
		border-radius: 3px;
	}
	.filtre-options::-webkit-scrollbar-thumb:hover {
		background: #e6381e;
	}

	/* Responsive Design - Mobile et Tablette */
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

		.section-filtres-expanded {
			max-height: 600px;
			padding: 15px;
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
		ul.liste-categories {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media screen and (max-width: 768px) {
		.section-filtres-expanded {
			max-height: 500px;
			overflow-y: auto;
		}
		
		#form-filtres {
			flex-direction: column;
			overflow-x: visible;
		}
		.filtre-groupe {
			min-width: auto;
			width: 100%;
		}
		
		.btn-toggle-filtres {
			font-size: 12px;
			padding: 6px 12px;
		}
		.barre-tri {
			flex-direction: column;
			gap: 10px;
		}
		#tri-sous-categories {
			width: 100px;
		}
		ul.liste-categories {
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
		}
		ul.liste-categories li {
			min-height: 0px;
		}
		ul.liste-categories li .titre-categorie{
			font-size: 14px;
		}
		.prix-produit {
			font-size: 16px;
		}
		.btn-ajouter {
			font-size: 11px;
		}
	}
</style>

<div class="barre-tri">
	<div style="display: flex; align-items: center;">
		<div id="nombre-produits"><?php echo count($produits); ?> résultats</div>
		<button id="toggle-filtres" class="btn-toggle-filtres">
			<span id="filtres-icon">🔽</span> Filtres
		</button>
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
        
<!-- Section filtres déroulante -->
<div id="section-filtres" class="section-filtres-collapsed">
	<div id="filtres-colonne" style="width:100%;">
		<?php echo $html_filtres; ?>
	</div>
</div>
        
<!-- Conteneur principal des produits -->
<div id="produits-container">
	<div style="flex:1;">
		<ul class="liste-categories" id="liste-produits">
			<?php for ($i = 0; $i < min($limite_affichage, $total); $i++): $prod = $all_produits[$i]; ?>
				<li data-price="<?php echo esc_attr($prod['price_numeric']); ?>" data-name="<?php echo esc_attr($prod['name']); ?>">
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
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Gestion du dropdown filtres
		const toggleFiltres = document.getElementById('toggle-filtres');
		const sectionFiltres = document.getElementById('section-filtres');
		const filtresIcon = document.getElementById('filtres-icon');
		let filtresOuverts = false;
		
		if (toggleFiltres && sectionFiltres) {
			toggleFiltres.addEventListener('click', function() {
				if (filtresOuverts) {
					// Fermer les filtres
					sectionFiltres.className = 'section-filtres-collapsed';
					filtresIcon.style.transform = 'rotate(0deg)';
					toggleFiltres.innerHTML = '<span id="filtres-icon">🔽</span> Filtres';
					filtresOuverts = false;
				} else {
					// Ouvrir les filtres
					sectionFiltres.className = 'section-filtres-expanded';
					filtresIcon.style.transform = 'rotate(180deg)';
					toggleFiltres.innerHTML = '<span id="filtres-icon" style="transform: rotate(180deg);">🔽</span> Masquer filtres';
					filtresOuverts = true;
				}
			});
		}
		
		// Tri sous-catégories
		const triSelect = document.getElementById('tri-sous-categories');
		const liste = document.getElementById('liste-produits');

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
				// Reset du champ prix
				var prixMax = document.getElementById('prix_max');
				if (prixMax) prixMax.value = '';
				// Reset du filtre prix sur les produits affichés
				resetPriceFilter();
				document.getElementById('form-filtres').submit();
			});
		}
		
		// Fonction de filtrage par prix
		function appliquerFiltrePrix() {
			var prixMax = parseFloat(document.getElementById('prix_max').value) || 999999;
			var produits = document.querySelectorAll('#liste-produits li');
			var compteurVisible = 0;
			var listeContainer = document.getElementById('liste-produits');
			
			// Créer un nouveau container temporaire pour les produits visibles
			var produitsVisibles = [];
			
			produits.forEach(function(produit) {
				var prixProduit = parseFloat(produit.getAttribute('data-price')) || 0;
				if (prixProduit <= prixMax) {
					produitsVisibles.push(produit);
					compteurVisible++;
				}
			});
			
			// Vider la liste et réafficher seulement les produits filtrés
			listeContainer.innerHTML = '';
			produitsVisibles.forEach(function(produit) {
				listeContainer.appendChild(produit);
			});
			
			// Stocker les produits cachés pour pouvoir les restaurer
			window.produitsCaches = [];
			produits.forEach(function(produit) {
				var prixProduit = parseFloat(produit.getAttribute('data-price')) || 0;
				if (!(prixProduit <= prixMax)) {
					window.produitsCaches.push(produit);
				}
			});
			
			// Mise à jour du compteur de résultats
			var compteurElement = document.getElementById('nombre-produits');
			if (compteurElement) {
				compteurElement.textContent = compteurVisible + ' résultats';
			}
			
			// Gérer le bouton "Voir plus" selon le filtrage
			var voirPlusBtn = document.getElementById('voir-plus');
			if (voirPlusBtn && compteurVisible < produits.length) {
				voirPlusBtn.style.display = 'none';
			}
		}
		
		// Fonction de reset du filtre prix
		function resetPriceFilter() {
			var listeContainer = document.getElementById('liste-produits');
			
			// Restaurer tous les produits (visibles + cachés)
			if (window.produitsCaches && window.produitsCaches.length > 0) {
				window.produitsCaches.forEach(function(produit) {
					listeContainer.appendChild(produit);
				});
				window.produitsCaches = [];
			}
			
			// Remettre le compteur original
			var compteurElement = document.getElementById('nombre-produits');
			if (compteurElement) {
				compteurElement.textContent = <?php echo $total; ?> + ' résultats';
			}
			
			// Rétablir le bouton "Voir plus" si nécessaire
			var voirPlusBtn = document.getElementById('voir-plus');
			if (voirPlusBtn && <?php echo $total; ?> > <?php echo $limite_affichage; ?>) {
				voirPlusBtn.style.display = 'block';
			}
		}
		
		// Gestionnaire du bouton "Appliquer le filtre prix"
		var appliquerPrixBtn = document.getElementById('appliquer-prix');
		if (appliquerPrixBtn) {
			appliquerPrixBtn.addEventListener('click', appliquerFiltrePrix);
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
						li.setAttribute('data-price', prod.price_numeric);
						li.setAttribute('data-name', prod.name);
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
