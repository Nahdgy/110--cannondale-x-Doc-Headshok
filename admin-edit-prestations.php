<?php
/**
 * Page d'administration pour éditer les prestations existantes
 * À ajouter dans le fichier functions.php ou créer un fichier séparé et l'inclure
 * 
 * Cette page permet de modifier manuellement les informations des prestations
 * pour compléter les données manquantes des commandes antérieures
 */

// Ajouter le menu dans l'administration WordPress
add_action('admin_menu', 'add_edit_prestations_menu');

function add_edit_prestations_menu() {
    add_menu_page(
        'Éditer Prestations',           // Titre de la page
        'Prestations',                  // Titre du menu
        'manage_options',               // Capacité requise
        'edit-prestations',             // Slug de la page
        'edit_prestations_page',        // Fonction de callback
        'dashicons-admin-tools',        // Icône
        30                              // Position
    );
}

// Fonction de callback pour afficher la page
function edit_prestations_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'demandes_prestations';
    
    // Traiter la mise à jour si le formulaire est soumis
    if (isset($_POST['update_prestation']) && check_admin_referer('update_prestation_nonce')) {
        $prestation_id = intval($_POST['prestation_id']);
        
        $data = array(
            'prestations_choisies' => sanitize_text_field($_POST['prestations_choisies']),
            'options_choisies' => sanitize_text_field($_POST['options_choisies']),
            'type_prestation_choisie' => sanitize_text_field($_POST['type_prestation_choisie']),
            'date_derniere_revision' => sanitize_text_field($_POST['date_derniere_revision']),
            'poids_pilote' => sanitize_text_field($_POST['poids_pilote']),
            'remarques' => sanitize_textarea_field($_POST['remarques'])
        );
        
        $wpdb->update($table_name, $data, array('id' => $prestation_id));
        
        echo '<div class="notice notice-success"><p>✅ Prestation mise à jour avec succès !</p></div>';
    }
    
    // Récupérer les prestations spécifiques mentionnées
    $numeros_suivis = array(
        'CMD-20260105-2cf19e',
        'CMD-20260105-2bcf5a',
        'CMD-20260106-de924a',
        'CMD-20260106-3a960c',
        'CMD-20260106-2dcc4d',
        'CMD-20260106-8fe25f',
        'CMD-20260107-bd5e1e',
        'CMD-20260108-a830a0',
        'CMD-20260108-e6e5f8',
        'CMD-20260111-c98006',
        'CMD-20260112-9b2aa2'
    );
    
    // Récupérer la prestation à éditer si sélectionnée
    $prestation_to_edit = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $prestation_to_edit = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $edit_id
        ));
    }
    
    ?>
    <div class="wrap">
        <h1>🔧 Édition des Prestations</h1>
        <p>Cette page permet de compléter les informations manquantes des prestations créées avant la mise à jour du système.</p>
        
        <?php if ($prestation_to_edit): ?>
            <!-- Formulaire d'édition -->
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Éditer la prestation</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('update_prestation_nonce'); ?>
                    <input type="hidden" name="prestation_id" value="<?php echo $prestation_to_edit->id; ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th>Numéro de suivi</th>
                            <td><strong><?php echo esc_html($prestation_to_edit->numero_suivi); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Type de prestation</th>
                            <td><?php echo esc_html($prestation_to_edit->type_prestation); ?></td>
                        </tr>
                        <tr>
                            <th>Vélo</th>
                            <td><?php echo esc_html($prestation_to_edit->modele_velo . ' (' . $prestation_to_edit->annee_velo . ')'); ?></td>
                        </tr>
                        <tr>
                            <th><label for="prestations_choisies">Prestations choisies</label></th>
                            <td>
                                <input type="text" id="prestations_choisies" name="prestations_choisies" 
                                       value="<?php echo esc_attr($prestation_to_edit->prestations_choisies); ?>" 
                                       class="regular-text" placeholder="Ex: Révision 200h">
                                <p class="description">Séparez plusieurs prestations par des virgules</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="options_choisies">Options choisies</label></th>
                            <td>
                                <input type="text" id="options_choisies" name="options_choisies" 
                                       value="<?php echo esc_attr($prestation_to_edit->options_choisies); ?>" 
                                       class="regular-text" placeholder="Ex: Joint SKF, Ressort Léger">
                                <p class="description">Séparez plusieurs options par des virgules</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="type_prestation_choisie">Type de prestation</label></th>
                            <td>
                                <select id="type_prestation_choisie" name="type_prestation_choisie">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="Express" <?php selected($prestation_to_edit->type_prestation_choisie, 'Express'); ?>>Express</option>
                                    <option value="Standard" <?php selected($prestation_to_edit->type_prestation_choisie, 'Standard'); ?>>Standard</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="date_derniere_revision">Date de la dernière révision</label></th>
                            <td>
                                <input type="date" id="date_derniere_revision" name="date_derniere_revision" 
                                       value="<?php echo esc_attr($prestation_to_edit->date_derniere_revision); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="poids_pilote">Poids du pilote (kg)</label></th>
                            <td>
                                <input type="number" id="poids_pilote" name="poids_pilote" 
                                       value="<?php echo esc_attr($prestation_to_edit->poids_pilote); ?>" 
                                       min="0" step="1" style="width: 100px;">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="remarques">Remarques</label></th>
                            <td>
                                <textarea id="remarques" name="remarques" rows="5" class="large-text"><?php echo esc_textarea($prestation_to_edit->remarques); ?></textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="update_prestation" class="button button-primary" value="Mettre à jour">
                        <a href="<?php echo admin_url('admin.php?page=edit-prestations'); ?>" class="button">Annuler</a>
                    </p>
                </form>
            </div>
        <?php else: ?>
            <!-- Liste des prestations à compléter -->
            <h2>Prestations à compléter</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>N° de suivi</th>
                        <th>Type</th>
                        <th>Vélo</th>
                        <th>Date création</th>
                        <th>Prestations</th>
                        <th>Options</th>
                        <th>Date révision</th>
                        <th>Poids</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Requête pour récupérer les prestations
                    $placeholders = implode(', ', array_fill(0, count($numeros_suivis), '%s'));
                    $query = $wpdb->prepare(
                        "SELECT * FROM $table_name WHERE numero_suivi IN ($placeholders) ORDER BY date_creation DESC",
                        ...$numeros_suivis
                    );
                    $prestations = $wpdb->get_results($query);
                    
                    if ($prestations) {
                        foreach ($prestations as $prestation) {
                            $is_complete = !empty($prestation->prestations_choisies) && 
                                          !empty($prestation->date_derniere_revision) && 
                                          !empty($prestation->poids_pilote);
                            
                            $row_class = $is_complete ? 'style="background-color: #d4edda;"' : '';
                            ?>
                            <tr <?php echo $row_class; ?>>
                                <td><strong><?php echo esc_html($prestation->numero_suivi); ?></strong></td>
                                <td><?php echo esc_html($prestation->type_prestation); ?></td>
                                <td><?php echo esc_html($prestation->modele_velo . ' (' . $prestation->annee_velo . ')'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($prestation->date_creation)); ?></td>
                                <td>
                                    <?php 
                                    echo $prestation->prestations_choisies 
                                        ? '<span style="color: green;">✓ ' . esc_html($prestation->prestations_choisies) . '</span>' 
                                        : '<span style="color: red;">✗ Manquant</span>'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $prestation->options_choisies 
                                        ? esc_html($prestation->options_choisies) 
                                        : '<span style="color: gray;">-</span>'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $prestation->date_derniere_revision 
                                        ? '<span style="color: green;">✓ ' . date('d/m/Y', strtotime($prestation->date_derniere_revision)) . '</span>' 
                                        : '<span style="color: red;">✗ Manquant</span>'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $prestation->poids_pilote 
                                        ? '<span style="color: green;">✓ ' . esc_html($prestation->poids_pilote) . ' kg</span>' 
                                        : '<span style="color: red;">✗ Manquant</span>'; 
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=edit-prestations&edit_id=' . $prestation->id); ?>" 
                                       class="button button-small">
                                        <?php echo $is_complete ? 'Modifier' : 'Compléter'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="9">Aucune prestation trouvée.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                <h3>💡 Comment retrouver les informations ?</h3>
                <ol>
                    <li><strong>Emails de confirmation</strong> : Vérifiez les emails envoyés aux clients et à l'administrateur</li>
                    <li><strong>Logs du serveur</strong> : Si vous avez accès aux logs, cherchez les soumissions de formulaires</li>
                    <li><strong>Contacter les clients</strong> : En dernier recours, contactez directement les clients pour les informations manquantes</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
        .form-table th {
            width: 200px;
        }
        .notice {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid;
        }
        .notice-success {
            border-color: #46b450;
            background-color: #ecf7ed;
        }
    </style>
    <?php
}
