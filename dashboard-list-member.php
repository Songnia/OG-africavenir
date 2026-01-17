<?php
require_once 'includes/auth-check.php';
?>
<!DOCTYPE html>
<html lang="fr">

<!-- Include Meta Data -->
 <?php 
 include('includes/meta-data.php'); 
 require_once 'src/Controllers/MemberController.php';
 $memberController = new \App\Controllers\MemberController();
 $members = $memberController->index();
 ?>
<body>
    <div class="page-container">
        <!-- Include Header-->
        <?php include('includes/header.php'); ?>

        <div class="main-content">
            <!-- Include Filer Barre-->
            <?php include('includes/filter-bare.php'); ?>

            <main>
                <section class="page-title-section">
                    <h2>Membres</h2>
                    <a href="dashboard-register-member.php" class="btn btn-primary">
                      <img src="assets/icons/add-user.svg" alt="" class="btn-icon"> Créer membre
                    </a>
                </section>

                <section class="filters-section card">
                    <div class="filter-group search-filter">
                        <label for="memberSearch" class="sr-only">Recherche Membre</label>
                        <div class="search-input-container">
                            <img src="assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="memberSearch" name="memberSearch" placeholder="recherche l'email, nom, prenom, telephone, ville">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterActivity" class="sr-only">Activité</label>
                        <select id="filterActivity" name="filterActivity">
                            <option value="">Activité</option>
                            <option value="medecin">Médecin</option>
                            <option value="informaticien">Informaticien</option>
                            <option value="agriculteur">Agriculteur</option>
                            <option value="fiscaliste">Fiscaliste</option>
                            <!-- Ajouter d'autres activités -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterCategory" class="sr-only">Catégorie</label>
                        <select id="filterCategory" name="filterCategory">
                            <option value="">Catégorie</option>
                            <option value="member-ships">Member-ships</option>
                            <option value="alumni">Alumni</option>
                            <option value="cercle-amis">Cercle d'amis</option>
                            <option value="mecene">Mécène</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary">Appliquer</button>
                </section>

                <section class="table-section card">
                    <div class="table-responsive">
                        <table class="data-table" aria-label="Liste des membres">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Téléphone</th>
                                    <th>Nom</th>
                                    <th>Activité</th>
                                    <th>Ville</th>
                                    <th>Catégorie</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <!-- Loading indicator -->
                                <tr id="loadingRow">
                                    <td colspan="7" style="text-align: center; padding: 40px;">
                                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--africavenir-yellow); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                        <p style="margin-top: 10px; color: var(--text-muted);">Chargement des membres...</p>
                                    </td>
                                </tr>
                                <!-- Error message -->
                                <tr id="errorRow" style="display: none;">
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--color-danger);">
                                        <p>Erreur lors du chargement des membres.</p>
                                    </td>
                                </tr>
                                <!-- No results message -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
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
                    <nav class="pagination" aria-label="Pagination des membres">
                        <a href="#" class="page-link disabled">< Précédent</a>
                        <a href="#" class="page-link active" aria-current="page">1</a>
                        <a href="#" class="page-link">2</a>
                        <a href="#" class="page-link">3</a>
                        <span class="page-link-ellipsis">...</span>
                        <a href="#" class="page-link">10</a>
                        <a href="#" class="page-link">Suivant ></a>
                    </nav>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    <!-- Modal pour créer/modifier membre -->
    <div id="memberModal" class="modal" aria-labelledby="memberModalTitle" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h3 id="memberModalTitle">Membre</h3>
                    <button type="button" class="btn-close-modal" aria-label="Fermer la modale">×</button>
                </header>
                <form id="memberForm">
                    <input type="hidden" id="memberId" name="id">
                    <div class="modal-body">
                        <div class="form-row">
                             <div class="form-group">
                                <label for="modalNom">Nom</label>
                                <input type="text" id="modalNom" name="nom" required>
                            </div>
                            <div class="form-group">
                                <label for="modalPrenom">Prénom</label>
                                <input type="text" id="modalPrenom" name="prenom">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modalEmail">Email</label>
                            <input type="email" id="modalEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="modalTelephone">Téléphone</label>
                            <input type="tel" id="modalTelephone" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="modalVille">Ville</label>
                            <input type="text" id="modalVille" name="ville">
                        </div>
                        <div class="form-group">
                            <label for="modalActivite">Activité</label>
                            <input type="text" id="modalActivite" name="activite">
                        </div>
                         <div class="form-group">
                            <label for="modalCategorie">Catégorie</label>
                            <select id="modalCategorie" name="categorie" required>
                                <option value="member-ships">Member-ships</option>
                                <option value="alumni">Alumni</option>
                                <option value="cercle-amis">Cercle d'amis</option>
                                <option value="mecene">Mécène</option>
                            </select>
                        </div>
                    </div>
                    <footer class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-cancel-modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="saveMemberBtn">Sauvegarder</button>
                    </footer>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal pour voir les détails (View Only) -->
    <div id="viewMemberModal" class="modal" aria-labelledby="viewMemberModalTitle" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h3 id="viewMemberModalTitle">Détails du Membre</h3>
                    <button type="button" class="btn-close-modal" aria-label="Fermer la modale">×</button>
                </header>
                <div class="modal-body" id="viewMemberContent">
                    <!-- Content will be loaded here -->
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-close-modal">Fermer</button>
                </footer>
            </div>
        </div>
    </div>
    <div class="modal-backdrop" style="display: none;"></div>

    <script src="js/members-filter.js"></script>
    <script src="js/main.js"></script>
</body>
</html>