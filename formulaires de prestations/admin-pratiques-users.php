<?php
// Ajouter une page d'administration pour voir les pratiques des utilisateurs
add_action('admin_menu', 'ajouter_menu_pratiques_users');

function ajouter_menu_pratiques_users() {
    add_users_page(
        'Pratiques des utilisateurs',
        'Pratiques',
        'manage_options',
        'pratiques-users',
        'afficher_pratiques_users'
    );
}

function afficher_pratiques_users() {
    ?>
    <div class="wrap">
        <h1>Pratiques des utilisateurs</h1>
        
        <style>
            .pratiques-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .pratiques-table th,
            .pratiques-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .pratiques-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .pratiques-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .pratique-tag {
                display: inline-block;
                background: #0073aa;
                color: white;
                padding: 2px 8px;
                margin: 2px;
                border-radius: 3px;
                font-size: 11px;
            }
        </style>
        
        <table class="pratiques-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Pratiques</th>
                    <th>Date d'inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer tous les utilisateurs avec leurs pratiques
                $users = get_users(array(
                    'meta_key' => 'pratique',
                    'fields' => 'all'
                ));
                
                foreach ($users as $user) {
                    $pratiques = get_user_meta($user->ID, 'pratique', true);
                    $civilite = get_user_meta($user->ID, 'civilite', true);
                    $telephone = get_user_meta($user->ID, 'telephone', true);
                    
                    // S'assurer que $pratiques est un tableau
                    if (!is_array($pratiques)) {
                        $pratiques = $pratiques ? [$pratiques] : [];
                    }
                    
                    echo '<tr>';
                    echo '<td>' . $user->ID . '</td>';
                    echo '<td>' . esc_html($user->user_login) . '</td>';
                    echo '<td>' . esc_html($user->first_name . ' ' . $user->last_name) . '</td>';
                    echo '<td>' . esc_html($user->user_email) . '</td>';
                    echo '<td>';
                    
                    if (!empty($pratiques)) {
                        foreach ($pratiques as $pratique) {
                            echo '<span class="pratique-tag">' . esc_html($pratique) . '</span>';
                        }
                    } else {
                        echo '<em>Aucune pratique</em>';
                    }
                    
                    echo '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($user->user_registered)) . '</td>';
                    echo '</tr>';
                }
                
                // Afficher aussi les utilisateurs sans pratiques
                $users_sans_pratiques = get_users(array(
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'pratique',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'pratique',
                            'value' => '',
                            'compare' => '='
                        )
                    )
                ));
                
                foreach ($users_sans_pratiques as $user) {
                    echo '<tr style="opacity: 0.6;">';
                    echo '<td>' . $user->ID . '</td>';
                    echo '<td>' . esc_html($user->user_login) . '</td>';
                    echo '<td>' . esc_html($user->first_name . ' ' . $user->last_name) . '</td>';
                    echo '<td>' . esc_html($user->user_email) . '</td>';
                    echo '<td><em>Aucune pratique</em></td>';
                    echo '<td>' . date('d/m/Y', strtotime($user->user_registered)) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px;">
            <h2>Statistiques</h2>
            <?php
            // Statistiques des pratiques
            $stats_pratiques = array();
            $total_users_with_pratiques = 0;
            
            foreach ($users as $user) {
                $pratiques = get_user_meta($user->ID, 'pratique', true);
                if (!is_array($pratiques)) {
                    $pratiques = $pratiques ? [$pratiques] : [];
                }
                
                if (!empty($pratiques)) {
                    $total_users_with_pratiques++;
                    foreach ($pratiques as $pratique) {
                        if (!isset($stats_pratiques[$pratique])) {
                            $stats_pratiques[$pratique] = 0;
                        }
                        $stats_pratiques[$pratique]++;
                    }
                }
            }
            
            echo '<p><strong>Total d\'utilisateurs avec des pratiques :</strong> ' . $total_users_with_pratiques . '</p>';
            echo '<p><strong>Répartition par pratique :</strong></p>';
            echo '<ul>';
            foreach ($stats_pratiques as $pratique => $count) {
                echo '<li><strong>' . esc_html($pratique) . ' :</strong> ' . $count . ' utilisateur(s)</li>';
            }
            echo '</ul>';
            ?>
        </div>
    </div>
    <?php
}
?>