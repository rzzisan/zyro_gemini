<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Fraud Checker</h1>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form id="fraud-checker-form">
            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter phone number">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" id="search-button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Search
                </button>
            </div>
        </form>
    </div>

    <div id="results-area" class="mt-8 bg-white p-6 rounded-lg shadow-md" style="display: none;">
        <h2 class="text-2xl font-bold mb-4">Results</h2>
        <div id="results-content"></div>
    </div>
</div>

<script>
    document.getElementById('fraud-checker-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const phoneNumberInput = document.getElementById('phone_number');
        const phoneNumber = phoneNumberInput.value;
        const searchButton = document.getElementById('search-button');
        const resultsArea = document.getElementById('results-area');
        const resultsContent = document.getElementById('results-content');

        // Basic client-side validation
        if (!phoneNumber) {
            resultsContent.innerHTML = `<p class="text-red-500">Phone number is required.</p>`;
            resultsArea.style.display = 'block';
            return;
        }

        // E.164 format validation
        if (!/^01[3-9]\d{8}$/.test(phoneNumber)) {
            resultsContent.innerHTML = `<p class="text-red-500">Invalid phone number format. Please use the format 01xxxxxxxxx.</p>`;
            resultsArea.style.display = 'block';
            return;
        }

        searchButton.disabled = true;
        searchButton.textContent = 'Searching...';
        resultsArea.style.display = 'none';

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
                resultsContent.innerHTML = `
                    <p><strong>Total Parcels:</strong> ${stats.total_parcels}</p>
                    <p><strong>Total Delivered:</strong> ${stats.total_delivered}</p>
                    <p><strong>Total Cancelled:</strong> ${stats.total_cancelled}</p>
                    <p><strong>Total Fraud Reports:</strong> ${stats.total_fraud_reports}</p>
                `;
                resultsArea.style.display = 'block';
            } else {
                resultsContent.innerHTML = `<p class="text-red-500">${data.message}</p>`;
                resultsArea.style.display = 'block';
            }
        })
        .catch(error => {
            resultsContent.innerHTML = `<p class="text-red-500">An error occurred while fetching data.</p>`;
            resultsArea.style.display = 'block';
        })
        .finally(() => {
            searchButton.disabled = false;
            searchButton.textContent = 'Search';
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
