<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/src/Controllers/MemberController.php';
require_once __DIR__ . '/src/Helpers/RoleHelper.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !\App\Helpers\RoleHelper::isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$member = null;
$isEdit = false;
if (isset($_GET['id'])) {
    $controller = new \App\Controllers\MemberController();
    $response = $controller->getMember($_GET['id']);
    if ($response['success']) {
        $member = $response['data'];
        $isEdit = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<!-- Include Meta Data -->
 <?php include('includes/meta-data.php'); ?>
<body>
    <div class="page-container">
        <!-- Include Header-->
        <?php include('includes/header.php'); ?>

        <div class="main-content">
            <header class="main-header">
                <button class="mobile-menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="hamburger-icon"></span>
                </button>
                <h1><?php echo $isEdit ? 'MODIFIER MEMBRE' : 'ENREGISTRER UN MEMBRE'; ?></h1>
                <button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Changer de thème">
                  <img src="assets/icons/moon.svg" alt="Thème sombre" id="themeIcon">
                </button>
            </header>

            <main>
                <div class="form-container card wizard-container">
                    <h2 class="form-title"><?php echo $isEdit ? 'Modifier les informations' : 'Enregistrer un membre'; ?></h2>
                    
                    <div class="wizard-steps">
                        <div class="step-indicator active" data-step="1">01</div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="2">02</div>
                        <div class="step-connector"></div>
                        <div class="step-indicator" data-step="3">03</div>
                    </div>

                    <form id="registerMemberForm" action="#" method="post">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $member['ID']; ?>">
                            <input type="hidden" name="action" value="update">
                        <?php endif; ?>
                        
                        <!-- Étape 1: Informations Personnelles & Contact -->
                        <div class="wizard-step active" id="step1">
                            <h3 class="step-title">INFORMATIONS PERSONNELLES</h3>
                            <div class="form-row">
                                <div class="form-group"><label for="regNom">Nom</label><input type="text" id="regNom" name="nom" value="<?php echo $member['last_name'] ?? ''; ?>" required></div>
                                <div class="form-group"><label for="regPrenom">Prénom</label><input type="text" id="regPrenom" name="prenom" value="<?php echo $member['first_name'] ?? ''; ?>" required></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label for="regDateNaissance">Date de naissance</label><input type="date" id="regDateNaissance" name="date_naissance" value="<?php echo $member['date_naissance'] ?? ''; ?>" required></div>
                                <div class="form-group"><label for="regEtatCivil">État civil</label>
                                    <select id="regEtatCivil" name="etat_civil" required>
                                        <option value="" disabled <?php echo empty($member['etat_civil']) ? 'selected' : ''; ?>>Choisir...</option>
                                        <option value="celibataire" <?php echo ($member['etat_civil'] ?? '') == 'celibataire' ? 'selected' : ''; ?>>Célibataire</option>
                                        <option value="marie" <?php echo ($member['etat_civil'] ?? '') == 'marie' ? 'selected' : ''; ?>>Marié(e)</option>
                                        <option value="divorce" <?php echo ($member['etat_civil'] ?? '') == 'divorce' ? 'selected' : ''; ?>>Divorcé(e)</option>
                                        <option value="veuf" <?php echo ($member['etat_civil'] ?? '') == 'veuf' ? 'selected' : ''; ?>>Veuf(ve)</option>
                                    </select>
                                </div>
                            </div>
                            <h3 class="step-title">CONTACT</h3>
                            <div class="form-row">
                                <div class="form-group"><label for="regAdresse">Adresse</label><input type="text" id="regAdresse" name="adresse" value="<?php echo $member['adresse'] ?? ''; ?>"></div>
                                <div class="form-group"><label for="regBoitePostale">Boîte postale</label><input type="text" id="regBoitePostale" name="boite_postale" value="<?php echo $member['boite_postale'] ?? ''; ?>"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label for="regPays">Pays</label><input type="text" id="regPays" name="pays" value="<?php echo $member['pays'] ?? ''; ?>"></div>
                                <div class="form-group"><label for="regQuartier">Quartier</label><input type="text" id="regQuartier" name="quartier" value="<?php echo $member['quartier'] ?? ''; ?>"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label for="regVille">Ville</label><input type="text" id="regVille" name="ville" value="<?php echo $member['ville'] ?? ''; ?>"></div>
                                <div class="form-group"><label for="regTelephone">Téléphone</label><input type="tel" id="regTelephone" name="telephone" value="<?php echo $member['telephone'] ?? ''; ?>" required></div>
                            </div>
                            <div class="form-group">
                                <label for="regEmail">Email</label><input type="email" id="regEmail" name="email" value="<?php echo $member['user_email'] ?? ''; ?>" required>
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
                                <label><input type="radio" name="parcours_type" value="ancien_membre_fondation" <?php echo ($member['parcours_type'] ?? '') == 'ancien_membre_fondation' ? 'checked' : ''; ?>> Je suis un ancien membre la fondation AfricAvenir</label>
                                <label><input type="radio" name="parcours_type" value="ancien_etudiant_prince" <?php echo ($member['parcours_type'] ?? '') == 'ancien_etudiant_prince' ? 'checked' : ''; ?>> Je suis un ancien étudiant du Prince</label>
                                <label><input type="radio" name="parcours_type" value="autres" <?php echo ($member['parcours_type'] ?? 'autres') == 'autres' ? 'checked' : ''; ?>> Autres</label>
                            </div>
                            <div class="form-group">
                                <label for="regEtablissement">Dernier établissement scolaire</label>
                                <input type="text" id="regEtablissement" name="etablissement_scolaire" value="<?php echo $member['etablissement_scolaire'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="regDiplome">Dernier diplôme</label>
                                <input type="text" id="regDiplome" name="dernier_diplome" value="<?php echo $member['dernier_diplome'] ?? ''; ?>">
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label for="regStatutPro">Statut</label>
                                    <select id="regStatutPro" name="statut_professionnel">
                                        <option value="" disabled <?php echo empty($member['statut_professionnel']) ? 'selected' : ''; ?>>Choisir...</option>
                                        <option value="etudiant" <?php echo ($member['statut_professionnel'] ?? '') == 'etudiant' ? 'selected' : ''; ?>>Étudiant</option>
                                        <option value="salarie" <?php echo ($member['statut_professionnel'] ?? '') == 'salarie' ? 'selected' : ''; ?>>Salarié</option>
                                        <option value="independant" <?php echo ($member['statut_professionnel'] ?? '') == 'independant' ? 'selected' : ''; ?>>Indépendant</option>
                                        <option value="sans_emploi" <?php echo ($member['statut_professionnel'] ?? '') == 'sans_emploi' ? 'selected' : ''; ?>>Sans emploi</option>
                                        <option value="retraite" <?php echo ($member['statut_professionnel'] ?? '') == 'retraite' ? 'selected' : ''; ?>>Retraité</option>
                                    </select>
                                </div>
                                <div class="form-group"><label for="regTypeActivite">Type d'activité</label>
                                    <input type="text" id="regTypeActivite" name="type_activite" value="<?php echo $member['type_activite'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="regCentreInteret">Centre d'intérêt</label>
                                <select id="regCentreInteret" name="centre_interet">
                                    <option value="" disabled <?php echo empty($member['centre_interet']) ? 'selected' : ''; ?>>Choisir...</option>
                                    <option value="culture" <?php echo ($member['centre_interet'] ?? '') == 'culture' ? 'selected' : ''; ?>>Culture</option>
                                    <option value="sport" <?php echo ($member['centre_interet'] ?? '') == 'sport' ? 'selected' : ''; ?>>Sport</option>
                                    <option value="technologie" <?php echo ($member['centre_interet'] ?? '') == 'technologie' ? 'selected' : ''; ?>>Technologie</option>
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
                                <label><input type="radio" name="montant_contribution" value="15000" <?php echo ($member['montant_contribution'] ?? '') == '15000' ? 'checked' : ''; ?> <?php echo $isEdit ? 'disabled' : ''; ?>> 15000F</label>
                                <label><input type="radio" name="montant_contribution" value="30000" <?php echo ($member['montant_contribution'] ?? '') == '30000' ? 'checked' : ''; ?> <?php echo $isEdit ? 'disabled' : ''; ?>> 30000F</label>
                                <label><input type="radio" name="montant_contribution" value="100000" <?php echo ($member['montant_contribution'] ?? '') == '100000' ? 'checked' : ''; ?> <?php echo $isEdit ? 'disabled' : ''; ?>> 100000F</label>
                                <label><input type="radio" name="montant_contribution" value="200000" <?php echo ($member['montant_contribution'] ?? '') == '200000' ? 'checked' : ''; ?> <?php echo $isEdit ? 'disabled' : ''; ?>> 200000F</label>
                            </div>
                            <div class="form-group">
                                <label class="inline-label"><input type="radio" name="montant_contribution" value="libre" <?php echo ($member['montant_contribution'] ?? '') == 'libre' ? 'checked' : ''; ?> <?php echo $isEdit ? 'disabled' : ''; ?>> Montant Libre</label>
                                <input type="number" id="regMontantLibreVal" name="montant_libre_val" placeholder="Entrez montant" style="display:none; margin-left:10px; width: auto;" value="<?php echo ($member['montant_contribution'] ?? '') == 'libre' ? ($member['montant_libre_val'] ?? '') : ''; ?>" <?php echo $isEdit ? 'disabled' : ''; ?>>
                            </div>
                            <h3 class="step-title">MODE DE PAIEMENT</h3>
                            <?php if ($isEdit): ?>
                            <div class="form-row">
                                <p><em>Note: La modification des informations ne nécessite pas de nouveau paiement.</em></p>
                            </div>
                            <?php else: ?>
                            <div class="form-group radio-group-horizontal">
                                <label><input type="radio" name="mode_paiement_contribution" value="mobile" checked> Paiement mobile</label>
                                <label><input type="radio" name="mode_paiement_contribution" value="carte"> Carte de crédit</label>
                                <label><input type="radio" name="mode_paiement_contribution" value="paypal" id="paypalRadio"> Paypal</label>
                                <label><input type="radio" name="mode_paiement_contribution" value="virement"> Virement bancaire</label>
                                <label><input type="radio" name="mode_paiement_contribution" value="cash"> Cash</label>
                            </div>
                            
                            <!-- Conteneur pour le bouton PayPal -->
                            <div id="paypal-button-container" style="display: none; margin-top: 20px; max-width: 400px;"></div>
                            
                            <div id="paypal-info" style="display: none; margin-top: 15px; padding: 15px; background-color: var(--bg-secondary); border-left: 4px solid #0070ba; border-radius: 4px;">
                                <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                    <strong>ℹ️ Paiement PayPal:</strong> Cliquez sur le bouton PayPal ci-dessous pour procéder au paiement sécurisé.
                                </p>
                            </div>
                            <?php endif; ?>
                            <div class="wizard-nav">
                                <button type="button" class="btn btn-secondary wizard-back">< Back</button>
                                <button type="submit" class="btn btn-primary wizard-finish"><?php echo $isEdit ? 'Mettre à jour' : 'Finish'; ?></button>
                            </div>
                        </div>

                        <!-- Étape 4: Confirmation avec Identifiants -->
                        <div class="wizard-step" id="step4" style="text-align: center;">
                            <img src="assets/icons/success-check.svg" alt="Succès" style="width: 80px; margin-bottom: 20px;"> <!-- Icône de succès -->
                            <h3 class="step-title" style="font-size: 1.8em;"><?php echo $isEdit ? 'Mise à jour Terminée' : 'Enregistrement Terminé'; ?></h3>
                            <p><?php echo $isEdit ? 'Les informations du membre ont été mises à jour.' : 'Merci, les informations du membre ont été enregistrées avec succès.'; ?></p>
                            
                            <!-- Zone d'affichage des identifiants (uniquement pour création) -->
                            <div id="credentialsDisplay" style="display: none; margin: 30px auto; max-width: 500px;">
                                <div style="background-color: var(--bg-secondary); border: 2px solid var(--africavenir-yellow); border-radius: 8px; padding: 25px; text-align: left;">
                                    <h4 style="margin-top: 0; color: var(--africavenir-yellow); text-align: center;">🔐 Identifiants de Connexion</h4>
                                    <p style="text-align: center; color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px;">
                                        Veuillez noter ces informations et les transmettre au membre
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
                                            Assurez-vous de les copier ou de les transmettre au membre immédiatement.
                                        </p>
                                    </div>
                                    
                                    <?php if (!isset($_SESSION['user_id'])): ?>
                                    <div style="text-align: center; margin-top: 20px;">
                                        <a href="index.php" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
                                            🔗 Aller à la page de connexion
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="wizard-nav" style="justify-content: center; margin-top: 20px;">
                                <a href="dashboard-list-member.php" class="btn btn-primary">Retour à la liste des membres</a>
                            </div>
                        </div>
                    </form>
                </div>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    
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
    <script src="https://www.paypal.com/sdk/js?client-id=AbVmr3bh_YOJU5KtyvxU_FO_hUhq0C7maKEpnfoUWOzCf-KtfrC-GEK9avkmJnDxHMmGAXJniyLBmd7f&currency=USD"></script>
    <script src="js/modal-utils.js"></script>
    <script src="js/paypal-handler.js"></script>
    <script src="js/paypal-integration.js"></script>
    <script src="js/main.js"></script>
</body>
</html>