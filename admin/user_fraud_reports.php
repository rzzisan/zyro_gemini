<?php require_once __DIR__ . '/../views/layouts/admin_header.php'; ?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">All User Fraud Reports</h1>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full" id="reports-table">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-3">Phone Number</th>
                        <th class="text-left p-3">Customer Name</th>
                        <th class="text-left p-3">Complaint</th>
                        <th class="text-left p-3">Reported By</th>
                        <th class="text-left p-3">Reported At</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Reports will be injected here by JS -->
                </tbody>
            </table>
        </div>
        <div id="loading-spinner" class="text-center p-8" style="display: none;">
            <i class="fas fa-spinner fa-spin text-4xl"></i>
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
    const editModal = document.getElementById('edit-report-modal');
    const editForm = document.getElementById('edit-report-form');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');

    function fetchReports() {
        loadingSpinner.style.display = 'block';
        tableBody.innerHTML = '';

        fetch('<?php echo APP_URL; ?>/get_all_user_reports.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Failed to fetch reports.'))
            .finally(() => loadingSpinner.style.display = 'none');
    }

    function renderTable(reports) {
        tableBody.innerHTML = '';
        if (reports.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center p-8">No user-submitted fraud reports found.</td></tr>';
            return;
        }

        reports.forEach(report => {
            const row = document.createElement('tr');
            row.className = 'border-b';
            row.innerHTML = `
                <td class="p-3">${report.phone_number}</td>
                <td class="p-3">${report.customer_name}</td>
                <td class="p-3">${report.complaint}</td>
                <td class="p-3">${report.user_name} (ID: ${report.user_id})</td>
                <td class="p-3">${new Date(report.reported_at).toLocaleString()}</td>
                <td class="p-3">
                    <button class="edit-btn text-blue-500 hover:underline" data-report='${JSON.stringify(report)}'>Edit</button>
                    <button class="delete-btn text-red-500 hover:underline ml-4" data-phone="${report.phone_number}" data-id="${report.report_id}">Delete</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

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
                        fetchReports(); // Refresh the list
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
                fetchReports(); // Refresh the list
            } else {
                document.getElementById('edit-error-message').textContent = data.message;
            }
        })
        .catch(() => document.getElementById('edit-error-message').textContent = 'An error occurred.');
    });

    fetchReports();
});
</script>

<?php require_once __DIR__ . '/../views/layouts/admin_footer.php'; ?>
