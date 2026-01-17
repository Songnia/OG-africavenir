// main.js
document.addEventListener('DOMContentLoaded', function () {
    console.log("DOM entièrement chargé et analysé pour AfricAvenir");

    // Login form is handled by PHP
    const loginForm = document.getElementById('loginForm');
    // if (loginForm) { ... } removed to allow PHP submission

    const signupForm = document.getElementById('signupForm');
    /* if (signupForm) {
         signupForm.addEventListener('submit', function (event) {
             event.preventDefault();
             console.log('Formulaire d\'inscription soumis');
 
             const password = document.getElementById('motdepasse').value;
             const confirmPassword = document.getElementById('confirmer_motdepasse').value;
 
             if (password !== confirmPassword) {
                 alert('Les mots de passe ne correspondent pas.');
                 return; // Arrête la soumission si les mots de passe ne correspondent pas
             }
 
             // Autres validations possibles ici (longueur du mot de passe, format email, etc.)
             // Pour la démo, on considère que c'est bon si les mots de passe correspondent
             alert('Inscription simulée réussie ! Vous seriez redirigé vers login.html ou dashboard.html');
             // window.location.href = 'login.html'; // Rediriger vers la connexion après inscription
         });
     }*/

    // Initialisation des autres pages (sera ajouté plus tard)
});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    console.log("DOM entièrement chargé et analysé pour AfricAvenir");

    const loginForm = document.getElementById('loginForm');
    // Listener removed to allow PHP submission

    const signupForm = document.getElementById('signupForm');
    /*if (signupForm) {
        signupForm.addEventListener('submit', function (event) {
            event.preventDefault();
            console.log('Formulaire d\'inscription soumis');

            const password = document.getElementById('motdepasse').value;
            const confirmPassword = document.getElementById('confirmer_motdepasse').value;

            if (password !== confirmPassword) {
                alert('Les mots de passe ne correspondent pas.');
                return; // Arrête la soumission si les mots de passe ne correspondent pas
            }

            // Autres validations possibles ici (longueur du mot de passe, format email, etc.)
            // Pour la démo, on considère que c'est bon si les mots de passe correspondent
            alert('Inscription simulée réussie ! Vous seriez redirigé vers login.html ou dashboard.html');
            // window.location.href = 'login.html'; // Rediriger vers la connexion après inscription
        });
    }*/

    // Initialisation des autres pages (sera ajouté plus tard)
});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    console.log("DOM entièrement chargé et analysé pour AfricAvenir");

    // ... (code pour loginForm et signupForm) ...

    // Gestion du menu mobile
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            const isExpanded = sidebar.classList.contains('open');
            mobileMenuToggle.setAttribute('aria-expanded', isExpanded);
            if (isExpanded) {
                mobileMenuToggle.setAttribute('aria-label', 'Fermer le menu');
            } else {
                mobileMenuToggle.setAttribute('aria-label', 'Ouvrir le menu');
            }
        });
    }

    // Initialisation du graphique Chart.js pour la page dashboard
    const contributionsChartCanvas = document.getElementById('contributionsChart');
    if (contributionsChartCanvas) {
        const ctx = contributionsChartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line', // Type de graphique (ligne, barre, etc.)
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'], // Mois
                datasets: [{
                    label: 'Contributions Totales (FCFA)',
                    data: window.dashboardData || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Use real data or empty
                    borderColor: '#f0ad4e', // Couleur de la ligne (jaune AfricAvenir)
                    backgroundColor: 'rgba(240, 173, 78, 0.1)', // Couleur de remplissage sous la ligne
                    tension: 0.1, // Courbure de la ligne
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#f0f0f0' // Couleur du texte des axes Y
                        },
                        grid: {
                            color: 'rgba(240, 240, 240, 0.1)' // Couleur des lignes de la grille Y
                        }
                    },
                    x: {
                        ticks: {
                            color: '#f0f0f0' // Couleur du texte des axes X
                        },
                        grid: {
                            color: 'rgba(240, 240, 240, 0.1)' // Couleur des lignes de la grille X
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#f0f0f0' // Couleur du texte de la légende
                        }
                    }
                }
            }
        });
    }

    // ===== DASHBOARD FILTERS =====

    // Real-time search filter for members table
    const mainSearch = document.getElementById('mainSearch');
    if (mainSearch) {
        mainSearch.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const table = document.querySelector('.data-table tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Dashboard filters (Month/Year) with reload
    const applyFiltersButton = document.querySelector('.filters-section .btn-secondary');
    if (applyFiltersButton) {
        applyFiltersButton.addEventListener('click', function () {
            const month = document.getElementById('filterMonth')?.value || '';
            const year = document.getElementById('filterYear')?.value || '';

            // Reload page with filter parameters
            const url = new URL(window.location.href);
            if (month) url.searchParams.set('filterMonth', month);
            else url.searchParams.delete('filterMonth');

            if (year) url.searchParams.set('filterYear', year);
            else url.searchParams.delete('filterYear');

            window.location.href = url.toString();
        });
    }

    // Payment search filter
    const paymentSearch = document.getElementById('paymentSearch');
    if (paymentSearch) {
        paymentSearch.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const table = document.querySelector('.data-table tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Payment status filter
    const statusFilter = document.getElementById('filterPaymentStatus');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            const selectedStatus = this.value.toLowerCase();
            const table = document.querySelector('.data-table tbody');
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                if (!selectedStatus) {
                    row.style.display = '';
                    return;
                }

                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge) {
                    const rowStatus = statusBadge.textContent.toLowerCase();
                    row.style.display = rowStatus.includes(selectedStatus) ? '' : 'none';
                }
            });
        });
    }
});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    // ... (code précédent pour auth, menu mobile, chart, filtres dashboard) ...

    // Gestion de la modale pour créer/modifier membre
    const openCreateMemberModalButton = document.getElementById('openCreateMemberModal');
    const memberModal = document.getElementById('memberModal');
    const closeModalButtons = document.querySelectorAll('.btn-close-modal, .btn-cancel-modal');
    const memberForm = document.getElementById('memberForm');
    const modalBackdrop = document.querySelector('.modal-backdrop'); // Si vous utilisez un backdrop géré par JS

    function openModal(modalElement) {
        if (modalElement) {
            modalElement.classList.add('open');
            modalElement.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden'; // Empêche le scroll de l'arrière-plan
            if (modalBackdrop) modalBackdrop.style.display = 'block';

            // Focus sur le premier élément focusable dans la modale (amélioration pour accessibilité)
            const firstFocusableElement = modalElement.querySelector('input, select, button, [tabindex]:not([tabindex="-1"])');
            if (firstFocusableElement) {
                firstFocusableElement.focus();
            }
        }
    }

    function closeModal(modalElement) {
        if (modalElement) {
            modalElement.classList.remove('open');
            modalElement.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = ''; // Rétablit le scroll
            if (modalBackdrop) modalBackdrop.style.display = 'none';
        }
    }

    if (openCreateMemberModalButton && memberModal) {
        openCreateMemberModalButton.addEventListener('click', function () {
            // Vous pourriez vouloir réinitialiser le formulaire ici ou changer le titre
            document.getElementById('memberModalTitle').textContent = 'Créer un nouveau membre';
            if (memberForm) memberForm.reset(); // Réinitialise le formulaire
            openModal(memberModal);
        });
    }

    closeModalButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Close whichever modal is currently open
            const openModal = document.querySelector('.modal.open');
            if (openModal) {
                closeModal(openModal);
            }
        });
    });

    // Fermer la modale en cliquant en dehors (sur le backdrop)
    if (memberModal) {
        memberModal.addEventListener('click', function (event) {
            if (event.target === memberModal) { // Si le clic est sur le .modal lui-même (fond)
                closeModal(memberModal);
            }
        });
    }

    // Handle viewMemberModal backdrop click
    const viewMemberModal = document.getElementById('viewMemberModal');
    if (viewMemberModal) {
        viewMemberModal.addEventListener('click', function (event) {
            if (event.target === viewMemberModal) {
                closeModal(viewMemberModal);
            }
        });
    }

    // Fermer la modale avec la touche Echap
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const openModalElement = document.querySelector('.modal.open');
            if (openModalElement) {
                closeModal(openModalElement);
            }
        }
    });


    if (memberForm) {
        memberForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(memberForm);

            fetch('member-create-handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Membre créé avec succès !');
                        closeModal(memberModal);
                        location.reload(); // Reload to show new member
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors de la création du membre.');
                });
        });
    }

    // Logique pour les filtres de la page membres (similaire au dashboard)
    const applyMemberFiltersButton = document.querySelector('.filters-section .btn-secondary'); // S'assurer que c'est le bon bouton
    if (applyMemberFiltersButton && window.location.pathname.includes('members.html')) { // Appliquer seulement sur la page membres
        applyMemberFiltersButton.addEventListener('click', function () {
            const search = document.getElementById('memberSearch').value;
            const activity = document.getElementById('filterActivity').value;
            const category = document.getElementById('filterCategory').value;
            console.log("Filtres membres appliqués:", { search, activity, category });
            alert(`Filtres membres: Recherche="${search}", Activité="${activity}", Catégorie="${category}". Mise à jour non implémentée.`);
            // Ici, vous feriez un appel AJAX pour recharger les données du tableau
        });
    }

    // Gestion des boutons d'action dans le tableau des membres
    const table = document.querySelector('.data-table');
    if (table && window.location.pathname.includes('dashboard-list-member.php')) {
        table.addEventListener('click', function (event) {
            const targetButton = event.target.closest('.btn-icon');
            if (!targetButton) return;

            const row = targetButton.closest('tr');
            const memberId = row.cells[0].textContent; // ID is in first cell
            const memberName = row.cells[2].textContent; // Name in 3rd cell

            if (targetButton.classList.contains('btn-delete')) {
                if (confirm(`Êtes-vous sûr de vouloir supprimer ${memberName} ?`)) {
                    fetch('member-action-handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${memberId}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                row.remove();
                            } else {
                                alert('Erreur: ' + data.message);
                            }
                        });
                }
            } else if (targetButton.classList.contains('btn-edit')) {
                // Redirect to register form with ID for editing
                window.location.href = `dashboard-register-member.php?id=${memberId}`;
            } else if (targetButton.classList.contains('btn-view')) {
                fetch(`member-action-handler.php?action=get&id=${memberId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const member = data.data;
                            const content = `
                            <div class="member-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <p><strong>ID:</strong> ${member.ID}</p>
                                <p><strong>Date d'inscription:</strong> ${member.user_registered}</p>
                                <p><strong>Nom:</strong> ${member.last_name || member.display_name}</p>
                                <p><strong>Prénom:</strong> ${member.first_name || ''}</p>
                                <p><strong>Email:</strong> ${member.user_email}</p>
                                <p><strong>Téléphone:</strong> ${member.telephone || 'N/A'}</p>
                                <p><strong>Date de naissance:</strong> ${member.date_naissance || 'N/A'}</p>
                                <p><strong>État civil:</strong> ${member.etat_civil || 'N/A'}</p>
                                <p><strong>Adresse:</strong> ${member.adresse || 'N/A'}</p>
                                <p><strong>Boîte postale:</strong> ${member.boite_postale || 'N/A'}</p>
                                <p><strong>Pays:</strong> ${member.pays || 'N/A'}</p>
                                <p><strong>Quartier:</strong> ${member.quartier || 'N/A'}</p>
                                <p><strong>Ville:</strong> ${member.ville || 'N/A'}</p>
                                <p><strong>Activité:</strong> ${member.activite || 'N/A'}</p>
                                <p><strong>Statut Pro:</strong> ${member.statut_professionnel || 'N/A'}</p>
                                <p><strong>Catégorie:</strong> ${member.categorie || 'N/A'}</p>
                                <p><strong>Parcours:</strong> ${member.parcours_type || 'N/A'}</p>
                                <p><strong>Etablissement:</strong> ${member.etablissement_scolaire || 'N/A'}</p>
                                <p><strong>Diplôme:</strong> ${member.dernier_diplome || 'N/A'}</p>
                                <p><strong>Centre d'intérêt:</strong> ${member.centre_interet || 'N/A'}</p>
                                <p><strong>Contribution:</strong> ${member.montant_contribution || 'N/A'} ${member.montant_contribution === 'libre' ? '(' + (member.montant_libre_val || '') + ')' : ''}</p>
                                <p><strong>Mode Paiement:</strong> ${member.mode_paiement_contribution || 'N/A'}</p>
                            </div>
                        `;
                            document.getElementById('viewMemberContent').innerHTML = content;
                            openModal(document.getElementById('viewMemberModal'));
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    });
            }
        });
    }

    // Handle Edit Form Submission
    if (memberForm) {
        memberForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const id = document.getElementById('memberId').value;
            const formData = new FormData(memberForm);

            // If ID exists, it's an update
            if (id) {
                formData.append('action', 'update');
                formData.append('id', id);

                fetch('member-action-handler.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Membre mis à jour avec succès !');
                            closeModal(memberModal);
                            location.reload();
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    });
            } else {
                // Create logic (already handled or needs to be merged)
                // For now, let's assume create is handled by the separate wizard page, 
                // but if we use this modal for creation too, we need to handle it.
                // The previous create logic was for the wizard. 
                // If this form is used for creation, we need to add creation logic here too.
                // But the user asked for "Edit, Delete, Show Detail".
            }
        });
    }

});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    // ... (code précédent pour auth, menu mobile, chart, filtres dashboard, gestion modale membre) ...

    // Gestion de la modale pour nouveau/modifier paiement
    const openNewPaymentModalButton = document.getElementById('openNewPaymentModal');
    const paymentModal = document.getElementById('paymentModal');
    const paymentForm = document.getElementById('paymentForm');
    // Les .btn-close-modal et .btn-cancel-modal sont déjà gérés par la logique de la modale membre si les classes sont les mêmes
    // et si la fonction closeModal est générique. Assurons-nous que c'est le cas.

    // Rendons les fonctions openModal et closeModal plus génériques
    // (Déjà fait dans l'itération précédente, on s'assure qu'elles sont bien utilisées)

    /*if (openNewPaymentModalButton && paymentModal) {
        openNewPaymentModalButton.addEventListener('click', function () {
            document.getElementById('paymentModalTitle').textContent = 'Nouveau paiement';
            if (paymentForm) paymentForm.reset();
            // Mettre la date actuelle par défaut pour un nouveau paiement
            const today = new Date().toISOString().split('T')[0];
            const modalPaymentDateInput = document.getElementById('modalPaymentDate');
            if (modalPaymentDateInput) {
                modalPaymentDateInput.value = today;
            }
            openModal(paymentModal);
        });
    }*/

    // La fermeture de la modale paymentModal (par X, Annuler, Escape, clic extérieur)
    // est gérée par les gestionnaires d'événements globaux déjà mis en place pour memberModal,
    // à condition que paymentModal ait les mêmes classes pour les boutons de fermeture et le fond.
    // Si vous avez besoin d'une logique spécifique à la fermeture de paymentModal, ajoutez-la ici.
    // Par exemple, pour les boutons spécifiques à paymentModal:
    const paymentModalCloseButtons = paymentModal ? paymentModal.querySelectorAll('.btn-close-modal, .btn-cancel-modal') : [];
    paymentModalCloseButtons.forEach(button => {
        button.addEventListener('click', function () {
            closeModal(paymentModal);
        });
    });
    if (paymentModal) {
        paymentModal.addEventListener('click', function (event) {
            if (event.target === paymentModal) {
                closeModal(paymentModal);
            }
        });
        // Le gestionnaire 'keydown' pour Escape est global et devrait déjà fonctionner.
    }


    /*if (paymentForm) {
        paymentForm.addEventListener('submit', function (event) {
            event.preventDefault();
            console.log('Formulaire paiement soumis:', new FormData(paymentForm));
            alert('Paiement sauvegardé (simulation) !');
            closeModal(paymentModal);
        });
    }*/

    // Logique pour les filtres de la page paiements
    const applyPaymentFiltersButton = document.querySelector('.filters-section .btn-secondary');
    if (applyPaymentFiltersButton && window.location.pathname.includes('payments.html')) {
        applyPaymentFiltersButton.addEventListener('click', function () {
            const search = document.getElementById('paymentSearch').value;
            const month = document.getElementById('filterPaymentMonth').value;
            const status = document.getElementById('filterPaymentStatus').value;
            console.log("Filtres paiements appliqués:", { search, month, status });
            alert(`Filtres paiements: Recherche="${search}", Mois="${month}", Statut="${status}". Mise à jour non implémentée.`);
        });
    }

    // Gestion des boutons d'action dans le tableau des paiements
    const paymentsTable = document.querySelector('.data-table');
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    const confirmDeleteMessage = document.getElementById('confirmDeleteMessage');
    const btnConfirmDelete = document.querySelector('.btn-confirm-delete');
    const btnCancelDelete = document.querySelector('.btn-cancel-delete');
    const btnCloseConfirmModal = confirmDeleteModal ? confirmDeleteModal.querySelector('.btn-close-modal') : null;

    let pendingDeletePaymentId = null;
    let pendingDeleteMemberName = null;

    // Fonctions pour gérer la modale de confirmation
    function showConfirmDeleteModal(paymentId, memberName) {
        if (!confirmDeleteModal) return;

        pendingDeletePaymentId = paymentId;
        pendingDeleteMemberName = memberName;

        confirmDeleteMessage.textContent = `Voulez-vous vraiment supprimer le paiement #${paymentId} de ${memberName} ?`;
        confirmDeleteModal.classList.add('open');
        confirmDeleteModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function hideConfirmDeleteModal() {
        if (!confirmDeleteModal) return;

        confirmDeleteModal.classList.remove('open');
        confirmDeleteModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        pendingDeletePaymentId = null;
        pendingDeleteMemberName = null;
    }

    // Event listeners pour la modale de confirmation
    if (btnCancelDelete) {
        btnCancelDelete.addEventListener('click', hideConfirmDeleteModal);
    }

    if (btnCloseConfirmModal) {
        btnCloseConfirmModal.addEventListener('click', hideConfirmDeleteModal);
    }

    if (confirmDeleteModal) {
        // Fermer en cliquant sur le fond
        confirmDeleteModal.addEventListener('click', function (event) {
            if (event.target === confirmDeleteModal) {
                hideConfirmDeleteModal();
            }
        });

        // Fermer avec la touche Escape
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && confirmDeleteModal.classList.contains('open')) {
                hideConfirmDeleteModal();
            }
        });
    }

    // Confirmer la suppression
    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function () {
            if (!pendingDeletePaymentId) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', pendingDeletePaymentId);

            fetch('payment-action-handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    hideConfirmDeleteModal();
                    if (data.success) {
                        alert('Paiement supprimé avec succès.');
                        location.reload();
                    } else {
                        alert('Erreur lors de la suppression : ' + data.message);
                    }
                })
                .catch(error => {
                    hideConfirmDeleteModal();
                    console.error('Error:', error);
                    alert('Une erreur est survenue.');
                });
        });
    }

    console.log('Payment table found:', paymentsTable);
    console.log('Current pathname:', window.location.pathname);
    console.log('Pathname includes dashboard-list-paiement.php:', window.location.pathname.includes('dashboard-list-paiement.php'));

    if (paymentsTable && window.location.pathname.includes('dashboard-list-paiement.php')) {
        console.log('Attaching payment action listener to table');
        paymentsTable.addEventListener('click', function (event) {
            console.log('Table clicked, target:', event.target);
            const targetButton = event.target.closest('.btn-icon');
            console.log('Closest btn-icon:', targetButton);

            if (!targetButton || targetButton.disabled) return;

            const row = targetButton.closest('tr');
            const paymentId = row.cells[0].textContent;
            const memberName = row.cells[1].textContent;

            console.log('Button classes:', targetButton.className);
            console.log('Payment ID:', paymentId, 'Member:', memberName);

            if (targetButton.classList.contains('btn-edit-payment')) {
                console.log('Edit button clicked');
                window.location.href = `dashboard-register-paiement.php?id=${paymentId}`;
            } else if (targetButton.classList.contains('btn-delete-payment')) {
                showConfirmDeleteModal(paymentId, memberName);
            } else if (targetButton.classList.contains('btn-receipt')) {
                console.log('Receipt button clicked');
                alert(`Affichage du reçu pour le paiement #${paymentId} (non implémenté).`);
            }
        });
    } else {
        console.log('Payment table listener NOT attached - table:', !!paymentsTable, 'pathname check:', window.location.pathname.includes('dashboard-list-paiement.php'));
    }

});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    // ... (code pour auth, menu mobile, chart, filtres dashboard) ...

    // SUPPRESSION de la logique des modales membres et paiements
    // (Tout ce qui concerne openCreateMemberModalButton, memberModal, openNewPaymentModalButton, paymentModal, etc.
    // et les fonctions openModal/closeModal si elles n'étaient utilisées que pour cela)
    // Assurez-vous de bien supprimer les anciennes logiques pour éviter les conflits.

    // Gestion du formulaire de nouveau paiement (create-payment.html)
    /*const newPaymentForm = document.getElementById('newPaymentForm');
    if (newPaymentForm) {
        // Mettre la date actuelle par défaut
        const today = new Date().toISOString().split('T')[0];
        const paymentDateInput = document.getElementById('paymentDate');
        if (paymentDateInput) {
            paymentDateInput.value = today;
        }

        newPaymentForm.addEventListener('submit', function (event) {
            event.preventDefault();
            console.log('Nouveau paiement soumis:', new FormData(newPaymentForm));
            alert('Paiement enregistré (simulation) ! Redirection vers la liste des paiements...');
            // window.location.href = 'payments.html'; // Redirection après soumission
        });
    }*/

    // Gestion du Wizard pour enregistrer un membre (register-member.html)
    const registerMemberForm = document.getElementById('registerMemberForm');
    if (registerMemberForm) {
        const wizardSteps = Array.from(registerMemberForm.querySelectorAll('.wizard-step'));
        const stepIndicators = Array.from(document.querySelectorAll('.wizard-steps .step-indicator'));
        const nextButtons = Array.from(registerMemberForm.querySelectorAll('.wizard-next'));
        const backButtons = Array.from(registerMemberForm.querySelectorAll('.wizard-back'));
        const finishButton = registerMemberForm.querySelector('.wizard-finish');
        const montantLibreRadio = registerMemberForm.querySelector('input[name="montant_contribution"][value="libre"]');
        const montantLibreValueInput = document.getElementById('regMontantLibreVal');

        let currentStep = 0;

        function updateWizardUI() {
            wizardSteps.forEach((step, index) => {
                step.classList.toggle('active', index === currentStep);
            });
            stepIndicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentStep);
                indicator.classList.toggle('completed', index < currentStep);
            });
            // Gérer l'état des boutons back (désactivé à la première étape)
            if (backButtons[currentStep]) { // S'assurer que le bouton existe pour l'étape actuelle
                backButtons[currentStep].disabled = (currentStep === 0);
            }
        }

        function validateStep(stepIndex) {
            // Validation basique HTML5. Peut être étendue.
            const currentStepFields = wizardSteps[stepIndex].querySelectorAll('input[required], select[required]');
            for (let field of currentStepFields) {
                if (!field.checkValidity()) {
                    field.reportValidity(); // Affiche le message d'erreur du navigateur
                    return false;
                }
            }
            return true;
        }

        nextButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (validateStep(currentStep) && currentStep < wizardSteps.length - 2) { // -2 car la dernière est la confirmation
                    currentStep++;
                    updateWizardUI();
                }
            });
        });

        backButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateWizardUI();
                }
            });
        });

        if (finishButton) {
            finishButton.addEventListener('click', function (event) { // Change to handle submit event on form for better practice
                event.preventDefault(); // Prevent default if it's not the form's submit handler

                // Check if PayPal is selected - if so, skip this handler (paypal-integration.js handles it)
                const selectedPaymentMode = document.querySelector('input[name="mode_paiement_contribution"]:checked');
                if (selectedPaymentMode) {
                    const mode = selectedPaymentMode.value;
                    if (mode === 'paypal') {
                        console.log('PayPal selected - skipping default handler');
                        return; // Let paypal-integration.js handle the submission
                    }
                    if (mode === 'card' || mode === 'carte') {
                        console.log('Stripe (Card) selected - skipping default handler');
                        return; // Let stripe-integration.js handle the submission
                    }
                }

                if (validateStep(currentStep)) {
                    // Soumettre le formulaire via AJAX
                    const formData = new FormData(registerMemberForm);

                    // Ajouter le montant libre si sélectionné
                    if (montantLibreRadio && montantLibreRadio.checked) {
                        formData.set('montant_contribution', montantLibreValueInput.value);
                    }

                    let url = 'member-create-handler.php';
                    if (formData.has('action') && formData.get('action') === 'update') {
                        url = 'member-action-handler.php';
                    }

                    fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Opération réussie', data);

                                // If payment URL exists, redirect immediately to CinetPay
                                if (data.payment_url) {
                                    window.location.href = data.payment_url;
                                    return; // Exit to prevent further execution
                                }

                                // Only show credentials if no payment (shouldn't happen in signup flow)
                                if (data.credentials) {
                                    const credentialsDisplay = document.getElementById('credentialsDisplay');
                                    const usernameInput = document.getElementById('displayUsername');
                                    const passwordInput = document.getElementById('displayPassword');

                                    if (credentialsDisplay && usernameInput && passwordInput) {
                                        usernameInput.value = data.credentials.username;
                                        passwordInput.value = data.credentials.password;
                                        credentialsDisplay.style.display = 'block';
                                    }
                                }

                                // Passer à l'étape de confirmation (only if no payment)
                                currentStep = wizardSteps.length - 1;
                                updateWizardUI();
                            } else {
                                alert('Erreur: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Une erreur est survenue.');
                        });
                }
            });
        }

        // Gérer l'affichage du champ "montant libre"
        if (montantLibreRadio && montantLibreValueInput) {
            const contributionRadios = registerMemberForm.querySelectorAll('input[name="montant_contribution"]');
            contributionRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    montantLibreValueInput.style.display = (montantLibreRadio.checked) ? 'inline-block' : 'none';
                    montantLibreValueInput.required = montantLibreRadio.checked;
                    if (!montantLibreRadio.checked) {
                        montantLibreValueInput.value = ''; // Clear value if not selected
                    }
                });
            });
        }

        updateWizardUI(); // Initialisation de l'UI du wizard
    }

    // Assurez-vous que les gestionnaires de filtres des pages table (members, payments) ne s'activent
    // que sur leurs pages respectives pour éviter les erreurs JS sur les nouvelles pages de formulaire.
    // Exemple (déjà fait dans les itérations précédentes, mais à vérifier) :
    const applyMemberFiltersButton = document.querySelector('#membersContent .filters-section .btn-secondary'); // Suppose un ID parent pour le contenu de la page membres
    if (applyMemberFiltersButton /* && window.location.pathname.includes('members.html') */) {
        // ...
    }
    // Faire de même pour les filtres de paiements et les actions de table.

});
// main.js
/*document.addEventListener('DOMContentLoaded', function () {
    // ... (tout le code JS existant pour admin, login, signup, formulaires, etc.)

    // Logique pour la page Mes Contributions (Espace Membre)
    const contributionsPage = document.querySelector('.sidebar.member-sidebar'); // Indicateur qu'on est dans l'espace membre
    const contributionsTableBody = document.getElementById('contributionsTableBody');
    const filterTabsContainer = document.querySelector('.filter-tabs');

    if (contributionsPage && contributionsTableBody && filterTabsContainer) {
        const tabButtons = filterTabsContainer.querySelectorAll('.tab-button');
        const dateFilterInput = document.getElementById('filterDate'); // Pour filtrer par mois/année
        const searchFilterInput = document.getElementById('contributionSearch');

        // Simuler des données (pourrait venir d'un API)
        const allUserTransactions = [
            { id: '01', type: 'contribution', motif: 'Contribution Janvier', montant: '15000f', date: '2025-01-17' },
            { id: '02', type: 'contribution', motif: 'Contributions Février', montant: '15000f', date: '2025-02-17' },
            { id: '03', type: 'don', motif: 'Don exceptionnel', montant: '50000f', date: '2025-03-17' },
            { id: '04', type: 'contribution', motif: 'Contribution Avril', montant: '15000f', date: '2025-04-17' },
            { id: '05', type: 'don', motif: 'Soutien projet X', montant: '25000f', date: '2025-04-20' },
            { id: '06', type: 'contribution', motif: 'Contribution Mai', montant: '15000f', date: '2025-05-17' },
        ];

        function formatDateForDisplay(dateString) { // AAAA-MM-JJ -> JJ/MM/AAAA
            const [year, month, day] = dateString.split('-');
            return `${day}/${month}/${year}`;
        }

        function renderTable(transactions) {
            contributionsTableBody.innerHTML = ''; // Vider le tableau
            if (transactions.length === 0) {
                contributionsTableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Aucune transaction trouvée.</td></tr>';
                return;
            }
            transactions.forEach(tr => {
                const row = document.createElement('tr');
                row.innerHTML = `
                  <td>${tr.id}</td>
                  <td>${tr.motif}</td>
                  <td>${tr.montant}</td>
                  <td>${formatDateForDisplay(tr.date)}</td>
              `;
                contributionsTableBody.appendChild(row);
            });
        }

        function applyFilters() {
            const activeTabFilter = filterTabsContainer.querySelector('.tab-button.active').dataset.filter;
            const selectedDate = dateFilterInput.value; // Format AAAA-MM
            const searchTerm = searchFilterInput.value.toLowerCase();

            let filteredTransactions = allUserTransactions;

            // Filtrer par type (onglet actif)
            if (activeTabFilter === 'contributions') {
                filteredTransactions = filteredTransactions.filter(tr => tr.type === 'contribution');
            } else if (activeTabFilter === 'dons') {
                filteredTransactions = filteredTransactions.filter(tr => tr.type === 'don');
            }

            // Filtrer par date (mois/année)
            if (selectedDate) { // selectedDate est au format "YYYY-MM"
                filteredTransactions = filteredTransactions.filter(tr => tr.date.startsWith(selectedDate));
            }

            // Filtrer par terme de recherche (motif)
            if (searchTerm) {
                filteredTransactions = filteredTransactions.filter(tr => tr.motif.toLowerCase().includes(searchTerm));
            }

            renderTable(filteredTransactions);
            // Ici, vous mettriez à jour la pagination si elle était dynamique
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', function () {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                applyFilters();
            });
        });

        if (dateFilterInput) dateFilterInput.addEventListener('change', applyFilters);
        if (searchFilterInput) searchFilterInput.addEventListener('input', applyFilters);

        // Rendu initial basé sur l'onglet "Mes contributions" actif par défaut
        applyFilters();
    }

    // ... (fin du DOMContentLoaded)
});*/
// main.js
/*
// ===== MEMBER LIST PAGE (Disabled - now using PHP data) =====
document.addEventListener('DOMContentLoaded', function () {
    // Logique pour la page Liste des Membres (Espace Membre)
    const memberListPage = document.querySelector('.sidebar.member-sidebar');
    const memberListTableBody = document.getElementById('memberListTableBody');
    // ... (Code de simulation désactivé) ...
});
*/
// main.js
document.addEventListener('DOMContentLoaded', function () {
    // ... (tout le code JS existant) ...

    // Logique pour la page Boîte de Réception (Espace Membre)
    const inboxPage = document.querySelector('.sidebar.member-sidebar'); // Indicateur espace membre
    const messageList = document.querySelector('.message-list');

    if (inboxPage && messageList && window.location.pathname.includes('member_inbox.html')) {
        console.log("Page Boîte de réception (membre) initialisée.");
        // Ici, vous pourriez ajouter la logique pour charger les messages,
        // gérer le clic sur un message, etc.
        // Par exemple, marquer un message comme lu au clic:
        messageList.addEventListener('click', function (event) {
            const messageLink = event.target.closest('.message-link');
            if (messageLink) {
                const messageItem = messageLink.closest('.message-item');
                if (messageItem && messageItem.classList.contains('unread')) {
                    // Simuler l'ouverture et le marquage comme lu
                    // event.preventDefault(); // Si le lien mène ailleurs, sinon inutile
                    // messageItem.classList.remove('unread');
                    // alert("Message ouvert (simulation). Il serait marqué comme lu.");
                    // Idéalement, cela impliquerait un appel API pour marquer comme lu côté serveur
                    // et potentiellement naviguer vers une vue détaillée du message.
                }
            }
        });
    }
    // ... (fin du DOMContentLoaded)
    // ... (fin du DOMContentLoaded)

    // Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggleBtn'); // Changed ID
    const themeIcon = document.getElementById('themeIcon'); // Changed selector to ID
    const body = document.body;

    // Check saved theme or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        body.setAttribute('data-theme', 'dark');
        if (themeIcon) themeIcon.src = 'assets/icons/sun.svg';
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = body.getAttribute('data-theme') === 'dark';
            if (isDark) {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                if (themeIcon) themeIcon.src = 'assets/icons/moon.svg';
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if (themeIcon) themeIcon.src = 'assets/icons/sun.svg';
            }
        });
    }

});
// main.js
document.addEventListener('DOMContentLoaded', function () {
    // ... (tout le code JS existant) ...

    // Logique pour la page Faire un Don/Contribution (Espace Membre)
    const donationFormPage = document.getElementById('memberDonationForm'); // ID du formulaire

    if (donationFormPage) {
        const formTitle = document.getElementById('donationFormTitle');
        const paymentTypeInput = document.getElementById('paymentType');
        const motifContainer = document.getElementById('motifContainer');
        const donationMotifInput = document.getElementById('donationMotif');
        const submitButton = document.getElementById('submitDonationButton');
        const paymentDetailsSection = document.getElementById('paymentDetailsSection');
        const donationModeSelect = document.getElementById('donationMode');

        // Récupérer le type de paiement depuis l'URL (don ou contribution)
        const urlParams = new URLSearchParams(window.location.search);
        const paymentPurpose = urlParams.get('type') || 'contribution'; // 'contribution' par défaut

        if (paymentPurpose === 'don') {
            if (formTitle) formTitle.textContent = 'Faire un Don';
            if (paymentTypeInput) paymentTypeInput.value = 'don';
            if (motifContainer) motifContainer.style.display = 'block'; // Ou 'none' si les dons n'ont pas de motif
            if (donationMotifInput) donationMotifInput.placeholder = "Motif du don (optionnel)";
            if (donationMotifInput) donationMotifInput.required = false; // Le motif peut être optionnel pour un don
            if (submitButton) submitButton.textContent = 'Faire un Don';
        } else { // contribution
            if (formTitle) formTitle.textContent = 'Effectuer une Contribution';
            if (paymentTypeInput) paymentTypeInput.value = 'contribution';
            if (motifContainer) motifContainer.style.display = 'block';
            if (donationMotifInput) donationMotifInput.placeholder = "Ex: Contribution annuelle, Soutien projet";
            if (donationMotifInput) donationMotifInput.required = true;
            if (submitButton) submitButton.textContent = 'Contribuer';
        }

        // DISABLED: Old placeholder message - PayPal integration is now active (paypal-integration.js handles this)
        /*
        if (donationModeSelect) {
            donationModeSelect.addEventListener('change', function () {
                const selectedMode = this.value;
                if (selectedMode === 'card' || selectedMode === 'paypal') { // Exemple
                    if (paymentDetailsSection) {
                        paymentDetailsSection.style.display = 'block';
                        paymentDetailsSection.innerHTML = `<p><em>Intégration pour ${selectedMode === 'card' ? 'Carte de Crédit' : 'Paypal'} à venir...</em></p>`;
                    }
                } else {
                    if (paymentDetailsSection) paymentDetailsSection.style.display = 'none';
                }
            });
        }
        */

        // DISABLED: Let PHP handle form submission and CinetPay redirection
        /*
        donationFormPage.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(donationFormPage);
            console.log(`Soumission de ${paymentTypeInput.value}:`);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            // Ici, intégration avec une passerelle de paiement (Stripe, PayPal, etc.)
            alert(`Traitement de votre ${paymentTypeInput.value} (simulation). Vous seriez redirigé vers une passerelle de paiement.`);
            // Potentielle redirection vers une page de succès ou member_contributions.html après paiement
            // window.location.href = 'member_contributions.html?payment_success=true';
        });
        */
    }

    // ... (fin du DOMContentLoaded)
});
// js/main.js
document.addEventListener('DOMContentLoaded', function () {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const body = document.body;

    const sunIconPath = 'assets/icons/sun.svg'; // ou le chemin vers votre icône soleil
    const moonIconPath = 'assets/icons/moon.svg'; // ou le chemin vers votre icône lune

    // Fonction pour appliquer le thème
    function applyTheme(theme) {
        body.setAttribute('data-theme', theme);
        if (themeIcon) {
            themeIcon.src = (theme === 'dark') ? sunIconPath : moonIconPath;
            themeIcon.alt = (theme === 'dark') ? 'Thème clair' : 'Thème sombre';
        }
        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('aria-label', (theme === 'dark') ? 'Activer le thème clair' : 'Activer le thème sombre');
        }
        localStorage.setItem('africavenir-theme', theme);

        // Mettre à jour les couleurs du graphique Chart.js si présent
        updateChartColors(theme);
    }

    // Fonction pour mettre à jour les couleurs du graphique Chart.js
    // (Suppose que `contributionsChartInstance` est une variable globale ou accessible)
    // Vous devrez stocker l'instance du graphique lorsque vous le créez.
    let contributionsChartInstance = null; // À assigner lors de l'init du chart

    function updateChartColors(theme) {
        if (contributionsChartInstance) {
            const isDark = theme === 'dark';
            const gridColor = isDark ? 'rgba(240, 240, 240, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            const ticksColor = isDark ? '#f0f0f0' : '#333333';
            const legendColor = isDark ? '#f0f0f0' : '#333333';

            contributionsChartInstance.options.scales.y.ticks.color = ticksColor;
            contributionsChartInstance.options.scales.y.grid.color = gridColor;
            contributionsChartInstance.options.scales.x.ticks.color = ticksColor;
            contributionsChartInstance.options.scales.x.grid.color = gridColor;
            contributionsChartInstance.options.plugins.legend.labels.color = legendColor;

            contributionsChartInstance.update();
        }
    }


    // Vérifier le thème stocké au chargement de la page
    const storedTheme = localStorage.getItem('africavenir-theme');
    // Vérifier aussi les préférences système si aucun thème n'est stocké
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)").matches;

    // Le thème clair est par défaut (pas de 'data-theme' sur body), donc on ne charge que si 'dark' est préféré ou stocké
    let currentTheme = 'light'; // Thème par défaut HTML
    if (storedTheme) {
        currentTheme = storedTheme;
    } else if (prefersDarkScheme) {
        currentTheme = 'dark'; // Si l'OS est en sombre et pas de préférence stockée
    }
    applyTheme(currentTheme); // Appliquer le thème initial


    // Gestionnaire d'événement pour le bouton
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            let newTheme = body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }


    // --- Reste de votre code JS (login, signup, wizard, filtres, etc.) ---

    // Exemple: initialisation du graphique du dashboard (doit être modifié)
    const contributionsChartCanvas = document.getElementById('contributionsChart');
    if (contributionsChartCanvas) {
        const ctx = contributionsChartCanvas.getContext('2d');
        const isDarkInitial = document.body.getAttribute('data-theme') === 'dark';
        const initialGridColor = isDarkInitial ? 'rgba(240, 240, 240, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        const initialTicksColor = isDarkInitial ? '#f0f0f0' : '#333333';
        const initialLegendColor = isDarkInitial ? '#f0f0f0' : '#333333';

        // Stocker l'instance du graphique
        contributionsChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Contributions Totales (en milliers de f)',
                    data: [120, 190, 300, 500, 220, 310, 450, 400, 600, 750, 800, 950],
                    borderColor: '#f0ad4e', // Jaune AfricAvenir, reste constant
                    backgroundColor: 'rgba(240, 173, 78, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: initialTicksColor },
                        grid: { color: initialGridColor }
                    },
                    x: {
                        ticks: { color: initialTicksColor },
                        grid: { color: initialGridColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: initialLegendColor }
                    }
                }
            }
        });
    }

    // ... (autre code JS)
});

// DISABLED: Duplicate form handler - handled by Wizard logic above (line 734-791)
/*
// Gestion du formulaire d'inscription
document.getElementById('registerMemberForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('inscription-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            // Afficher l'étape de confirmation
            document.getElementById('step3').classList.remove('active');
            document.getElementById('step4').classList.add('active');
        } else {
            alert('Erreur: ' + result.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de l\'inscription.');
    }
});
*/

// Gestion du montant libre
document.querySelectorAll('input[name="montant_contribution"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const libreInput = document.getElementById('regMontantLibreVal');
        if (this.value === 'libre') {
            libreInput.style.display = 'inline-block';
            libreInput.required = true;
        } else {
            libreInput.style.display = 'none';
            libreInput.required = false;
        }
    });
});
