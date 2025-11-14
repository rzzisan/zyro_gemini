# Changelog

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
*   **Why:** To ensure that all phone numbers are in the correct format (`8801XXXXXXXXX`) before being sent to the SMS gateway, regardless of the user's input format.
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
