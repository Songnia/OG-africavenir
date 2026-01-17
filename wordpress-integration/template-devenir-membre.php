<?php
/**
 * Template Name: Page Devenir Membre
 * Description: Template personnalisé pour afficher la page "Devenir Membre"
 */

// Charger le header WordPress
get_header();
?>

<!-- Charger le CSS de la page membership -->
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/membership-page.css">

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <!-- Hero Section -->
        <section class="membership-hero">
            <div class="hero-content">
                <h1 class="hero-title">Devenez Membre d'AfricAvenir</h1>
                <p class="hero-subtitle">Rejoignez notre communauté et participez activement à la réalisation de notre mission</p>
            </div>
        </section>

        <!-- Introduction Section -->
        <section class="membership-intro">
            <div class="container">
                <h2>Pourquoi Devenir Membre ?</h2>
                <p class="intro-text">
                    En devenant membre d'AfricAvenir, vous intégrez une communauté dynamique et engagée. 
                    Vous bénéficiez d'opportunités de réseautage, d'accès à nos événements exclusifs, 
                    et vous contribuez directement à nos projets visant à promouvoir le développement 
                    et l'innovation en Afrique.
                </p>
            </div>
        </section>

        <!-- Membership Types Section -->
        <section class="membership-types">
            <div class="container">
                <h2 class="section-title">Types de Membership</h2>
                
                <!-- Liste des types de membres -->
                <div class="membership-list">
                    
                    <!-- Membre en Charge -->
                    <article class="membership-item">
                        <div class="membership-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                                <path d="M24 14L26.5 21.5L34 22L28 27.5L30 35L24 31L18 35L20 27.5L14 22L21.5 21.5L24 14Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="membership-content">
                            <h3 class="membership-title">Membre en Charge</h3>
                            <div class="membership-description">
                                <p>Les membres en charge jouent un rôle actif dans la gouvernance et le développement stratégique d'AfricAvenir.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Conditions d'Adhésion</h4>
                                <p>Expérience démontrée dans nos domaines de mission, disponibilité pour les réunions du conseil, cooptation par les membres actuels.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Avantages</h4>
                                <p>Droit de vote, participation aux comités de direction, accès privilégié aux événements, possibilité de représenter l'organisation.</p>
                            </div>
                            <div class="membership-pricing">
                                <span class="price-label">Contribution annuelle :</span>
                                <span class="price-amount">150 000 FCFA</span>
                            </div>
                            <a href="<?php echo home_url('/inscription?type=membre_charge'); ?>" class="btn-membership">Je deviens Membre en Charge</a>
                        </div>
                    </article>

                    <!-- Membre Régulier -->
                    <article class="membership-item">
                        <div class="membership-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                                <path d="M24 12C17.4 12 12 17.4 12 24C12 30.6 17.4 36 24 36C30.6 36 36 30.6 36 24C36 17.4 30.6 12 24 12ZM24 20C25.66 20 27 21.34 27 23C27 24.66 25.66 26 24 26C22.34 26 21 24.66 21 23C21 21.34 22.34 20 24 20ZM24 33C21 33 18.34 31.34 17 28.88C17.04 26.36 22 25 24 25C25.99 25 30.96 26.36 31 28.88C29.66 31.34 27 33 24 33Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="membership-content">
                            <h3 class="membership-title">Membre Régulier</h3>
                            <div class="membership-description">
                                <p>Les membres réguliers constituent le cœur de notre communauté et participent activement aux événements et initiatives.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Conditions d'Adhésion</h4>
                                <p>Ouvert à toute personne majeure partageant nos valeurs. Formulaire d'inscription et cotisation annuelle requis.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Avantages</h4>
                                <p>Accès aux événements, groupes de travail, newsletter exclusive, annuaire des membres, tarifs préférentiels.</p>
                            </div>
                            <div class="membership-pricing">
                                <span class="price-label">Contribution annuelle :</span>
                                <span class="price-amount">25 000 FCFA</span>
                            </div>
                            <a href="<?php echo home_url('/inscription?type=membre_regulier'); ?>" class="btn-membership">Je deviens Membre Régulier</a>
                        </div>
                    </article>

                    <!-- Membre d'Honneur -->
                    <article class="membership-item featured">
                        <div class="featured-badge">Reconnaissance Spéciale</div>
                        <div class="membership-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                                <path d="M24 8L27.5 18.5H38.5L29.5 25.5L33 36L24 29L15 36L18.5 25.5L9.5 18.5H20.5L24 8Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="membership-content">
                            <h3 class="membership-title">Membre d'Honneur</h3>
                            <div class="membership-description">
                                <p>Distinction réservée aux personnalités ayant apporté une contribution exceptionnelle à notre organisation.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Conditions d'Attribution</h4>
                                <p>Décerné par le conseil d'administration sur proposition de membres en charge. Engagement exceptionnel requis.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Privilèges</h4>
                                <p>Reconnaissance officielle, invitation consultative, exemption de cotisation, accès illimité, rôle d'ambassadeur.</p>
                            </div>
                            <div class="membership-pricing">
                                <span class="price-label">Contribution annuelle :</span>
                                <span class="price-amount">Exemption</span>
                            </div>
                            <a href="<?php echo home_url('/inscription?type=membre_honneur'); ?>" class="btn-membership btn-featured">Candidature sur Nomination</a>
                        </div>
                    </article>

                    <!-- Institution Membre -->
                    <article class="membership-item">
                        <div class="membership-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                                <rect x="14" y="16" width="20" height="18" stroke="currentColor" stroke-width="2" fill="none"/>
                                <path d="M18 20H30M18 24H30M18 28H26" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="membership-content">
                            <h3 class="membership-title">Institution Membre</h3>
                            <div class="membership-description">
                                <p>Pour les organisations, entreprises, universités et ONG souhaitant s'associer à notre mission.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Conditions d'Adhésion</h4>
                                <p>Organisation légalement constituée avec valeurs alignées. Désignation d'un représentant et dossier de candidature requis.</p>
                            </div>
                            <div class="membership-details">
                                <h4>Avantages</h4>
                                <p>Visibilité sur nos plateformes, co-organisation d'événements, accès aux rapports, réseau de partenaires stratégiques.</p>
                            </div>
                            <div class="membership-pricing">
                                <span class="price-label">Contribution annuelle :</span>
                                <span class="price-amount">500 000 FCFA</span>
                            </div>
                            <a href="<?php echo home_url('/inscription?type=institution'); ?>" class="btn-membership">Devenir Institution Membre</a>
                        </div>
                    </article>

                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="membership-cta">
            <div class="container">
                <h2>Prêt à nous rejoindre ?</h2>
                <p>Choisissez le type de membership qui vous convient et faites partie de notre aventure.</p>
                <a href="<?php echo home_url('/inscription'); ?>" class="btn-cta-large">Commencer mon inscription</a>
            </div>
        </section>

    </main>
</div>

<?php
// Charger le footer WordPress
get_footer();
?>
