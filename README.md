# ShopVerse — PHP + MySQL E-commerce Starter

A locally runnable e-commerce website for XAMPP using HTML, CSS, vanilla JavaScript, PHP, and MySQL.

## Included
- Responsive storefront with search, category filters, sorting, product cards
- Product detail pages
- Session-based shopping cart (add, update, remove)
- Registration/login/logout with password hashing
- Checkout that creates an order and order items
- Customer order history
- Admin dashboard to add/edit products and view orders
- Prepared SQL statements, basic CSRF protection, output escaping
- Demo product seed data

## Requirements
- XAMPP with Apache + MySQL/MariaDB
- PHP 8.0+ recommended
- Browser

## Install
1. Copy the project folder into `C:\xampp\htdocs\`. The application detects its folder name automatically.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/shopverse.sql` (creates the `shopverse` database, tables, and demo products).
5. Open `http://localhost/shopverse_ecommerce/` if you kept the current folder name. If you renamed the folder, use that folder name in the URL.

## Admin account
Open `http://localhost/shopverse_ecommerce/admin_setup.php` (replace the folder name if yours differs) and create the single admin account with a strong password. The one-time setup closes automatically after that account is created. Admin sign-in is at `/admin/login.php`.

The admin dashboard can add/edit/remove products, assign products to an existing or new category, view registered users and total order counts, and update order statuses. Removing a product does not erase its saved order history.

## Configuration
Edit `config/config.php` if your MySQL credentials or folder name differs.
Default XAMPP credentials: host `localhost`, user `root`, blank password, database `shopverse`.

## Important production notes
This is a functional local project scaffold, not a production-certified commerce platform. Before public deployment add HTTPS, email verification, password reset, rate limiting, robust audit logging, shipping/tax rules, payment-gateway integration, privacy/legal pages, backups, and server-side validation tailored to your jurisdiction. The checkout currently records **cash-on-delivery / manual** orders; it does not charge cards or collect payment-card data.
