<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4">Fraud Checker</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form id="fraud-checker-form" action="fraud_checker.php" method="POST">
            <div class="flex items-center">
                <input type="text" id="phone-number" name="phone_number" class="w-full px-4 py-2 border rounded-l-lg focus:outline-none" placeholder="Enter phone number...">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg hover:bg-blue-600">Search</button>
            </div>
        </form>

        <div id="fraud-checker-results" class="mt-8">
            <!-- Results will be displayed here -->
        </div>
    </div>
</div>

<script>
    document.getElementById('fraud-checker-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const phoneNumber = document.getElementById('phone-number').value;
        const resultsDiv = document.getElementById('fraud-checker-results');

        resultsDiv.innerHTML = '<p>Loading...</p>';

        fetch('<?php echo rtrim(APP_URL, '/'); ?>/fraud_checker.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'phone_number=' + encodeURIComponent(phoneNumber),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = '<p class="text-red-500">' + data.error + '</p>';
            } else {
                resultsDiv.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-bold">Courier Name:</p>
                            <p>${data.courier_name}</p>
                        </div>
                        <div>
                            <p class="font-bold">Total Orders:</p>
                            <p>${data.total_orders}</p>
                        </div>
                        <div>
                            <p class="font-bold">Total Delivered:</p>
                            <p>${data.total_delivered}</p>
                        </div>
                        <div>
                            <p class="font-bold">Total Cancelled:</p>
                            <p>${data.total_cancelled}</p>
                        </div>
                        <div>
                            <p class="font-bold">Success Rate:</p>
                            <p>${data.success_rate}%</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultsDiv.innerHTML = '<p class="text-red-500">An error occurred while fetching data.</p>';
            console.error('Error:', error);
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
