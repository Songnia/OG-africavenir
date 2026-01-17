<?php 
ob_start(); // Buffer output to prevent headers already sent errors
require_once __DIR__ . '/vendor/autoload.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_log("Donation Page: Request Method: " . $_SERVER['REQUEST_METHOD']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = false;
$error = null;
$contributionId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'src/Models/Contribution.php';
    
    $amount = $_POST['amount'] ?? 0;
    $motif = $_POST['motif'] ?? 'Contribution';
    $mode = $_POST['mode'] ?? 'mobile_money';
    $paymentType = $_POST['payment_type'] ?? 'contribution';
    
    // Validate amount
    if ($amount < 1000) {
        $error = "Le montant minimum est de 1000 FCFA";
    } else {
        $contributionModel = new \App\Models\Contribution();
        
        // Map payment mode to database format
        $paymentMethod = match($mode) {
            'mobile_money' => 'Mobile Money',
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Virement bancaire',
            default => 'Mobile Money'
        };
        
        // Create contribution with pending status
        $result = $contributionModel->create(
            $_SESSION['user_id'],
            $amount,
            $motif,
            'pending',
            $paymentMethod,
            date('Y-m-d H:i:s')
        );
        
        if ($result) {
            $success = true;
            $contributionId = $contributionModel->getLastInsertId();
            
            // Initier le paiement CinetPay
            require_once 'src/Controllers/PaymentController.php';
            $paymentController = new \App\Controllers\PaymentController();
            $paymentResponse = $paymentController->initiatePayment($contributionId);
            
            if ($paymentResponse['success']) {
                error_log("Donation Page: Payment Init Success. Redirecting to: " . $paymentResponse['payment_url']);
                // Rediriger vers CinetPay (AVANT tout output HTML)
                header('Location: ' . $paymentResponse['payment_url']);
                exit;
            } else {
                error_log("Donation Page: Payment Init Failed. Message: " . $paymentResponse['message']);
                $error = "Erreur de paiement: " . $paymentResponse['message'];
                $success = false;
            }
        } else {
            $error = "Erreur lors de l'enregistrement de la contribution";
        }
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
                <!-- Le titre de section global est géré par le formulaire lui-même -->
                
                <div class="form-container card">
                    <!-- Le lien back pourrait être dynamique pour retourner à la page précédente -->
                    <a href="javascript:history.back()" class="back-link-form">< Back</a>
                    
                    <?php if ($success): ?>
                        <div style="padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; margin-bottom: 20px; color: #155724;">
                            <h3 style="margin: 0 0 10px 0;">✅ Contribution enregistrée!</h3>
                            <p style="margin: 0 0 10px 0;">
                                Votre contribution de <strong><?php echo number_format($_POST['amount'], 0, ',', ' '); ?> FCFA</strong> a été enregistrée avec succès.
                            </p>
                            <p style="margin: 0; font-size: 0.9em;">
                                Référence: #<?php echo $contributionId; ?><br>
                                Statut: En attente de confirmation de paiement
                            </p>
                            <div style="margin-top: 15px;">
                                <a href="member-contribution.php" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none;">
                                    Voir mes contributions
                                </a>
                                <a href="member-make-donation.php" class="btn btn-secondary" style="display: inline-block; padding: 10px 20px; text-decoration: none; margin-left: 10px;">
                                    Nouvelle contribution
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin-bottom: 20px; color: #721c24;">
                            <strong>❌ Erreur:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="form-title" id="donationFormTitle">Faire un Paiement</h2>
                    <form id="memberDonationForm" action="" method="post">
                        <input type="hidden" id="paymentType" name="payment_type" value="contribution"> <!-- Valeur par défaut, modifiée par JS -->

                        <div class="form-group">
                            <label for="donationAmount">Montant (en F CFA)</label>
                            <input type="number" id="donationAmount" name="amount" min="1000" step="500" required placeholder="Ex: 15000">
                        </div>

                        <div class="form-group" id="motifContainer"> <!-- Géré dynamiquement pour "Contribution" -->
                            <label for="donationMotif">Motif</label>
                            <input type="text" id="donationMotif" name="motif" placeholder="Ex: Contribution mensuelle, Soutien projet Alpha">
                        </div>

                        <div class="form-group">
                            <label for="donationMode">Mode de paiement</label>
                            <select id="donationMode" name="mode" required>
                                <option value="" disabled selected>Choisir le mode...</option>
                                <option value="mobile_money">Paiement Mobile (MTN, Orange)</option>
                                <option value="card">Carte de Crédit</option>
                                <option value="paypal">Paypal</option>
                                <!-- <option value="bank_transfer">Virement Bancaire</option>-->
                                <!-- <option value="cash">Cash (si applicable)</option> -->
                            </select>
                        </div>
                        
                        <!-- Section pour les détails de paiement spécifiques au mode, si nécessaire -->
                        <!-- Conteneur pour le bouton PayPal -->
                        <div id="paymentDetailsSection" style="display: none;">
                            <div id="paypal-button-container" style="margin-top: 20px; max-width: 400px;"></div>
                            <div id="paypal-info" style="margin-top: 15px; padding: 15px; background-color: var(--bg-secondary); border-left: 4px solid #0070ba; border-radius: 4px;">
                                <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                    <strong>ℹ️ Paiement PayPal:</strong> Cliquez sur le bouton PayPal ci-dessous pour procéder au paiement sécurisé.
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block-form" id="submitDonationButton">Procéder au Paiement</button>
                    </form>
                </div>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    
    <!-- Charger le SDK PayPal -->
    <script src="https://www.paypal.com/sdk/js?client-id=AbVmr3bh_YOJU5KtyvxU_FO_hUhq0C7maKEpnfoUWOzCf-KtfrC-GEK9avkmJnDxHMmGAXJniyLBmd7f&currency=USD"></script>
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