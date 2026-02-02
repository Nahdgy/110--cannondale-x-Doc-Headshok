// Réinitialise le fil d'Ariane sur la page d'accueil
function reset_fil_ariane_on_home() {
    if (!is_admin()) {
        if (!session_id()) { session_start(); }
        if (is_front_page() && isset($_SESSION['fil_ariane'])) {
            unset($_SESSION['fil_ariane']);
        }
    }
}
add_action('wp', 'reset_fil_ariane_on_home');
<?php
// Fil d'Ariane évolutif basé sur l'historique de navigation (session)
function fil_d_ariane_contextuel() {
    if (!is_admin()) {
        if (!session_id()) { session_start(); }
        if (!isset($_SESSION['fil_ariane'])) { $_SESSION['fil_ariane'] = []; }
        $current_id = get_the_ID();
        // Si la page courante est déjà dans le fil, on gère le retour arrière
        $prev_url_raw = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $prev_id = false;
        if ($prev_url_raw) {
            foreach ($_SESSION['fil_ariane'] as $key => $id) {
                if (get_permalink($id) == $prev_url_raw) {
                    $prev_id = $id;
                    break;
                }
            }
        }
        if ($current_id && in_array($current_id, $_SESSION['fil_ariane'])) {
            // Si la page précédente est la dernière du fil, on la retire
            if ($prev_id && end($_SESSION['fil_ariane']) == $prev_id) {
                array_pop($_SESSION['fil_ariane']);
            }
            // On ajoute la page courante si ce n'est pas déjà la dernière
            if (end($_SESSION['fil_ariane']) != $current_id) {
                $_SESSION['fil_ariane'][] = $current_id;
            }
        } elseif ($current_id) {
            $_SESSION['fil_ariane'][] = $current_id;
        }
        // Déterminer l'URL de la page précédente
        $prev_url = isset($_SERVER['HTTP_REFERER']) ? esc_url($_SERVER['HTTP_REFERER']) : '';
        echo '<nav class="fil-ariane"><ul style="display:flex;align-items:center;gap:8px;">';
        // Flèche retour au début
        if ($prev_url) {
            echo '<li><a href="' . $prev_url . '" style="display:flex;align-items:center;color:#FF3F22;font-weight:700;text-decoration:none;"><span style="font-size:18px;margin-right:6px;">&#8592;</span>Retour</a></li>';
        }
        foreach ($_SESSION['fil_ariane'] as $i => $id) {
            $title = get_the_title($id);
            $url = get_permalink($id);
            echo '<li><a href="' . esc_url($url) . '" style="color:#FF3F22;font-weight:700;text-decoration:none;">' . esc_html($title) . '</a>';
            if ($i < count($_SESSION['fil_ariane']) - 1) {
                echo ' <span style="color:#FF3F22;">&rsaquo;</span> ';
            }
            echo '</li>';
        }
        echo '</ul></nav>';
    }
}
// Shortcode pour insérer le fil d'Ariane où tu veux
add_shortcode('fil_ariane', 'fil_d_ariane_contextuel');