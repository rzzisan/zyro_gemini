<?php
require_once __DIR__ . '/../views/layouts/admin_header.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/SmsCredit.php';

$db = getDb();

if (!isset($_GET['id'])) {
    redirect('/admin/users.php');
}

$user_id = $_GET['id'];
$userModel = new User($db);
$user_details = $userModel->getUserDetails($user_id);

if (!$user_details) {
    $_SESSION['flash_message'] = 'User not found.';
    redirect('/admin/users.php');
}

$planModel = new Plan($db);
$plans = $planModel->findAll();

$savedDistrict = $user_details['district'] ?? '';
$savedUpazila = $user_details['upazila'] ?? '';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Edit User: <?php echo htmlspecialchars($user_details['name']); ?></h1>

<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Edit User Details</h2>
    <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
        <input type="hidden" name="action" value="update_user">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_details['name']); ?>" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            
            <div class="mb-4">
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_details['phone_number'] ?? ''); ?>" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700">Role:</label>
                <select id="role" name="role" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="user" <?php echo ($user_details['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($user_details['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
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

            <div class="mb-6 md:col-span-2">
                <label for="password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter new password">
            </div>
        
        </div> <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update User Details</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Manage Subscription</h2>
        <p class="text-gray-600 mb-4">Current Plan: <span class="font-bold"><?php echo htmlspecialchars($user_details['plan_name'] ?? 'Not Subscribed'); ?></span></p>
        
        <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
            <input type="hidden" name="action" value="update_subscription">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
            
            <div class="mb-4">
                <label for="new_plan_id" class="block text-sm font-medium text-gray-700">New Plan:</label>
                <select id="new_plan_id" name="new_plan_id" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>" <?php echo ($plan['name'] == $user_details['plan_name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($plan['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Plan</button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Manage SMS Credits</h2>
        <p class="text-gray-600 mb-4">Current Credits: <span class="font-bold"><?php echo htmlspecialchars($user_details['balance'] ?? '0'); ?></span></p>
        
        <form action="<?php echo APP_URL; ?>/controllers/adminController.php" method="POST">
            <input type="hidden" name="action" value="set_credits">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_details['id']); ?>">
            
            <div class="mb-4">
                <label for="new_balance" class="block text-sm font-medium text-gray-700">Set New Balance:</label>
                <input type="number" id="new_balance" name="new_balance" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter new credit balance" required>
            </div>
            
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Set Credits</button>
        </form>
    </div>
</div>

<div class="mt-6">
    <a href="<?php echo APP_URL; ?>/admin/users.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Back to User List</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const districtSelect = document.getElementById('district');
    const upazilaSelect = document.getElementById('upazila');

    const savedDistrict = '<?php echo $savedDistrict; ?>';
    const savedUpazila = '<?php echo $savedUpazila; ?>';

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

        if (districtName === savedDistrict && savedUpazila) {
            upazilaSelect.value = savedUpazila;
        }
    };

    if (savedDistrict) {
        districtSelect.value = savedDistrict;
    }

    loadUpazilas(districtSelect.value);

    districtSelect.addEventListener('change', () => {
        loadUpazilas(districtSelect.value);
    });
});
</script>


<?php
require_once __DIR__ . '/../views/layouts/admin_footer.php';
?>