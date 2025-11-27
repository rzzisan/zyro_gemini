<?php
// PHP logic remains at the top
require_once '../../controllers/userController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_user_id();
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            UserController::handleProfileUpdate($user_id, $_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['district'], $_POST['upazila']);
        } elseif ($_POST['action'] === 'change_password') {
            UserController::handleChangePassword($user_id, $_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password']);
        }
    }
}

require_once '../layouts/header.php';

$user_id = get_user_id();
$userModel = new User(getDb());
$user_profile = $userModel->find($user_id);

$savedDistrict = $user_profile['district'] ?? '';
$savedUpazila = $user_profile['upazila'] ?? '';
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">User Profile</h1>

<?php display_message(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Update Profile Details</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                <?php csrf_field(); ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user_profile['name']); ?>" required class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" required class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_profile['phone_number'] ?? ''); ?>" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                        <select name="district" id="district" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="mb-4 md:col-span-2">
                        <label for="upazila" class="block text-sm font-medium text-gray-700">Upazila</label>
                        <select name="upazila" id="upazila" class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Upazila</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Profile
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Change Password</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <?php csrf_field(); ?>
                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="new_password" id="new_password" required class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label for="confirm_new_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_new_password" id="confirm_new_password" required class="mt-1 shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Change Password
                </button>
            </form>
        </div>
    </div>
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

<?php require_once '../layouts/footer.php'; ?>