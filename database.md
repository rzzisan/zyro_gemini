# Database Schema Documentation

This document outlines the database schema for the application, based on the PHP model files.

---

## `users` table
Stores user account information.
- `id`: (Primary Key) Unique identifier for the user.
- `name`: The full name of the user.
- `email`: The user's email address (used for login).
- `password`: Hashed password for the user.
- `role`: The role of the user (e.g., 'admin', 'user').
- `created_at`: Timestamp of when the user account was created.

---

## `plans` table
Stores information about the different subscription plans available.
- `id`: (Primary Key) Unique identifier for the plan.
- `name`: The name of the plan (e.g., 'Basic', 'Premium').
- `price`: The price of the plan.
- `daily_courier_limit`: The number of courier checks a user can perform daily.
- `sms_credit_bonus`: The number of bonus SMS credits included with the plan.

---

## `subscriptions` table
Links users to their subscription plans.
- `id`: (Primary Key) Unique identifier for the subscription record.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `plan_id`: (Foreign Key) References the `id` in the `plans` table.
- `starts_at`: Timestamp for when the subscription starts.
- `ends_at`: Timestamp for when the subscription ends.
- `created_at`: Timestamp of when the subscription was created.

---

## `sms_credits` table
Stores the SMS credit balance for each user.
- `id`: (Primary Key) Unique identifier for the credit record.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `balance`: The current number of SMS credits the user has.

---

## `sms_credit_history` table
Logs all additions and deductions of SMS credits for auditing purposes.
- `id`: (Primary Key) Unique identifier for the history record.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `type`: The type of transaction ('credit' or 'debit').
- `amount`: The number of credits added or deducted.
- `created_at`: Timestamp of the transaction.

---

## `sms_history` table
Logs all SMS messages sent by users.
- `id`: (Primary Key) Unique identifier for the SMS record.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `to_number`: The recipient's phone number.
- `message`: The content of the SMS message.
- `sms_count`: The number of SMS segments the message was split into.
- `credit_deducted`: The amount of credits deducted for sending the message.
- `created_at`: Timestamp of when the SMS was sent.

---

## `websites` table
Stores websites/domains associated with a user account, used for API key generation.
- `id`: (Primary Key) Unique identifier for the website record.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `domain`: The domain name of the website.

---

## `api_tokens` table
Stores API tokens generated for user websites.
- `id`: (Primary Key) Unique identifier for the API token.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `website_id`: (Foreign Key) References the `id` in the `websites` table.
- `token`: The generated API token string.
- `last_used_at`: Timestamp of when the token was last used.

---

## `auth_tokens` table
Stores temporary authentication tokens for "Remember Me" functionality.
- `id`: (Primary Key) Unique identifier for the auth token.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `token`: The authentication token string.
- `expires_at`: Timestamp for when the token expires.

---

## `usage_logs` table
Logs user activity for features like the fraud checker to enforce daily limits.
- `id`: (Primary Key) Unique identifier for the log entry.
- `user_id`: (Foreign Key) References the `id` in the `users` table.
- `service_type`: The type of service used (e.g., 'FRAUD_CHECK').
- `details`: Specific details of the usage (e.g., the phone number checked).
- `created_at`: Timestamp of the usage event.

---

## `settings` table
A key-value store for application-wide settings.
- `setting_key`: (Primary Key) The unique key for the setting.
- `setting_value`: The value of the setting.

---

## `courier_stats` table
Stores aggregated statistics and user-submitted fraud reports for phone numbers.
- `id`: (Primary Key) Unique identifier for the record.
- `courier_name`: The name of the courier associated with the phone number.
- `phone_number`: The phone number being tracked.
- `total_parcels`: Total number of parcels handled.
- `total_delivered`: Total number of parcels successfully delivered.
- `total_cancelled`: Total number of parcels cancelled.
- `total_fraud_reports`: A count of fraud reports from an external source.
- `user_reports`: (JSON) A JSON array of fraud reports submitted by users of this application. Each report contains `report_id`, `user_id`, `customer_name`, `complaint`, and `reported_at`.
- `last_updated_at`: Timestamp of the last update to this record.

---

## `trash_fraud_report` table
Acts as a backup for deleted fraud reports.
- `id`: (Primary Key) Unique identifier for the trashed report.
- `user_id`: The ID of the user who originally filed the report.
- `phone_number`: The phone number the report was against.
- `customer_name`: The name of the customer in the report.
- `complaint`: The details of the complaint.
- `reported_at`: The original report timestamp.
- `deleted_at`: Timestamp of when the report was moved to the trash.
