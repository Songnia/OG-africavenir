<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Controllers/MemberController.php';
require_once __DIR__ . '/src/Services/MaketouService.php';

$guestId = $_GET['guest_id'] ?? $_GET['registration_id'] ?? null;
$error = null;
$success = null;
$guestData = [];
$credentials = null;

if (!$guestId) {
    $error = "Identifiant de don manquant.";
} else {
    $memberController = new \App\Controllers\MemberController();
    $registration = $memberController->getRegistrationById($guestId);
    
    if (!$registration) {
        $error = "Donation introuvable.";
    } else {
        $guestData = json_decode($registration['data'], true);
        $paymentProvider = strtolower((string) ($guestData['payment_provider'] ?? $guestData['mode_paiement'] ?? ''));
        
        // S'il est dejà devenu membre
        if ($registration['status'] === 'completed') {
            $success = "Vous êtes déjà inscrit en tant que membre ! Vos identifiants vous ont été envoyés par email.";
        } 
        // Si le webhook n'a pas encore validé, on peut tolérer ou demander d'attendre (Ici on permet de remplir)
        elseif ($registration['status'] === 'pending_guest') {
            if ($paymentProvider === 'maketou' && !empty($registration['transaction_id'])) {
                $maketou = new \App\Services\MaketouService();
                $cartStatus = $maketou->getCart((string) $registration['transaction_id']);

                if (!empty($cartStatus['success']) && ($cartStatus['status'] ?? '') === 'COMPLETED') {
                    $memberController->confirmGuestDonationPayment($guestId, (string) $registration['transaction_id']);
                    $registration = $memberController->getRegistrationById($guestId);
                    $guestData = json_decode($registration['data'], true);
                } elseif ($maketou->isFailedStatus($cartStatus['status'] ?? null)) {
                    $error = 'Le paiement Maketou n\'a pas pu être confirmé.';
                    $guestData = [];
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_to_member'])) {
            // Collect all additional data
            $additionalData = [
                'email' => trim($_POST['email'] ?? ''),
                'date_naissance' => $_POST['date_naissance'] ?? '',
                'etat_civil' => $_POST['etat_civil'] ?? '',
                'adresse' => $_POST['adresse'] ?? '',
                'boite_postale' => $_POST['boite_postale'] ?? '',
                'pays' => $_POST['pays'] ?? '',
                'quartier' => $_POST['quartier'] ?? '',
                'ville' => $_POST['ville'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'parcours_type' => $_POST['parcours_type'] ?? 'autres',
                'etablissement_scolaire' => $_POST['etablissement_scolaire'] ?? '',
                'dernier_diplome' => $_POST['dernier_diplome'] ?? '',
                'statut_professionnel' => $_POST['statut_professionnel'] ?? '',
                'type_activite' => $_POST['type_activite'] ?? '',
                'centre_interet' => $_POST['centre_interet'] ?? '',
                'role' => \App\Helpers\RoleHelper::LEVEL_MEMBER,
                'category' => 'Membre Simple'
            ];

            // Here we assume webhook passed, if not, convertGuestToMember will return error "Donation non payée"
            $result = $memberController->convertGuestToMember($guestId, $additionalData);
            
            if ($result['success']) {
                $success = "Félicitations ! Vous êtes désormais membre de l'association AfricAvenir.";
                $credentials = $result['credentials'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<?php include('includes/meta-data.php'); ?>
<style>
    .auth-container { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; background-color: var(--bg-primary); }
    .auth-main { width: 100%; max-width: 800px; margin: 0 auto; padding: 30px; background-color: var(--bg-secondary); border-radius: 12px; box-shadow: 0 4px 20px var(--shadow-color); }
    .form-row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
    .form-group { flex: 1; min-width: 200px; }
    .step-title { font-size: 1.3em; color: var(--text-primary); margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid var(--brand-primary); }
    .required-asterisk { color: var(--color-danger); margin-left: 3px; font-weight: bold; }
    #credentialsDisplay input[readonly] { background-color: var(--bg-primary); font-family: monospace; font-size: 1.1em; text-align: center; }
</style>
<body>
    <div class="auth-container">
        <header class="auth-header">
            <img src="assets/logo-africavenir.png" alt="AfricAvenir Logo" style="height: 50px;">
        </header>

        <main class="auth-main">
            <?php if ($success): ?>
                <div style="text-align: center;">
                    <img src="assets/icons/success-check.svg" alt="Succès" style="width: 80px; margin-bottom: 20px;">
                    <h3 class="step-title" style="font-size: 1.8em;"><?php echo htmlspecialchars($success); ?></h3>
                    
                    <?php if ($credentials): ?>
                    <div id="credentialsDisplay" style="margin: 30px auto; max-width: 500px;">
                        <div style="background-color: var(--bg-secondary); border: 2px solid var(--africavenir-yellow); border-radius: 8px; padding: 25px; text-align: left;">
                            <h4 style="margin-top: 0; color: var(--africavenir-yellow); text-align: center;">🔐 Vos Identifiants de Connexion</h4>
                            
                            <div style="margin: 15px 0;">
                                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nom d'utilisateur:</label>
                                <input type="text" value="<?php echo htmlspecialchars($credentials['username']); ?>" readonly style="width:100%; padding: 10px; border-radius: 4px;">
                            </div>
                            
                            <div style="margin: 15px 0;">
                                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Mot de passe:</label>
                                <input type="text" value="<?php echo htmlspecialchars($credentials['password']); ?>" readonly style="width:100%; padding: 10px; border-radius: 4px;">
                            </div>
                            
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="index.php" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">🔗 Se connecter maintenant</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>

                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="assets/icons/success-check.svg" alt="Succès" style="width: 60px; margin-bottom: 10px;">
                    <h2 style="color: var(--color-success);">Merci pour votre don !</h2>
                    <p style="color: var(--text-muted);">Votre paiement a été initié/réceptionné avec succès.</p>
                </div>

                <?php if ($error): ?>
                    <div style="background-color: var(--bg-danger-light); color: var(--color-danger); padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid var(--color-danger);">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($guestData): ?>
                <div style="background: var(--bg-tertiary); padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid var(--brand-primary);">
                    <h3 style="margin-top:0;">Devenez membre officiel (Gratuitement)</h3>
                    <p>Puisque vous venez de faire une contribution, vous pouvez finaliser votre profil pour devenir un membre officiel d'AfricAvenir <strong>sans frais supplémentaires</strong> !</p>
                </div>

                <form method="post" action="">
                    <input type="hidden" name="convert_to_member" value="1">
                    
                    <h3 class="step-title">INFORMATIONS PERSONNELLES</h3>
                    <div class="form-row">
                        <div class="form-group"><label>Nom</label><input type="text" value="<?php echo htmlspecialchars($guestData['last_name'] ?? ''); ?>" disabled></div>
                        <div class="form-group"><label>Prénom</label><input type="text" value="<?php echo htmlspecialchars($guestData['first_name'] ?? ''); ?>" disabled></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Téléphone</label><input type="tel" value="<?php echo htmlspecialchars($guestData['telephone'] ?? ''); ?>" disabled></div>
                        <div class="form-group">
                            <label for="regEmail">Email<span class="required-asterisk">*</span></label>
                            <input type="email" id="regEmail" name="email" value="<?php echo htmlspecialchars($guestData['email'] ?? ''); ?>" placeholder="Votre adresse email" required>
                        </div>
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
                        <div class="form-group"><label for="regBoitePostale">Boîte postale</label><input type="text" id="regBoitePostale" name="boite_postale" value="0000"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regPays">Pays<span class="required-asterisk">*</span></label><input type="text" id="regPays" name="pays" required></div>
                        <div class="form-group"><label for="regQuartier">Quartier</label><input type="text" id="regQuartier" name="quartier"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="regVille">Ville<span class="required-asterisk">*</span></label><input type="text" id="regVille" name="ville" required></div>
                        <div class="form-group">
                            <label for="regTelephone">Téléphone<span class="required-asterisk">*</span></label>
                            <input type="tel" id="regTelephone" name="telephone"
                                value="<?php echo htmlspecialchars($guestData['telephone'] ?? ''); ?>"
                                placeholder="Ex: +237 6XX XXX XXX"
                                pattern="[+0-9\s\-]{8,20}"
                                required>
                            <small style="color: var(--text-muted); font-size: 0.78em;">Format international : <strong>+237 6XX XXX XXX</strong></small>
                        </div>
                    </div>

                    <h3 class="step-title">PARCOURS</h3>
                    <div class="form-row">
                        <div class="form-group"><label for="regStatutPro">Statut<span class="required-asterisk">*</span></label>
                            <select id="regStatutPro" name="statut_professionnel" required>
                                <option value="" disabled selected>Choisir...</option>
                                <option value="etudiant">Étudiant</option>
                                <option value="salarie">Salarié</option>
                                <option value="independant">Indépendant</option>
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
                            <option value="technologie">Technologie</option>
                            <option value="education">Éducation</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div style="text-align: right; margin-top: 30px;">
                        <a href="index.php" class="btn btn-secondary" style="margin-right: 15px; text-decoration: none;">Non merci, retourner à l'accueil</a>
                        <button type="submit" class="btn btn-primary" onclick="this.textContent='Création en cours...';">Terminer mon Inscription</button>
                    </div>
                </form>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
