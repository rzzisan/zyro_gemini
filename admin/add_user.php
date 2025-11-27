<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/Plan.php';

$db = getDb();
$planModel = new Plan($db);
$plans = $planModel->findAll();
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Add New User</h1>

<div class="bg-white shadow-md rounded-lg p-6">
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="create_user">
        <?php csrf_field(); ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            
            <div class="mb-4">
                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                <select id="role" name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="plan_id" class="block text-gray-700 text-sm font-bold mb-2">Plan:</label>
                <select id="plan_id" name="plan_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="district" class="block text-sm font-medium text-gray-700">District:</label>
                <select name="district" id="district" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select District</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="upazila" class="block text-sm font-medium text-gray-700">Upazila:</label>
                <select name="upazila" id="upazila" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Upazila</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create User</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const districtSelect = document.getElementById('district');
    const upazilaSelect = document.getElementById('upazila');

    let districts = [];
    let upazilas = [];

    try {
        const [districtsRes, upazilasRes] = await Promise.all([
            fetch('../content/bd-districts.json'),
            fetch('../content/bd-upazilas.json')
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
});
</script>

<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>
