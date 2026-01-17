<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Services/CinetPayService.php';
require_once __DIR__ . '/src/Models/Contribution.php';

use App\Services\CinetPayService;
use App\Models\Contribution;

// Get transaction ID from URL parameter (CinetPay sends it as 'transaction_id')
$transactionId = $_GET['transaction_id'] ?? $_GET['cpm_trans_id'] ?? null;

if (!$transactionId) {
    // Redirect to homepage if no transaction ID
    header('Location: index.php');
    exit;
}

// Initialize services
$cinetpay = new CinetPayService();
$contributionModel = new Contribution();

// Check payment status from CinetPay
$paymentStatus = $cinetpay->checkPaymentStatus($transactionId);

// Log the response
$cinetpay->log('RETURN_PAGE_CHECK', [
    'transaction_id' => $transactionId,
    'status' => $paymentStatus
]);

// Get contribution details from database
$database = new Database();
$conn = $database->getConnection();
$stmt = $conn->prepare("
    SELECT c.*, u.ID as user_id, u.display_name, u.user_email, u.user_login
    FROM app_contributions c
    JOIN wp_users u ON c.user_id = u.ID
    WHERE c.transaction_id = :transaction_id
");
$stmt->execute([':transaction_id' => $transactionId]);
$contribution = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contribution) {
    // Transaction not found
    header('Location: index.php');
    exit;
}

// Determine payment result
$isSuccess = isset($paymentStatus['code']) && 
              $paymentStatus['code'] === '00' && 
              isset($paymentStatus['data']['status']) && 
              $paymentStatus['data']['status'] === 'ACCEPTED';

// Get user credentials from session (stored during registration)
$username = $contribution['user_login'] ?? '';
$userId = $contribution['user_id'];

// Get password from usermeta if available (we need to store plaintext temporarily for this)
// Alternative: generate a password reset link
$stmt = $conn->prepare("SELECT meta_value FROM wp_usermeta WHERE user_id = :user_id AND meta_key = 'temp_password'");
$stmt->execute([':user_id' => $userId]);
$tempPassword = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du paiement - AfricAvenir</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <style>
        .payment-result-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .result-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .result-icon.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        .result-icon.failure {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        .credentials-box {
            background-color: var(--bg-primary);
            border: 2px solid var(--africavenir-yellow);
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        .credential-field {
            margin: 15px 0;
        }
        .credential-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .credential-field input {
            width: 100%;
            padding: 10px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: monospace;
            font-size: 1.1em;
        }
        .btn-retry {
            background-color: var(--africavenir-yellow);
            color: var(--bg-primary);
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-retry:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <header class="auth-header">
            <img src="assets/logo-africavenir.png" alt="AfricAvenir" class="decorative-image">
        </header>

        <main class="auth-main">
            <div class="payment-result-container">
                <?php if ($isSuccess): ?>
                    <!-- SUCCESS SCENARIO -->
                    <div class="result-icon success">✓</div>
                    <h1 style="color: #4CAF50; margin-bottom: 10px;">Paiement réussi !</h1>
                    <p style="color: var(--text-muted); margin-bottom: 30px;">
                        Votre contribution de <?= number_format($contribution['amount'], 0, ',', ' ') ?> FCFA a été validée.
                    </p>

                    <div class="credentials-box">
                        <h3 style="color: var(--africavenir-yellow); text-align: center; margin-top: 0;">
                            🔐 Vos identifiants de connexion
                        </h3>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.9em; margin-bottom: 20px;">
                            Veuillez noter ces informations précieusement.
                        </p>

                        <div class="credential-field">
                            <label>Nom d'utilisateur:</label>
                            <input type="text" value="<?= htmlspecialchars($username) ?>" readonly>
                        </div>

                        <?php if ($tempPassword): ?>
                        <div class="credential-field">
                            <label>Mot de passe:</label>
                            <input type="text" value="<?= htmlspecialchars($tempPassword) ?>" readonly>
                        </div>
                        <?php else: ?>
                        <div style="background-color: var(--bg-warning-light); padding: 15px; border-radius: 4px; margin-top: 15px;">
                            <p style="margin: 0; font-size: 0.9em;">
                                ⚠️ Vos identifiants ont été envoyés par email à <strong><?= htmlspecialchars($contribution['user_email']) ?></strong>
                            </p>
                        </div>
                        <?php endif; ?>

                        <div style="background-color: var(--bg-warning-light); border-left: 4px solid var(--color-warning); padding: 15px; margin-top: 20px; border-radius: 4px;">
                            <p style="margin: 0; font-size: 0.9em; color: var(--text-primary);">
                                <strong>⚠️ Important:</strong> Ces identifiants ne seront affichés qu'une seule fois. Copiez-les maintenant.
                            </p>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <a href="index.php" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
                            🔗 Se connecter maintenant
                        </a>
                    </div>

                <?php else: ?>
                    <!-- FAILURE SCENARIO -->
                    <div class="result-icon failure">✕</div>
                    <h1 style="color: #f44336; margin-bottom: 10px;">Paiement échoué</h1>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">
                        Votre paiement de <?= number_format($contribution['amount'], 0, ',', ' ') ?> FCFA n'a pas pu être traité.
                    </p>

                    <div style="background-color: var(--bg-secondary); border: 2px solid #f44336; border-radius: 8px; padding: 20px; margin: 20px 0;">
                        <p style="margin: 0 0 10px 0;"><strong>Transaction ID:</strong> <?= htmlspecialchars($transactionId) ?></p>
                        <p style="margin: 0;"><strong>Statut:</strong> 
                            <?= htmlspecialchars($paymentStatus['message'] ?? 'Échec') ?>
                        </p>
                    </div>

                    <p style="color: var(--text-muted); margin: 20px 0;">
                        Votre compte a été créé mais le paiement n'a pas abouti. Vous pouvez réessayer le paiement en cliquant sur le bouton ci-dessous.
                    </p>

                    <form method="POST" action="retry-payment.php">
                        <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($transactionId) ?>">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
                        <button type="submit" class="btn-retry">
                            🔄 Réessayer le paiement
                        </button>
                    </form>

                    <div style="margin-top: 20px;">
                        <a href="index.php" style="color: var(--text-muted); text-decoration: underline;">
                            Retour à l'accueil
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="site-footer auth-footer">
        <p>©2025 AfricAvenir tous droits réservés</p>
    </footer>
</body>
</html>
<?php
// Clean up temp password after displaying (if it exists)
if ($tempPassword && $isSuccess) {
    $stmt = $conn->prepare("DELETE FROM wp_usermeta WHERE user_id = :user_id AND meta_key = 'temp_password'");
    $stmt->execute([':user_id' => $userId]);
}
?>
