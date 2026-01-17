// My Contributions Filter - Dynamic Loading with Tabs
(function () {
    'use strict';

    // DOM Elements
    const tableBody = document.getElementById('contributionsTableBody');
    const loadingRow = document.getElementById('loadingRow');
    const errorRow = document.getElementById('errorRow');
    const noResultsRow = document.getElementById('noResultsRow');

    const searchInput = document.getElementById('contributionSearch');
    const dateFilter = document.getElementById('filterDate');
    const tabButtons = document.querySelectorAll('.tab-button');

    // Check if we're on the contributions page
    if (!tableBody) return;

    let currentType = 'contributions'; // Default tab

    /**
     * Fetch contributions from API with filters
     */
    async function fetchContributions(filters = {}) {
        showLoading();

        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.date) params.append('date', filters.date);
        if (filters.type) params.append('type', filters.type);

        try {
            const response = await fetch(`api/api-my-contributions.php?${params.toString()}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            if (data.success) {
                displayContributions(data.data);
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error fetching contributions:', error);
            showError();
        }
    }

    /**
     * Display contributions in table
     */
    function displayContributions(contributions) {
        // Clear existing content
        clearTable();

        if (contributions.length === 0) {
            showNoResults();
            return;
        }

        // Create rows for each contribution
        contributions.forEach(contrib => {
            const row = createContributionRow(contrib);
            tableBody.appendChild(row);
        });
    }

    /**
     * Create a table row for a contribution
     */
    function createContributionRow(contrib) {
        const row = document.createElement('tr');
        row.setAttribute('data-type', 'contribution');

        // Determine status class
        let statusClass = '';
        let status = contrib.status || '';
        if (status === 'completed') statusClass = 'status-paid';
        else if (status === 'pending') statusClass = 'status-pending';
        else if (status === 'failed') statusClass = 'status-cancelled';

        const statusBadge = statusClass ? `
            <span class="status-badge ${statusClass}" style="margin-left: 10px; font-size: 0.8em;">
                ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}
            </span>
        ` : '';

        const formattedDate = formatDate(contrib.created_at);
        const formattedAmount = formatAmount(contrib.amount);

        row.innerHTML = `
            <td>${escapeHtml(contrib.id || '')}</td>
            <td>
                ${escapeHtml(contrib.motif || 'Contribution')}
                ${statusBadge}
            </td>
            <td>${formattedAmount} FCFA</td>
            <td>${formattedDate}</td>
        `;
        return row;
    }

    /**
     * Format amount with spaces as thousands separator
     */
    function formatAmount(amount) {
        if (!amount) return '0';
        return Number(amount).toLocaleString('fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
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

        return `${day}/${month}/${year}`;
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
            date: dateFilter.value, // Format: YYYY-MM
            type: currentType
        };

        fetchContributions(filters);
    }

    // Event Listeners

    // Tab switching
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            // Update current type
            currentType = this.getAttribute('data-filter');

            // Apply filters with new type
            applyFilters();
        });
    });

    // Real-time search (debounced)
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500); // Wait 500ms after user stops typing
    });

    // Date filter change
    dateFilter.addEventListener('change', applyFilters);

    // Initial load
    fetchContributions({ type: currentType });

})();
