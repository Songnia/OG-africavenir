// Payment List Filters - Dynamic Loading
(function () {
    'use strict';

    // DOM Elements
    const tableBody = document.getElementById('paymentsTableBody');
    const loadingRow = document.getElementById('loadingRow');
    const errorRow = document.getElementById('errorRow');
    const noResultsRow = document.getElementById('noResultsRow');

    const searchInput = document.getElementById('paymentSearch');
    const monthFilter = document.getElementById('filterPaymentMonth');
    const statusFilter = document.getElementById('filterPaymentStatus');
    const applyButton = document.querySelector('.filters-section .btn-secondary');

    // Check if we're on the payments page
    if (!tableBody) return;

    /**
     * Fetch payments from API with filters
     */
    async function fetchPayments(filters = {}) {
        showLoading();

        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.month) params.append('month', filters.month);
        if (filters.status) params.append('status', filters.status);

        try {
            const response = await fetch(`api/api-payments.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.success) {
                displayPayments(data.data);
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error fetching payments:', error);
            showError();
        }
    }

    /**
     * Display payments in table
     */
    function displayPayments(payments) {
        // Clear existing content
        clearTable();

        if (payments.length === 0) {
            showNoResults();
            return;
        }

        // Create rows for each payment
        payments.forEach(payment => {
            const row = createPaymentRow(payment);
            tableBody.appendChild(row);
        });
    }

    /**
     * Create a table row for a payment
     */
    function createPaymentRow(payment) {
        const row = document.createElement('tr');

        // Determine status class
        let statusClass = 'status-pending';
        const status = payment.status?.toLowerCase() || '';
        if (status === 'paye' || status === 'completed') statusClass = 'status-paid';
        if (status === 'a-payer') statusClass = 'status-due';
        if (status === 'annule' || status === 'failed') statusClass = 'status-cancelled';

        row.innerHTML = `
            <td>${escapeHtml(payment.id || '')}</td>
            <td>${escapeHtml(payment.member_name || 'Inconnu')}</td>
            <td>${escapeHtml(payment.amount || '0')} FCFA</td>
            <td>${escapeHtml(payment.motif || payment.transaction_id || '')}</td>
            <td>${escapeHtml(payment.payment_method || 'N/A')}</td>
            <td><span class="status-badge ${statusClass}">${escapeHtml(payment.status || 'N/A')}</span></td>
            <td>${formatDate(payment.created_at)}</td>
            <td class="actions-cell">
                <div class="action-buttons">
                    <a href="dashboard-register-paiement.php?id=${escapeHtml(payment.id)}" class="btn-icon" title="Modifier">
                        <img src="assets/icons/edit.svg" alt="Modifier">
                    </a>
                    <button type="button" class="btn-icon btn-update-status" onclick="openUpdateStatusModal(${payment.id}, '${payment.status}')" title="Changer Statut">
                        <img src="assets/icons/refresh.svg" alt="Changer Statut" style="width: 16px; height: 16px;">
                    </button>
                    <button type="button" class="btn-icon btn-delete-payment" data-id="${escapeHtml(payment.id)}" title="Supprimer">
                        <img src="assets/icons/delete.svg" alt="Supprimer">
                    </button>
                </div>
            </td>
        `;
        return row;
    }

    /**
     * Format date string
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
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
            search: searchInput.value.trim(),
            month: monthFilter.value,
            status: statusFilter.value
        };

        fetchPayments(filters);
    }

    // Event Listeners
    applyButton.addEventListener('click', applyFilters);

    // Real-time search (debounced)
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500); // Wait 500ms after user stops typing
    });

    // Filter change events
    monthFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Initial load
    fetchPayments();

})();

// --- Status Update Logic ---

function openUpdateStatusModal(id, currentStatus) {
    const modal = document.getElementById('updateStatusModal');
    const idInput = document.getElementById('updateStatusId');
    const statusSelect = document.getElementById('newStatus');

    if (modal && idInput && statusSelect) {
        idInput.value = id;

        // Map French/Legacy statuses to standard ones if needed
        let standardStatus = currentStatus;
        if (currentStatus === 'paye') standardStatus = 'completed';
        if (currentStatus === 'a-payer') standardStatus = 'pending';
        if (currentStatus === 'annule') standardStatus = 'cancelled';

        statusSelect.value = standardStatus;

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
}

function closeUpdateStatusModal() {
    const modal = document.getElementById('updateStatusModal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

async function submitStatusUpdate() {
    const id = document.getElementById('updateStatusId').value;
    const status = document.getElementById('newStatus').value;

    if (!id || !status) return;

    try {
        const response = await fetch('api/update-payment-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, status })
        });

        const data = await response.json();

        if (data.success) {
            closeUpdateStatusModal();
            // Refresh table
            window.location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Erreur lors de la mise à jour');
    }
}

// Expose functions globally
window.openUpdateStatusModal = openUpdateStatusModal;
window.closeUpdateStatusModal = closeUpdateStatusModal;
window.submitStatusUpdate = submitStatusUpdate;
