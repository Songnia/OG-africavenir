<!DOCTYPE html>
<html lang="fr">
<!-- Include Meta Data -->
 <?php 
 session_start();
 include('includes/meta-data.php'); 
 
 // Check if user is logged in
 if (!isset($_SESSION['user_id'])) {
     header('Location: index.php');
     exit;
 }
 
 $userName = $_SESSION['user_name'] ?? 'Membre';
 ?>
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
                <section class="page-title-section">
                    <h2>Boîte de reception</h2>
                    <div class="actions-group">
                        <a href=" member-make-donation.php?type=don" class="btn btn-secondary">Faire un Don</a>
                        <a href=" member-make-donation.php?type=contribution" class="btn btn-primary">Contribuer</a>
                    </div>
                </section>

                <section class="inbox-section card">
                    <!-- Filtres ou options de tri pour la boîte de réception pourraient être ajoutés ici si nécessaire -->
                    <!-- Par exemple: <input type="search" placeholder="Rechercher messages..."> -->
                    
                    <ul class="message-list" aria-label="Liste des messages">
                        <li class="message-item" style="padding: 40px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                            <div style="margin-bottom: 20px;">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #6c757d; margin: 0 auto;">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <h3 style="color: #495057; margin-bottom: 10px;">Système de messagerie</h3>
                            <p style="color: #6c757d; margin-bottom: 20px;">
                                La fonctionnalité de messagerie sera bientôt disponible.<br>
                                Vous pourrez recevoir des notifications et communiquer avec l'administration.
                            </p>
                            <p style="color: #6c757d; font-size: 0.9em;">
                                En attendant, vous pouvez nous contacter par email ou téléphone.
                            </p>
                        </li>
                    </ul>

                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>