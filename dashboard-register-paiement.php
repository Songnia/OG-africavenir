<?php
require_once __DIR__ . '/src/Controllers/PaymentController.php';

$payment = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $controller = new \App\Controllers\PaymentController();
    $response = $controller->get($_GET['id']); // Assuming get() method exists and returns ['success' => true, 'data' => ...]
    if ($response['success']) {
        $payment = $response['data'];
        $isEdit = true;
    }
}

// Handle Form Submission via PHP (Fallback if JS is disabled or commented out)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new \App\Controllers\PaymentController();
    $data = $_POST;
    
    // Map 'nom' to 'member' as expected by controller
    if (isset($data['nom']) && !isset($data['member'])) {
        $data['member'] = $data['nom'];
    }

    if (isset($data['action']) && $data['action'] === 'update' && isset($data['id'])) {
        $response = $controller->update($data['id'], $data);
    } else {
        $response = $controller->store($data);
    }

    if ($response['success']) {
        //Redirect to list on success
        header('Location: dashboard-list-paiement.php');
        exit;
    } else {
        $error_message = $response['message'];
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
                <h1>HOME</h1>
            </header>

            <main>
                <section class="page-title-section">
                    <!-- Le titre "Paiements" est le titre de la section globale, ici on est dans un sous-formulaire -->
                    <!-- Le titre principal du formulaire est géré ci-dessous -->
                </section>

                <div class="form-container card">
                    <a href="dashboard-list-paiement.php" class="back-link-form">< Back</a>
                    <h2 class="form-title"><?php echo $isEdit ? 'Modifier Paiement' : 'Nouveau Paiement'; ?></h2>
                    <p class="form-subtitle">Informations du Paiement</p>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                        <script>
                            // Display error in a popup as requested
                            alert("<?php echo addslashes($error_message); ?>");
                        </script>
                    <?php endif; ?>
                    <form id="newPaymentForm" action="" method="post">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($payment['id']); ?>">
                            <input type="hidden" name="action" value="update">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="paymentNom">Membre (ID ou Nom)</label>
                            <input type="text" id="paymentNom" name="nom" required placeholder="ID du membre" value="<?php echo $isEdit ? htmlspecialchars($payment['user_id']) : ''; ?>">
                            <!-- Note: Displaying user_id for now as that's what the backend expects/stores directly in the simple version. 
                                 Ideally we would show the name and have a hidden ID, but the controller currently maps 'nom' -> 'member' -> user_id.
                                 If we have member_name from the join, we could show that, but we need to ensure the form submits the ID.
                                 For now, let's stick to user_id to be safe or member_name if we handle the lookup. 
                                 The previous JS mapped 'nom' to 'member'. 
                                 Let's use user_id here to ensure update works correctly without lookup logic yet. -->
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="paymentMontant">Montant</label>
                                <input type="number" id="paymentMontant" name="montant" required placeholder="Ex: 5000" value="<?php echo $isEdit ? htmlspecialchars($payment['amount']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="paymentMotif">Motif</label>
                                <input type="text" id="paymentMotif" name="motif" required placeholder="Ex: Adhésion, Don" value="<?php echo $isEdit ? htmlspecialchars($payment['transaction_id']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="paymentMode">Mode de paiement</label>
                                <select id="paymentMode" name="mode" required>
                                    <option value="" disabled <?php echo !$isEdit ? 'selected' : ''; ?>>Choisir...</option>
                                    <?php
                                    $modes = ['Carte Bancaire', 'MTN Momo', 'Orange Money', 'Virement', 'Cache', 'Chèque', 'Paypal'];
                                    foreach ($modes as $mode) {
                                        $selected = ($isEdit && $payment['payment_method'] === $mode) ? 'selected' : '';
                                        echo "<option value=\"$mode\" $selected>$mode</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="paymentDate">Date</label>
                                <?php 
                                    // Extract date part from created_at (YYYY-MM-DD HH:MM:SS)
                                    $dateValue = $isEdit ? date('Y-m-d', strtotime($payment['created_at'])) : '';
                                ?>
                                <input type="date" id="paymentDate" name="date" required value="<?php echo $dateValue; ?>">
                            </div>
                             <?php if ($isEdit): ?>
                            <div class="form-group">
                                <label for="paymentStatus">Statut</label>
                                <select id="paymentStatus" name="status" required>
                                    <?php
                                    $statuses = ['pending' => 'En attente', 'completed' => 'Payé', 'failed' => 'Échoué', 'paye' => 'Payé', 'a-payer' => 'À payer', 'annule' => 'Annulé'];
                                    foreach ($statuses as $val => $label) {
                                        $selected = ($payment['status'] === $val) ? 'selected' : '';
                                        echo "<option value=\"$val\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block-form"><?php echo $isEdit ? 'Mettre à jour' : 'Valider'; ?></button>
                    </form>
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