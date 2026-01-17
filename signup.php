<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/src/Controllers/MemberController.php';
// No admin check here - this is public
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inscription membre AfricAvenir">
    <title>AfricAvenir | Inscription</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <style>
        /* Specific styles for signup to match auth layout but accommodate wizard */

        .wizard-steps {
            margin-bottom: 30px;
        }
        /* Ensure form elements look good in auth container */
        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .wizard-nav {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .required-asterisk {
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <header class="auth-header">
            <img src="assets/logo-africavenir.png" alt="Image décorative AfricAvenir" class="decorative-image">
        </header>

        <main class="auth-main">
            <a href="index.php" class="back-link">< Retour à la connexion</a>
            <h1>Je deviens membre</h1>
            <p class="form-instruction">Rejoignez la communauté AfricAvenir</p>
            
            <div class="wizard-steps">
                <div class="step-indicator active" data-step="1">01</div>
                <div class="step-connector"></div>
                <div class="step-indicator" data-step="2">02</div>
                <div class="step-connector"></div>
                <div class="step-indicator" data-step="3">03</div>
            </div>

            <form id="registerMemberForm" action="#" method="post">
                <!-- Étape 1: Informations Personnelles & Contact -->
                <div class="wizard-step active" id="step1">
                    <h3 class="step-title">INFORMATIONS PERSONNELLES</h3>
                    <div class="form-row">
                        <div class="form-group"><label for="regNom">Nom<span class="required-asterisk">*</span></label><input type="text" id="regNom" name="nom" required></div>
                        <div class="form-group"><label for="regPrenom">Prénom<span class="required-asterisk">*</span></label><input type="text" id="regPrenom" name="prenom" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regDateNaissance">Date de naissance<span class="required-asterisk">*</span></label><input type="date" id="regDateNaissance" name="date_naissance" required></div>
                        <div class="form-group"><label for="regEtatCivil">État civil<span class="required-asterisk">*</span></label>
                            <select id="regEtatCivil" name="etat_civil" required>
                                <option value="" disabled selected>Choisir...</option>
                                <option value="celibataire">Célibataire</option>
                                <option value="marie">Marié(e)</option>
                                <option value="en-couple">En Couple</option>
                                <option value="divorce">Divorcé(e)</option>
                                <option value="veuf">Veuf(ve)</option>
                            </select>
                        </div>
                    </div>
                    <h3 class="step-title">CONTACT</h3>
                    <div class="form-row">
                        <div class="form-group"><label for="regAdresse">Adresse</label><input type="text" id="regAdresse" name="adresse"></div>
                        <div class="form-group"><label for="regBoitePostale">Boîte postale</label><input type="text" id="regBoitePostale" name="boite_postale" value="<?php echo '0000'; ?>" placeholder="0000"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regPays">Pays<span class="required-asterisk">*</span></label><input type="text" id="regPays" name="pays" required></div>
                        <div class="form-group"><label for="regQuartier">Quartier</label><input type="text" id="regQuartier" name="quartier"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regVille">Ville<span class="required-asterisk">*</span></label><input type="text" id="regVille" name="ville" required></div>
                        <div class="form-group"><label for="regTelephone">Téléphone<span class="required-asterisk">*</span></label><input type="tel" id="regTelephone" name="telephone" required></div>
                    </div>
                    <div class="form-group">
                        <label for="regEmail">Email<span class="required-asterisk">*</span></label><input type="email" id="regEmail" name="email" required>
                    </div>
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-secondary wizard-back" disabled>< Back</button>
                        <button type="button" class="btn btn-primary wizard-next">Next ></button>
                    </div>
                </div>

                <!-- Étape 2: Parcours -->
                <div class="wizard-step" id="step2">
                    <h3 class="step-title">PARCOURS</h3>
                    <div class="form-group radio-group">
                        <label><input type="radio" name="parcours_type" value="ancien_membre_fondation"> Je suis un ancien membre la fondation AfricAvenir</label>
                        <label><input type="radio" name="parcours_type" value="ancien_etudiant_prince"> Je suis un ancien étudiant du Prince</label>
                        <label><input type="radio" name="parcours_type" value="autres" checked> Autres</label>
                    </div>
                    <div class="form-group">
                        <label for="regEtablissement">Dernier établissement scolaire</label>
                        <input type="text" id="regEtablissement" name="etablissement_scolaire">
                    </div>
                    <div class="form-group">
                        <label for="regDiplome">Dernier diplôme</label>
                        <input type="text" id="regDiplome" name="dernier_diplome">
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regStatutPro">Statut<span class="required-asterisk">*</span></label>
                            <select id="regStatutPro" name="statut_professionnel" required>
                                <option value="" disabled selected>Choisir...</option>
                                <option value="etudiant">Étudiant</option>
                                <option value="salarie">Salarié</option>
                                <option value="fonctionnaire">Fonctionnaire</option>
                                <option value="independant">Indépendant</option>
                                <option value="entrepreneur">Entrepreneur</option>
                                <option value="stagiaire">Stagiaire</option>
                                <option value="chercheur_emploi">Chercheur d'emploi</option>
                                <option value="sans_emploi">Sans emploi</option>
                                <option value="retraite">Retraité</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group"><label for="regTypeActivite">Type d'activité<span class="required-asterisk">*</span></label>
                            <input type="text" id="regTypeActivite" name="type_activite" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="regCentreInteret">Centre d'intérêt<span class="required-asterisk">*</span></label>
                        <select id="regCentreInteret" name="centre_interet" required>
                            <option value="" disabled selected>Choisir...</option>
                            <option value="arts-et-culture">Arts et Culture</option>
                            <option value="ateliers-et-seminaires">Ateliers et Séminaires</option>
                            <option value="bibliotheque">Bibliothèque</option>
                            <option value="cabinet-dexpertise">Cabinet d'Expertise</option>
                            <option value="cinema-africain">Cinéma Africain</option>
                            <option value="conferences">Conférences</option>
                            <option value="conseil-d-administration">Conseil d'Administration</option>
                            <option value="sport">Sport</option>
                            <option value="technologie">Technologie</option>
                            <option value="education">Éducation</option>
                            <option value="environnement">Environnement</option>
                            <option value="sante">Santé</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-secondary wizard-back">< Back</button>
                        <button type="button" class="btn btn-primary wizard-next">Next ></button>
                    </div>
                </div>

                <!-- Étape 3: Contribution & Paiement -->
                <div class="wizard-step" id="step3">
                    <h3 class="step-title">MONTANT DE LA CONTRIBUTION</h3>
                    <div class="form-group radio-group-horizontal">
                        <label><input type="radio" name="montant_contribution" value="15000"> 15000F</label>
                        <label><input type="radio" name="montant_contribution" value="30000"> 30000F</label>
                        <label><input type="radio" name="montant_contribution" value="100000"> 100000F</label>
                        <label><input type="radio" name="montant_contribution" value="libre"> Libre</label>
                        <input type="number" id="regMontantLibreVal" name="montant_libre_val" placeholder="Entrez montant" style="display:none; margin-left:10px; width: auto;">
                    </div>
                    <h3 class="step-title">MODE DE PAIEMENT</h3>
                    <div class="form-group radio-group-horizontal">
                        <label><input type="radio" name="mode_paiement_contribution" value="mobile" checked> Paiement mobile</label>
                        <label><input type="radio" name="mode_paiement_contribution" value="carte"> Carte de crédit</label>
                        <label><input type="radio" name="mode_paiement_contribution" value="paypal"> Paypal</label>
                        <!-- <label><input type="radio" name="mode_paiement_contribution" value="virement"> Virement bancaire</label> -->
                        <!-- <label><input type="radio" name="mode_paiement_contribution" value="cash"> Cash</label> -->
                    </div>
                    
                    <!-- Conteneur pour le bouton PayPal -->
                    <div id="paypal-button-container" style="display: none; margin-top: 20px; max-width: 400px;"></div>
                    
                    <div id="paypal-info" style="display: none; margin-top: 15px; padding: 15px; background-color: var(--bg-secondary); border-left: 4px solid #0070ba; border-radius: 4px;">
                        <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                            <strong>ℹ️ Paiement PayPal:</strong> Cliquez sur le bouton PayPal ci-dessous pour procéder au paiement sécurisé.
                        </p>
                    </div>
                    
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-secondary wizard-back">< Back</button>
                        <button type="submit" class="btn btn-primary wizard-finish">proceder au paiement</button>
                    </div>
                </div>

                <!-- Étape 4: Confirmation avec Identifiants -->
                <div class="wizard-step" id="step4" style="text-align: center;">
                    <img src="assets/icons/success-check.svg" alt="Succès" style="width: 80px; margin-bottom: 20px;">
                    <h3 class="step-title" style="font-size: 1.8em;">Enregistrement Terminé</h3>
                    <p>Merci, votre inscription a été enregistrée avec succès.</p>
                    
                    <!-- Zone d'affichage des identifiants -->
                    <div id="credentialsDisplay" style="display: none; margin: 30px auto; max-width: 500px;">
                        <div style="background-color: var(--bg-secondary); border: 2px solid var(--africavenir-yellow); border-radius: 8px; padding: 25px; text-align: left;">
                            <h4 style="margin-top: 0; color: var(--africavenir-yellow); text-align: center;">🔐 Vos Identifiants de Connexion</h4>
                            <p style="text-align: center; color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px;">
                                Veuillez noter ces informations précieusement.
                            </p>
                            
                            <div style="margin: 15px 0;">
                                <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Nom d'utilisateur:</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" id="displayUsername" readonly style="flex: 1; padding: 10px; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                    <button type="button" onclick="copyToClipboard('displayUsername', this)" class="btn btn-secondary" style="padding: 10px 15px;">
                                        📋 Copier
                                    </button>
                                </div>
                            </div>
                            
                            <div style="margin: 15px 0;">
                                <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Mot de passe:</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" id="displayPassword" readonly style="flex: 1; padding: 10px; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                    <button type="button" onclick="copyToClipboard('displayPassword', this)" class="btn btn-secondary" style="padding: 10px 15px;">
                                        📋 Copier
                                    </button>
                                </div>
                            </div>
                            
                            <div style="background-color: var(--bg-warning-light); border-left: 4px solid var(--color-warning); padding: 15px; margin-top: 20px; border-radius: 4px;">
                                <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                    <strong>⚠️ Important:</strong> Ces identifiants ne seront affichés qu'une seule fois. 
                                    Copiez-les maintenant.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="index.php" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
                                    🔗 Se connecter maintenant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <footer class="site-footer auth-footer">
        <p>©2025 AfricAvenir tous droits réservés</p>
    </footer>

    <script>
        // Fonction pour copier dans le presse-papier
        function copyToClipboard(inputId, button) {
            const input = document.getElementById(inputId);
            input.select();
            input.setSelectionRange(0, 99999); // Pour mobile
            
            try {
                document.execCommand('copy');
                const originalText = button.textContent;
                button.textContent = '✓ Copié!';
                button.style.backgroundColor = 'var(--color-success)';
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.backgroundColor = '';
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie');
            }
        }
    </script>
    
    <!-- Charger le SDK PayPal -->
    <script src="https://www.paypal.com/sdk/js?client-id=AbVmr3bh_YOJU5KtyvxU_FO_hUhq0C7maKEpnfoUWOzCf-KtfrC-GEK9avkmJnDxHMmGAXJniyLBmd7f&currency=EUR"></script>
    <!-- Charger le SDK Stripe -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <script src="js/modal-utils.js"></script>
    <script src="js/paypal-handler.js"></script>
    <script src="js/paypal-integration.js"></script>
    <script src="js/stripe-handler.js"></script>
    <script src="js/stripe-integration.js"></script>
    <script src="js/main.js"></script>
</body>
</html>