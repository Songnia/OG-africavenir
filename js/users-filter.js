// User List Filters - Dynamic Loading
(function () {
    'use strict';

    // DOM Elements
    const tableBody = document.getElementById('usersTableBody');
    const loadingRow = document.getElementById('loadingRow');
    const errorRow = document.getElementById('errorRow');
    const noResultsRow = document.getElementById('noResultsRow');

    const searchInput = document.getElementById('userSearch');

    // Check if we're on the users page
    if (!tableBody) return;

    // Get current user ID from session (needed to prevent self-deletion)
    let currentUserId = null;

    /**
     * Fetch users from API with filters
     */
    async function fetchUsers(filters = {}) {
        showLoading();

        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);

        try {
            const response = await fetch(`api/api-users.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.success) {
                displayUsers(data.data);
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            showError();
        }
    }

    /**
     * Display users in table
     */
    function displayUsers(users) {
        // Clear existing content
        clearTable();

        if (users.length === 0) {
            showNoResults();
            return;
        }

        // Create rows for each user
        users.forEach(user => {
            const row = createUserRow(user);
            tableBody.appendChild(row);
        });

        // Re-attach delete button event listeners (since we recreated the buttons)
        attachDeleteListeners();
    }

    /**
     * Create a table row for a user
     */
    function createUserRow(user) {
        const row = document.createElement('tr');

        const role = user.role || '';
        const roleLabel = user.role_label || role;

        // Check if this is the current user (to prevent self-deletion)
        const isCurrentUser = (currentUserId && user.ID == currentUserId);

        const deleteButtonHtml = isCurrentUser ? '' : `
            <button type="button" class="btn-icon btn-delete" data-id="${escapeHtml(user.ID)}" aria-label="Supprimer ${escapeHtml(user.user_login || '')}">
                <img src="assets/icons/delete.svg" alt="Supprimer">
            </button>
        `;

        row.innerHTML = `
            <td>${escapeHtml(user.ID || '')}</td>
            <td>${escapeHtml(user.user_email || '')}</td>
            <td>${escapeHtml(user.user_login || '')}</td>
            <td><span class="status-badge status-${escapeHtml(role)}">${escapeHtml(roleLabel)}</span></td>
            <td class="actions-cell">
                <a href="user-edit.php?id=${escapeHtml(user.ID)}" class="btn-icon btn-edit" aria-label="Modifier ${escapeHtml(user.user_login || '')}">
                    <img src="assets/icons/edit.svg" alt="Modifier">
                </a>
                ${deleteButtonHtml}
            </td>
        `;
        return row;
    }

    /**
     * Attach event listeners to delete buttons
     */
    function attachDeleteListeners() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const modal = document.getElementById('deleteUserModal');
        const confirmBtn = document.getElementById('confirmDeleteUserBtn');
        const cancelBtns = document.querySelectorAll('.btn-cancel-modal, .btn-close-modal');
        const backdrop = document.querySelector('.modal-backdrop');

        if (!modal) return; // Modal doesn't exist on this page

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
            btn.addEventListener('click', function () {
                userIdToDelete = this.getAttribute('data-id');
                openModal();
            });
        });

        cancelBtns.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        // Unbind previous click events to prevent duplicates
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', function () {
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
                            closeModal();
                            applyFilters(); // Reload the list
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de la suppression.');
                    });
            }
        });
    }

    /**
     * Show loading indicator
     */
    function showLoading() {
        clearTable();
        loadingRow.style.display = '';
    }

    /**
     * Show error message
     */
    function showError() {
        clearTable();
        errorRow.style.display = '';
    }

    /**
     * Show no results message
     */
    function showNoResults() {
        clearTable();
        noResultsRow.style.display = '';
    }

    /**
     * Clear table and hide all special rows
     */
    function clearTable() {
        // Remove all data rows (keep special rows)
        const dataRows = tableBody.querySelectorAll('tr:not(#loadingRow):not(#errorRow):not(#noResultsRow)');
        dataRows.forEach(row => row.remove());

        // Hide all special rows
        loadingRow.style.display = 'none';
        errorRow.style.display = 'none';
        noResultsRow.style.display = 'none';
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Apply filters
     */
    function applyFilters() {
        const filters = {
            search: searchInput.value.trim()
        };

        fetchUsers(filters);
    }

    // Event Listeners
    // Real-time search (debounced)
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500); // Wait 500ms after user stops typing
    });

    // Try to get current user ID from PHP session (if available in page)
    // We'll need to pass this from the backend or skip it
    // For now, we'll rely on the backend to not return the delete button

    // Initial load
    fetchUsers();

})();
