<?php
/**
 * Shortcode pour la section "Who Are We" avec compteurs animés
 * Usage: [whoarewe_counter]
 */

function whoarewe_counter_shortcode($atts) {
    // Paramètres par défaut
    $atts = shortcode_atts(array(
        'border_color' => '#ff3f21',
        'background_color' => '#f5f5f5',
    ), $atts);
    
    // Commencer la capture de sortie
    ob_start();
    ?>
    
    <style>
        .grid-whoarewecountersection {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, auto);
            gap: 2rem;
            text-align: center;
            margin: 4rem 0;
            border: 2px solid <?php echo esc_attr($atts['border_color']); ?>;
            border-radius: 10px;
            padding: 2rem;
        }
        
        .whomAmI h3 {
            font-family: Helvetica, sans-serif;
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        
        .whomAmI p {
            font-family: din-next-lt-pro, Arial, sans-serif;
            font-weight: 300;
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
            color: #666;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .grid-whoarewecountersection {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(3, auto);
                gap: 1.5rem;
                margin: 2rem 0;
                padding: 1rem;
            }
            
            .whomAmI {
                padding: 1.5rem;
            }
            
            .whomAmI h3 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .grid-whoarewecountersection {
                grid-template-columns: 1fr;
                grid-template-rows: repeat(6, auto);
                gap: 1rem;
            }
            
            .whomAmI h3 {
                font-size: 1.3rem;
            }
        }
    </style>

    <div class="grid-whoarewecountersection">
        <div class="whomAmI">
            <h3>+ de 25 ans</h3>
            <p>d'expérience dans le domaine du vélo et des fourches</p>
        </div>
                        
        <div class="whomAmI">
            <h3>4 professionnels</h3>
            <p>à votre écoute</p>
        </div>
        
        <div class="whomAmI">
            <h3>110 %</h3>
            <p>passionnés</p>
        </div>
        
        <div class="whomAmI">
            <h3>+ de 10 000</h3>
            <p>interventions à l'atelier ou sur les évènements</p>
        </div>
        
        <div class="whomAmI">
            <h3>5-7 jours</h3>
            <p>de délai de traitement en moyenne</p>
        </div>
        
        <div class="whomAmI">
            <h3>95 %</h3>
            <p>de clients satisfaits</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éviter les conflits avec d'autres scripts
        if (window.whoareweCounterInitialized) return;
        window.whoareweCounterInitialized = true;
        
        // Fonction pour animer un compteur
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16); // 60fps
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    start = target;
                    clearInterval(timer);
                }
                
                // Utiliser la nouvelle fonction de remplacement
                const originalText = element.dataset.originalText;
                element.textContent = replaceNumberInText(originalText, Math.floor(start));
            }, 16);
        }
        
        // Fonction pour extraire et parser les nombres
        function extractNumber(text) {
            const patterns = [
                /(\d+)\s*%/, // pourcentage (95 %)
                /\+\s*de\s*([\d\s]+)/, // + de 25 ans, + de 10 000
                /([\d\s]+)\s*-\s*\d+/, // 5-7 jours
                /(\d+)/ // nombres simples
            ];
            
            for (let pattern of patterns) {
                const match = text.match(pattern);
                if (match) {
                    const cleanNumber = match[1].replace(/\s/g, '');
                    return parseInt(cleanNumber);
                }
            }
            return 0;
        }
        
        // Fonction pour remplacer le nombre dans le texte
        function replaceNumberInText(originalText, newNumber) {
            if (originalText.includes('+ de')) {
                if (originalText.includes('10 000')) {
                    return originalText.replace(/10\s*000/, newNumber.toLocaleString('fr-FR'));
                } else if (originalText.includes('25')) {
                    return originalText.replace(/25/, newNumber);
                } else {
                    return originalText.replace(/\d+/, newNumber);
                }
            } else if (originalText.includes('%')) {
                return originalText.replace(/\d+/, newNumber);
            } else if (originalText.includes('-')) {
                return originalText.replace(/^\d+/, newNumber);
            } else {
                return originalText.replace(/\d+/, newNumber);
            }
        }
        
        // Observer pour détecter quand l'élément entre dans la vue
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.animated) {
                    const h3 = entry.target.querySelector('h3');
                    if (h3) {
                        const originalText = h3.textContent.trim();
                        const targetNumber = extractNumber(originalText);
                        
                        if (targetNumber > 0) {
                            h3.dataset.originalText = originalText;
                            h3.dataset.animated = 'true';
                            entry.target.dataset.animated = 'true';
                            
                            setTimeout(() => {
                                animateCounter(h3, targetNumber, 2500);
                            }, Math.random() * 500);
                        }
                    }
                }
            });
        }, { threshold: 0.3 });
        
        // Observer tous les éléments .whomAmI
        document.querySelectorAll('.whomAmI').forEach(element => {
            observer.observe(element);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

// Enregistrer le shortcode
add_shortcode('whoarewe_counter', 'whoarewe_counter_shortcode');

// Ajouter une fonction pour personnaliser les couleurs
function whoarewe_counter_with_colors($border_color = '#ff3f21', $bg_color = '#f5f5f5') {
    return whoarewe_counter_shortcode(array(
        'border_color' => $border_color,
        'background_color' => $bg_color
    ));
}
?>