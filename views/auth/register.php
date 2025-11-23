<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/functions.php';
if (is_logged_in()) {
    redirect('/views/dashboard/index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg px-8 py-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Register</h2>
            <?php display_message(); ?>
            <form action="../../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone_number" name="phone_number" required>
                    <p id="phone-error" class="text-red-500 text-xs italic hidden"></p>
                </div>
                <div class="mb-4">
                    <label for="district" class="block text-gray-700 text-sm font-bold mb-2">District (Optional)</label>
                    <select id="district" name="district" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="upazila" class="block text-gray-700 text-sm font-bold mb-2">Upazila (Optional)</label>
                    <select id="upazila" name="upazila" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Select Upazila</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
                </div>
                <div class="mb-6">
                    <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password_confirm" name="password_confirm" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" id="submit-button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Register
                    </button>
                    <p class="text-sm text-gray-600">
                        Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-800">Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const districtSelect = document.getElementById('district');
    const upazilaSelect = document.getElementById('upazila');
    const phoneInput = document.getElementById('phone_number');
    const phoneError = document.getElementById('phone-error');
    const submitButton = document.getElementById('submit-button');

    let districts = [];
    let upazilas = [];

    try {
        const [districtsRes, upazilasRes] = await Promise.all([
            fetch('../../content/bd-districts.json'),
            fetch('../../content/bd-upazilas.json')
        ]);
        
        districts = (await districtsRes.json()).districts;
        upazilas = (await upazilasRes.json()).upazilas;

    } catch (error) {
        console.error('Failed to load location data:', error);
        return;
    }

    districts.forEach(district => {
        const option = new Option(district.name, district.name);
        districtSelect.add(option);
    });

    const loadUpazilas = (districtName) => {
        upazilaSelect.innerHTML = '<option value="">Select Upazila</option>';
        if (!districtName) return;

        const selectedDistrict = districts.find(d => d.name === districtName);
        if (!selectedDistrict) return;

        const filteredUpazilas = upazilas.filter(u => u.district_id === selectedDistrict.id);
        
        filteredUpazilas.forEach(upazila => {
            const option = new Option(upazila.name, upazila.name);
            upazilaSelect.add(option);
        });
    };

    loadUpazilas(districtSelect.value);

    districtSelect.addEventListener('change', () => {
        loadUpazilas(districtSelect.value);
    });

    phoneInput.addEventListener('input', async (e) => {
        const phoneNumber = e.target.value;
        
        phoneError.textContent = '';
        phoneError.classList.add('hidden');
        submitButton.disabled = false;
        submitButton.classList.remove('cursor-not-allowed');

        if (phoneNumber.length === 11) {
            try {
                const response = await fetch(`../../check_phone.php?phone=${phoneNumber}`);
                const data = await response.json();

                if (data.exists) {
                    phoneError.textContent = 'This phone number is already registered.';
                    phoneError.classList.remove('hidden');
                    submitButton.disabled = true;
                    submitButton.classList.add('cursor-not-allowed');
                }
            } catch (error) {
                console.error('Error checking phone number:', error);
            }
        }
    });
});
</script>
</body>
</html>