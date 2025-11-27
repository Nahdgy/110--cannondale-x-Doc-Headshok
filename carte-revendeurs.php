<?php
/**
 * Système de Carte Interactive avec Liste de Lieux
 * Gestion des revendeurs/partenaires avec marqueurs sur carte
 * Shortcode: [carte_revendeurs]
 * 
 * INSTALLATION : Exécutez d'abord le fichier SQL carte-revendeurs-table.sql
 * pour créer la table wp_carte_lieux dans votre base de données
 */

// Ajouter le menu dans l'administration WordPress
add_action('admin_menu', 'ajouter_menu_carte_lieux');
function ajouter_menu_carte_lieux() {
    add_menu_page(
        'Carte Interactive',
        'Carte Lieux',
        'manage_options',
        'carte-lieux',
        'afficher_page_gestion_carte',
        'dashicons-location-alt',
        57
    );
}

// Page d'administration
function afficher_page_gestion_carte() {
    if (!current_user_can('manage_options')) {
        wp_die('Vous n\'avez pas les permissions nécessaires');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'carte_lieux';
    
    // Traitement des actions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'ajouter' && wp_verify_nonce($_POST['_wpnonce'], 'ajouter_lieu')) {
            $wpdb->insert(
                $table_name,
                array(
                    'titre' => sanitize_text_field($_POST['titre']),
                    'adresse' => sanitize_textarea_field($_POST['adresse']),
                    'ville' => sanitize_text_field($_POST['ville']),
                    'code_postal' => sanitize_text_field($_POST['code_postal']),
                    'latitude' => floatval($_POST['latitude']),
                    'longitude' => floatval($_POST['longitude']),
                    'telephone' => sanitize_text_field($_POST['telephone']),
                    'email' => sanitize_email($_POST['email']),
                    'site_web' => esc_url_raw($_POST['site_web']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'icone' => sanitize_text_field($_POST['icone']),
                    'couleur' => sanitize_hex_color($_POST['couleur']),
                    'ordre' => intval($_POST['ordre']),
                    'actif' => isset($_POST['actif']) ? 1 : 0
                ),
                array('%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
            );
            echo '<div class="notice notice-success"><p>Lieu ajouté avec succès !</p></div>';
        }
        
        if ($_POST['action'] === 'modifier' && wp_verify_nonce($_POST['_wpnonce'], 'modifier_lieu')) {
            $wpdb->update(
                $table_name,
                array(
                    'titre' => sanitize_text_field($_POST['titre']),
                    'adresse' => sanitize_textarea_field($_POST['adresse']),
                    'ville' => sanitize_text_field($_POST['ville']),
                    'code_postal' => sanitize_text_field($_POST['code_postal']),
                    'latitude' => floatval($_POST['latitude']),
                    'longitude' => floatval($_POST['longitude']),
                    'telephone' => sanitize_text_field($_POST['telephone']),
                    'email' => sanitize_email($_POST['email']),
                    'site_web' => esc_url_raw($_POST['site_web']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'icone' => sanitize_text_field($_POST['icone']),
                    'couleur' => sanitize_hex_color($_POST['couleur']),
                    'ordre' => intval($_POST['ordre']),
                    'actif' => isset($_POST['actif']) ? 1 : 0
                ),
                array('id' => intval($_POST['lieu_id'])),
                array('%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'),
                array('%d')
            );
            echo '<div class="notice notice-success"><p>Lieu modifié avec succès !</p></div>';
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'supprimer_lieu_' . $_GET['id'])) {
            $wpdb->delete($table_name, array('id' => intval($_GET['id'])), array('%d'));
            echo '<div class="notice notice-success"><p>Lieu supprimé avec succès !</p></div>';
        }
    }
    
    // Récupérer tous les lieux
    $lieux = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ordre ASC, titre ASC");
    
    // Mode édition
    $editing = false;
    $lieu_edit = null;
    if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
        $editing = true;
        $lieu_edit = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['edit'])));
    }
    
    ?>
    <div class="wrap">
        <h1>🗺️ Gestion de la Carte Interactive</h1>
        
        <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #FF3F22;">
            <h3>📋 Shortcode</h3>
            <p>Pour afficher la carte sur votre site, utilisez ce shortcode :</p>
            <code style="background: #f0f0f0; padding: 10px; display: inline-block; font-size: 14px;">[carte_revendeurs]</code>
            <p style="margin-top: 10px;"><small><strong>Astuce :</strong> Les lieux s'afficheront automatiquement sur la carte et dans la liste à droite.</small></p>
        </div>
        
        <!-- Formulaire d'ajout/modification -->
        <div style="background: #fff; padding: 25px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2><?php echo $editing ? '✏️ Modifier le lieu' : '➕ Ajouter un nouveau lieu'; ?></h2>
            
            <form method="post" action="">
                <?php if ($editing): ?>
                    <?php wp_nonce_field('modifier_lieu'); ?>
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="lieu_id" value="<?php echo $lieu_edit->id; ?>">
                <?php else: ?>
                    <?php wp_nonce_field('ajouter_lieu'); ?>
                    <input type="hidden" name="action" value="ajouter">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="titre">Titre / Nom du lieu *</label></th>
                        <td>
                            <input type="text" id="titre" name="titre" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->titre) : ''; ?>" required>
                            <p class="description">Ex: Cycles Dupont, Atelier Vélo Paris, etc.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="adresse">Adresse *</label></th>
                        <td>
                            <textarea id="adresse" name="adresse" class="large-text" rows="2" required><?php echo $editing ? esc_textarea($lieu_edit->adresse) : ''; ?></textarea>
                            <p class="description">Adresse complète (rue, numéro)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="code_postal">Code postal *</label></th>
                        <td>
                            <input type="text" id="code_postal" name="code_postal" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->code_postal) : ''; ?>" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="ville">Ville *</label></th>
                        <td>
                            <input type="text" id="ville" name="ville" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->ville) : ''; ?>" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="latitude">Latitude *</label></th>
                        <td>
                            <input type="number" id="latitude" name="latitude" class="regular-text" step="0.00000001" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->latitude) : ''; ?>" required>
                            <p class="description">Ex: 48.8566 (Paris) - <a href="https://www.latlong.net/" target="_blank">Trouver les coordonnées</a></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="longitude">Longitude *</label></th>
                        <td>
                            <input type="number" id="longitude" name="longitude" class="regular-text" step="0.00000001" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->longitude) : ''; ?>" required>
                            <p class="description">Ex: 2.3522 (Paris)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="telephone">Téléphone</label></th>
                        <td>
                            <input type="text" id="telephone" name="telephone" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->telephone) : ''; ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td>
                            <input type="email" id="email" name="email" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->email) : ''; ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="site_web">Site web</label></th>
                        <td>
                            <input type="url" id="site_web" name="site_web" class="regular-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->site_web) : ''; ?>" 
                                   placeholder="https://example.com">
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="description">Description</label></th>
                        <td>
                            <textarea id="description" name="description" class="large-text" rows="4"><?php echo $editing ? esc_textarea($lieu_edit->description) : ''; ?></textarea>
                            <p class="description">Informations complémentaires sur le lieu</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="icone">Icône (Emoji)</label></th>
                        <td>
                            <input type="text" id="icone" name="icone" class="small-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->icone) : '📍'; ?>" maxlength="2">
                            <p class="description">Choisissez un emoji : 🚲 🔧 🏪 📍 ⚙️ 🛠️</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="couleur">Couleur du marqueur</label></th>
                        <td>
                            <input type="color" id="couleur" name="couleur" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->couleur) : '#FF3F22'; ?>">
                            <p class="description">Couleur personnalisée pour ce lieu</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="ordre">Ordre d'affichage</label></th>
                        <td>
                            <input type="number" id="ordre" name="ordre" class="small-text" 
                                   value="<?php echo $editing ? esc_attr($lieu_edit->ordre) : '0'; ?>">
                            <p class="description">Les lieux seront triés par ordre croissant</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="actif">Actif</label></th>
                        <td>
                            <input type="checkbox" id="actif" name="actif" value="1" 
                                   <?php echo ($editing && $lieu_edit->actif) || !$editing ? 'checked' : ''; ?>>
                            <label for="actif">Afficher ce lieu sur la carte</label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        <?php echo $editing ? '💾 Enregistrer les modifications' : '✅ Ajouter le lieu'; ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="?page=carte-lieux" class="button button-secondary">Annuler</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- Liste des lieux -->
        <div style="background: #fff; padding: 25px; margin: 20px 0; border-radius: 8px;">
            <h2>📌 Liste des lieux (<?php echo count($lieux); ?>)</h2>
            
            <?php if (empty($lieux)): ?>
                <p style="text-align: center; padding: 40px; color: #999;">Aucun lieu ajouté pour le moment.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="40">Icône</th>
                            <th>Titre</th>
                            <th>Adresse</th>
                            <th>Coordonnées</th>
                            <th width="80">Ordre</th>
                            <th width="80">Statut</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lieux as $lieu): ?>
                            <tr>
                                <td style="text-align: center; font-size: 24px;"><?php echo esc_html($lieu->icone); ?></td>
                                <td>
                                    <strong><?php echo esc_html($lieu->titre); ?></strong>
                                    <?php if ($lieu->telephone): ?>
                                        <br><small>📞 <?php echo esc_html($lieu->telephone); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($lieu->adresse); ?><br>
                                    <small><?php echo esc_html($lieu->code_postal . ' ' . $lieu->ville); ?></small>
                                </td>
                                <td>
                                    <small>
                                        Lat: <?php echo esc_html($lieu->latitude); ?><br>
                                        Lng: <?php echo esc_html($lieu->longitude); ?>
                                    </small>
                                </td>
                                <td style="text-align: center;"><?php echo esc_html($lieu->ordre); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($lieu->actif): ?>
                                        <span style="color: #4CAF50; font-weight: bold;">✓ Actif</span>
                                    <?php else: ?>
                                        <span style="color: #999;">○ Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?page=carte-lieux&edit=<?php echo $lieu->id; ?>" class="button button-small">Modifier</a>
                                    <a href="?page=carte-lieux&action=supprimer&id=<?php echo $lieu->id; ?>&_wpnonce=<?php echo wp_create_nonce('supprimer_lieu_' . $lieu->id); ?>" 
                                       class="button button-small" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu ?');">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .wrap h1 { color: #FF3F22; }
        .form-table th { width: 200px; }
        .button-primary { background: #FF3F22 !important; border-color: #e6381e !important; }
        .button-primary:hover { background: #e6381e !important; }
    </style>
    <?php
}

// Shortcode pour afficher la carte
add_shortcode('carte_revendeurs', 'afficher_carte_revendeurs_shortcode');
function afficher_carte_revendeurs_shortcode($atts) {
    // Paramètres du shortcode
    $atts = shortcode_atts(array(
        'hauteur' => '600px',
        'zoom' => '6',
        'centre_lat' => '46.603354',
        'centre_lng' => '1.888334'
    ), $atts);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'carte_lieux';
    
    // Récupérer tous les lieux actifs
    $lieux = $wpdb->get_results("SELECT * FROM $table_name WHERE actif = 1 ORDER BY ordre ASC, titre ASC");
    
    // Convertir en JSON pour JavaScript
    $lieux_json = json_encode($lieux);
    
    ob_start();
    ?>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    
    <div class="carte-revendeurs-container">
        <div class="carte-revendeurs-wrapper">
            <div id="carte-map" style="height: <?php echo esc_attr($atts['hauteur']); ?>; width: 100%;"></div>
            <div class="carte-liste">
                <h3>📍 Trouvez votre partenaire</h3>
                <div class="carte-liste-items">
                    <?php if (empty($lieux)): ?>
                        <p class="no-lieux">Aucun lieu disponible pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($lieux as $index => $lieu): ?>
                            <div class="lieu-item" data-id="<?php echo $lieu->id; ?>" data-index="<?php echo $index; ?>">
                                <div class="lieu-icone" style="background: <?php echo esc_attr($lieu->couleur); ?>;">
                                    <?php echo esc_html($lieu->icone); ?>
                                </div>
                                <div class="lieu-info">
                                    <h4><?php echo esc_html($lieu->titre); ?></h4>
                                    <p class="lieu-adresse">
                                        <?php echo esc_html($lieu->adresse); ?><br>
                                        <?php echo esc_html($lieu->code_postal . ' ' . $lieu->ville); ?>
                                    </p>
                                    <?php if ($lieu->telephone): ?>
                                        <p class="lieu-contact">📞 <?php echo esc_html($lieu->telephone); ?></p>
                                    <?php endif; ?>
                                    <?php if ($lieu->site_web): ?>
                                        <p class="lieu-contact">
                                            <a href="<?php echo esc_url($lieu->site_web); ?>" target="_blank">🌐 Site web</a>
                                        </p>
                                    <?php endif; ?>
                                    <a href="https://maps.google.com/maps?daddr=<?php echo urlencode($lieu->adresse . ', ' . $lieu->code_postal . ' ' . $lieu->ville); ?>" 
                                       target="_blank" class="btn-itineraire">
                                        🗺️ Itinéraire
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        (function() {
            const lieux = <?php echo $lieux_json; ?>;
            
            // Initialiser la carte
            const map = L.map('carte-map', {
                zoomControl: true,
                attributionControl: true
            }).setView([<?php echo esc_js($atts['centre_lat']); ?>, <?php echo esc_js($atts['centre_lng']); ?>], <?php echo esc_js($atts['zoom']); ?>);
            
            // Fond de carte
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Stocker les marqueurs
            const markers = [];
            
            // Ajouter les marqueurs
            lieux.forEach((lieu, index) => {
                const customIcon = L.divIcon({
                    html: `
                        <div style="
                            width: 40px;
                            height: 40px;
                            background: ${lieu.couleur};
                            border-radius: 50% 50% 50% 0;
                            transform: rotate(-45deg);
                            border: 3px solid white;
                            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                            <div style="
                                transform: rotate(45deg);
                                font-size: 18px;
                            ">${lieu.icone}</div>
                        </div>
                    `,
                    className: 'custom-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 35]
                });
                
                const marker = L.marker([lieu.latitude, lieu.longitude], {icon: customIcon}).addTo(map);
                
                const popupContent = `
                    <div class="custom-popup">
                        <h3>${lieu.titre}</h3>
                        <p class="popup-address">${lieu.adresse}<br>${lieu.code_postal} ${lieu.ville}</p>
                        ${lieu.telephone ? `<p>📞 ${lieu.telephone}</p>` : ''}
                        ${lieu.email ? `<p>📧 ${lieu.email}</p>` : ''}
                        ${lieu.description ? `<p>${lieu.description}</p>` : ''}
                        <a href="https://maps.google.com/maps?daddr=${encodeURIComponent(lieu.adresse + ', ' + lieu.code_postal + ' ' + lieu.ville)}" 
                           target="_blank">
                            🗺️ Itinéraire GPS
                        </a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                markers.push(marker);
                
                // Clic sur le marqueur
                marker.on('click', function() {
                    document.querySelectorAll('.lieu-item').forEach(item => item.classList.remove('active'));
                    const lieuElement = document.querySelector(`.lieu-item[data-index="${index}"]`);
                    if (lieuElement) {
                        lieuElement.classList.add('active');
                        lieuElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
            
            // Clic sur les éléments de la liste
            document.querySelectorAll('.lieu-item').forEach((item, index) => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.lieu-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    
                    if (markers[index]) {
                        map.setView([lieux[index].latitude, lieux[index].longitude], 14, {
                            animate: true,
                            duration: 1
                        });
                        markers[index].openPopup();
                    }
                });
            });
            
            // Ajuster la vue pour montrer tous les marqueurs
            if (lieux.length > 0) {
                const bounds = L.latLngBounds(lieux.map(l => [l.latitude, l.longitude]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
            
            // Responsive
            window.addEventListener('resize', function() {
                setTimeout(() => map.invalidateSize(), 100);
            });
        })();
    </script>
    
    <style>
        .carte-revendeurs-container {
            width: 100%;
            margin: 0 auto;
            font-family: 'din-next-lt-pro', Arial, sans-serif;
        }
        
        .carte-revendeurs-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        #carte-map {
            border: 3px solid #FF3F22;
        }
        
        .carte-liste {
            background: #f8f9fa;
            overflow-y: auto;
            max-height: 600px;
            padding: 20px;
        }
        
        .carte-liste h3 {
            color: #FF3F22;
            font-size: 1.4em;
            margin: 0 0 20px 0;
            font-weight: 700;
            padding-bottom: 15px;
            border-bottom: 3px solid #FF3F22;
        }
        
        .carte-liste-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .lieu-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            gap: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .lieu-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(255, 63, 34, 0.2);
            border-color: #FF3F22;
        }
        
        .lieu-item.active {
            border-color: #FF3F22;
            background: linear-gradient(135deg, #fff5f3 0%, #ffffff 100%);
        }
        
        .lieu-icone {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .lieu-info {
            flex: 1;
        }
        
        .lieu-info h4 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 1.1em;
            font-weight: 700;
        }
        
        .lieu-adresse {
            font-size: 0.9em;
            color: #666;
            line-height: 1.5;
            margin: 0 0 8px 0;
        }
        
        .lieu-contact {
            font-size: 0.85em;
            color: #555;
            margin: 4px 0;
        }
        
        .lieu-contact a {
            color: #FF3F22;
            text-decoration: none;
            font-weight: 600;
        }
        
        .lieu-contact a:hover {
            text-decoration: underline;
        }
        
        .btn-itineraire {
            display: inline-block;
            background: linear-gradient(135deg, #FF3F22, #e6381e);
            color: white !important;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85em;
            margin-top: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-itineraire:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 63, 34, 0.3);
        }
        
        .no-lieux {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .custom-popup {
            font-family: 'din-next-lt-pro', Arial, sans-serif;
            max-width: 300px;
        }
        
        .custom-popup h3 {
            color: #FF3F22;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        
        .custom-popup p {
            margin: 8px 0;
            line-height: 1.5;
            color: #333;
        }
        
        .custom-popup .popup-address {
            font-weight: 500;
            color: #555;
            margin-bottom: 12px;
        }
        
        .custom-popup a {
            display: inline-block;
            background: #FF3F22;
            color: white;
            padding: 8px 16px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
            transition: background 0.3s ease;
            margin-top: 10px;
        }
        
        .custom-popup a:hover {
            background: #e6381e;
        }
        
        /* Responsive */
        @media screen and (max-width: 1024px) {
            .carte-revendeurs-wrapper {
                grid-template-columns: 1fr;
            }
            
            .carte-liste {
                max-height: none;
                border-top: 3px solid #FF3F22;
            }
        }
        
        @media screen and (max-width: 768px) {
            .carte-liste {
                padding: 15px;
            }
            
            .carte-liste h3 {
                font-size: 1.2em;
            }
            
            .lieu-item {
                padding: 12px;
            }
            
            .lieu-icone {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }
    </style>
    
    <?php
    return ob_get_clean();
}
