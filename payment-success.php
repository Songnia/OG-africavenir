<?php
session_start();
require_once 'includes/auth-check.php';
require_once 'src/Services/CinetPayService.php';
require_once 'src/Models/Contribution.php';
require_once 'src/Controllers/MemberController.php'; // Add MemberController

use App\Controllers\MemberController;

$transaction_id = $_GET['transaction_id'] ?? '';
$session_id = $_GET['session_id'] ?? '';
$paymentSuccess = false;
$contribution = null;

if ($session_id) {
    // Handle Stripe Return
    require_once 'src/Services/StripeService.php';
    $stripe = new \App\Services\StripeService();
    $status = $stripe->checkPaymentStatus($session_id);
    
    if ($status['success'] && $status['status'] === 'ACCEPTED') {
        $paymentSuccess = true;
        $transaction_id = $status['transaction_id']; // This is our internal transaction ID
        
        // Update DB status
        $contributionModel = new \App\Models\Contribution();
        $contributionModel->updateByTransactionId($transaction_id, [
            'status' => 'completed',
            'payment_method' => 'Carte bancaire' // Ensure consistency
        ]);
        
        // Get contribution details using model method
        $contribution = $contributionModel->getByTransactionId($transaction_id);
    }
} elseif ($transaction_id) {
    // Handle CinetPay Return
    $cinetpay = new \App\Services\CinetPayService();
    $status = $cinetpay->checkPaymentStatus($transaction_id);
    
    $cinetpay->log('RETURN_PAGE', [
        'transaction_id' => $transaction_id,
        'status' => $status
    ]);
    
    $paymentSuccess = ($status['code'] === '00' && 
                       isset($status['data']['status']) && 
                       $status['data']['status'] === 'ACCEPTED');
    
    // Récupérer les détails de la contribution
    if ($paymentSuccess) {
        $contributionModel = new \App\Models\Contribution();
        
        // Update DB status just in case IPN missed it
        $contributionModel->updateByTransactionId($transaction_id, [
            'status' => 'completed'
        ]);

        // Get contribution details using model method
        $contribution = $contributionModel->getByTransactionId($transaction_id);
    }
}

// This page is now ONLY for donations/contributions from existing members
// Registrations are handled by registration-success.php
?>
<!DOCTYPE html>
<html lang="fr">
<?php include('includes/meta-data.php'); ?>
<body>
    <div class="page-container">
        <?php include('includes/header-contribution.php'); ?>
        
        <div class="main-content">
            <header class="main-header member-main-header">
                <button class="mobile-menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="hamburger-icon"></span>
                </button>
                <h1>ESPACE MEMBRE</h1>
                <button id="themeToggleBtn" class="theme-toggle-btn" aria-label="Changer de thème">
                  <img src="assets/icons/moon.svg" alt="Thème sombre" id="themeIcon">
                </button>
            </header>

            <main>
                <div class="form-container card" style="max-width: 600px; margin: 40px auto;">
                    <?php if ($paymentSuccess && $contribution): ?>
                        <div style="text-align: center; padding: 40px 20px;">
                            <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
                            <h2 style="color: #28a745; margin-bottom: 20px;">Paiement Réussi!</h2>
                            
                            <p style="font-size: 1.1em; margin-bottom: 30px;">
                                Votre contribution de <strong><?php echo number_format($contribution['amount'], 0, ',', ' '); ?> FCFA</strong> 
                                a été enregistrée avec succès.
                            </p>
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: left;">
                                <p style="margin: 5px 0;"><strong>Référence:</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
                                <p style="margin: 5px 0;"><strong>Montant:</strong> <?php echo number_format($contribution['amount'], 0, ',', ' '); ?> FCFA</p>
                                <p style="margin: 5px 0;"><strong>Mode:</strong> <?php echo htmlspecialchars($contribution['payment_method']); ?></p>
                                <p style="margin: 5px 0;"><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($contribution['created_at'])); ?></p>
                                <p style="margin: 5px 0;"><strong>Statut:</strong> <span style="color: #28a745;">✓ Confirmé</span></p>
                            </div>
                            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                                <a href="member-contribution.php" class="btn btn-primary">Voir mes contributions</a>
                                <a href="member-make-donation.php" class="btn btn-secondary">Nouvelle contribution</a>
                            </div>
                        </div>
                    <?php elseif ($transaction_id): ?>
                        <div style="text-align: center; padding: 40px 20px;">
                            <div style="font-size: 64px; margin-bottom: 20px;">⏳</div>
                            <h2 style="color: #ffc107; margin-bottom: 20px;">Paiement en cours de traitement</h2>
                            <p style="margin-bottom: 30px;">
                                Votre paiement est en cours de vérification.<br>
                                Vous recevrez une confirmation par email une fois le paiement validé.
                            </p>
                            <p style="font-size: 0.9em; color: #6c757d;">
                                Référence: <?php echo htmlspecialchars($transaction_id); ?>
                            </p>
                            <div style="margin-top: 30px;">
                                <a href="member-contribution.php" class="btn btn-primary">Voir mes contributions</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 20px;">
                            <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                            <h2 style="color: #dc3545; margin-bottom: 20px;">Erreur</h2>
                            <p style="margin-bottom: 30px;">
                                Aucune transaction trouvée.
                            </p>
                            <a href="member-make-donation.php" class="btn btn-primary">Faire une contribution</a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
