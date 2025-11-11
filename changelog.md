# Changelog

## Tuesday, November 11, 2025

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
*   **Why:** The Alpine.js library, which controls the modal's behavior, was not being loaded in the user dashboard header. Without Alpine.js, the browser would not process the `x-show` directive intended to hide the modal by default, causing it to be visible. The close button also relied on Alpine.js and was therefore non-functional.
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
*   **Why:** To ensure that all phone numbers are in the correct format (`8801XXXXXXXXX`) before being sent to the SMS gateway, regardless of the user's input format.
*   **Where:**
    *   Created a new function `formatPhoneNumber()` in `core/functions.php`.
    *   Updated the `handleSendSmsRequest()` method in `controllers/smsController.php` to use the new `formatPhoneNumber()` function.
*   **Changes:**
    *   The `formatPhoneNumber()` function handles various phone number formats, including those with or without leading zeros, country codes, and special characters.
    *   The `smsController` now uses this function to sanitize and format the phone number before sending the SMS.

### Send SMS Feature

*   **What:** Moved the "Send SMS" functionality from the dashboard to a new, dedicated page.
*   **Why:** To improve the user experience by separating the "Send SMS" functionality from the main dashboard, making the interface cleaner and more intuitive.
*   **Where:**
    *   Created a new file `views/dashboard/send_sms.php` to house the HTML form for sending SMS messages.
    *   Created a new file `send_sms.php` in the project root to handle the form submission and call the `smsController`.
    *   Modified `views/layouts/header.php` to add a "Send SMS" link to the navigation bar for both desktop and mobile views.
    *   Modified `controllers/smsController.php` to redirect back to the new `send_sms.php` page after sending a message and to improve the phone number formatting logic.
    *   Modified `core/functions.php` to add a helper function `is_active()` to highlight the current page in the navigation.
*   **Changes:**
    *   Extracted the SMS form from `views/dashboard/index.php` into `views/dashboard/send_sms.php`.
    *   Created a new controller-like file `send_sms.php` in the root to handle the form submission.
    *   Updated the `smsController` to redirect to the new SMS page and to handle phone number formatting more robustly.
    *   Added a navigation link to the new page in the header.
    *   Added a helper function to improve navigation highlighting.
    *   Debugged and fixed an issue where the `send_sms.php` file was not being executed due to a server configuration issue.
    *   Debugged and fixed an issue with the `ROOT_PATH` constant.
    *   Debugged and fixed an "Undefined array key" warning.
    *   Corrected the phone number formatting to meet the gateway's requirements.
