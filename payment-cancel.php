<?php
session_start();
require_once 'includes/auth-check.php';
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
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                        <h2 style="color: #dc3545; margin-bottom: 20px;">Paiement Annulé</h2>
                        <p style="margin-bottom: 30px;">
                            Vous avez annulé le paiement.<br>
                            Aucun montant n'a été débité de votre compte.
                        </p>
                        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                            <a href="member-make-donation.php" class="btn btn-primary">Réessayer</a>
                            <a href="member-contribution.php" class="btn btn-secondary">Retour à mes contributions</a>
                        </div>
                    </div>
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
