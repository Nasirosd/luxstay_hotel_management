# LuxStay Hotel Management System

PHP 8+ / MySQL 8+ hotel booking and administration project. The admin dashboard follows the supplied reference image: green sidebar, summary cards, revenue chart, activity feed and booking split.

## Requirements
- XAMPP (Apache + MySQL) or equivalent
- PHP 8.0+ with PDO MySQL enabled
- MySQL 8.0+ / MariaDB 10.4+
- Modern browser

## Installation (XAMPP)
1. Extract the `luxstay_hotel_management` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/`.
4. Choose **Import**, select `database/luxstay_hotel_db.sql`, and run it. The SQL creates tables if missing and does not DROP existing data. For a clean test, use an empty `luxstay_hotel_db` database.
5. Confirm `config/database.php` matches your MySQL host, database, username and password. Defaults are `127.0.0.1`, `luxstay_hotel_db`, `root`, blank password.
6. Open `http://localhost/luxstay_hotel_management/`.

## Demo logins
**Admin:** `admin@gmail.com` / `admin123`  
**Customer:** `customer@example.com` / `Customer@2026`

Change the demo passwords after first login. Admin password can be changed in Admin → Settings. Customer password can be changed by replacing the hash in the database (password reset is not implemented).

## Main routes
- Public website: `/index.php`
- Admin login: `/admin/login.php`
- Admin dashboard: `/admin/dashboard.php`
- Customer login/register: `/user/login.php`, `/user/register.php`
- Room browsing/booking: `/booking/rooms.php`
- Customer bookings: `/booking/my_bookings.php`

## Implemented features
- Password-hashed customer and admin authentication, session ID regeneration, CSRF validation on POST forms, escaped output and prepared SQL statements.
- Admin dashboard with live database metrics and recent activity.
- Admin CRUD for hotels, rooms and bookings; guest directory; payment status management; feedback moderation; analytics; account settings.
- Customer registration/login, profile, room browsing, date validation, overlap prevention with transaction/row lock, booking cancellation (pending only), payment request creation and feedback.
- Online/offline booking source and payment status reporting.

## Important limitations
- This is an academic/demo hotel management application, not a hosted production booking platform.
- Payment methods are **manual payment requests only**. No real bKash/Nagad/card/bank transaction is charged or verified. A hotel operator must confirm payments in Admin → Payments.
- Email verification, password reset, live payment gateway, SMS, multi-hotel tenant permissions, image uploads and production deployment hardening are not included.
- Configure HTTPS, secure cookies, backups, least-privilege DB credentials, rate limiting and monitoring before public deployment.
- If using a project folder name other than `luxstay_hotel_management`, update the base paths in `includes/header.php` and `includes/functions.php`.

## Structure
See `PROJECT_STRUCTURE.txt` for the complete directory map.
