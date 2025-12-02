# Changelog

## Tuesday, December 2, 2025

### Feat: Filter Users by Plan in Admin Panel
*   **What:** Added a "Filter by Plan" dropdown to the Admin User Management page.
*   **Why:** To allow administrators to easily view users subscribed to specific plans (e.g., Free, Basic, Pro).
*   **Where:**
    *   Modified `models/User.php` to support filtering by `plan_id`.
    *   Modified `admin/users.php` to include the filter UI and handle the filtering logic.
*   **How:**
    *   Updated `User::findAll` and `User::getUserCount` to accept an optional `$planId` parameter.
    *   Updated `admin/users.php` to fetch all plans using the `Plan` model and display them in a select dropdown.
    *   Updated the JavaScript `fetchData` function and the PHP AJAX handler to pass the selected `plan_id` to the backend.

### Fix: Invalid CSRF Token on Resend Verification Link
*   **What:** Fixed an "Invalid CSRF token" error when clicking the "Resend Verification Link" button in the user dashboard.
*   **Why:** The form for resending the verification link was missing the required CSRF token field, causing the server's security check to fail.
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Added the `<?php csrf_field(); ?>` helper function inside the `resend_verification` form to inject the valid CSRF token.

## Sunday, November 30, 2025

### Feat: Enhanced User Management (Search, Filter, Pagination)
*   **What:** Upgraded the User Management page in the admin panel with advanced search capabilities, pagination, and improved data display.
*   **Why:** To improve usability and performance for administrators managing a growing list of users, allowing them to quickly find specific users and navigate through large datasets efficiently.
*   **Where:**
    *   Modified `models/User.php`.
    *   Modified `admin/users.php`.
*   **How:**
    *   **Backend:** Updated `models/User.php` to:
        *   Modify `findAll` to accept `$search` parameter and support `LIKE` queries on name, email, and phone number.
        *   Update SQL query to include `phone_number` in the result set.
        *   Update `getUserCount` to support counting filtered results.
    *   **Frontend:** Updated `admin/users.php` to:
        *   Add a search form with fields for keyword search and "Rows per page" selection.
        *   Implement pagination logic to handle large lists of users.
        *   Add a "Phone Number" column to the users table and mobile card view.
        *   Display pagination controls (Previous, Page X of Y, Next) at the bottom of the list.

### Feat: AJAX Live Search and Pagination for User Management
*   **What:** Enhanced the Admin User Management page to support real-time searching and dynamic pagination using AJAX, and refined the search and filter bar UI.
*   **Why:** To provide a smoother, faster user experience by updating the user list instantly as the administrator types or navigates, without reloading the entire page. The UI was also adjusted to a cleaner, detached DataTables-style layout, addressing visual inconsistencies.
*   **Where:**
    *   Modified `admin/users.php`.
*   **How:**
    *   **Backend:** Implemented logic to detect AJAX requests (`ajax=1`) and return a JSON response containing the HTML for the user table rows, mobile card view, and pagination controls, instead of the full page layout.
    *   **Frontend:**
        *   Added a debounced event listener to the search input (300ms delay) to trigger searches without flooding the server.
        *   Added change listeners to the "Rows per page" dropdown.
        *   Implemented event delegation for pagination links to intercept clicks and fetch data asynchronously.
        *   Used `history.pushState` to update the URL with current search and page parameters, preserving the state for browser navigation.
        *   **UI Fix:** The search and filter bar was detached from the main table card, removing its `bg-white p-4 rounded-t-lg shadow-sm border-b border-gray-200` styling, and the table's wrapper div was adjusted to `rounded-lg` for a complete visual separation.

### Fix: DataTables-style Layout for Admin User Management Filter Bar
*   **What:** Corrected the layout of the search and pagination filter bar on the Admin User Management page (`admin/users.php`) to strictly adhere to the DataTables design pattern.
*   **Why:** The previous redesign attempt resulted in a centered, stacked layout, which was not the intended "Show entries" on the far left and "Search" on the far right alignment. This fix ensures the proper visual structure.
*   **Where:**
    *   Modified `admin/users.php`.
*   **How:**
    *   Updated the form container to use `w-full flex flex-col sm:flex-row justify-between items-center mb-4 gap-4`.
    *   Structured the "Show entries" section into a `div` on the left with `flex items-center gap-2`.
    *   Structured the "Search" section into a `div` on the right with `flex items-center gap-2`.
    *   Applied precise Tailwind CSS classes (`border`, `rounded`, `px`, `py`, `text-sm`, `focus:outline-none`, `focus:border-indigo-500`, `shadow-sm`) to `select` and `input` elements for a consistent, compact, and responsive design, mimicking the DataTables aesthetic.

## Thursday, November 27, 2025

### Security: Critical Fixes (Audit Remediation)
*   **What:** Remedied critical security vulnerabilities identified in a security audit.
*   **Why:** To secure the application against potential attacks involving credential exposure, weak cryptography, and information leakage.
*   **Where:**
    *   `core/config.php`
    *   `.env` (New File)
    *   `controllers/authController.php`
    *   `admin/index.php`, `admin/sms_credit.php`
*   **How:**
    *   **Credential Management:** Moved all hardcoded secrets (DB credentials, API keys, SMTP settings) from `core/config.php` to a new `.env` file (added to `.gitignore`). Updated `core/config.php` to load these values using `getenv()`.
    *   **Cryptographic Strength:** Replaced insecure `rand()` usage with `random_int()` for OTP generation in `controllers/authController.php`.
    *   **Production Safety:** Removed `ini_set('display_errors', 1)` from admin dashboard files to prevent exposing sensitive error details in production.

### Security: XSS Prevention
*   **What:** Implemented strict input sanitization and verified output escaping to prevent Cross-Site Scripting (XSS) attacks.
*   **Why:** To ensure that no malicious scripts can be stored in the database or executed in the user's browser.
*   **Where:**
    *   `core/functions.php`: Added `sanitize_input()` function.
    *   **Controllers:** Updated `authController.php`, `adminController.php`, `smsController.php`, and `report_fraud.php`, `edit_my_report.php`, `delete_my_report.php` to use `sanitize_input()` for all user-provided data.
    *   **Views:** Audited `admin/users.php` and other views to ensure `htmlspecialchars()` is used correctly for all dynamic output.
*   **How:**
    *   Input: All POST data is now processed through `trim()` and `strip_tags()` before being used.
    *   Output: Verified that `htmlspecialchars()` is applied to data before rendering in HTML.

### Fix: Server Configuration Compatibility (Part 2)
*   **What:** Replaced `DirectoryMatch` with `RewriteRule` in `.htaccess`.
*   **Why:** To resolve an "Internal Server Error" (500). The `DirectoryMatch` directive is not allowed in `.htaccess` files on standard Apache configurations, causing the server to crash.
*   **Where:** `.htaccess`.

### Fix: Server Configuration Compatibility
*   **What:** Updated `.htaccess` to use Apache 2.4 compatible syntax (`Require all denied`) instead of the deprecated 2.2 syntax (`Order allow,deny`).
*   **Why:** To resolve an "Internal Server Error" (500) caused by the server lacking the backward-compatibility module for old access control directives.
*   **Where:** `.htaccess`.

### Security: Implemented CSRF Protection and Server Hardening
*   **What:** Conducted a comprehensive security audit and implemented critical fixes to secure the application.
*   **Why:** To protect against Cross-Site Request Forgery (CSRF) attacks, prevent source code exposure, and harden the server configuration.
*   **Where:**
    *   `core/functions.php`: Added CSRF helper functions (`generate_csrf_token`, `verify_csrf_token`, `csrf_field`).
    *   `.htaccess` (New File): Created to block access to sensitive files (`.git`, `.env`, `composer.json`, etc.).
    *   **Views (Forms):** Updated all forms to include the hidden CSRF token field:
        *   `views/auth/login.php`, `register.php`, `forgot_password.php`, `verify_otp.php`, `verify_reset_otp.php`, `new_password.php`, `select_reset_method.php`.
        *   `admin/login.php`, `add_user.php`, `edit_user.php`, `add_website.php`, `sms_credit.php`, `plans.php`.
        *   `views/dashboard/send_sms.php`, `profile.php`.
        *   `views/dashboard/fraud_checker.php`, `my_fraud_reports.php` (AJAX forms).
    *   **Controllers:** Updated all controllers to verify the CSRF token before processing POST requests:
        *   `controllers/authController.php`
        *   `controllers/adminController.php`
        *   `controllers/smsController.php`
        *   `controllers/websiteController.php`
        *   `fraud_checker.php`, `report_fraud.php`, `edit_my_report.php`, `delete_my_report.php`.
*   **How:**
    *   **CSRF:** Generated a unique token per session. Injected this token into all forms. Verified the token on the server-side for every state-changing request (POST).
    *   **Server Security:** Configured Apache via `.htaccess` to deny access to dotfiles and other sensitive system files.

### Fix: Fraud Checker Robustness and Error Handling
*   **What:** Improved `fraud_checker.php` to handle external API failures gracefully.
*   **Why:** To prevent the "no results" issue when the external API is blocked (HTTP 429) or fails. The system now falls back to showing cached data (if available) even if it's expired, ensuring users still see relevant history.
*   **Where:**
    *   Modified `fraud_checker.php`.
*   **How:**
    *   Disabled `display_errors` to prevent PHP warnings from corrupting the JSON response.
    *   Implemented a fallback logic: if the API call fails, the system checks for existing data in the local database and returns it with a warning message ("Showing cached data...").
    *   Added a timeout to the cURL request to prevent long hangs.

### Fix: Fraud Checker Search Functionality
*   **What:** Updated the AJAX request URL in `views/dashboard/fraud_checker.php` to use a relative path instead of the absolute `APP_URL`.
*   **Why:** To resolve an issue where search requests were failing (or hitting the wrong server) when the local environment's URL differed from the hardcoded `APP_URL` in the configuration file. This ensures the search functionality works correctly across different environments (e.g., local dev vs. production).
*   **Where:**
    *   Modified `views/dashboard/fraud_checker.php`.
*   **How:**
    *   Replaced `<?php echo APP_URL; ?>/fraud_checker.php` with `../../fraud_checker.php` in the `fetch` call.
    *   Also updated the `report_fraud.php` fetch call and the `my_fraud_reports.php` link to use relative paths for consistency.

### Feat: Complete Forgot Password Implementation
*   **What:** Implemented a full "Forgot Password" feature, including database schema, backend logic for OTP generation and verification, UI for user identity input, OTP method selection, OTP verification, and new password setting.
*   **Why:** To allow users to securely regain access to their accounts if they forget their password, improving user experience and account management.
*   **Where:**
    *   `password_resets.sql` (New File)
    *   `models/PasswordReset.php` (New File)
    *   `models/User.php`
    *   `views/auth/forgot_password.php` (New File)
    *   `views/auth/select_reset_method.php` (New File)
    *   `views/auth/verify_reset_otp.php` (New File)
    *   `views/auth/new_password.php` (New File)
    *   `views/auth/login.php`
    *   `controllers/authController.php`
    *   `controllers/EmailController.php`
*   **How:**
    1.  **Database Schema:** Created `password_resets.sql` for the `password_resets` table, including `user_id` (BIGINT UNSIGNED), `otp`, `created_at`, and `expires_at`.
    2.  **PasswordReset Model:** Created `models/PasswordReset.php` with `create()`, `verify()`, and `deleteUserOtps()` methods for managing OTPs.
    3.  **User Model Enhancement:** Added `findByIdentity($identity)` to `models/User.php` to allow searching by email or phone number.
    4.  **Forgot Password Initiation UI:** Created `views/auth/forgot_password.php` for users to enter their email or phone number.
    5.  **OTP Method Selection UI:** Created `views/auth/select_reset_method.php` to allow users to choose between email or SMS for OTP delivery, including masking sensitive contact information.
    6.  **OTP Sending Logic:**
        *   Added `find_user_for_reset` and `send_reset_otp` cases to `controllers/authController.php`.
        *   `send_reset_otp` generates a 6-digit OTP, stores it using the `PasswordReset` model, and sends it via `EmailController::sendEmail()` or `SmsController::sendSystemSms()`.
        *   `EmailController.php` was updated with a generic `sendEmail` method.
    7.  **OTP Verification UI:** Created `views/auth/verify_reset_otp.php` for users to enter the received OTP.
    8.  **New Password UI:** Created `views/auth/new_password.php` for users to set their new password after successful OTP verification.
    9.  **Password Update Logic:**
        *   Added `verify_otp_reset` and `update_password` cases to `controllers/authController.php`.
        *   `verify_otp_reset` validates the OTP and sets a session flag.
        *   `update_password` validates the new password, updates the user's password using `User::updatePassword()`, clears old OTPs, and logs the user out from the reset flow.
    10. **UI Linking:** Updated `views/auth/login.php` to include a "Forgot password?" link to `forgot_password.php`.
    11. **Bug Fixes and Refinements:**
        *   Corrected SMS formatting in `controllers/authController.php` by using `formatPhoneNumber()`.
        *   Refined OTP verification and creation in `models/PasswordReset.php` to use SQL's `NOW()` and `DATE_ADD(NOW(), INTERVAL 15 MINUTE)` for accurate time comparisons and expiration.
        *   Fixed a database error by changing `password_resets.user_id` to `BIGINT UNSIGNED` to match `users.id`.

## Wednesday, November 26, 2025

### Improvement: Modernized Email Verification Notice
*   **What:** Updated the styling of the email verification notice in the user dashboard.
*   **Why:** To implement a more robust and standard "Warning" style design using Tailwind's yellow colors, ensuring better visibility and consistency with modern UI/UX practices.
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Replaced the existing email verification notice block with a new HTML structure that utilizes Tailwind CSS classes for a yellow-themed warning box, including an SVG icon and revised text styling.
    *   Ensured the notice is only displayed for logged-in users whose email address is not yet verified.

### Fix: Email Verification UI and Logic
*   **What:** Fixed the broken design of the email verification notice and ensured that flash messages are displayed correctly. The backend logic for resending verification emails was also improved.
*   **Why:** To provide a clear and functional user interface for email verification, ensuring users are properly notified and can easily resend the verification link.
*   **Where:**
    *   `views/layouts/header.php`
    *   `controllers/authController.php`
    *   `verify_email.php`
*   **How:**
    *   **`views/layouts/header.php`**:
        *   Added a call to `display_message()` at the top of the `<main>` tag to ensure all success, error, and info messages are rendered.
        *   Replaced the old verification notice with a new, more visually appealing design using Tailwind CSS classes (`bg-orange-50`, `border-orange-500`, etc.) to make it more prominent.
    *   **`controllers/authController.php`**:
        *   Updated the `resend_verification` case to include a check to see if the user's email is already verified, preventing unnecessary emails.
        *   Added more specific user feedback messages for success (`Verification link sent to your email!`) and failure (`Failed to send email.`).
    *   **`verify_email.php`**:
        *   Updated the success and error messages to be more user-friendly (`Email verified successfully!` and `Invalid or expired token.`).

### Feat: Implement Email Verification System
*   **What:** Implemented a complete email verification system to ensure that users have a valid and accessible email address.
*   **Why:** To improve account security, reduce spam registrations, and ensure that users can receive important notifications.
*   **Where:**
    *   `core/config.php`
    *   `controllers/EmailController.php` (New File)
    *   `models/User.php`
    *   `controllers/authController.php`
    *   `verify_email.php` (New File)
    *   `views/layouts/header.php`
*   **How:**
    1.  **Configuration:** Added dummy SMTP credentials to `core/config.php` to configure `PHPMailer`.
    2.  **Email Sending:** Created `controllers/EmailController.php` with a `sendVerificationEmail` method that uses `PHPMailer` to send a verification link to the user.
    3.  **Database & Model:**
        *   Updated `models/User.php`:
            *   The `updateProfile` method now resets a user's `email_verified_at` and `email_verification_token` status if they change their email address.
            *   Added `setEmailVerificationToken()` to save a unique token to the user's record.
            *   Added `verifyEmailByToken()` to find a user by their token, mark their email as verified, and clear the token.
    4.  **Verification Flow:**
        *   Created `verify_email.php` in the root directory to handle the verification link clicked by the user from their email. It validates the token and updates the user's status.
    5.  **User Experience:**
        *   Added a `resend_verification` case to `controllers/authController.php` to allow logged-in users to request a new verification link.
        *   Modified `views/layouts/header.php` to display a prominent alert banner for users whose `email_verified_at` status is `NULL`. The banner includes a "Resend Verification Link" button that triggers the resend functionality.

### Feat: System-Generated SMS Logging
*   **What:** Implemented a feature to log system-generated SMS messages (e.g., OTPs) into the database. These messages now appear in the Admin Panel's SMS History with the user name "System".
*   **Why:** To provide administrators with a complete audit trail of all SMS messages sent by the system, improving transparency and making it easier to track and debug system-generated notifications.
*   **Where:**
    *   Modified `models/SmsHistory.php`.
    *   Modified `controllers/smsController.php`.
    *   Modified `controllers/authController.php`.
*   **How:**
    *   **`models/SmsHistory.php`**:
        *   Updated the `getSmsHistoryPaginated` method to use a `LEFT JOIN` on the `users` table, ensuring that messages with a `NULL` `user_id` are included.
        *   Used `COALESCE(u.name, 'System')` to display "System" as the user name for these messages.
    *   **`controllers/smsController.php`**:
        *   Created a new `sendSystemSms($to, $message)` method that sends an SMS and then logs it to the `sms_history` table with a `NULL` `user_id` and `0` `credit_deducted`.
    *   **`controllers/authController.php`**:
        *   Replaced the existing `SmsController::sendSms` calls with the new `SmsController::sendSystemSms` method in both the `register` and `resend_otp` cases.

### Feat: Real-time Email Validation on Registration
*   **What:** Implemented real-time, client-side validation for the email field on the user registration page.
*   **Why:** To provide immediate feedback to users if an email address is already registered, improving the user experience and preventing form submission errors.
*   **Where:**
    *   Modified `views/auth/register.php`.
*   **How:**
    *   Added a `<span>` element with `id="email-error"` to display validation messages for the email field.
    *   Added an `input` event listener to the email field.
    *   When the input value is a valid email format, a `fetch` request is sent to the `check_email.php` endpoint.
    *   If the API returns `{"exists": true}`, an error message is displayed, and the submit button is disabled to prevent the user from proceeding.
    *   If the email is valid, any existing error message is cleared, and the submit button is enabled.

### Feat: Implement Real-time Email Uniqueness Check
*   **What:** Created a new API endpoint to check for email uniqueness in real-time.
*   **Why:** To provide immediate feedback to users during registration if an email address is already in use, improving user experience and preventing duplicate accounts.
*   **Where:**
    *   Created `check_email.php`.
*   **How:**
    *   Created a new file `check_email.php` that acts as a JSON API endpoint.
    *   The endpoint receives an `email` via a GET request.
    *   It uses the existing `findByEmail()` method in the `User` model to check if the email exists in the database.
    *   It returns a JSON response: `{"exists": true}` if the email is found, and `{"exists": false}` otherwise.

### Fix: Server-side Phone Number Uniqueness Validation
*   **What:** Added server-side validation to the registration process to check for phone number uniqueness.
*   **Why:** To provide a robust, secure check that prevents duplicate phone numbers from being registered, even if client-side validation is bypassed.
*   **Where:**
    *   Modified `controllers/authController.php`.
*   **How:**
    *   In the `register` case, before generating the OTP, the `findByPhoneNumber()` method is now called.
    *   If the method returns a user, it indicates the phone number is already in use.
    *   A flash message "Phone number already registered." is set, and the user is redirected back to the registration page.

### Feat: Real-time Phone Number Validation on Registration
*   **What:** Implemented real-time, client-side validation for the phone number field on the user registration page.
*   **Why:** To provide immediate feedback to users if a phone number is already registered, improving the user experience and preventing form submission errors.
*   **Where:**
    *   Modified `views/auth/register.php`.
*   **How:**
    *   Added a `<span>` element with `id="phone-error"` to display validation messages for the phone number field.
    *   Added an `input` event listener to the phone number field.
    *   When the input value reaches 11 digits, a `fetch` request is sent to the `check_phone.php` endpoint.
    *   If the API returns `{"exists": true}`, an error message is displayed, and the submit button is disabled to prevent the user from proceeding.
    *   If the number is valid, any existing error message is cleared, and the submit button is enabled.

### Feat: Implement Phone Number Uniqueness Check
*   **What:** Implemented a server-side check to ensure that all user phone numbers are unique.
*   **Why:** To prevent duplicate user accounts associated with the same phone number and maintain data integrity.
*   **Where:**
    *   Modified `models/User.php`.
    *   Created `check_phone.php`.
*   **How:**
    *   Added a new `findByPhoneNumber($phoneNumber)` method to the `User` model, which queries the database to find a user by their phone number.
    *   Created a new API endpoint at `check_phone.php` that takes a phone number as a GET parameter.
    *   The endpoint uses the `findByPhoneNumber` method to check for the existence of the phone number and returns a JSON response (`{"exists": true}` or `{"exists": false}`).

### Fix: Add Phone Number Validation to Admin Create User
*   **What:** Added validation for the `phone_number` field in the `create_user` case within `controllers/adminController.php`.
*   **Why:** To ensure that the mandatory `phone_number` field is not empty when an administrator creates a new user, preventing incomplete user data.
*   **Where:**
    *   Modified `controllers/adminController.php`.
*   **How:**
    *   Updated the `if` condition in the `create_user` case to include `!empty($_POST['phone_number'])`, making the phone number a required field for user creation.
    *   Adjusted the flash message to reflect that the phone number is now a required field.

### Feat: Add Contact and Location Fields to Create User Form
*   **What:** Updated the "Add New User" page in the admin panel to include `phone_number`, `district`, and `upazila` fields.
*   **Why:** To allow administrators to enter more complete user information, including contact and location details, directly upon user creation, aligning the "Add User" form with the "Edit User" form.
*   **Where:**
    *   Modified `admin/add_user.php`.
    *   Modified `controllers/adminController.php`.
*   **How:**
    *   Added input fields for `phone_number`, and dropdowns for `district` and `upazila` to the `admin/add_user.php` form, organized within a responsive grid layout.
    *   Included the JavaScript logic from `admin/edit_user.php` to dynamically populate the district and upazila dropdowns from JSON files.
    *   Updated the `create_user` case in `controllers/adminController.php` to retrieve and pass the new `phone_number`, `district`, and `upazila` values to the `userModel->createUser()` method.

### Feat: Prevent Admin Self-Role Change
*   **What:** Updated `admin/edit_user.php` to prevent an administrator from changing their own role.
*   **Why:** To enhance security and prevent accidental lockouts or privilege escalations, an administrator should not be able to modify their own role.
*   **Where:**
    *   Modified `admin/edit_user.php`.
*   **How:**
    *   Identified the current admin by retrieving `$_SESSION['admin_id']`.
    *   Created a boolean variable `$is_self_edit` to check if the user being edited is the same as the logged-in admin.
    *   Disabled the role `<select>` dropdown and added a visual `bg-gray-100` class if `$is_self_edit` is true.
    *   Added a hidden input field to submit the user's current role, ensuring the form submission does not fail when the dropdown is disabled.
    *   Added a user-friendly message ("You cannot change your own role.") below the dropdown to inform the admin why it is disabled.

### Fix: Remove Debug Logging from Remember Me Guard
*   **What:** Removed all `error_log` statements from `core/remember_me_guard.php`.
*   **Why:** The debug logging was causing "Permission denied" warnings on the production server, and the "Remember Me" functionality is now confirmed to be working correctly.
*   **Where:**
    *   Modified `core/remember_me_guard.php`.
*   **How:**
    *   Deleted all lines containing `error_log(...)` from the `check_remember_me` function.

### Fix: Remember Me Functionality for Admin Users
*   **What:** Modified `core/remember_me_guard.php` to ensure admin users remain logged into the User Dashboard after closing the browser.
*   **Why:** Previously, the "Remember Me" functionality only set `admin_id` for admins, but the User Dashboard requires `user_id`. This fix ensures both are set correctly.
*   **Where:**
    *   Modified `core/remember_me_guard.php`.
*   **How:**
    *   Ensured that `$_SESSION['user_id']` and `$_SESSION['user_name']` are always set when a user is authenticated via the "Remember Me" token.
    *   Additionally, if the authenticated user has the 'admin' role, `$_SESSION['admin_id']`, `$_SESSION['admin_name']`, and `$_SESSION['is_admin']` are also set.
    *   Updated debug logging file paths from `ROOT_PATH` to `__DIR__ . '/../../debug.log'` for consistency and reliability.

### Feat: Add Debug Logging to Remember Me Guard
*   **What:** Added extensive debug logging to `core/remember_me_guard.php`.
*   **Why:** To help identify and troubleshoot issues with the "Remember Me" functionality by logging key steps and data points during the authentication process.
*   **Where:**
    *   Modified `core/remember_me_guard.php`.
*   **How:**
    *   Added `error_log` statements at various stages: when the cookie is found, when the token matches in the database, when the user is found, and when the token or user is not found.
    *   Logs include timestamps, relevant data (e.g., user ID, email, role), and a clear description of the event.

## Saturday, November 23, 2025

### Feat: Implement OTP Verification for User Registration
* **What:** Added a mandatory OTP (One-Time Password) verification step to the user registration process.
* **Why:** To verify the ownership of the provided phone number and prevent fake or unverified account registrations. User data is now committed to the database only after successful verification.
* **Where:**
    * `controllers/authController.php`
    * `views/auth/verify_otp.php` (New File)
    * `views/auth/register.php`
    * `models/User.php`
* **How:**
    * **Registration Flow:** Modified `controllers/authController.php` to intercept the registration request. Instead of saving to the database immediately, user data is validated and stored temporarily in `$_SESSION['temp_registration']`.
    * **OTP Generation:** A random 4-digit OTP is generated and sent to the user's phone using the `SmsController`.
    * **Verification Page:** Created `views/auth/verify_otp.php`, a standalone page that displays the target phone number and an input for the code.
    * **Auto-Submit & Timer:** Implemented JavaScript in the verification page to automatically submit the form upon entering 4 digits and added a 2-minute countdown timer for the "Resend OTP" button.
    * **Finalization:** Added `verify_otp` logic in the controller to match the code. Upon success, the user is created in the database, subscribed to the free plan, and logged in.
    * **Resend Logic:** Added `resend_otp` action to generate and send a new code if the previous one expires or is lost.
    * **Data Fields:** Updated `views/auth/register.php` and `models/User.php` to include and handle `phone_number` (mandatory), `district`, and `upazila` fields.

## Wednesday, November 19, 2025

### Feat: Install and Configure Tailwind CSS
*   **What:** Installed and configured Tailwind CSS for the project.
*   **Why:** To enable the use of the Tailwind CSS framework for styling the application.
*   **Where:**
    *   `package.json`
    *   `tailwind.config.js`
    *   `public/css/input.css`
*   **How:**
    *   Installed `tailwindcss`, `postcss`, and `autoprefixer` as dev dependencies using `npm install -D tailwindcss postcss autoprefixer`.
    *   Initialized the Tailwind CSS configuration file using `npx tailwindcss init`.
    *   Updated the `content` array in `tailwind.config.js` to include all `*.php` files in the project.
    *   Ensured that `public/css/input.css` contains the necessary Tailwind directives (`@tailwind base;`, `@tailwind components;`, `@tailwind utilities;`).
    *   Ran the `npm run build` command to compile the CSS into `public/css/style.css`.

## Tuesday, November 18, 2025

### Fix: Project Styling
*   **What:** Fixed the project's styling, which was not being applied correctly.
*   **Why:** The project was attempting to use Tailwind CSS directives directly in the browser, which is not supported. A proper build process was needed to compile the Tailwind CSS into a usable stylesheet.
*   **Where:**
    *   Created `tailwind.config.js`.
    *   Created `package.json`.
    *   Modified `views/layouts/header.php`.
    *   Modified `public/css/app.css`.
*   **How:**
    *   Created a `tailwind.config.js` file to configure Tailwind CSS.
    *   Created a `package.json` file to manage dependencies and scripts.
    *   Installed `tailwindcss` as a dev dependency.
    *   Added a `build` script to `package.json` to compile `public/css/input.css` into `public/css/app.css`.
    *   Ran the build script to generate the `app.css` file.
    *   Removed the conflicting Tailwind CSS CDN script from `views/layouts/header.php`.

## Sunday, November 16, 2025

### Fix: Admin Dashboard Hamburger Menu
*   **What:** Fixed the hamburger menu functionality, positioning, and mobile visibility in the admin dashboard.
*   **Why:** The hamburger menu was not working correctly, was misplaced, and was not visible on mobile devices.
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Moved the hamburger button for mobile view to be a direct child of the main `div` and positioned it using `fixed top-4 left-4 z-40`.
    *   Added the `lg:hidden` class to the mobile hamburger button so it only appears on smaller screens.
    *   Added a new hamburger button inside the `<aside>` element that is only visible on large screens (`hidden lg:block`) to toggle the sidebar when it's in its collapsed state.
    *   Modified the original hamburger button inside the `<header>` to be visible only on large screens (`hidden lg:block`).
    *   Added `@resize.window="sidebarOpen = window.innerWidth > 1024"` to the main `div` to ensure the sidebar state is correct when the window is resized.

## Saturday, November 15, 2025

### Fix: Update Admin Panel footer to match new layout structure
*   **What:** Replaced the entire content of `views/layouts/admin_footer.php` to correctly close HTML tags.
*   **Why:** To ensure proper HTML structure and rendering after the admin dashboard layout redesign, as the new `admin_header.php` introduced new opening tags that needed corresponding closing tags in the footer.
*   **Where:**
    *   Modified `views/layouts/admin_footer.php`.
*   **How:**
    *   Updated the content of `admin_footer.php` to include the necessary closing `</main>`, `</div>`, `</div>`, `</body>`, and `</html>` tags.

### Fix: Correct Admin Panel layout, fix content gap, and restore mobile toggle
*   **What:** Replaced the entire content of `views/layouts/admin_header.php` with corrected code.
*   **Why:** The previous implementation of the admin panel sidebar had two issues: a content gap on desktop when the sidebar was collapsed, and the mobile toggle button was hidden within the sidebar, making it inaccessible on small screens. This fix addresses both.
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Moved the sidebar toggle button from inside the `<aside>` element to the `<header>` element, making it always visible.
    *   Adjusted the `x-data` initialization for `sidebarOpen` to ensure the sidebar is open by default on large screens (`window.innerWidth > 1024`).
    *   Ensured the main content area's left margin (`lg:ml-64` or `lg:ml-20`) correctly adjusts based on the `sidebarOpen` state, eliminating the content gap.
    *   Updated the Alpine.js CDN link to a more stable version.

### Fix: Correct User Dashboard layout, fix content gap, and restore mobile toggle
*   **What:** Replaced the entire content of `views/layouts/header.php` with corrected code.
*   **Why:** The previous implementation of the user dashboard sidebar had two issues: a content gap on desktop when the sidebar was collapsed, and the mobile toggle button was hidden within the sidebar, making it inaccessible on small screens. This fix addresses both.
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Moved the sidebar toggle button from inside the `<aside>` element to the `<header>` element, making it always visible.
    *   Adjusted the `x-data` initialization for `sidebarOpen` to ensure the sidebar is open by default on large screens (`window.innerWidth > 1024`).
    *   Ensured the main content area's left margin (`lg:ml-64` or `lg:ml-20`) correctly adjusts based on the `sidebarOpen` state, eliminating the content gap.
    *   Updated the Alpine.js CDN link to a more stable version.

### Fix: Update Admin Panel footer to match new layout structure
*   **What:** Replaced the entire content of `views/layouts/admin_footer.php` to correctly close HTML tags.
*   **Why:** To ensure proper HTML structure and rendering after the admin dashboard layout redesign, as the new `admin_header.php` introduced new opening tags that needed corresponding closing tags in the footer.
*   **Where:**
    *   Modified `views/layouts/admin_footer.php`.
*   **How:**
    *   Updated the content of `admin_footer.php` to include the necessary closing `</main>`, `</div>`, `</div>`, `</body>`, and `</html>` tags.
*   **What:** Replaced the entire content of `views/layouts/admin_header.php` to introduce a fully responsive sidebar that pushes content on desktop and overlays on mobile.
*   **Why:** To provide a more modern and flexible navigation experience for administrators, optimizing for both large and small screens by dynamically adjusting sidebar behavior (pushing content on desktop, overlaying on mobile) and width (expanded or collapsed).
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Updated the `x-data` directive to initialize `sidebarOpen` based on `window.innerWidth` for desktop-first behavior.
    *   Modified the `<aside>` element's classes to control width (`w-64` for open, `w-20` for collapsed) and transform behavior (`-translate-x-full` for closed mobile, `translate-x-0` for open mobile and desktop) using Alpine.js `:class` bindings.
    *   Adjusted the main content area's left margin (`lg:ml-64` or `lg:ml-20`) dynamically based on `sidebarOpen` state to create the "push" effect on desktop.
    *   Implemented conditional rendering (`x-show`) for sidebar elements like the logo and navigation text to adapt to collapsed state.
    *   Ensured the mobile overlay (`x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak`) is present for proper mobile interaction.
    *   Adjusted the `SMS` dropdown button to hide the chevron icon when the sidebar is collapsed (`'hidden': !sidebarOpen`).

### Feat: Implement dynamic push/overlay sidebar layout for Admin Panel
*   **What:** Replaced the entire content of `views/layouts/admin_header.php` to introduce a fully responsive sidebar that pushes content on desktop and overlays on mobile.
*   **Why:** To provide a more modern and flexible navigation experience for administrators, optimizing for both large and small screens by dynamically adjusting sidebar behavior (pushing content on desktop, overlaying on mobile) and width (expanded or collapsed).
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Updated the `x-data` directive to initialize `sidebarOpen` based on `window.innerWidth` for desktop-first behavior.
    *   Modified the `<aside>` element's classes to control width (`w-64` for open, `w-20` for collapsed) and transform behavior (`-translate-x-full` for closed mobile, `translate-x-0` for open mobile and desktop) using Alpine.js `:class` bindings.
    *   Adjusted the main content area's left margin (`lg:ml-64` or `lg:ml-20`) dynamically based on `sidebarOpen` state to create the "push" effect on desktop.
    *   Implemented conditional rendering (`x-show`) for sidebar elements like the logo and navigation text to adapt to collapsed state.
    *   Ensured the mobile overlay (`x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak`) is present for proper mobile interaction.
    *   Adjusted the `SMS` dropdown button to hide the chevron icon when the sidebar is collapsed (`'hidden': !sidebarOpen`).

### Fix: Update User Dashboard footer to match new layout structure
*   **What:** Replaced the entire content of `views/layouts/footer.php` to correctly close HTML tags.
*   **Why:** To ensure proper HTML structure and rendering after the user dashboard layout redesign, as the new `header.php` introduced new opening tags that needed corresponding closing tags in the footer.
*   **Where:**
    *   Modified `views/layouts/footer.php`.
*   **How:**
    *   Updated the content of `footer.php` to include the necessary closing `</main>`, `</div>`, `</div>`, `</body>`, and `</html>` tags.

### Feat: Implement dynamic push/overlay sidebar layout for User Dashboard
*   **What:** Replaced the entire content of `views/layouts/header.php` to introduce a fully responsive sidebar that pushes content on desktop and overlays on mobile.
*   **Why:** To provide a more modern and flexible navigation experience, optimizing for both large and small screens by dynamically adjusting sidebar behavior (pushing content on desktop, overlaying on mobile) and width (expanded or collapsed).
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Updated the `x-data` directive to initialize `sidebarOpen` based on `window.innerWidth` for desktop-first behavior.
    *   Modified the `<aside>` element's classes to control width (`w-64` for open, `w-20` for collapsed) and transform behavior (`-translate-x-full` for closed mobile, `translate-x-0` for open mobile and desktop) using Alpine.js `:class` bindings.
    *   Adjusted the main content area's left margin (`lg:ml-64` or `lg:ml-20`) dynamically based on `sidebarOpen` state to create the "push" effect on desktop.
    *   Implemented conditional rendering (`x-show`) for sidebar elements like the logo and navigation text to adapt to collapsed state.
    *   Ensured the mobile overlay (`x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak`) is present for proper mobile interaction.

### Fix: Correct sidebar logic for Admin Panel layout
*   **What:** Replaced the entire content of `views/layouts/admin_header.php` with corrected code to ensure proper sidebar behavior.
*   **Why:** The previous implementation of the sidebar in `admin_header.php` did not correctly handle responsiveness, causing both the sidebar and its toggle button to be hidden on desktop. This fix ensures the sidebar is static and visible on desktop (lg:) while acting as a toggled overlay on mobile.
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Added a fixed overlay `div` (`x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak`) for mobile to dim the content when the sidebar is open.
    *   Modified the `<aside>` element's classes to include `lg:translate-x-0 lg:static lg:inset-0` to make the sidebar static on large screens.
    *   Adjusted the main content area's classes to include `lg:ml-64` to account for the static sidebar width on large screens.
    *   Ensured the sidebar toggle button is hidden on large screens (`lg:hidden`).

### Fix: Correct sidebar logic for User Dashboard layout
*   **What:** Replaced the entire content of `views/layouts/header.php` with corrected code to ensure proper sidebar behavior.
*   **Why:** The previous implementation of the sidebar in `header.php` did not correctly handle responsiveness, causing the sidebar to behave inconsistently on different screen sizes. This fix ensures the sidebar is static and visible on desktop (lg:) while acting as a toggled overlay on mobile.
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Added a fixed overlay `div` (`x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 transition-opacity lg:hidden" x-cloak`) for mobile to dim the content when the sidebar is open.
    *   Modified the `<aside>` element's classes to include `lg:translate-x-0 lg:static lg:inset-0` to make the sidebar static on large screens.
    *   Adjusted the main content area's classes to include `lg:ml-64` to account for the static sidebar width on large screens.
    *   Ensured the sidebar toggle button is hidden on large screens (`lg:hidden`).

### Fix: Update Admin Panel footer for new sidebar layout
*   **What:** Replaced the entire content of `views/layouts/admin_footer.php` to correctly close HTML tags.
*   **Why:** To ensure proper HTML structure and rendering after the admin dashboard layout redesign, as the new `admin_header.php` introduced new opening tags that needed corresponding closing tags in the footer.
*   **Where:**
    *   Modified `views/layouts/admin_footer.php`.
*   **How:**
    *   Updated the content of `admin_footer.php` to include the necessary closing `</main>`, `</div>`, `</div>`, `</body>`, and `</html>` tags.

### Feat: Redesign Admin Panel layout with a sidebar
*   **What:** Replaced the entire content of `views/layouts/admin_header.php` to introduce a new responsive sidebar layout for the Admin Panel.
*   **Why:** To improve the overall user experience and navigation for administrators by implementing a modern sidebar design using Alpine.js and Tailwind CSS, and preparing the main content area for a consistent look and feel.
*   **Where:**
    *   Modified `views/layouts/admin_header.php`.
*   **How:**
    *   Implemented a `div` with `x-data="{ sidebarOpen: false }"` to manage the sidebar's open/close state.
    *   Created an `<aside>` element for the sidebar with responsive classes (`fixed`, `inset-y-0`, `left-0`, `z-30`, `w-64`, `px-2`, `py-4`, `overflow-y-auto`, `bg-gray-800`, `text-gray-100`, `transition-transform`, `duration-300`, `ease-in-out`, `transform`) and Alpine.js directives (`:class`, `x-show.transition`, `@click.away`, `x-cloak`).
    *   Added navigation links within the sidebar, dynamically applying active states using `is_active()` function.
    *   Created a header section with a button to toggle the sidebar on smaller screens and an admin greeting with a logout link.
    *   Ensured that no backend logic was altered, focusing solely on the presentation layer.

### Feat: Redesign User Dashboard layout with a sidebar
*   **What:** Replaced the entire content of `views/layouts/header.php` to introduce a new responsive sidebar layout.
*   **Why:** To improve the overall user experience and navigation by implementing a modern sidebar design using Alpine.js and Tailwind CSS, and preparing the main content area for a consistent look and feel.
*   **Where:**
    *   Modified `views/layouts/header.php`.
*   **How:**
    *   Implemented a `div` with `x-data="{ sidebarOpen: false }"` to manage the sidebar's open/close state.
    *   Created an `<aside>` element for the sidebar with responsive classes (`fixed`, `inset-y-0`, `left-0`, `z-30`, `w-64`, `px-2`, `py-4`, `overflow-y-auto`, `bg-white`, `border-r`, `transition-transform`, `duration-300`, `ease-in-out`, `transform`) and Alpine.js directives (`:class`, `x-show.transition`, `@click.away`, `x-cloak`).
    *   Added navigation links within the sidebar, dynamically applying active states using `is_active()` function.
    *   Created a header section with a button to toggle the sidebar on smaller screens and a user greeting with a logout link.
    *   Ensured that no backend logic was altered, focusing solely on the presentation layer.

### Refactor: Redesign user profile page for UI/UX and responsiveness
*   **What:** Redesigned the user profile page (`views/dashboard/profile.php`) to improve its UI/UX and responsiveness.
*   **Why:** To provide a more modern and user-friendly layout, especially on larger screens, by arranging the profile and password forms into a two-column layout.
*   **Where:**
    *   Modified `views/dashboard/profile.php`.
*   **How:**
    *   Implemented a `grid grid-cols-1 lg:grid-cols-3 gap-6` layout for the main content area.
    *   The "Update Profile Details" form now occupies two columns (`lg:col-span-2`) and the "Change Password" form occupies one column (`lg:col-span-1`) on large screens.
    *   The input fields within the "Update Profile Details" form were further organized into a `grid grid-cols-1 md:grid-cols-2 gap-4` for better spacing and alignment.
    *   Updated styling of labels and input fields for consistency.

### Refactor: Redesign admin edit user form for UI/UX
*   **What:** Redesigned the main 'Edit User Details' form in `admin/edit_user.php` to use a responsive `md:grid-cols-2` layout.
*   **Why:** To improve the UI/UX by making the form more compact and user-friendly, especially on desktop screens, without altering the backend logic or other forms on the page.
*   **Where:**
    *   Modified `admin/edit_user.php`.
*   **How:**
    *   Wrapped the input fields for name, email, phone number, role, district, upazila, and password within a `div` with `grid grid-cols-1 md:grid-cols-2 gap-4` classes.
    *   Adjusted the `password` field to span two columns using `md:col-span-2` for better layout.
    *   Updated the styling of labels and input fields to align with the new design.

### Fix: Correctly parse nested JSON for district/upazila in admin panel
*   **What:** Modified `admin/edit_user.php` to correctly parse the nested JSON structure for districts and upazilas.
*   **Why:** The previous JavaScript code was failing to load districts and upazilas because it was not correctly accessing the `.districts` and `.upazilas` properties of the fetched JSON responses.
*   **Where:**
    *   Modified the `<script>` block in `admin/edit_user.php`.
*   **How:**
    *   Updated the `fetch` calls to explicitly access `(await districtsRes.json()).districts` and `(await upazilasRes.json()).upazilas` to correctly extract the array data from the JSON objects.

### Fix: Correctly parse nested JSON for district/upazila in user profile
*   **What:** Modified `views/dashboard/profile.php` to correctly parse the nested JSON structure for districts and upazilas.
*   **Why:** The previous JavaScript code was failing to load districts and upazilas because it was not correctly accessing the `.districts` and `.upazilas` properties of the fetched JSON responses.
*   **Where:**
    *   Modified the `<script>` block in `views/dashboard/profile.php`.
*   **How:**
    *   Updated the `fetch` calls to explicitly access `(await districtsRes.json()).districts` and `(await upazilasRes.json()).upazilas` to correctly extract the array data from the JSON objects.

### Feat: Add new fields and district/upazila logic to admin edit user page
*   **What:** Replaced the entire content of `admin/edit_user.php` to include new form fields for `phone_number`, `district`, and `upazila`, along with JavaScript logic to dynamically load districts and upazilas from JSON files.
*   **Why:** To allow administrators to update user profiles with phone numbers and select their district and upazila from a predefined list, enhancing user data management.
*   **Where:**
    *   Modified `admin/edit_user.php`.
*   **How:**
    *   Added input fields for `phone_number`, and dropdowns for `district` and `upazila`.
    *   Implemented JavaScript to fetch `bd-districts.json` and `bd-upazilas.json` from the `content/` directory.
    *   Populated the `district` dropdown with data from `bd-districts.json`.
    *   Added an event listener to the `district` dropdown to dynamically load and populate the `upazila` dropdown based on the selected district.
    *   Pre-selected saved district and upazila values from the user's profile.

### Feat: Add new fields and district/upazila logic to user profile
*   **What:** Replaced the entire content of `views/dashboard/profile.php` to include new form fields for `phone_number`, `district`, and `upazila`, along with JavaScript logic to dynamically load districts and upazilas from JSON files.
*   **Why:** To allow users to update their phone number and select their district and upazila from a predefined list, enhancing user profile completeness and data accuracy.
*   **Where:**
    *   Modified `views/dashboard/profile.php`.
*   **How:**
    *   Added input fields for `phone_number`, and dropdowns for `district` and `upazila`.
    *   Implemented JavaScript to fetch `bd-districts.json` and `bd-upazilas.json` from the `content/` directory.
    *   Populated the `district` dropdown with data from `bd-districts.json`.
    *   Added an event listener to the `district` dropdown to dynamically load and populate the `upazila` dropdown based on the selected district.
    *   Pre-selected saved district and upazila values from the user's profile.

### Refactor: Update adminController to handle new user fields
*   **What:** Modified `controllers/adminController.php` to update the `case 'update_user':` block.
*   **Why:** To ensure that administrators can update the new `phone_number`, `district`, and `upazila` fields for users.
*   **Where:**
    *   Modified `controllers/adminController.php`.
*   **How:**
    *   Updated the `data` array within the `update_user` case to include `phone_number`, `district`, and `upazila` from the `$_POST` data.
    *   These new fields are then passed to the `userModel->update()` method.

### Refactor: Update userController to handle new profile fields
*   **What:** Modified `controllers/userController.php` to update the `handleProfileUpdate` method.
*   **Why:** To accommodate the new `phone_number`, `district`, and `upazila` fields when updating a user's profile.
*   **Where:**
    *   Modified `controllers/userController.php`.
*   **How:**
    *   Updated the `handleProfileUpdate` method signature to accept `phone`, `district`, and `upazila` parameters.
    *   Passed these new parameters to the `updateProfile` method of the `User` model.

### Feat: Update User model to support new profile fields
*   **What:** Modified `models/User.php` to include the new `phone_number`, `district`, and `upazila` fields.
*   **Why:** To allow the application to retrieve and update the new user profile fields.
*   **Where:**
    *   Modified `models/User.php`.
*   **How:**
    *   Replaced the `find()` method with a new version that selects the new fields.
    *   Replaced the `getUserDetails()` method with a new version that selects the new fields.
    *   Replaced the `updateProfile()` method to handle the new fields.
    *   Replaced the `update()` method (for admins) to handle the new fields robustly.

### Database Migration: Add User Contact and Location Fields
*   **What:** Added `phone_number`, `district`, and `upazila` columns to the `users` table.
*   **Why:** To store additional user contact and location information, which may be used for future features or user management.
*   **Where:**
    *   Modified the `users` table in the database.
*   **How:**
    *   Executed an `ALTER TABLE` SQL command to add the `phone_number` (VARCHAR(20), NULLABLE), `district` (VARCHAR(100), NULLABLE), and `upazila` (VARCHAR(100), NULLABLE) columns to the `users` table.


*   **What:** Fixed a critical "Headers already sent" error that occurred when submitting the profile update or password change forms, resulting in a blank page.
*   **Why:** The `views/dashboard/profile.php` file was including the HTML header (`header.php`) before processing the POST request. Since the controller logic performs a `redirect()`, which modifies HTTP headers, it failed because output had already started.
*   **Where:**
    *   Rewrote `views/dashboard/profile.php`.
*   **How:**
    *   Restructured the file to move all PHP logic for handling POST requests to the very top, before any HTML output is generated.
    *   The `require_once '../layouts/header.php';` statement is now called *after* the entire `if ($_SERVER['REQUEST_METHOD'] === 'POST')` block has executed.
    *   This ensures that the `redirect()` function in the controller can execute successfully without conflicts, and the user is now correctly redirected back to the profile page with the appropriate success or error message.

### Feature: User Profile Management
*   **What:** Added a new "Profile" page to the user dashboard, allowing users to update their name, email, and password.
*   **Why:** To give users the ability to manage their own account details directly, improving usability and self-service capabilities.
*   **Where:**
    *   Modified `views/layouts/header.php`
    *   Modified `models/User.php`
    *   Rewrote `controllers/userController.php`
    *   Rewrote `views/dashboard/profile.php`
*   **How:**
    1.  **Navigation Link:**
        *   Added a "Profile" link to both the desktop and mobile navigation menus in `views/layouts/header.php`, pointing to the new profile page.
    2.  **Backend Methods:**
        *   Added two new methods to the `User` model (`models/User.php`):
            *   `updateProfile($id, $name, $email)`: This method updates the user's name and email. It includes a crucial validation step to check if the new email is already in use by another user before performing the update, preventing duplicate email addresses.
            *   `updatePassword($id, $hashedPassword)`: This method securely updates only the user's password.
        *   These methods are distinct from the existing `update()` method, which is reserved for the admin panel, ensuring that user-facing profile changes follow a different and more constrained logic.
    3.  **Controller Logic:**
        *   Rewrote `controllers/userController.php` to include a `UserController` class with two static public methods:
            *   `handleProfileUpdate($userId, $name, $email)`: This method orchestrates the profile update process, including calling the `User` model's `updateProfile` method, handling email uniqueness validation, setting appropriate session messages, and redirecting the user. It also updates the `$_SESSION['user_name']` upon successful name change.
            *   `handleChangePassword($userId, $currentPassword, $newPassword, $confirmPassword)`: This method handles password change requests, including validating new password confirmation, verifying the current password against the stored hash, hashing the new password, calling the `User` model's `updatePassword` method, setting session messages, and redirecting the user.
    4.  **Frontend UI:**
        *   Rewrote `views/dashboard/profile.php` to create a complete user profile management page.
        *   The page now includes two distinct forms within styled cards: one for updating profile details (name and email) and another for changing the password.
        *   It handles POST requests for both actions, calling the appropriate `UserController` methods.
        *   It fetches the current user's data to pre-fill the profile form and uses the `display_message()` function to show success or error alerts to the user.

## Friday, November 14, 2025

### Improvement: Modernized Admin User Fraud Reports UI
*   **What:** Completely redesigned the "All User Fraud Reports" page in the admin panel to include advanced filtering, pagination, and a more modern UI.
*   **Why:** To improve the administrator's user experience by making it easier and more efficient to manage a large volume of user-submitted fraud reports. The previous UI was a simple, non-paginated table that would become slow and difficult to use as data grew.
*   **Where:**
    *   Modified `admin/user_fraud_reports.php`
    *   Modified `get_all_user_reports.php`
*   **How:**
    1.  **UI Overhaul (`admin/user_fraud_reports.php`):**
        *   Added a filter bar at the top with a search input (for phone, name, or complaint) and a "rows per page" selector.
        *   Updated the table styling to match the modern look of other admin pages, including a serial number (`SL`) column for easier reference.
        *   Implemented a full-featured, responsive pagination component at the bottom of the table.
        *   The entire process is now handled by a single, comprehensive JavaScript implementation that manages state (current page, search term), fetches data dynamically, and renders the table and pagination controls.
    2.  **Backend Enhancement (`get_all_user_reports.php`):**
        *   Modified the endpoint to accept `page`, `rows_per_page`, and `search` GET parameters.
        *   The script now fetches all reports, performs filtering on the server-side in PHP based on the search term, and then uses `array_slice` to return only the data for the requested page.
        *   The JSON response was enhanced to include a `pagination` object containing `total_rows`, `total_pages`, and `current_page`, giving the frontend all the information needed to render the controls.
    3.  **Improved Functionality:**
        *   Admins can now instantly search across all reports without waiting for the entire dataset to load on the client.
        *   Pagination allows the system to handle thousands of reports efficiently.
        *   The "Edit" and "Delete" actions now correctly refresh the current paginated and filtered view, preserving the admin's context.

## Thursday, November 13, 2025

### Feature: Admin Fraud Report Management
*   **What:** Implemented a new page in the admin panel for administrators to view, edit, and delete all fraud reports submitted by all users.
*   **Why:** To provide administrators with complete oversight and moderation capabilities over user-generated content, ensuring data quality and allowing them to manage reports centrally.
*   **Where:**
    *   `models/User.php`
    *   `models/CourierStats.php`
    *   `edit_my_report.php`
    *   `delete_my_report.php`
    *   Created `get_all_user_reports.php`
    *   Created `admin/user_fraud_reports.php`
    *   Modified `admin/fraud_checker.php`
*   **How:**
    1.  **Backend Enhancements:**
        *   Added a `getAllUsersAsMap()` method to the `User` model to create an efficient mapping of user IDs to user names.
        *   Added a `getAllUserReports()` method to the `CourierStats` model. This method fetches all reports from the database and uses the user map to enrich each report with the reporter's name.
        *   Created a new `get_all_user_reports.php` endpoint, restricted to administrators, to serve this comprehensive list of reports.
        *   Modified the `updateUserReport()` and `deleteUserReport()` methods in the `CourierStats` model to accept an `$isAdmin` flag, which bypasses the standard user ownership check and allows admins to modify any report.
    2.  **Frontend Implementation:**
        *   Created a new admin page at `admin/user_fraud_reports.php` that fetches and displays all user reports in a table, including the reporter's name and ID.
        *   The page reuses the existing edit/delete modals and JavaScript logic, providing a consistent management experience.
        *   Added a "User Fraud Reports" button to the `admin/fraud_checker.php` page for easy navigation to the new management interface.

### Bug Fix: Incorrect Administrator Access Check
*   **What:** Fixed a bug that caused an "Administrator access required" error on the new `admin/user_fraud_reports.php` page.
*   **Why:** The code was checking for an incorrect session variable (`$_SESSION['role'] === 'admin'`) to verify administrator privileges. The project's convention is to use `$_SESSION['is_admin'] === true`.
*   **Where:**
    *   `get_all_user_reports.php`
    *   `edit_my_report.php`
    *   `delete_my_report.php`
*   **How:**
    *   Replaced the incorrect condition `isset($_SESSION['role']) && $_SESSION['role'] === 'admin'` with the correct one: `isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true`. This was applied to all new and modified endpoints to ensure the application correctly identifies logged-in administrators.

### Feature: Implement 'My Fraud Report' list with edit/delete functionality and fix report ID issue
*   **What:** Implemented the 'My Fraud Report' feature, allowing users to view, edit, and delete their submitted fraud reports.
*   **Why:** To provide users with full control over the fraud reports they submit, including the ability to correct or remove them, while also ensuring data integrity and security.
*   **Where:**
    *   Modified `models/CourierStats.php`
    *   Modified `views/dashboard/fraud_checker.php`
    *   Created `delete_my_report.php`
    *   Created `edit_my_report.php`
    *   Created `get_my_reports.php`
    *   Created `views/dashboard/my_fraud_reports.php`
*   **How:**
    1.  **Database:**
        *   Created a new `trash_fraud_report` table to store deleted reports, preserving historical data.
        *   Ran a one-time data migration script to add unique `report_id`s to existing reports in the `courier_stats` table that were missing them, resolving issues with editing/deleting older reports.
    2.  **Backend:**
        *   Updated the `CourierStats.php` model with new methods (`getReportsByUserId`, `updateUserReport`, `deleteUserReport`) to handle fetching, editing, and deleting user-specific reports.
        *   Created new endpoints (`get_my_reports.php`, `edit_my_report.php`, `delete_my_report.php`) to securely manage these operations.
        *   Implemented user ownership verification in the backend for all report modifications, ensuring users can only interact with their own reports.
    3.  **Frontend:**
        *   Added a "My Fraud Report List" button to the `fraud_checker.php` page, linking to the new reports page.
        *   Created a dedicated `my_fraud_reports.php` page to display a paginated table of the user's submitted reports.
        *   Implemented "Edit" and "Delete" buttons for each report, which trigger modals for editing and confirmation for deletion, respectively.
        *   Enhanced client-side JavaScript to fetch, display, and manage the lifecycle of these reports, including form submissions and table refreshes.
    4.  **Bug Fix:**
        *   Resolved the "Failed to update report." and "Failed to delete report." errors by ensuring all reports have a unique `report_id` and implementing proper ownership checks, which was identified through a debugging process.

### Feature: User Fraud Reporting
*   **What:** Implemented a comprehensive fraud reporting system within the user-facing Fraud Checker page.
*   **Why:** To empower users to contribute to the fraud detection system by reporting suspicious phone numbers directly. This creates a community-driven layer of data on top of the external API, making the tool more effective and informative for everyone.
*   **Where:**
    *   Modified `models/CourierStats.php`
    *   Created `report_fraud.php`
    *   Modified `fraud_checker.php`
    *   Modified `views/dashboard/fraud_checker.php`
*   **How:**
    1.  **Database Enhancement:**
        *   Added a `user_reports` column (`TEXT` type) to the `courier_stats` table to store user-submitted reports in JSON format. This was done via a temporary migration script.
    2.  **Backend Logic:**
        *   Created a new `report_fraud.php` endpoint to securely handle AJAX requests for submitting fraud reports. This script validates user authentication, checks input data (phone number, customer name, complaint), and saves the report along with the reporting user's ID and a timestamp.
        *   Updated the `CourierStats` model by adding an `addFraudReport()` method, which fetches the existing JSON reports, appends the new one, and updates the database.
        *   Modified the main `fraud_checker.php` endpoint to integrate this new data. When serving cached results, it now reads the `user_reports` JSON, adds the count of these reports to the `total_fraud_reports` metric, and includes the full report details in the API response for the frontend to use.
    3.  **Frontend User Experience:**
        *   In `views/dashboard/fraud_checker.php`, a "Report Fraud" button now appears alongside the search results, allowing for immediate action.
        *   Clicking the button opens a "Report Fraud" modal with a form to input the customer's name and a complaint (up to 250 characters).
        *   If a searched number has existing user reports, a "View Report Reason" link is now displayed.
        *   Clicking this link opens a second modal that lists all submitted reports for that number, showing the (anonymized) reporting user's ID, the customer name, the complaint, and the date of the report.
        *   The entire process is handled via JavaScript, from showing/hiding modals to submitting the report asynchronously and dynamically updating the UI based on the API response.

### Improvement: Redesigned Fraud Checker UI
*   **What:** Redesigned the Fraud Checker page to match the user-provided design.
*   **Why:** To align the UI with the user's vision, which includes a simplified left sidebar and icon-less summary cards.
*   **Where:**
    *   Modified `views/dashboard/fraud_checker.php`.
*   **Changes:**
    *   Removed the circular progress bar from the left sidebar and replaced it with a large text display for the delivery success ratio.
    *   Removed the icons from all summary cards in the main content area.
    *   Adjusted the styling of the summary cards to be text-aligned to the center.
    *   Updated the JavaScript to remove the logic for the circular progress bar.

### Improvement: Fraud Checker UI Modernization
*   **What:** Modernized the user interface of the Fraud Checker page by removing the courier-specific table and adding a "Total Fraud Reports" summary card.
*   **Why:** To streamline the display of fraud-related information, focusing on overall statistics rather than individual courier data, and to align with the current operational focus on a single courier.
*   **Where:**
    *   Modified `views/dashboard/fraud_checker.php`.
*   **Changes:**
    *   Removed the detailed table displaying statistics for individual couriers (Pathao, Steadfast, PaperFly, Redx).
    *   Added a new summary card for "Total Fraud Reports" to the main statistics section.
    *   Updated the grid layout to accommodate the new card.
    *   Updated the JavaScript to populate the "Total Fraud Reports" card with data from the API response.

### Improvement: Complete Redesign of User Fraud Checker UI
*   **What:** Completely redesigned the user interface of the fraud checker page to match a new, modern design.
*   **Why:** To provide a more visually appealing, intuitive, and data-rich user experience, inspired by a user-provided screenshot.
*   **Where:**
    *   Modified `views/dashboard/fraud_checker.php`.
    *   Modified `views/layouts/header.php` to include the Font Awesome CDN.
*   **Changes:**
    *   Implemented a two-column layout with a left sidebar for the delivery success ratio and a main content area on the right.
    *   Added a circular progress bar (using SVG) to visualize the delivery success rate.
    *   The progress bar dynamically changes color based on the success rate (green for excellent, yellow for good, red for poor).
    *   Incorporated Bengali text and a new color scheme to match the provided design.
    *   Added summary cards with Font Awesome icons for "Total Orders," "Total Delivered," "Total Cancelled," and "Delivery Rate."
    *   Included a static table with per-courier data as a placeholder, since the current API does not provide this level of detail.
    *   Updated the JavaScript to populate all the new UI elements with data from the API response.

## Wednesday, November 12, 2025

### Improvement: Redesigned Admin Fraud Checker UI
*   **What:** Redesigned the user interface of the admin fraud checker page.
*   **Why:** To provide a more modern, visually appealing, and user-friendly experience.
*   **Where:**
    *   Modified `admin/fraud_checker.php`.
*   **Changes:**
    *   Updated the layout of the form and table to be more organized and spacious.
    *   Improved the design of form elements, including adding an icon to the search bar.
    *   Enhanced the table's appearance with a cleaner header, hover effects on rows, and better typography.
    *   Added a "no data" message with an icon for empty table states.
    *   Implemented a more advanced and responsive pagination component.

### Bug Fix: Admin Fraud Checker Pagination
*   **What:** Fixed a bug that was causing a fatal error on the admin fraud checker page due to incorrect SQL syntax for pagination.
*   **Why:** The `LIMIT` and `OFFSET` values in the SQL query were being quoted as strings, which is not valid syntax. This was happening because PDO's `execute()` method treats all parameters in the array as strings.
*   **Where:**
    *   Modified `models/CourierStats.php` in the `getCourierStatsPaginated` function.
*   **Changes:**
    *   The `getCourierStatsPaginated` function now uses `bindValue()` with `PDO::PARAM_INT` to explicitly bind the `LIMIT` and `OFFSET` values as integers.
    *   Removed debugging `var_dump` statements from `admin/fraud_checker.php`.

### Feature: Admin Cached Courier History
*   **What:** Added a feature to the admin fraud checker page to display all cached courier history with pagination and search functionality.
*   **Why:** To provide administrators with a way to view and manage all cached courier data.
*   **Where:**
    *   Modified `admin/fraud_checker.php` to display the cached data in a paginated and searchable table.
    *   Modified `models/CourierStats.php` to add methods for fetching paginated and searchable data from the `courier_stats` table.
*   **Changes:**
    *   The admin fraud checker page now displays a paginated and searchable table of all cached courier history.

### Feature: Admin Fraud Checker
*   **What:** Added the Fraud Checker feature to the admin panel.
*   **Why:** To allow administrators to use the Fraud Checker feature.
*   **Where:**
    *   Created `admin/fraud_checker.php` to provide the user interface for the fraud checker in the admin panel.
    *   Modified `views/layouts/admin_header.php` to add a "Fraud Checker" link to the admin dashboard menu.
*   **Changes:**
    *   The Fraud Checker feature is now available in the admin panel.

### Housekeeping: Removed Unnecessary Files
*   **What:** Removed the `api_tokens.sql`, `websites.sql`, `debug.log`, `auth_tokens.sql`, `composer.json`, and `package.json` files.
*   **Why:** The files were no longer needed for the project.
*   **Where:**
    *   Removed `api_tokens.sql`, `websites.sql`, `debug.log`, `auth_tokens.sql`, `composer.json`, and `package.json` from the root directory.
*   **Changes:**
    *   The project directory is now cleaner.

### Improvement: Fraud Checker
*   **What:** Refactored the Fraud Checker feature to improve its reliability, security, and user experience.
*   **Why:** The previous implementation had several issues, including incorrect phone number validation, improper API response handling, and security vulnerabilities.
*   **Where:**
    *   Modified `fraud_checker.php` to add robust data validation, error handling, and secure logging.
    *   Modified `models/CourierStats.php` to use an `upsert` method for more efficient database operations.
    *   Modified `core/config.php` to remove unnecessary API keys.
    *   Modified `views/dashboard/fraud_checker.php` to add client-side validation and improve error display.
    *   Removed the `courier_stats.sql` file as it is no longer needed.
*   **Changes:**
    *   The Fraud Checker now validates phone numbers against the `01xxxxxxxxx` format on both the client-side and server-side.
    *   The feature now correctly handles the API response, including the `total_fraud_reports` field.
    *   Fixed a bug that was causing an "Incorrect integer value" error when saving data to the database.
    *   Fixed a bug that was causing an "Unknown error from API" error due to incorrect API response handling.
    *   Unnecessary API keys have been removed, and the API integration is more robust.
    *   The database now correctly stores and updates courier statistics.

### Feature: Fraud Checker
*   **What:** Implemented a "Fraud Checker" feature that allows users to check the delivery history of a phone number.
*   **Why:** To help users identify potentially fraudulent customers by providing them with a summary of their order history with a specific courier.
*   **Where:**
    *   Created `courier_stats.sql` to define the `courier_stats` table schema.
    *   Created `models/CourierStats.php` to manage courier stats data.
    *   Created `fraud_checker.php` in the root directory to handle the AJAX requests.
    *   Created `views/dashboard/fraud_checker.php` to provide the user interface for the fraud checker.
    *   Modified `views/layouts/header.php` to add a "Fraud Checker" link to the user dashboard menu.
*   **Changes:**
    *   Users can now search for a phone number on the "Fraud Checker" page to view its delivery history.
    *   The system fetches data from the SteadFast API and stores it in a local database to reduce API calls and improve performance.
    *   The results show the total parcels, deliveries, cancellations, and fraud reports for the given phone number.

### Feature Removal: Admin Courier History
*   **What:** Removed the "Courier History" feature from the admin panel.
*   **Why:** The feature was no longer needed.
*   **Where:**
    *   Deleted `admin/courier_history.php`.
    *   Deleted `models/CourierHistory.php`.
    *   Deleted `controllers/courierController.php`.
    *   Dropped the `courier_history` table from the database.
    *   Removed the "Courier History" link from `views/layouts/admin_header.php`.
*   **Changes:**
    *   The "Courier History" feature has been completely removed from the admin panel.

### Bug Fix: Admin Courier History Page
*   **What:** Fixed a bug that was causing the admin courier history page to return a blank page.
*   **Why:** The issue was caused by the `CourierHistory` model not being instantiated with a database connection.
*   **Where:**
    *   Modified `admin/courier_history.php` to instantiate the `CourierHistory` model with a database connection.
    *   Modified `models/CourierHistory.php` to accept a database connection in the constructor and to use it in the methods.
*   **Changes:**
    *   The admin courier history page now displays correctly.

### Feature: Admin Courier History Page
*   **What:** Created a new page in the admin dashboard to display the courier history.
*   **Why:** To allow administrators to view and search all courier history records in the database.
*   **Where:**
    *   Created `admin/courier_history.php` to display the courier history.
    *   Modified `models/CourierHistory.php` to add `getCourierHistoryPaginated()` and `getTotalCourierHistoryCount()` methods.
    *   Modified `views/layouts/admin_header.php` to add a "Courier History" link to the admin menu.
*   **Changes:**
    *   Admins can now view a paginated and searchable table of all courier history records.
    *   The table displays the phone number, total orders, total delivered, total cancelled, success rate, and last updated date.
    *   The page includes options to change the number of rows displayed per page (50, 100, 500, 1000).

### Improvement: Clickable Messages in Admin SMS History
*   **What:** Modified the admin SMS history page to make the truncated message text clickable for viewing the full message.
*   **Why:** To provide a more intuitive and seamless user experience, removing the need for a separate "View Full" button.
*   **Where:**
    *   Modified `admin/sms_history.php`.
*   **Changes:**
    *   The truncated SMS message in both the desktop and mobile views is now a clickable element that opens the full message modal.
    *   Removed the redundant "View Full" button.

### Feature: Re-implemented Admin SMS History
*   **What:** Re-implemented the admin SMS history page, which was lost after a git reset.
*   **Why:** To restore the functionality for administrators to monitor the SMS usage of all users.
*   **Where:**
    *   Created `admin/sms_history.php` to display the SMS history for all users.
    *   Modified `models/SmsHistory.php` to add `getSmsHistoryPaginated()` and `getTotalSmsHistoryCount()` methods for fetching all SMS records with pagination and filtering.
    *   Modified `views/layouts/admin_header.php` to add a link to the new page in the admin menu.
*   **Changes:**
    *   Admins can now view a paginated and filterable table of all SMS messages sent by users.
    *   The table is responsive and includes filtering by user, search term, and date range.
    *   A modal, powered by Alpine.js, allows for viewing the full text of long messages.
    *   The page design is consistent with the rest of the admin panel, including hover effects and a mobile-friendly layout.

### Feature: Remember Me
*   **What:** Implemented a "Remember Me" functionality to keep users logged in across browser sessions.
*   **Why:** To prevent users from having to log in repeatedly after closing their browser.
*   **Where:**
    *   Created `auth_tokens.sql` to define the `auth_tokens` table schema.
    *   Created `models/AuthToken.php` to manage authentication tokens.
    *   Modified `admin/login.php` and `views/auth/login.php` to add a "Remember Me" checkbox.
    *   Modified `controllers/authController.php` to handle the "Remember Me" functionality during login.
    *   Created `core/remember_me_guard.php` to automatically log in users based on the "Remember Me" cookie.
    *   Modified `core/config.php` to include the `remember_me_guard.php`.
    *   Modified `logout.php` and `admin/logout.php` to delete the token and cookie upon logout.
*   **Changes:**
    *   Users can now choose to stay logged in by checking the "Remember Me" box during login.
    *   A long-lived cookie is used to store a secure token for automatic authentication.
    *   The token is invalidated upon logout.

## Tuesday, November 11, 2025

### Feature: Modernized User SMS History Table
*   **What:** Improved the design of the user SMS history page.
*   **Why:** To give the page a more modern look and feel, consistent with the admin SMS history page.
*   **Where:**
    *   Modified `views/dashboard/sms_history.php`.
*   **Changes:**
    *   Added a hover effect to the table rows.
    *   Added a hover effect and a border to the mobile view cards.
    *   Removed the default table dividers and added a border to the table rows.
    *   Added a subtle box shadow to the table.

### Feature: Modernized Admin SMS History Table
*   **What:** Improved the design of the admin SMS history page.
*   **Why:** To give the page a more modern look and feel.
*   **Where:**
    *   Modified `admin/sms_history.php`.
*   **Changes:**
    *   Added a hover effect to the table rows.
    *   Added a hover effect and a border to the mobile view cards.
    *   Removed the default table dividers and added a border to the table rows.
    *   Added a subtle box shadow to the table.

### Bug Fix: Admin SMS History Target Phone
*   **What:** Fixed an issue where the target phone number was not showing in the admin panel's SMS history.
*   **Why:** The code was using the wrong array key (`target_phone`) to access the target phone number. The correct key is `to_number`.
*   **Where:**
    *   Modified `admin/sms_history.php`.
*   **Changes:**
    *   Replaced `target_phone` with `to_number` in both the desktop and mobile views.
    *   The target phone number is now displayed correctly.

### Bug Fix: Admin SMS History Modal
*   **What:** Fixed an issue where the JavaScript popup to view the complete message in the admin panel's SMS history was not working correctly.
*   **Why:** The modal was not implemented using Alpine.js, which is the standard in the project.
*   **Where:**
    *   Modified `admin/sms_history.php`.
*   **Changes:**
    *   Replaced the simple JavaScript modal with the Alpine.js modal from the user panel.
    *   The modal now works as expected.

### Bug Fix: User SMS History Modal
*   **What:** Fixed an issue where the JavaScript popup to view the complete message in the user panel's SMS history was not working.
*   **Why:** The issue was caused by special characters in the message breaking the JavaScript when the message was directly embedded in the `x-on:click` handler.
*   **Where:**
    *   Modified `views/dashboard/sms_history.php`.
*   **Changes:**
    *   Updated the `x-on:click` handler to retrieve the full message from a `data-message` attribute instead of directly embedding it in the JavaScript.
    *   This change was applied to both the desktop and mobile views.

### Feature: Admin SMS History
*   **What:** Added a new page in the admin panel to display the SMS history of all users.
*   **Why:** To allow administrators to monitor the SMS usage of all users in one place.
*   **Where:**
    *   Created `admin/sms_history.php`.
    *   Modified `views/layouts/admin_header.php` to add a link to the new page in the admin menu.
    *   Modified `models/SmsHistory.php` to add `getSmsHistoryPaginated()` and `getTotalSmsHistoryCount()` methods.
*   **Changes:**
    *   Admins can now view a paginated and filterable table of all SMS messages sent by users.
    *   The table is responsive and includes filtering by user, search term, and date range.
    *   A modal, powered by Alpine.js, allows for viewing the full text of long messages.
    *   The page design is consistent with the rest of the admin panel, including hover effects and a mobile-friendly layout.

### Feature: Mobile Responsive Design
*   **What:** Made the entire project's design mobile responsive.
*   **Why:** To improve the user experience on mobile devices.
*   **Where:**
    *   Modified `views/layouts/header.php` to add a functional mobile menu.
    *   Modified `views/layouts/admin_header.php` to add a functional mobile menu.
    *   Modified `views/dashboard/websites.php` to make the table responsive.
    *   Modified `views/dashboard/sms_history.php` to make the table responsive.
    *   Modified `admin/users.php` to make the table responsive.
    *   Modified `admin/plans.php` to make the table responsive.
*   **Changes:**
    *   Added a functional mobile menu to the main and admin headers.
    *   Made the tables on the `websites.php`, `sms_history.php`, `users.php`, and `plans.php` pages responsive by displaying them as cards on smaller screens.
    *   Checked that the other pages have responsive layouts.

### Feature: Redesigned SMS History Filter
*   **What:** Redesigned the filtering and search box on the SMS history page.
*   **Why:** The previous design was cluttered and not user-friendly.
*   **Where:**
    *   Modified `views/dashboard/sms_history.php`.
*   **Changes:**
    *   Replaced the single-line form with a more organized and visually appealing grid layout.
    *   Made the labels for all form fields visible to improve accessibility.
    *   Added a "Clear" button to allow users to easily reset the filters.

### Fix: Empty SMS History Page

*   **What:** Resolved an issue causing the SMS history page to appear empty, particularly after applying search and filtering criteria.
*   **Why:** The investigation revealed two logical flaws. First, the date range filtering for the end date used a `<=` operator, which inadvertently excluded any records from the specified end date. Second, the `LIMIT` and `OFFSET` values for pagination were being passed as strings instead of integers, causing a syntax error in the SQL query.
*   **Where:**
    *   The primary changes were made in the `models/SmsHistory.php` file, specifically within the `getSmsHistoryByUserPaginated()` and `getTotalSmsHistoryCountByUser()` methods.
*   **Changes:**
    *   Modified the SQL query in both affected methods to use a `<` operator for the end date and adjusted the end date parameter to be the day after the user-selected end date.
    *   Updated the query to use named parameters and `bindValue` with `PDO::PARAM_INT` for the `LIMIT` and `OFFSET` values to ensure they are treated as integers.
    *   The SMS history page now correctly displays all relevant records when filtering by date, search term, or a combination of both.

### Feature: SMS History Pagination

*   **What:** Implemented a comprehensive pagination system for the SMS history page.
*   **Why:** To improve the user experience and performance when viewing a large number of SMS history records.
*   **Where:**
    *   Modified `models/SmsHistory.php` to add `getSmsHistoryByUserPaginated()` and `getTotalSmsHistoryCountByUser()` methods for fetching paginated data.
    *   Modified `views/dashboard/sms_history.php` to include a "Rows per page" selector, a numbering column, and pagination links.
*   **Changes:**
    *   Users can now select the number of rows to display per page (25, 50, 100, 500, 1000).
    *   The SMS history table now includes a numbering column.
    *   Pagination links are available to navigate through the SMS history.

### Bug Fix: SMS History Modal Behavior

*   **What:** Fixed an issue where a blank "Full SMS Message" modal would appear on page load for various user dashboard pages and could not be closed.
*   - **Why:** The Alpine.js library, which controls the modal's behavior, was not being loaded in the user dashboard header. Without Alpine.js, the browser would not process the `x-show` directive intended to hide the modal by default, causing it to be visible. The close button also relied on Alpine.js and was therefore non-functional.
*   **Where:**
    *   Modified `views/layouts/header.php` to include the Alpine.js script.
*   **Changes:**
    *   The SMS history modal now functions correctly, remaining hidden until a user clicks to view a full message.
    *   The close button on the modal is now functional.
    *   The issue of the phantom modal appearing on other pages like `send_sms.php` is resolved.

## Monday, November 10, 2025

### Bug Fix: SMS History Modal

*   **What:** Fixed an issue where the SMS history modal would not close.
*   **Why:** The Alpine.js `x-data` directive was being re-initialized on every click, preventing the modal from closing.
*   **Where:**
    *   Modified `views/dashboard/sms_history.php` to move the `x-data` directive to a parent element, ensuring a single instance of the modal state.
*   **Changes:**
    *   The SMS history modal now opens and closes correctly.

### User SMS History

*   **What:** Implemented an SMS history page for users.
*   **Why:** To allow users to view a log of all the SMS messages they have sent.
*   **Where:**
    *   Created a new `sms_history` table to store SMS message details.
    *   Created a new `models/SmsHistory.php` model to interact with the new table.
    *   Updated `controllers/smsController.php` to log sent messages in the `sms_history` table.
    *   Created a new `views/dashboard/sms_history.php` page to display the user's SMS history.
    *   Added a link to the SMS history page on the `views/dashboard/send_sms.php` page.
*   **Changes:**
    *   Users can now view a history of their sent SMS messages, including the date, recipient, message content (with a popup for the full message), SMS count, and credits deducted.

### Admin Panel Refactor: SMS Credit Management

*   **What:** Refactored the admin panel to create a dedicated section for SMS credit management.
*   **Why:** To improve the organization of the admin panel and provide a centralized location for all SMS credit-related information.
*   **Where:**
    *   Created a new `admin/sms_credit.php` file to house the SMS credit management UI.
    *   Moved the SMS credit information from `admin/index.php` to the new `admin/sms_credit.php` file.
    *   Added a new "SMS" menu with a "SMS Credit" submenu to the admin navigation in `views/layouts/admin_header.php`.
    *   Included Alpine.js in `views/layouts/admin_header.php` to enable the dropdown menu.
*   **Changes:**
    *   The admin dashboard is now cleaner and more focused on general statistics.
    *   All SMS credit management functionality is now located in the "SMS Credit" page under the "SMS" menu.

### Detailed SMS Credit Tracking

*   **What:** Implemented a more detailed SMS credit tracking system.
*   **Why:** To provide a clear overview of the SMS credit lifecycle, including credits assigned, spent, and remaining.
*   **Where:**
    *   Created a new `sms_credit_history` table to log all credit and debit transactions.
    *   Created a new `models/SmsCreditHistory.php` model to interact with the new table.
    *   Updated `controllers/adminController.php` to log credit assignments in the `sms_credit_history` table.
    *   Updated `controllers/smsController.php` to log credit deductions in the `sms_credit_history` table.
    *   Updated `admin/index.php` to display the total credits given, total credits spent, and total credits remaining.
*   **Changes:**
    *   The admin dashboard now provides a comprehensive view of SMS credit distribution and usage.

### Internal SMS Balance Management

*   **What:** Implemented an internal SMS balance management system for the admin panel.
*   **Why:** Since the SMS gateway API does not provide an endpoint to fetch the live account balance, this system allows the admin to manage the SMS credits internally.
*   **Where:**
    *   Created a new `settings` table in the database to store the master SMS balance.
    *   Created a new `models/Settings.php` model to interact with the `settings` table.
    *   Added a `getTotalAssignedCredits()` method to the `models/User.php` model.
    *   Updated the `admin/index.php` file to display the master balance, total assigned credits, and available unassigned credits.
    *   Added a form to the admin dashboard to set the master balance.
    *   Updated the `controllers/adminController.php` file to handle the form submission for setting the master balance.
    *   Updated the `controllers/smsController.php` file to deduct the sent SMS count from the `master_sms_balance`.
*   **Changes:**
    *   The admin dashboard now displays a summary of the SMS balance, including the master balance, total assigned credits, and available unassigned credits.
    *   The admin can now set the master SMS balance from the admin dashboard.
    *   The master balance is now correctly updated when a user sends an SMS.

### Bug Fix: PDOStatement::bind_param() Error

*   **What:** Corrected the database interaction logic in `models/Settings.php` and `models/User.php`.
*   **Why:** The previous implementation incorrectly used `mysqli` syntax (`bind_param`) with `PDO` objects, leading to a "Call to undefined method PDOStatement::bind_param()" fatal error.
*   **Where:**
    *   Modified `models/Settings.php` to use `PDO`'s `execute()` method with an array of parameters instead of `bind_param()`.
    *   Modified `models/User.php` to revert the constructor to accept a `PDO` object and updated the `getTotalAssignedCredits()` method to use `PDO` syntax.
*   **Changes:**
    *   Ensured compatibility between the database connection object and the methods used for preparing and executing SQL statements in `Settings.php` and `User.php`.

### SMS Credit Calculation

*   **What:** Implemented a real-time character counter and SMS credit calculator on the "Send SMS" page.
*   **Why:** To provide users with immediate feedback on the number of characters they have typed and how many SMS credits will be used, depending on whether the message contains Unicode characters.
*   **Where:**
    *   Added JavaScript code to `views/dashboard/send_sms.php` to count characters and calculate SMS credits.
    *   Updated the `handleSendSmsRequest()` method in `controllers/smsController.php` to deduct the correct number of credits based on the message content.
*   **Changes:**.
    *   The "Send SMS" page now displays a real-time character count and the number of SMS credits that will be used.
    *   The character limit per SMS is 160 for English (GSM 03.38) characters and 70 for Unicode characters.
    *   The `smsController` now correctly calculates and deducts the number of SMS credits based on the message length and content.

### Phone Number Formatting

*   **What:** Implemented a robust phone number formatting function.
*   **Why:** to ensure that all phone numbers are in the correct format (`8801XXXXXXXXX`) before being sent to the SMS gateway, regardless of the user's input format.
*   **Where:**
    *   Created a new function `formatPhoneNumber()` in `core/functions.php`.
    *   Updated the `handleSendSmsRequest()` method in `controllers/smsController.php` to use the new `formatPhoneNumber()` function.
*   **Changes:**
    *   The `formatPhoneNumber()` function handles various phone number formats, including those with or without leading zeros, country codes, and special characters.
    *   The `smsController` now uses this function to sanitize and format the phone number before sending the SMS.

### Send SMS Feature

*   **What:** Moved the "Send SMS" functionality from the dashboard to a new, dedicated page.
*   **Why:** To improve the user experience by separating the "Send SMS" functionality from the main dashboard, making the interface cleaner and more.
*   **Where:**
    *   Created a new file `views/dashboard/send_sms.php` to house the HTML form for sending SMS messages.
    *   Created a new file `send_sms.php` in the project root to handle the form submission and call the `smsController`.
    *   Modified `views/layouts/header.php` to add a "Send SMS" link to the navigation bar for both desktop and mobile views.
    *   Modified `controllers/smsController.php` to redirect back to the new `send_sms.php` page after sending a message and to improve the phone number formatting logic.
    *   Modified `core/functions.php` to add a helper function `is_active()` to highlight the current page in the navigation.
*   **Changes:**
    *   Extracted the SMS form from `views/dashboard/index.php` into `views/dashboard/send_sms.php`.
    *   Created a new controller-like file `send_sms.php` in the project root to handle the form submission.
    *   Updated the `smsController` to redirect to the new SMS page and to handle phone number formatting more robustly.
    *   Added a navigation link to the new page in the header.
    *   Added a helper function to improve navigation highlighting.
    *   Debugged and fixed an issue where the `send_sms.php` file was not being executed due to a server configuration issue.
    *   Debugged and fixed an issue with the `ROOT_PATH` constant.
    *   Debugged and fixed an "Undefined array key" warning.
    *   Corrected the phone number formatting to meet the gateway's requirements.
### Feat: Implement Full OTP Verification Flow for User Registration
*   **What:** Implemented a complete, secure OTP (One-Time Password) verification flow for new user registrations, including OTP sending, verification, and resending capabilities with advanced UI features.
*   **Why:** To enhance security by verifying the user's phone number, which prevents spam, ensures data accuracy, and improves the overall user experience during registration.
*   **Where:**
    *   Modified `controllers/authController.php`
    *   Modified `models/User.php`
    *   Created and rewrote `views/auth/verify_otp.php`
*   **How:**
    1.  **Backend Logic (`controllers/authController.php`):**
        *   The `register` action was modified to save user data temporarily to `$_SESSION['temp_registration']` instead of directly to the database.
        *   It now generates a 4-digit OTP, uses `formatPhoneNumber()` to validate the phone number, sends the OTP via `SmsController`, and redirects to the new verification page.
        *   A new `verify_otp` action was added to validate the submitted OTP against the one in the session. It also checks for OTP expiry (5 minutes). If valid, it creates the user, assigns the 'Free' plan, logs the user in, and clears the temporary session data.
        *   A new `resend_otp` action was added to generate a new OTP, update it in the session, and resend it to the user's phone.
    2.  **Database Model (`models/User.php`):**
        *   Fixed a critical bug in the `createUser` method that was causing passwords to be double-hashed, which would have prevented users from logging in. The method was updated to accept a pre-hashed password from the controller.
    3.  **Frontend UI (`views/auth/verify_otp.php`):**
        *   Created a new standalone page for OTP verification to prevent redirection issues caused by included headers.
        *   The page now displays the user's phone number (e.g., "OTP sent to +8801xxxxxxxxx") for better user feedback.
        *   Implemented an `oninput` event listener for **auto-submission** of the form as soon as the user enters the 4th digit of the OTP.
        *   Added a **2-minute countdown timer** that visually updates every second. While the timer is active, the "Resend OTP" button is hidden. Once the countdown finishes, the button becomes visible, allowing the user to request a new OTP.

## Friday, November 22, 2025

### Feature: Add new fields to user registration
*   **What:** Implemented new fields (`phone_number`, `district`, `upazila`) in the user registration process.
*   **Why:** To collect more comprehensive user information during registration, enhancing user profiles and enabling future location-based features.
*   **Where:**
    *   Modified `views/auth/register.php`.
    *   Modified `models/User.php`.
    *   Modified `controllers/authController.php`.
*   **How:**
    *   Added mandatory 'Phone Number' input field and optional 'District' and 'Upazila' dropdowns to `views/auth/register.php`.
    *   Implemented JavaScript in `views/auth/register.php` to dynamically load and filter district and upazila data from `bd-districts.json` and `bd-upazilas.json`.
    *   Updated the `createUser` method in `models/User.php` to accept `phone_number`, `district`, and `upazila` as parameters, and modified the SQL `INSERT` statement accordingly.
    *   Modified the `register` case in `controllers/authController.php` to retrieve these new fields from the `$_POST` request, validate the `phone_number`, and pass all relevant data to the `createUser` method.