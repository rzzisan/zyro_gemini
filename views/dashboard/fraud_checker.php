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

            <div id="error-area" class="mt-8 bg-white p-6 rounded-lg shadow-md" style="display: none;">
                 <p id="error-message" class="text-red-500"></p>
            </div>
    </main>
</div>

<script>
    document.getElementById('fraud-checker-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const phoneNumberInput = document.getElementById('phone_number');
        const phoneNumber = phoneNumberInput.value;
        const searchButton = document.getElementById('search-button');
        const resultsArea = document.getElementById('results-area');
        const errorArea = document.getElementById('error-area');
        const errorMessage = document.getElementById('error-message');

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

        const formData = new FormData();
        formData.append('phone_number', phoneNumber);

        fetch('<?php echo APP_URL; ?>/fraud_checker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                
                // Update summary cards
                document.getElementById('total-orders').textContent = stats.total_parcels;
                document.getElementById('total-delivered').textContent = stats.total_delivered;
                document.getElementById('total-cancelled').textContent = stats.total_cancelled;
                document.getElementById('total-fraud-reports').textContent = stats.total_fraud_reports;

                const deliveryRate = stats.total_parcels > 0 ? (stats.total_delivered / stats.total_parcels) * 100 : 0;
                const cancellationRate = stats.total_parcels > 0 ? (stats.total_cancelled / stats.total_parcels) * 100 : 0;
                document.getElementById('delivery-rate').textContent = `${deliveryRate.toFixed(1)}%`;

                // Update progress text and status
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

                // Update linear progress bar
                const combinedProgressBar = document.getElementById('combined-progress-bar');
                const greenWidth = deliveryRate;
                const redWidth = cancellationRate; // This will be the actual cancellation rate

                // Ensure total doesn't exceed 100%
                const totalPercentage = greenWidth + redWidth;
                if (totalPercentage > 100) {
                    // Adjust if sum exceeds 100, e.g., scale proportionally or cap
                    // For simplicity, let's cap the red part if it overflows
                    const scaleFactor = 100 / totalPercentage;
                    // greenWidth *= scaleFactor; // Not needed if we just cap red
                    // redWidth *= scaleFactor;
                }

                combinedProgressBar.style.background = `linear-gradient(to right, #22C55E 0%, #22C55E ${greenWidth}%, #EF4444 ${greenWidth}%, #EF4444 ${greenWidth + redWidth}%)`;
                // If the sum of green and red is less than 100, the remaining part will be transparent by default.
                // If we want the remaining part to be gray, we would need a more complex gradient or another div.
                // For now, this should work as per the request (green for delivery, red for cancellation).
                // The background of the parent div is already gray, so the remaining part will show gray.


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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
