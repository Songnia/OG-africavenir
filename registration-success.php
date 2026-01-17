<?php
session_start();
require_once __DIR__ . '/src/Controllers/MemberController.php';
require_once __DIR__ . '/src/Services/StripeService.php';

use App\Controllers\MemberController;
use App\Services\StripeService;

$credentials = null;
$memberName = 'Membre';
$email = '';
$showCredentials = false;
$status = 'pending';

// 1. Check if we have a transaction_id from CinetPay return
$transactionId = $_POST['cpm_trans_id'] ?? $_GET['transaction_id'] ?? null;
$sessionId = $_GET['session_id'] ?? null;

// Handle Stripe Session
if ($sessionId) {
    $stripeService = new StripeService();
    $status = $stripeService->checkPaymentStatus($sessionId);
    
    if ($status['success'] && $status['status'] === 'ACCEPTED') {
        $transactionId = $status['transaction_id'];
        
        // Try to finalize registration immediately if not done
        // We need the registration ID. It should be in metadata or we find by transaction.
        // Let's rely on finding by transaction ID.
    }
}

// 2. Or registration_id from session (PayPal flow might set this or we pass it)
$registrationId = $_GET['registration_id'] ?? null;

if ($transactionId || $registrationId) {
    $controller = new MemberController();
    $registration = null;
    
    if ($transactionId) {
        $registration = $controller->getRegistrationByTransaction($transactionId);
    } elseif ($registrationId) {
        // We need a method getRegistrationById, let's assume getRegistrationByTransaction works or add it.
        // Actually getRegistrationByTransaction is what we added.
        // Let's add getRegistrationById to MemberController or use direct DB here?
        // Better to use controller.
        // For now let's try to find by transaction if we have it.
    }
    
    if ($registration) {
        $status = $registration['status'];
        $registrationId = $registration['id']; // Ensure we have ID
        
        // If pending but we have a successful transaction (e.g. from Stripe check above), finalize it!
        if ($status === 'pending' && isset($sessionId)) {
            $userId = $controller->finalizeRegistration($registrationId);
            if ($userId) {
                // Refresh data
                $registration = $controller->getRegistrationByTransaction($transactionId);
                $status = 'completed';
                $data = json_decode($registration['data'], true); // Refresh data
            }
        }

        $data = json_decode($registration['data'], true);
        $memberName = $data['display_name'] ?? 'Membre';
        $email = $data['email'] ?? '';
        
        if ($status === 'completed') {
            $showCredentials = true;
            $credentials = $data['generated_credentials'] ?? $data['credentials'] ?? null;
            error_log("Registration Success Debug: Status=completed, Credentials found=" . ($credentials ? 'YES' : 'NO'));
        } else {
            error_log("Registration Success Debug: Status=$status");
        }
    } else {
        error_log("Registration Success Debug: Registration not found for ID $registrationId or Transaction $transactionId");
    }
} elseif (isset($_SESSION['registration_success'])) {
    // Legacy/Fallback flow
    $data = $_SESSION['registration_success'];
    $credentials = $data['credentials'] ?? null;
    $memberName = $data['member_name'] ?? 'Membre';
    $email = $data['email'] ?? '';
    $showCredentials = true;
    $status = 'completed';
    unset($_SESSION['registration_success']);
} else {
    // Redirect if no context
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inscription réussie">
    <title>AfricAvenir | Inscription Réussie</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <style>
        /* Specific styles for success page to match signup wizard */
        .wizard-steps {
            margin-bottom: 30px;
        }
        .auth-container {
            max-width: 100%; /* Match signup width if needed, or keep standard auth width */
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
            <h1>Inscription Membre</h1>
            <p class="form-instruction">Rejoignez la communauté AfricAvenir</p>
            
            <div class="wizard-steps">
                <div class="step-indicator" data-step="1">01</div>
                <div class="step-connector"></div>
                <div class="step-indicator" data-step="2">02</div>
                <div class="step-connector"></div>
                <div class="step-indicator active" data-step="3">03</div>
            </div>

            <div class="wizard-step active" style="text-align: center;">
                <?php if ($status === 'completed' && $showCredentials): ?>
                    <img src="assets/icons/success-check.svg" alt="Succès" style="width: 80px; margin-bottom: 20px;">
                    <h3 class="step-title" style="font-size: 1.8em;">Enregistrement Terminé</h3>
                    <p style="margin-bottom: 30px;">Merci, votre inscription a été enregistrée avec succès.</p>
                    
                    <div style="background-color: var(--bg-secondary); border: 2px solid var(--africavenir-yellow); border-radius: 8px; padding: 25px; text-align: left; margin: 0 auto; max-width: 500px;">
                        <h4 style="margin-top: 0; color: var(--africavenir-yellow); text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span>🔐</span> Vos Identifiants de Connexion
                        </h4>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px;">
                            Veuillez noter ces informations précieusement.
                        </p>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Nom d'utilisateur:</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="displayUsername" value="<?php echo htmlspecialchars($credentials['username'] ?? ''); ?>" readonly style="flex: 1; padding: 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                <button type="button" onclick="copyToClipboard('displayUsername', this)" class="btn btn-secondary" style="padding: 10px 15px;">📋 Copier</button>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px; color: var(--text-primary);">Mot de passe:</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="displayPassword" value="<?php echo htmlspecialchars($credentials['password'] ?? ''); ?>" readonly style="flex: 1; padding: 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; font-family: monospace; font-size: 1.1em;">
                                <button type="button" onclick="copyToClipboard('displayPassword', this)" class="btn btn-secondary" style="padding: 10px 15px;">📋 Copier</button>
                            </div>
                        </div>
                        
                        <div style="background-color: var(--bg-warning-light); border-left: 4px solid var(--color-warning); padding: 15px; margin-top: 20px; border-radius: 4px;">
                            <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                <strong>⚠️ Important:</strong> Ces identifiants ne seront affichés qu'une seule fois. Copiez-les maintenant.
                            </p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <a href="index.php" class="btn btn-primary">Se connecter maintenant</a>
                    </div>

                <?php elseif ($status === 'pending'): ?>
                    <div style="font-size: 64px; margin-bottom: 20px;">⏳</div>
                    <h2 style="color: #ffc107; margin-bottom: 20px;">Paiement en attente</h2>
                    <p style="margin-bottom: 30px;">
                        Nous attendons la confirmation de votre paiement.<br>
                        Une fois confirmé, vous recevrez vos identifiants par email.
                    </p>
                    <button onclick="window.location.reload()" class="btn btn-secondary">🔄 Actualiser le statut</button>

                <?php else: ?>
                    <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                    <h2 style="color: #dc3545; margin-bottom: 20px;">Erreur</h2>
                    <p style="margin-bottom: 30px;">Impossible de trouver votre inscription ou le paiement a échoué.</p>
                    <a href="signup.php" class="btn btn-primary">Retour à l'inscription</a>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="site-footer auth-footer">
            <p>©2025 AfricAvenir tous droits réservés</p>
        </footer>
    </div>

    <script>
        function copyToClipboard(inputId, button) {
            const input = document.getElementById(inputId);
            input.select();
            input.setSelectionRange(0, 99999);
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
    <script src="js/main.js"></script>
</body>
</html>
