<?php require_once __DIR__ . '/../views/layouts/admin_header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">All User Fraud Reports</h1>

    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-8">
        <form id="filter-form" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
            <div class="flex-grow w-full sm:w-auto">
                <label for="search" class="sr-only">Search:</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" id="search" name="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search by phone, name, complaint...">
                </div>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="w-full sm:w-auto">
                    <label for="rows_per_page" class="sr-only">Rows per page:</label>
                    <select id="rows_per_page" name="rows_per_page" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="reports-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported At</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                </tbody>
            </table>
        </div>
        <div id="loading-spinner" class="text-center p-8" style="display: none;">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600"></i>
        </div>
        <div id="pagination-container" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <!-- Pagination will be injected here -->
        </div>
    </div>
</div>

<!-- Edit Report Modal -->
<div id="edit-report-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg p-8 shadow-xl w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Edit Fraud Report</h2>
        <form id="edit-report-form">
            <input type="hidden" id="edit_report_id" name="report_id">
            <input type="hidden" id="edit_phone_number" name="phone_number">
            <div class="mb-4">
                <label for="edit_customer_name" class="block text-gray-700">Customer's Name</label>
                <input type="text" id="edit_customer_name" name="customer_name" class="w-full border rounded-lg p-3 mt-1 focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="mb-4">
                <label for="edit_complaint" class="block text-gray-700">Your Complaint (Max 250 chars)</label>
                <textarea id="edit_complaint" name="complaint" rows="4" maxlength="250" class="w-full border rounded-lg p-3 mt-1 focus:outline-none focus:ring-2 focus:ring-green-500" required></textarea>
            </div>
            <div id="edit-error-message" class="text-red-500 mb-4"></div>
            <div class="flex justify-end gap-4">
                <button type="button" id="cancel-edit-btn" class="bg-gray-300 text-gray-800 font-bold px-6 py-3 rounded-lg hover:bg-gray-400">Cancel</button>
                <button type="submit" id="submit-edit-btn" class="bg-green-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-green-600">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#reports-table tbody');
    const loadingSpinner = document.getElementById('loading-spinner');
    const paginationContainer = document.getElementById('pagination-container');
    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('search');
    const rowsPerPageSelect = document.getElementById('rows_per_page');

    const editModal = document.getElementById('edit-report-modal');
    const editForm = document.getElementById('edit-report-form');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');

    let currentPage = 1;
    let currentSearch = '';
    let currentRowsPerPage = 25;

    function fetchReports(page = 1, rowsPerPage = 25, search = '') {
        loadingSpinner.style.display = 'block';
        tableBody.innerHTML = '';
        paginationContainer.innerHTML = '';

        // Update state
        currentPage = page;
        currentRowsPerPage = rowsPerPage;
        currentSearch = search;

        const url = new URL('<?php echo APP_URL; ?>/get_all_user_reports.php');
        url.searchParams.append('page', page);
        url.searchParams.append('rows_per_page', rowsPerPage);
        if (search) {
            url.searchParams.append('search', search);
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.data, data.pagination);
                    renderPagination(data.pagination);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-8 text-red-500">${data.message}</td></tr>`;
                }
            })
            .catch(() => {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-8 text-red-500">Failed to fetch reports.</td></tr>`;
            })
            .finally(() => loadingSpinner.style.display = 'none');
    }

    function renderTable(reports, pagination) {
        tableBody.innerHTML = '';
        if (reports.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center p-8">No user-submitted fraud reports found.</td></tr>';
            return;
        }

        const offset = (pagination.current_page - 1) * pagination.rows_per_page;
        reports.forEach((report, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${offset + index + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.phone_number}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.customer_name}</td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${report.complaint}">${report.complaint}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${report.user_name} <span class="text-gray-500">(${report.user_id})</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(report.reported_at).toLocaleString()}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="edit-btn text-blue-600 hover:text-blue-900" data-report='${JSON.stringify(report)}'>Edit</button>
                    <button class="delete-btn text-red-600 hover:text-red-900 ml-4" data-phone="${report.phone_number}" data-id="${report.report_id}">Delete</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function renderPagination(pagination) {
        const { total_rows, total_pages, current_page, rows_per_page } = pagination;
        paginationContainer.innerHTML = '';

        if (total_pages <= 1) {
            paginationContainer.innerHTML = `<div class="text-sm text-gray-700">Showing ${total_rows} results</div>`;
            return;
        }

        const startItem = (current_page - 1) * rows_per_page + 1;
        const endItem = Math.min(startItem + rows_per_page - 1, total_rows);

        let paginationHTML = `
            <div class="flex-1 flex justify-between sm:hidden">
                <button data-page="${current_page - 1}" class="pagination-link relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" ${current_page === 1 ? 'disabled' : ''}>Previous</button>
                <button data-page="${current_page + 1}" class="pagination-link relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" ${current_page === total_pages ? 'disabled' : ''}>Next</button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">${startItem}</span> to <span class="font-medium">${endItem}</span> of <span class="font-medium">${total_rows}</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
        `;

        // Previous button
        paginationHTML += `<button data-page="${current_page - 1}" class="pagination-link relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" ${current_page === 1 ? 'disabled' : ''}><span class="sr-only">Previous</span><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg></button>`;

        // Page numbers
        for (let i = 1; i <= total_pages; i++) {
            if (i === current_page) {
                paginationHTML += `<button aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</button>`;
            } else {
                paginationHTML += `<button data-page="${i}" class="pagination-link bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</button>`;
            }
        }

        // Next button
        paginationHTML += `<button data-page="${current_page + 1}" class="pagination-link relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50" ${current_page === total_pages ? 'disabled' : ''}><span class="sr-only">Next</span><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></button>`;
        
paginationHTML += `</nav></div></div>`;
        paginationContainer.innerHTML = paginationHTML;
    }

    // Event Listeners
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchReports(1, rowsPerPageSelect.value, searchInput.value);
    });

    paginationContainer.addEventListener('click', function(e) {
        const target = e.target.closest('.pagination-link');
        if (target && !target.disabled) {
            const page = parseInt(target.dataset.page);
            fetchReports(page, currentRowsPerPage, currentSearch);
        }
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-btn')) {
            const report = JSON.parse(e.target.dataset.report);
            document.getElementById('edit_report_id').value = report.report_id;
            document.getElementById('edit_phone_number').value = report.phone_number;
            document.getElementById('edit_customer_name').value = report.customer_name;
            document.getElementById('edit_complaint').value = report.complaint;
            editModal.style.display = 'flex';
        }

        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this report? This action is permanent for the user.')) {
                const phone = e.target.dataset.phone;
                const reportId = e.target.dataset.id;
                
                const formData = new FormData();
                formData.append('phone_number', phone);
                formData.append('report_id', reportId);

                fetch('<?php echo APP_URL; ?>/delete_my_report.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchReports(currentPage, currentRowsPerPage, currentSearch); // Refresh the list
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => alert('Failed to delete report.'));
            }
        }
    });

    cancelEditBtn.addEventListener('click', () => {
        editModal.style.display = 'none';
    });

    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(editForm);
        
        fetch('<?php echo APP_URL; ?>/edit_my_report.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editModal.style.display = 'none';
                fetchReports(currentPage, currentRowsPerPage, currentSearch); // Refresh the list
            } else {
                document.getElementById('edit-error-message').textContent = data.message;
            }
        })
        .catch(() => document.getElementById('edit-error-message').textContent = 'An error occurred.');
    });

    // Initial fetch
    fetchReports(currentPage, currentRowsPerPage, currentSearch);
});
</script>

<?php require_once __DIR__ . '/../views/layouts/admin_footer.php'; ?>
