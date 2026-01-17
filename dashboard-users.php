<?php
require_once 'includes/admin-check.php';
require_once 'src/Helpers/RoleHelper.php';
require_once 'src/Controllers/MemberController.php';

use App\Helpers\RoleHelper;

// Strict Super Admin Check
if (!RoleHelper::isSuperAdmin($_SESSION['user_id'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$memberController = new \App\Controllers\MemberController();
$users = $memberController->index();
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
                    <h2>Gestion des Utilisateurs</h2>
                    <a href="user-edit.php" class="btn btn-primary">
                      <img src="assets/icons/add-user.svg" alt="" class="btn-icon"> Créer un utilisateur
                    </a>
                </section>

                <section class="filters-section card">
                    <div class="filter-group search-filter">
                        <label for="userSearch" class="sr-only">Recherche Utilisateur</label>
                        <div class="search-input-container">
                            <img src="assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="userSearch" name="userSearch" placeholder="Rechercher par email, nom...">
                        </div>
                    </div>
                </section>

                <section class="table-section card">
                    <div class="table-responsive">
                        <table class="data-table" aria-label="Liste des utilisateurs">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Loading indicator -->
                                <tr id="loadingRow">
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--africavenir-yellow); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                        <p style="margin-top: 10px; color: var(--text-muted);">Chargement des utilisateurs...</p>
                                    </td>
                                </tr>
                                <!-- Error message -->
                                <tr id="errorRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--color-danger);">
                                        <p>Erreur lors du chargement des utilisateurs.</p>
                                    </td>
                                </tr>
                                <!-- No results message -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        <p>Aucun utilisateur trouvé.</p>
                                    </td>
                                </tr>
                                <!-- Users will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteUserModal" class="modal" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h3>Confirmer la suppression</h3>
                    <button type="button" class="btn-close-modal">×</button>
                </header>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel-modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">Supprimer</button>
                </footer>
            </div>
        </div>
    </div>
    <div class="modal-backdrop" style="display: none;"></div>

    <script src="js/users-filter.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Simple script for delete modal since main.js might not cover this specific page's delete logic yet
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const modal = document.getElementById('deleteUserModal');
            const confirmBtn = document.getElementById('confirmDeleteUserBtn');
            const cancelBtns = document.querySelectorAll('.btn-cancel-modal, .btn-close-modal');
            const backdrop = document.querySelector('.modal-backdrop');
            let userIdToDelete = null;

            function openModal() {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                backdrop.style.display = 'block';
            }

            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                backdrop.style.display = 'none';
                userIdToDelete = null;
            }

            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    userIdToDelete = this.getAttribute('data-id');
                    openModal();
                });
            });

            cancelBtns.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });

            confirmBtn.addEventListener('click', function() {
                if (userIdToDelete) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', userIdToDelete);

                    fetch('user-action-handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
