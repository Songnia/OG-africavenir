// Member List Filters - Dynamic Loading
(function () {
    'use strict';

    // DOM Elements
    const tableBody = document.getElementById('membersTableBody');
    const loadingRow = document.getElementById('loadingRow');
    const errorRow = document.getElementById('errorRow');
    const noResultsRow = document.getElementById('noResultsRow');

    const searchInput = document.getElementById('memberSearch');
    const activityFilter = document.getElementById('filterActivity');
    const categoryFilter = document.getElementById('filterCategory');
    const applyButton = document.querySelector('.filters-section .btn-secondary');

    // Check if we're on the members page
    if (!tableBody) return;

    /**
     * Fetch members from API with filters
     */
    async function fetchMembers(filters = {}) {
        showLoading();

        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.activity) params.append('activity', filters.activity);
        if (filters.category) params.append('category', filters.category);

        try {
            const response = await fetch(`api/api-members.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.success) {
                displayMembers(data.data);
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error fetching members:', error);
            showError();
        }
    }

    /**
     * Display members in table
     */
    function displayMembers(members) {
        // Clear existing content
        clearTable();

        if (members.length === 0) {
            showNoResults();
            return;
        }

        // Create rows for each member
        members.forEach(member => {
            const row = createMemberRow(member);
            tableBody.appendChild(row);
        });
    }

    /**
     * Create a table row for a member
     */
    function createMemberRow(member) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(member.ID || '')}</td>
            <td>${escapeHtml(member.telephone || 'N/A')}</td>
            <td>${escapeHtml(member.display_name || '')}</td>
            <td>${escapeHtml(member.activite || 'N/A')}</td>
            <td>${escapeHtml(member.ville || 'N/A')}</td>
            <td>${escapeHtml(member.categorie || 'N/A')}</td>
            <td class="actions-cell">
                <button type="button" class="btn-icon btn-view" aria-label="Voir ${escapeHtml(member.display_name || '')}">
                    <img src="assets/icons/view.svg" alt="Voir">
                </button>
                <button type="button" class="btn-icon btn-edit" aria-label="Modifier ${escapeHtml(member.display_name || '')}">
                    <img src="assets/icons/edit.svg" alt="Modifier">
                </button>
                <button type="button" class="btn-icon btn-delete" aria-label="Supprimer ${escapeHtml(member.display_name || '')}">
                    <img src="assets/icons/delete.svg" alt="Supprimer">
                </button>
            </td>
        `;
        return row;
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
            activity: activityFilter.value,
            category: categoryFilter.value
        };

        fetchMembers(filters);
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
    activityFilter.addEventListener('change', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);

    // Initial load
    fetchMembers();

})();
