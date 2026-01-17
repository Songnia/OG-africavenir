<?php
require_once 'includes/auth-check.php';
?>
<!DOCTYPE html>
<html lang="fr">
<!-- Include Meta Data -->
 <?php 
 session_start();
 include('includes/meta-data.php'); 
 require_once 'src/Models/Contribution.php';
 
 // Check if user is logged in
 if (!isset($_SESSION['user_id'])) {
     header('Location: index.php');
     exit;
 }
 
 $contributionModel = new \App\Models\Contribution();
 $contributions = $contributionModel->getByUserId($_SESSION['user_id']);
 
 // Calculate totals
 $totalContributions = 0;
 $completedContributions = 0;
 $pendingContributions = 0;
 
 foreach ($contributions as $contrib) {
     $totalContributions += $contrib['amount'];
     if ($contrib['status'] === 'completed') {
         $completedContributions += $contrib['amount'];
     } elseif ($contrib['status'] === 'pending') {
         $pendingContributions += $contrib['amount'];
     }
 }
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
                    <h2>Mes contributions</h2>
                    <div class="actions-group">
                        <a href=" member-make-donation.php?type=don" class="btn btn-secondary">Faire un Don</a>
                        <a href=" member-make-donation.php?type=contribution" class="btn btn-primary">Contribuer</a>
                    </div>
                </section>

                <section class="filters-section card">
                    <div class="filter-tabs">
                        <button type="button" class="tab-button active" data-filter="contributions">Mes contributions</button>
                        <button type="button" class="tab-button" data-filter="dons">Mes dons</button>
                    </div>
                    <div class="filter-group">
                        <label for="filterDate" class="sr-only">Date</label>
                        <input type="month" id="filterDate" name="filterDate" placeholder="date"> <!-- type="month" pour choisir mois/année -->
                    </div>
                    <div class="filter-group search-filter">
                        <label for="contributionSearch" class="sr-only">Recherche</label>
                        <div class="search-input-container">
                            <img src="../assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="contributionSearch" name="contributionSearch" placeholder="recherche">
                        </div>
                    </div>
                    <!-- Pas de bouton "Appliquer" visible dans l'OCR pour cette section de filtres -->
                </section>

                <section class="table-section card">
                    <div class="table-responsive">
                        <table class="data-table" aria-label="Liste de mes contributions et dons">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Détail/Motif</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="contributionsTableBody">
                                <!-- Loading indicator -->
                                <tr id="loadingRow">
                                    <td colspan="4" style="text-align: center; padding: 40px;">
                                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--africavenir-yellow); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                        <p style="margin-top: 10px; color: var(--text-muted);">Chargement des contributions...</p>
                                    </td>
                                </tr>
                                <!-- Error message -->
                                <tr id="errorRow" style="display: none;">
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--color-danger);">
                                        <p>Erreur lors du chargement des contributions.</p>
                                    </td>
                                </tr>
                                <!-- No results message -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        <p>Aucune contribution trouvée. <a href="member-make-donation.php?type=contribution">Faire une contribution</a></p>
                                    </td>
                                </tr>
                                <!-- Contributions will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                    <nav class="pagination" aria-label="Pagination des contributions">
                        <a href="#" class="page-link disabled">< Précédent</a>
                        <a href="#" class="page-link active" aria-current="page">1</a>
                        <a href="#" class="page-link">Suivant ></a>
                    </nav>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    <script src="js/my-contributions-filter.js?v=<?php echo time(); ?>"></script>
    <script src="js/main.js"></script> <!-- Chemin relatif vers le JS global -->
</body>
</html>
          