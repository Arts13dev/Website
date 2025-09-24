# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

Project type: PHP application served via XAMPP (Apache + MySQL), with procedural PHP across public pages, an admin panel, and small API endpoints.

- Local root: C:\xampp\htdocs\Website
- Primary entry points: public PHP pages in the repo root (e.g., home.php, products.php) and admin interface under /admin
- Database schema: database/smarttech_beauty.sql

Core commands (Windows PowerShell)

- Launch (via Apache in XAMPP)
  - Ensure Apache and MySQL are running in XAMPP
  - Browse: http://localhost/Website/
  - Admin panel: http://localhost/Website/admin/

- Optional: Serve with PHP built-in server (useful for quick checks)
  Note: This does not replace MySQL; ensure MySQL is running.
  ```powershell path=null start=null
  php -S localhost:8000 -t C:\xampp\htdocs\Website
  # Then open http://localhost:8000/
  ```

- Import database schema
  If mysql is on PATH:
  ```powershell path=null start=null
  mysql -u {{MYSQL_USER}} -p -e "CREATE DATABASE IF NOT EXISTS smarttech_beauty;"
  mysql -u {{MYSQL_USER}} -p smarttech_beauty < C:\xampp\htdocs\Website\database\smarttech_beauty.sql
  ```
  Or use phpMyAdmin: http://localhost/phpmyadmin → Import → database/smarttech_beauty.sql

- PHP syntax check (single file)
  ```powershell path=null start=null
  php -l C:\xampp\htdocs\Website\admin\products.php
  ```

- PHP syntax check (entire project)
  ```powershell path=null start=null
  Get-ChildItem -Recurse -Include *.php C:\xampp\htdocs\Website |
    ForEach-Object { php -l $_.FullName } |
    Where-Object { $_ -notmatch "No syntax errors detected" }
  ```

- Run quick environment verification
  UI: open http://localhost/Website/test_fixes.php
  CLI (optional):
  ```powershell path=null start=null
  php C:\xampp\htdocs\Website\test_fixes.php
  ```

- File permissions (images directory should be writable by the web server)
  Directory: uploads/products/

High-level architecture and flow

- config/
  - config.php: application-wide constants and helpers used across admin and API code. Based on CODE_DEBUG_REPORT.md and ADMIN_TESTING_GUIDE.md, it centralizes constants like:
    - PRODUCTS_IMAGES_DIR (upload/storage location relative to repo)
    - MAX_FILE_SIZE (e.g., 5MB)
    - ALLOWED_IMAGE_TYPES (e.g., jpg/png/gif/webp)
    - CSRF helpers (token generation/validation)
  - database.php: database connection bootstrap (PDO/MySQL). All DB access should include this to get a consistent, configured PDO handle.

- database/
  - smarttech_beauty.sql: canonical schema for products, categories, and related constraints. products.category_id is a foreign key to categories.id. Default categories include Smartphones, Laptops, Beauty Products, Accessories.

- includes/
  - header.php, footer.php: shared layout fragments included by public/admin pages.

- api/
  Thin HTTP endpoints consumed by admin UI and potentially public pages:
  - upload_product.php: server-side validation, CSRF checks, filename generation (uniqid("product_")), file moves to PRODUCTS_IMAGES_DIR, persistent insert/update using category_id, and consistent relative path storage; relies on constants from config.
  - get_categories.php: returns available categories from DB for dropdowns (used in admin products form).
  - get_stats.php: admin dashboard metrics.
  - cart.php, delete_product.php, admin_products.php: small, focused handlers for cart and admin operations.

- admin/
  - index.php: admin landing/login/index.
  - products.php: admin product management UI (add/edit), now aligned to database schema using category_id. It renders a category dropdown populated from categories and posts data with CSRF token. Image handling is standardized around PRODUCTS_IMAGES_DIR and global constants for size/type. Displays product tables joined with categories to show category names.

- Public pages (repo root)
  - home.php, products.php, cart.php, checkout.php, login.php, register.php, orders.php, about.php, contact.php, admin_dashboard.php. These compose the storefront experience. Shared header/footer are included from includes/. Image paths are relative to uploads/products/ and DB-stored paths.

- Assets/storage
  - uploads/products/: image storage for products. Contains placeholder images and a README mapping images to sample products for admin testing.

Operational notes distilled from existing docs

- From CODE_DEBUG_REPORT.md and ADMIN_TESTING_GUIDE.md:
  - The application was standardized to use products.category_id (FK) instead of a string category field. Ensure any additions respect this.
  - Image path handling is unified around PRODUCTS_IMAGES_DIR; do not reintroduce mixed absolute/relative conventions.
  - CSRF protection is in place for admin forms; when adding admin endpoints or forms, include the hidden token and validate server-side.
  - File validation uses shared constants (MAX_FILE_SIZE, ALLOWED_IMAGE_TYPES). Reuse them rather than hardcoding.
  - Test images are available under uploads/products/ and are referenced in uploads/products/README.md for quick admin testing.
  - Test/verification page: test_fixes.php performs a series of checks (DB connection, categories count, schema columns, CSRF setup, directories, etc.). Use it to quickly validate a local setup after environment changes.

How to run a single targeted workflow

- Add a product via admin with a known image
  1) Start Apache/MySQL in XAMPP
  2) Import database if not present (see command above)
  3) Open http://localhost/Website/admin/products.php
  4) Fill out form (choose a category from dropdown), attach an image from uploads/products/, submit

- Call the categories API directly (quick check)
  ```powershell path=null start=null
  Invoke-WebRequest -UseBasicParsing http://localhost/Website/api/get_categories.php | Select-Object -ExpandProperty Content
  ```

Conventions to preserve when editing/adding code

- Always include config/config.php and config/database.php in server-side handlers needing constants/DB.
- Use prepared statements via the configured PDO for DB reads/writes.
- Respect CSRF token generation/validation for any state-changing admin requests.
- Store product image paths consistently relative to uploads/products/ so public/admin UIs render correctly.
