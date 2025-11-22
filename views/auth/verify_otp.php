<?php
require_once __DIR__ . '/../../core/config.php';
require_once ROOT_PATH . '/core/functions.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    redirect('/views/dashboard/index.php');
}

// Check if temporary registration data exists in session
if (!isset($_SESSION['temp_registration'])) {
    set_message('Session expired. Please register again.', 'danger');
    redirect('/views/auth/register.php');
}

$phone_number = $_SESSION['temp_registration']['phone_number'] ?? '';
$pageTitle = "Verify OTP";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-md rounded-lg px-8 py-6 w-full max-w-md">
            <h3 class="text-2xl font-bold text-center">Verify Your Phone Number</h3>
            <p class="text-center text-gray-600 mt-2">An OTP has been sent to <?php echo htmlspecialchars($phone_number); ?>.</p>
            <?php display_message(); ?>
            <form action="/controllers/authController.php" method="POST" class="mt-4" id="otp-form">
                <input type="hidden" name="action" value="verify_otp">
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700">OTP</label>
                    <input type="text" name="otp" id="otp" placeholder="Enter 4-digit OTP"
                           class="w-full px-4 py-2 mt-1 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-600"
                           maxlength="4" required
                           oninput="if (this.value.length === 4) { this.form.submit(); }">
                </div>
                <div class="flex items-center justify-between mt-6">
                    <a href="register.php" class="text-sm text-blue-600 hover:underline">Back to Register</a>
                    <button type="submit"
                            class="px-6 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-900">
                        Verify & Register
                    </button>
                </div>
            </form>
            <div class="text-center mt-4">
                <span id="timer-text">Resend OTP in <span id="timer">02:00</span></span>
                <form action="/controllers/authController.php" method="POST" id="resend-form" class="inline" style="display:none;">
                    <input type="hidden" name="action" value="resend_otp">
                    <button type="submit" id="resend-btn" class="text-sm text-blue-600 hover:underline">Resend OTP</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const timerText = document.getElementById('timer-text');
        const timerEl = document.getElementById('timer');
        const resendForm = document.getElementById('resend-form');
        let duration = 120; // 2 minutes

        function startTimer() {
            let timer = duration, minutes, seconds;
            timerText.style.display = 'inline';
            resendForm.style.display = 'none';

            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                timerEl.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    timerText.style.display = 'none';
                    resendForm.style.display = 'inline';
                    duration = 120; // reset for next time
                }
            }, 1000);
        }

        document.getElementById('resend-form').addEventListener('submit', function() {
            startTimer();
        });

        window.onload = function () {
            startTimer();
        };
    </script>
</body>
</html>
