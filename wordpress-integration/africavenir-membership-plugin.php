<?php
/**
 * Plugin Name: AfricAvenir Membership Page
 * Plugin URI: https://africavenir-international.org
 * Description: Affiche le contenu de la page "Devenir Membre" via un shortcode [devenir_membre]
 * Version: 1.0
 * Author: SONGNIA WILFRIED
 * Author URI: https://africavenir-international.org
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode pour afficher la page membership
 * Usage: [devenir_membre]
 */
function africavenir_membership_shortcode($atts) {
    // Enqueue le CSS si ce n'est pas déjà fait
    wp_enqueue_style(
        'africavenir-membership',
        plugins_url('css/membership-page.css', __FILE__),
        array(),
        '1.0.0'
    );
    
    // Démarrer la capture de sortie
    ob_start();
    ?>
    
    <!-- Hero Section -->
    <section class="membership-hero">
        <div class="hero-content">
            <h1 class="hero-title">Soutenir la Fondation AfricAvenir en devenant membre</h1>
            <p class="hero-subtitle">Engagez vous et faites partie de cette belle aventure??? </p>
            <a href="#types-membership" class="btn-cta-large" style="padding: 15px 40px; font-size: 1em; margin-top: 20px;">C'est partie!</a>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="membership-intro">
        <div class="intro-container">
            <div class="intro-content">
                <h2>Qui peut devenir membre?</h2>
                <p class="intro-text">
                    Vous êtes passionné de l'Afrique, sa culture, sa science et ses savoirs,
                    ses peuples haut en couleurs /chaleurs, de coopération internationale et de paix durable et équitable dans le monde... 
                    Vous aimez nos publications, livres, vidéos spots et autres fora de partage et d'échange Vous aspirez à ne pas être simple spectateur de la construction d'une trajectoire dynamique et florissante pour l'Afrique et le monde entier  Vous être passionné de découvertes, de partage solidaire et de vivre ensemble interculturelle respectueux ......  .Faites partie de l'aventure et rejoignez nous en tant que membre!
                </p>
                <p class="intro-text" style="margin-top: 20px;">
                    Votre adhésion soutient nos programmes éducatifs, nos initiatives culturelles et nos projets de recherche. 
                    Ensemble, nous construisons un avenir meilleur.
                </p>
            </div>
            <div class="intro-image">
                <img src="https://africavenir-international.org/wp-content/uploads/2025/08/IMG-20250731-WA0087.jpg" alt="Communauté AfricAvenir">
            </div>
        </div>
    </section>

    <!-- Membership Types Section -->
    <section class="membership-types" id="types-membership">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; font-size: 2.5em; color: #1e2128;">Nos Formules d'Adhésion</h2>
            
            <!-- Liste des types de membres -->
            <div class="membership-list">
                
                <!-- 1. Membre en charge (Élèves, Étudiants) -->
                <article class="membership-item">
                    <div class="membership-icon">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 4C12.95 4 4 12.95 4 24C4 35.05 12.95 44 24 44C35.05 44 44 35.05 44 24C44 12.95 35.05 4 24 4ZM24 10C26.76 10 29 12.24 29 15C29 17.76 26.76 20 24 20C21.24 20 19 17.76 19 15C19 12.24 21.24 10 24 10ZM34 34H14V32C14 28.69 20.69 27 24 27C27.31 27 34 28.69 34 32V34Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="membership-content">
                        <h3 class="membership-title">Membre en charge</h3>
                        <p style="text-align: center; color: var(--text-muted); margin-top: -10px; margin-bottom: 15px;">(Élèves, Étudiants)</p>
                        
                        <div class="membership-description">
                            <p>
                                Tarif préférentiel pour les étudiants et élèves souhaitant s'engager.
                            </p>
                        </div>

                        <div class="membership-pricing">
                            <span class="price-label">Contribution annuelle</span>
                            <span class="price-amount">15 000 FCFA</span>
                            <span class="price-label" style="margin-top: 5px;">25 EUR / 30 US$</span>
                        </div>

                        <a href="signup.php?type=membre_charge" class="btn-membership">Je deviens Membre</a>
                    </div>
                </article>

                <!-- 2. Membre Régulier -->
                <article class="membership-item featured">
                    <div class="featured-badge">Populaire</div>
                    <div class="membership-icon">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                            <path d="M24 12C17.4 12 12 17.4 12 24C12 30.6 17.4 36 24 36C30.6 36 36 30.6 36 24C36 17.4 30.6 12 24 12ZM24 20C25.66 20 27 21.34 27 23C27 24.66 25.66 26 24 26C22.34 26 21 24.66 21 23C21 21.34 22.34 20 24 20ZM24 33C21 33 18.34 31.34 17 28.88C17.04 26.36 22 25 24 25C25.99 25 30.96 26.36 31 28.88C29.66 31.34 27 33 24 33Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="membership-content">
                        <h3 class="membership-title">Membre Régulier</h3>
                        
                        <div class="membership-description">
                            <p>
                                Le statut standard pour soutenir activement la fondation.
                            </p>
                        </div>

                        <div class="membership-pricing">
                            <span class="price-label">Contribution annuelle</span>
                            <span class="price-amount">30 000 FCFA</span>
                            <span class="price-label" style="margin-top: 5px;">50 EUR / 60 US$</span>
                        </div>

                        <a href="signup.php?type=membre_regulier" class="btn-membership btn-featured">Je deviens Membre Régulier</a>
                    </div>
                </article>

                <!-- 3. Membre d'Honneur -->
                <article class="membership-item">
                    <div class="membership-icon">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 8L27.5 18.5H38.5L29.5 25.5L33 36L24 29L15 36L18.5 25.5L9.5 18.5H20.5L24 8Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="membership-content">
                        <h3 class="membership-title">Membre d'Honneur</h3>
                        
                        <div class="membership-description">
                            <p>
                                Pour ceux qui souhaitent apporter un soutien exceptionnel.
                            </p>
                        </div>

                        <div class="membership-pricing">
                            <span class="price-label">Contribution annuelle</span>
                            <span class="price-amount">100 000 FCFA</span>
                            <span class="price-label" style="margin-top: 5px;">160 EUR / 180 US$</span>
                        </div>

                        <a href="signup.php?type=membre_honneur" class="btn-membership">Je deviens Membre d'Honneur</a>
                    </div>
                </article>

                <!-- 4. Institution Membre -->
                <article class="membership-item">
                    <div class="membership-icon">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="14" y="16" width="20" height="18" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M18 20H30M18 24H30M18 28H26" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="membership-content">
                        <h3 class="membership-title">Institution Membre</h3>
                        
                        <div class="membership-description">
                            <p>
                                Pour les organisations et entreprises partenaires.
                            </p>
                        </div>

                        <div class="membership-pricing">
                            <span class="price-label">Contribution annuelle</span>
                            <span class="price-amount">200 000 FCFA</span>
                            <span class="price-label" style="margin-top: 5px;">310 EUR / 350 US$</span>
                        </div>

                        <a href="signup.php?type=institution" class="btn-membership">Devenir Institution Membre</a>
                    </div>
                </article>

            </div>
        </div>
    </section>

    <!-- Privilèges Section -->
    <section class="membership-privileges">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 40px; color: #1e2128;">Privilèges des membres</h2>
            <div class="privileges-list">
                <ul>
                    <li>
                        <span>✓</span>
                        Vous recevez online le Magazine AfricAvenir (français et anglais)
                    </li>
                    <li>
                        <span>✓</span>
                        Vous recevez le AfricAvenir Newsletter de la section de Berlin, si vous lisez l’allemand
                    </li>
                    <li>
                        <span>✓</span>
                        Vous recevez automatiquement les invitations à nos manifestations au Cameroun, en Allemagne, en France et en Autriche, selon votre zone de résidence
                    </li>
                    <li>
                        <span>✓</span>
                        Vous aurez accès à prix réduits aux manifestations organisées par le siège de la Fondation AfricAvenir International à Douala, ou par les sections AfricAvenir International à Berlin, Paris et Vienne
                    </li>
                    <li>
                        <span>✓</span>
                        Vous êtes mis au courant de nouvelles acquisitions de la Bibliothèque Cheikh Anta Diop à Douala, de la librairie-galerie d’art Le Génie Africain, à Douala, et de nouvelles parutions des Editions AfricAvenir
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <!-- Call to Action Section -->
    <section class="membership-cta">
        <div class="container">
            <h2>Prêt à nous rejoindre ?</h2>
            <p style="font-size: 1.1em; color: #666; margin-bottom: 30px; max-width: 800px; margin-left: auto; margin-right: auto;">
                Vous pouvez devenir membre en vous inscrivant directement sur notre site via le formulaire en ligne et effectuer votre paiement de manière sécurisée.
            </p>
            <a href="signup.php" class="btn-cta-large">Commencer mon inscription</a>
        </div>
    </section>
    <!-- Payment Methods Section -->
    <section class="membership-payment">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 20px; color: #1e2128;">Modalités de Paiement</h2>
            <p style="text-align: center; color: #666; margin-bottom: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">
                Le paiement peut également s'effectuer par virement bancaire et autres moyens listés ci-dessous.
            </p>
            
            <div class="payment-methods-grid">
                
                <div class="payment-method-card">
                    <h3>Paiement Mobile</h3>
                    <p><strong>Orange Money:</strong> #150*47*831733#</p>
                    <p><strong>Mobile Money:</strong> *126*4*351616*montant#</p>
                </div>

                <div class="payment-method-card">
                    <h3>Virement Bancaire - Cameroun</h3>
                    <div style="margin-bottom: 15px;">
                        <strong>SCB – Société Camerounaise de Banque</strong><br>
                        Compte: 90000647024 | Clé: 89<br>
                        Agence: 00086 | Code Banque: 10002
                    </div>
                    <div>
                        <strong>UBA Cameroun</strong><br>
                        Compte: 10011000888 | Clé: 61<br>
                        Agence: 05210 | Code Banque: 10033
                    </div>
                </div>

                <div class="payment-method-card">
                    <h3>Virement Bancaire - Europe</h3>
                    <div>
                        <strong>Société Générale / SG Paris Saint Michel</strong><br>
                        IBAN: FR76 3000 3030 8500 0372 6173 851<br>
                        BIC: SOGEFRPP<br>
                        Titulaire: AFRICAVENIR INTERNATIONAL
                    </div>
                </div>

                 <div class="payment-method-card">
                    <h3>Autres Moyens</h3>
                    <p><strong>Espèces:</strong> Sur place au Siège de la Fondation (Bonabéri, Douala)</p>
                    <p><strong>Western Union / Money Gram:</strong> Nous contacter au +237 695559844</p>
                </div>
            </div>
            
            <div class="payment-note">
                <p>Bien vouloir exiger le reçu et une copie originale de la fiche de donateur après validation de tous types de paiements.</p>
            </div>
        </div>
    </section>

    <?php
    // Retourner le contenu capturé
    return ob_get_clean();
}

// Enregistrer le shortcode
add_shortcode('devenir_membre', 'africavenir_membership_shortcode');

/**
 * Enregistrer le style CSS du plugin
 */
function africavenir_membership_enqueue_styles() {
    // Le CSS sera chargé seulement quand le shortcode est utilisé
}
add_action('wp_enqueue_scripts', 'africavenir_membership_enqueue_styles');
