<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="flex flex-col lg:flex-row gap-8 p-4 lg:p-8">
    <!-- Left Column -->
    <aside class="w-full lg:w-1/4 bg-green-50 rounded-lg p-8 flex flex-col items-center text-center">
        <h2 class="text-2xl font-bold mb-4">ফ্রডচেকার</h2>
        <p class="text-green-700 font-semibold mb-4">Delivery Success Ratio</p>
        <div id="progress-text" class="text-5xl font-bold text-gray-700 mb-4">
            0%
        </div>
        <p id="delivery-status-text" class="text-xl font-bold text-gray-800 mb-2">Excellent</p>
        <p class="text-gray-600">এটি একটি নিরাপদ ডেলিভারি।</p>
    </aside>

    <!-- Right Column -->
    <main class="w-full lg:w-3/4">
        <div class="flex justify-end mb-4">
            <a href="my_fraud_reports.php" class="bg-red-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                My Fraud Report List
            </a>
        </div>
        <!-- CSRF Token for JS -->
        <input type="hidden" id="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <form id="fraud-checker-form" class="flex items-center mb-6">
            <input type="text" id="phone_number" name="phone_number" class="w-full border rounded-l-lg p-3 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="মোবাইল নম্বর দিন! যেমনঃ 01303352482">
            <button type="submit" id="search-button" class="bg-green-500 text-white font-bold px-6 py-3 rounded-r-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                Search
            </button>
        </form>

        <!-- <div class="text-center border rounded-lg p-3 mb-6">
            <p>মার্চেন্ট রিপোর্ট সহ দেখতে একাউন্ট রেজিস্টার করে লগিন করুন</p>
        </div> -->

        <div id="results-area" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center gap-4">
                    <i class="fas fa-shopping-cart text-blue-500 text-3xl"></i>
                    <div>
                        <p class="text-gray-500">মোট অর্ডার</p>
                        <p id="total-orders" class="text-2xl font-bold">0</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center gap-4">
                    <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                    <div>
                        <p class="text-gray-500">মোট ডেলিভারি</p>
                        <p id="total-delivered" class="text-2xl font-bold">0</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center gap-4">
                    <i class="fas fa-times-circle text-red-500 text-3xl"></i>
                    <div>
                        <p class="text-gray-500">মোট বাতিল</p>
                        <p id="total-cancelled" class="text-2xl font-bold">0</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center gap-4">
                    <i class="fas fa-percentage text-blue-500 text-3xl"></i>
                    <div>
                        <p class="text-gray-500">ডেলিভারি রেট</p>
                        <p id="delivery-rate" class="text-2xl font-bold">0%</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center gap-4">
                    <i class="fas fa-exclamation-triangle text-orange-500 text-3xl"></i>
                    <div>
                        <p class="text-gray-500">মোট ফ্রড রিপোর্ট</p>
                        <p id="total-fraud-reports" class="text-2xl font-bold">0</p>
                    </div>
                </div>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div id="combined-progress-bar" class="h-4 rounded-full" style="width: 100%; background: linear-gradient(to right, #34D399 0%, #34D399 0%, #EF4444 0%, #EF4444 100%);"></div>
            </div>

            <div id="report-actions" class="mt-4 flex gap-4">
                <button id="report-fraud-btn" class="bg-red-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Report Fraud
                </button>
                <a href="#" id="view-reports-link" class="text-green-600 font-bold py-3" style="display: none;">View Report Reason</a>
            </div>

            <div id="error-area" class="mt-8 bg-white p-6 rounded-lg shadow-md" style="display: none;">
                 <p id="error-message" class="text-red-500"></p>
            </div>
    </main>
</div>

<!-- Report Fraud Modal -->
<div id="report-fraud-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg p-8 shadow-xl w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Report Fraud</h2>
        <form id="report-fraud-form">
            <div class="mb-4">
                <label for="customer_name" class="block text-gray-700">Customer's Name</label>
                <input type="text" id="customer_name" name="customer_name" class="w-full border rounded-lg p-3 mt-1 focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="mb-4">
                <label for="complaint" class="block text-gray-700">Your Complaint (Max 250 chars)</label>
                <textarea id="complaint" name="complaint" rows="4" maxlength="250" class="w-full border rounded-lg p-3 mt-1 focus:outline-none focus:ring-2 focus:ring-green-500" required></textarea>
            </div>
            <div id="report-error-message" class="text-red-500 mb-4"></div>
            <div class="flex justify-end gap-4">
                <button type="button" id="cancel-report-btn" class="bg-gray-300 text-gray-800 font-bold px-6 py-3 rounded-lg hover:bg-gray-400">Cancel</button>
                <button type="submit" id="submit-report-btn" class="bg-green-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-green-600">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<!-- View Reports Modal -->
<div id="view-reports-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg p-8 shadow-xl w-full max-w-2xl">
        <h2 class="text-2xl font-bold mb-4">User Submitted Reports</h2>
        <div id="reports-list" class="space-y-4 max-h-96 overflow-y-auto">
            <!-- Reports will be injected here by JS -->
        </div>
        <div class="flex justify-end mt-6">
            <button type="button" id="close-view-reports-btn" class="bg-gray-300 text-gray-800 font-bold px-6 py-3 rounded-lg hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fraudCheckerForm = document.getElementById('fraud-checker-form');
        const phoneNumberInput = document.getElementById('phone_number');
        const searchButton = document.getElementById('search-button');
        const resultsArea = document.getElementById('results-area');
        const errorArea = document.getElementById('error-area');
        const errorMessage = document.getElementById('error-message');

        // Report fraud elements
        const reportFraudBtn = document.getElementById('report-fraud-btn');
        const reportFraudModal = document.getElementById('report-fraud-modal');
        const cancelReportBtn = document.getElementById('cancel-report-btn');
        const reportFraudForm = document.getElementById('report-fraud-form');
        const submitReportBtn = document.getElementById('submit-report-btn');
        const reportErrorMessage = document.getElementById('report-error-message');

        // View reports elements
        const viewReportsLink = document.getElementById('view-reports-link');
        const viewReportsModal = document.getElementById('view-reports-modal');
        const closeViewReportsBtn = document.getElementById('close-view-reports-btn');
        const reportsList = document.getElementById('reports-list');

        let currentPhoneNumber = '';

        fraudCheckerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const phoneNumber = phoneNumberInput.value;
            currentPhoneNumber = phoneNumber; // Store for reporting

            // Client-side validation
            if (!phoneNumber) {
                errorMessage.textContent = 'Phone number is required.';
                errorArea.style.display = 'block';
                resultsArea.style.display = 'none';
                return;
            }
            if (!/^01[3-9]\d{8}$/.test(phoneNumber)) {
                errorMessage.textContent = 'Invalid phone number format. Please use the format 01xxxxxxxxx.';
                errorArea.style.display = 'block';
                resultsArea.style.display = 'none';
                return;
            }

            searchButton.disabled = true;
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            resultsArea.style.display = 'none';
            errorArea.style.display = 'none';
            viewReportsLink.style.display = 'none';

            const formData = new FormData();
            formData.append('phone_number', phoneNumber);
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            fetch('../../fraud_checker.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data;
                    
                    document.getElementById('total-orders').textContent = stats.total_parcels;
                    document.getElementById('total-delivered').textContent = stats.total_delivered;
                    document.getElementById('total-cancelled').textContent = stats.total_cancelled;
                    document.getElementById('total-fraud-reports').textContent = stats.total_fraud_reports;

                    const deliveryRate = stats.total_parcels > 0 ? (stats.total_delivered / stats.total_parcels) * 100 : 0;
                    const cancellationRate = stats.total_parcels > 0 ? (stats.total_cancelled / stats.total_parcels) * 100 : 0;
                    document.getElementById('delivery-rate').textContent = `${deliveryRate.toFixed(1)}%`;

                    const progressText = document.getElementById('progress-text');
                    const deliveryStatusText = document.getElementById('delivery-status-text');
                    progressText.textContent = `${deliveryRate.toFixed(1)}%`;

                    if (deliveryRate >= 95) {
                        deliveryStatusText.textContent = 'Excellent';
                    } else if (deliveryRate >= 80) {
                        deliveryStatusText.textContent = 'Good';
                    } else {
                        deliveryStatusText.textContent = 'Poor';
                    }

                    const combinedProgressBar = document.getElementById('combined-progress-bar');
                    const greenWidth = deliveryRate;
                    const redWidth = cancellationRate;
                    combinedProgressBar.style.background = `linear-gradient(to right, #22C55E 0%, #22C55E ${greenWidth}%, #EF4444 ${greenWidth}%, #EF4444 ${greenWidth + redWidth}%)`;

                    // Handle user reports
                    reportsList.innerHTML = ''; // Clear previous reports
                    if (stats.user_reports_data && stats.user_reports_data.length > 0) {
                        viewReportsLink.style.display = 'block';
                        stats.user_reports_data.forEach(report => {
                            const reportEl = document.createElement('div');
                            reportEl.className = 'p-4 border rounded-lg bg-gray-50';
                            reportEl.innerHTML = `
                                <p class="font-bold text-gray-800">Reported by User #${report.user_id}</p>
                                <p><span class="font-semibold">Customer Name:</span> ${report.customer_name}</p>
                                <p><span class="font-semibold">Complaint:</span> ${report.complaint}</p>
                                <p class="text-sm text-gray-500 mt-1">On: ${new Date(report.reported_at).toLocaleString()}</p>
                            `;
                            reportsList.appendChild(reportEl);
                        });
                    } else {
                        viewReportsLink.style.display = 'none';
                    }

                    resultsArea.style.display = 'block';
                } else {
                    errorMessage.textContent = data.message;
                    errorArea.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'An error occurred while fetching data.';
                errorArea.style.display = 'block';
            })
            .finally(() => {
                searchButton.disabled = false;
                searchButton.innerHTML = 'Search';
            });
        });

        // --- Modal Handling ---

        // Report Fraud Modal
        reportFraudBtn.addEventListener('click', () => {
            reportFraudModal.style.display = 'flex';
        });

        cancelReportBtn.addEventListener('click', () => {
            reportFraudModal.style.display = 'none';
            reportFraudForm.reset();
            reportErrorMessage.textContent = '';
        });

        reportFraudForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitReportBtn.disabled = true;
            submitReportBtn.textContent = 'Submitting...';
            reportErrorMessage.textContent = '';

            const formData = new FormData(reportFraudForm);
            formData.append('phone_number', currentPhoneNumber);
            formData.append('csrf_token', document.getElementById('csrf_token').value);

            fetch('../../report_fraud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Report submitted successfully!');
                    reportFraudModal.style.display = 'none';
                    reportFraudForm.reset();
                    // Optionally, re-run the search to update stats
                    fraudCheckerForm.dispatchEvent(new Event('submit'));
                } else {
                    reportErrorMessage.textContent = data.message || 'An unknown error occurred.';
                }
            })
            .catch(() => {
                reportErrorMessage.textContent = 'A network error occurred. Please try again.';
            })
            .finally(() => {
                submitReportBtn.disabled = false;
                submitReportBtn.textContent = 'Submit Report';
            });
        });

        // View Reports Modal
        viewReportsLink.addEventListener('click', (e) => {
            e.preventDefault();
            viewReportsModal.style.display = 'flex';
        });

        closeViewReportsBtn.addEventListener('click', () => {
            viewReportsModal.style.display = 'none';
        });

        // Close modals by clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === reportFraudModal) {
                reportFraudModal.style.display = 'none';
            }
            if (e.target === viewReportsModal) {
                viewReportsModal.style.display = 'none';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
