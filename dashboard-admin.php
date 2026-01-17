<?php
require_once 'includes/admin-check.php';
require_once 'src/Helpers/RoleHelper.php';

use App\Helpers\RoleHelper;

// Get current user info
$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['user_level'];

// Fetch Dashboard Stats
require_once __DIR__ . '/src/Controllers/StatsController.php';
$statsController = new \App\Controllers\StatsController();

// Get filters if set
$filterYear = $_GET['filterYear'] ?? date('Y');
$filterMonth = $_GET['filterMonth'] ?? null;

$stats = $statsController->getDashboardStats($filterYear, $filterMonth);
$graphData = json_encode($stats['graph_data']); // Prepare for JS
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
            <!-- Include Filter Barre-->
            <?php include('includes/filter-bare.php'); ?>

            <main>
                <section class="page-title-section">
                    <h2>Tableau de bord Administrateur</h2>
                </section>

                <section class="filters-section card">
                    <div class="filter-group search-filter">
                        <label for="mainSearch" class="sr-only">Recherche</label>
                        <div class="search-input-container">
                            <img src="assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="mainSearch" name="mainSearch" placeholder="recherche l'email, nom, prenom, telephone, ville">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterMonth" class="sr-only">Mois</label>
                        <select id="filterMonth" name="filterMonth">
                            <option value="">Mois</option>
                            <option value="01">Janvier</option>
                            <option value="02">Février</option>
                            <option value="03">Mars</option>
                            <option value="04">Avril</option>
                            <option value="05">Mai</option>
                            <option value="06">Juin</option>
                            <option value="07">Juillet</option>
                            <option value="08">Août</option>
                            <option value="09">Septembre</option>
                            <option value="10">Octobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Décembre</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterYear" class="sr-only">Année</label>
                        <select id="filterYear" name="filterYear">
                            <option value="">Année</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary">Appliquer</button>
                </section>

                <section class="stats-grid">
                    <?php //if (RoleHelper::isSuperAdmin($userId)): ?>
                    <!--<div class="stat-card card" style="background: var(--bg-secondary); border: 1px solid var(--africavenir-yellow);">
                        <h3>Utilisateurs</h3>
                        <p class="stat-label">Gestion des accès</p>
                        <p class="stat-number"><img src="assets/icons/users.svg" alt="" style="width: 32px; height: 32px;"></p>
                        <a href="dashboard-users.php" class="details-link">Gérer les utilisateurs</a>
                    </div>-->
                    <?php// endif; ?>
                    <div class="stat-card card">
                        <h3>Member-ships</h3>
                        <p class="stat-label">Nombre de membre</p>
                        <p class="stat-number"><?php echo number_format($stats['member_ships']['count']); ?></p>
                        <p class="stat-label">Contributions Totals</p>
                        <p class="stat-contribution"><?php echo number_format($stats['member_ships']['total'], 0, ',', ' '); ?> FCFA</p>
                        <a href="dashboard-list-member.php?category=member-ships" class="details-link">voir les details</a>
                    </div>
                    <div class="stat-card card">
                        <h3>Alumni</h3>
                        <p class="stat-label">Nombre de membre</p>
                        <p class="stat-number"><?php echo number_format($stats['alumni']['count']); ?></p>
                        <p class="stat-label">Contributions Totals</p>
                        <p class="stat-contribution"><?php echo number_format($stats['alumni']['total'], 0, ',', ' '); ?> FCFA</p>
                        <a href="dashboard-list-member.php?category=alumni" class="details-link">voir les details</a>
                    </div>
                    <div class="stat-card card">
                        <h3>Cercle d'amis</h3>
                        <p class="stat-label">Nombre de membre</p>
                        <p class="stat-number"><?php echo number_format($stats['cercle_amis']['count']); ?></p>
                        <p class="stat-label">Contributions Totals</p>
                        <p class="stat-contribution"><?php echo number_format($stats['cercle_amis']['total'], 0, ',', ' '); ?> FCFA</p>
                        <a href="dashboard-list-member.php?category=cercle-amis" class="details-link">voir les details</a>
                    </div>
                    <div class="stat-card card">
                        <h3>Mécènes</h3>
                        <p class="stat-label">Nombre de membre</p>
                        <p class="stat-number"><?php echo number_format($stats['mecenes']['count']); ?></p>
                        <p class="stat-label">Contributions Totals</p>
                        <p class="stat-contribution"><?php echo number_format($stats['mecenes']['total'], 0, ',', ' '); ?> FCFA</p>
                        <a href="dashboard-list-member.php?category=mecenes" class="details-link">voir les details</a>
                    </div>
                </section>

                <section class="charts-section card">
                    <h2>Évolution mensuelle des contributions</h2>
                    <div class="chart-container">
                        <canvas id="contributionsChart"></canvas>
                    </div>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>

    <script>
        window.dashboardData = <?php echo $graphData; ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>