<?php
require_once '../../core/auth_guard.php';
require_once '../../controllers/userController.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_profile = [];

if ($user_id) {
    $user_profile = UserController::getUserProfile($user_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    if (UserController::updateUserProfile($user_id, ['username' => $username, 'email' => $email])) {
        set_message('Profile updated successfully!', 'success');
        // Update session user data if needed
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        redirect('/views/dashboard/profile.php');
    } else {
        set_message('Failed to update profile.', 'danger');
    }
}

require_once '../layouts/header.php';
?>

<div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6 mt-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">User Profile</h2>
    <?php display_message(); ?>
    <?php if ($user_profile): ?>
        <form action="" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" value="<?php echo htmlspecialchars($user_profile['username']); ?>" required>
            </div>
            <div class="mb-6">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email address</label>
                <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" required>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update Profile
            </button>
        </form>
    <?php else: ?>
        <p class="text-gray-600">User profile not found.</p>
    <?php endif; ?>
</div>

<?php
require_once '../layouts/footer.php';
?>
