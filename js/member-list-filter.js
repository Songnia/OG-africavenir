// Member List Filters (Member Contribution Page) - Dynamic Loading
(function () {
    'use strict';

    // DOM Elements
    const tableBody = document.getElementById('memberListTableBody');
    const loadingRow = document.getElementById('loadingRow');
    const errorRow = document.getElementById('errorRow');
    const noResultsRow = document.getElementById('noResultsRow');

    const searchInput = document.getElementById('memberListSearch');
    const metierFilter = document.getElementById('filterMetier');
    const dateFilter = document.getElementById('filterMemberDate');
    const applyButton = document.getElementById('applyMemberListFilters');

    // Check if we're on the member list page
    if (!tableBody) return;

    /**
     * Fetch members from API with filters
     */
    async function fetchMembers(filters = {}) {
        showLoading();

        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.metier) params.append('metier', filters.metier);
        if (filters.date) params.append('date', filters.date);

        try {
            const response = await fetch(`api/api-member-list.php?${params.toString()}`);

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
            metier: metierFilter.value,
            date: dateFilter.value // Format: YYYY-MM
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
    metierFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);

    // Initial load
    fetchMembers();

})();
