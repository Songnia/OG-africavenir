<?php
require_once 'includes/auth-check.php';
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
            <!-- Include Filer Barre-->
            <?php include('includes/filter-bare.php'); ?>
            <main>
                <section class="page-title-section">
                    <h2>Paiements</h2>
                    <a href="dashboard-register-paiement.php" class="btn btn-primary">
                      <img src="assets/icons/add-payment.svg" alt="" class="btn-icon"> Nouveau paiement
                    </a>
                </section>

                <section class="filters-section card">
                    <div class="filter-group search-filter">
                        <label for="paymentSearch" class="sr-only">Recherche Paiement</label>
                        <div class="search-input-container">
                             <img src="assets/icons/search.svg" alt="Icône de recherche" class="search-icon-prefix">
                            <input type="search" id="paymentSearch" name="paymentSearch" placeholder="recherche nom, Motif">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="filterPaymentMonth" class="sr-only">Mois</label>
                        <select id="filterPaymentMonth" name="filterPaymentMonth">
                            <option value="">Mois</option>
                            <option value="01">Janvier</option>
                            <option value="02">Fevrier</option>
                            <option value="03">Mars</option>
                            <option value="04">Avril</option>
                            <option value="05">Mai</option>
                            <option value="06">Juin</option>
                            <option value="07">Juillet</option>
                            <option value="08">Aout</option>
                            <option value="09">Septembre</option>
                            <option value="10">Octobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Décembre</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterPaymentStatus" class="sr-only">Statut</label>
                        <select id="filterPaymentStatus" name="filterPaymentStatus">
                            <option value="">Statut</option>
                            <option value="paye">Payé</option>
                            <option value="a-payer">À payer</option>
                            <option value="en-attente">En attente</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary">Appliquer</button>
                </section>

                <section class="table-section card">
                    <div class="table-responsive">
                        <table class="data-table" aria-label="Liste des paiements">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Montant</th>
                                    <th>Détail/Motif</th>
                                    <th>Mode</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                <!-- Loading indicator -->
                                <tr id="loadingRow">
                                    <td colspan="8" style="text-align: center; padding: 40px;">
                                        <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--africavenir-yellow); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                        <p style="margin-top: 10px; color: var(--text-muted);">Chargement des paiements...</p>
                                    </td>
                                </tr>
                                <!-- Error message -->
                                <tr id="errorRow" style="display: none;">
                                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--color-danger);">
                                        <p>Erreur lors du chargement des paiements.</p>
                                    </td>
                                </tr>
                                <!-- No results message -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        <p>Aucun paiement trouvé.</p>
                                    </td>
                                </tr>
                                <!-- Payments will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                    <nav class="pagination" aria-label="Pagination des paiements">
                        <a href="#" class="page-link disabled">< Précédent</a>
                        <a href="#" class="page-link active" aria-current="page">1</a>
                        <a href="#" class="page-link">2</a>
                        <a href="#" class="page-link">3</a>
                        <a href="#" class="page-link">Suivant ></a>
                    </nav>
                </section>
            </main>

            <footer class="site-footer main-footer">
                <p>©2025 AfricAvenir tous droits réservés</p>
            </footer>
        </div>
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div id="confirmDeleteModal" class="modal confirm-modal" aria-labelledby="confirmDeleteTitle" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h3 id="confirmDeleteTitle">Confirmer la suppression</h3>
                    <button type="button" class="btn-close-modal" aria-label="Fermer la modale">×</button>
                </header>
                <div class="modal-body">
                    <p id="confirmDeleteMessage">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel-delete">Annuler</button>
                    <button type="button" class="btn btn-danger btn-confirm-delete">Supprimer</button>
                </footer>
            </div>
        </div>
    </div>
    
    <!-- Modal de mise à jour du statut -->
    <div id="updateStatusModal" class="modal" aria-labelledby="updateStatusTitle" aria-hidden="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <header class="modal-header">
                    <h3 id="updateStatusTitle">Modifier le statut</h3>
                    <button type="button" class="btn-close-modal" onclick="closeUpdateStatusModal()" aria-label="Fermer la modale">×</button>
                </header>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="updateStatusId" name="id">
                        <div class="form-group">
                            <label for="newStatus">Nouveau Statut</label>
                            <select id="newStatus" name="status" class="form-control" required>
                                <option value="completed">Payé (Completed)</option>
                                <option value="pending">En attente (Pending)</option>
                                <option value="cancelled">Annulé (Cancelled)</option>
                                <option value="failed">Échoué (Failed)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUpdateStatusModal()">Annuler</button>
                    <button type="button" class="btn btn-primary" onclick="submitStatusUpdate()">Enregistrer</button>
                </footer>
            </div>
        </div>
    </div>

    <script src="js/payments-filter.js"></script>
    <script src="js/main.js"></script>
</body>
</html>