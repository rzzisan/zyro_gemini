<?php
require_once '../layouts/header.php';
require_once '../../controllers/userController.php';

$user_id = get_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            UserController::handleProfileUpdate($user_id, $_POST['name'], $_POST['email']);
        } elseif ($_POST['action'] === 'change_password') {
            UserController::handleChangePassword($user_id, $_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password']);
        }
    }
}

$userModel = new User(getDb());
$user_profile = $userModel->find($user_id);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">User Profile</h1>

<?php display_message(); ?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold text-gray-700 mb-4">Update Profile Details</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="update_profile">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user_profile['name']); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Update Profile
        </button>
    </form>
</div>

<div class="mt-6 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold text-gray-700 mb-4">Change Password</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="change_password">
        <div class="mb-4">
            <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password</label>
            <input type="password" name="current_password" id="current_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
            <input type="password" name="new_password" id="new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="confirm_new_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password</label>
            <input type="password" name="confirm_new_password" id="confirm_new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Change Password
        </button>
    </form>
</div>

<?php require_once '../layouts/footer.php'; ?>