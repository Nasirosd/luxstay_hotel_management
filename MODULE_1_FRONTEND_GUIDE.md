# Module 1 — Frontend Design (Completed)

## Pages and files
- `index.php`: homepage, hero banner, date/guest search panel, features, room previews and booking call-to-action.
- `booking/rooms.php`: room listing; reads available rooms from MySQL and falls back to sample preview cards if the database has no rooms yet.
- `gallery.php`: responsive photo gallery.
- `contact.php`: contact details and demo enquiry form.
- `includes/header.php` and `includes/footer.php`: shared responsive navigation and footer.
- `assets/css/style.css`: complete responsive visual theme.
- `assets/js/main.js`: homepage check-in/check-out date validation.

## Run locally
1. Extract to `C:\xampp\htdocs\luxstay_hotel_management`.
2. Start Apache and MySQL in XAMPP.
3. Import `database/luxstay_hotel_db.sql` in phpMyAdmin.
4. Check `config/database.php` database credentials.
5. Visit `http://localhost/luxstay_hotel_management/`.

## Notes
- Bootstrap, Google Fonts and hotel photos load from external services; internet is required for these visual assets.
- The contact form is a frontend demo only; it does not send or save messages.
- Room cards show sample rooms until available room records exist in MySQL.
- The room booking button currently sends visitors to login. Booking submission and user authentication are handled in later modules.
