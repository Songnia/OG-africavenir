<!DOCTYPE html>
<html lang="fr">
<!-- Include Meta Data -->
 <?php 
 session_start();
 include('includes/meta-data.php'); 
 require_once 'src/Models/Member.php';
 
 // Check if user is logged in
 if (!isset($_SESSION['user_id'])) {
     header('Location: index.php');
     exit;
 }
 
 $memberModel = new \App\Models\Member();
 $members = $memberModel->getAllMembers();
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
                    <h2>Liste des membres</h2>
                    <div class="actions-group">
                        <a href=" member-make-donation.php?type=don" class="btn btn-secondary">Faire un Don</a>
                        <a href=" member-make-donation.php?type=contribution" class="btn btn-primary">Contribuer</a>
                    </div>
                </section>

                <section class="filters-section card">
                    <div class="filter-group search-filter">
                        <label for="memberListSearch" class="sr-only">Recherche</label>
                        <div class="search-input-container">
                            <img src="../assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="memberListSearch" name="memberListSearch" placeholder="recherche l'email, nom, prenom, telephone, ville">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterMetier" class="sr-only">Métier</label>
                        <select id="filterMetier" name="filterMetier">
                            <option value="">Métier</option>
                            <option value="medecin">Médecin</option>
                            <option value="informaticien">Informaticien</option>
                            <option value="agriculteur">Agriculteur</option>
                            <option value="fiscaliste">Fiscaliste</option>
                            <!-- Ajouter d'autres métiers/activités -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterMemberDate" class="sr-only">Date d'inscription (Mois)</label>
                        <input type="month" id="filterMemberDate" name="filterMemberDate" placeholder="Date">
                    </div>
                    <button type="button" class="btn btn-secondary" id="applyMemberListFilters">Appliquer</button>
                </section>

                <section class="table-section card">
                    <div class="table-responsive">
                        <table class="data-table" aria-label="Liste des membres de l'association">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Téléphone</th>
                                    <th>Nom</th>
                                    <th>Activité</th> <!-- Corrigé "Activiter" en "Activité" -->
                                    <th>Ville</th>
                                    <!-- Pas de colonne "Actions" pour la vue membre simple -->
                                </tr>
                            </thead>
                            <tbody id="memberListTableBody">
                                <!-- Loading indicator -->
                                <tr id="loadingRow">
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--africavenir-yellow); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                        <p style="margin-top: 10px; color: var(--text-muted);">Chargement des membres...</p>
                                    </td>
                                </tr>
                                <!-- Error message -->
                                <tr id="errorRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--color-danger);">
                                        <p>Erreur lors du chargement des membres.</p>
                                    </td>
                                </tr>
                                <!-- No results message -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        <p>Aucun membre trouvé.</p>
                                    </td>
                                </tr>
                                <!-- Members will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                    <nav class="pagination" aria-label="Pagination de la liste des membres">
                        <a href="#" class="page-link disabled">< Précédent</a>
                        <a href="#" class="page-link active" aria-current="page">1</a>
                        <a href="#" class="page-link">2</a>
                        <a href="#" class="page-link">Suivant ></a>
                    </nav>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    <script src="js/member-list-filter.js"></script>
    <script src="js/main.js"></script>
</body>
</html>