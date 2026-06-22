# Tasty Tales — Animated Recipe Blog (PHP + MySQLi)

## Setup

1. **Database**: Create the DB by importing `database.sql` (via phpMyAdmin or `mysql -u root -p < database.sql`). This creates the `recipe_blog` database and all tables.

2. **Config**: Open `includes/config.php` and set your DB credentials (`DB_HOST`, `DB_USER`, `DB_PASS`) and `SITE_URL` to match your local server, e.g. `http://localhost/recipe-blog`.

3. **Admin account**: Visit `http://localhost/recipe-blog/setup-admin.php` once in your browser. It creates/resets the admin login to:
   - Email: `admin@example.com`
   - Password: `Admin@123`

   **Delete `setup-admin.php` after running it once** — it's a security risk to leave on a live server.

4. **Google Sign-In** (optional): Get OAuth credentials at https://console.cloud.google.com/apis/credentials, create an "OAuth 2.0 Client ID" (Web application), add `http://localhost/recipe-blog/google-callback.php` as an authorized redirect URI, then paste the client ID/secret into `includes/config.php` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`). Without this, the "Continue with Google" buttons just won't authenticate (everything else works fine).

5. **Uploads folder**: Make sure `uploads/` is writable (`chmod 755 uploads`) so admin can upload recipe images.

## How permissions work

- **Anyone** can browse `index.php` and `blog.php` (view recipes) without logging in.
- **Logging in** (`login.php`) unlocks liking and commenting on `blog.php`. The same login form handles both regular users and the admin — it checks the `role` column on the `users` table and redirects accordingly.
- **Admin** (role = `admin`) lands on `admin/dashboard.php` after login, with sidebar access to: add/edit/delete recipes, manage users (block/unblock/delete), and moderate comments (view/delete).
- **Forgot password** generates a one-hour reset token. Since no mail server is configured, the reset link is displayed directly on screen for local testing — wire up `MAIL_FROM` and a real mailer (e.g. PHPMailer) in `forgot-password.php` for production.

## File map

```
index.php            home page (public)
blog.php             single recipe + like/comment (gated)
ajax_like.php        AJAX endpoint for the like button
register.php / login.php / forgot-password.php / reset-password.php
google-login.php / google-callback.php   OAuth flow
logout.php
admin/               dashboard, blogs, blog-add, blog-edit, users, comments
includes/            config.php, functions.php, header.php, footer.php
assets/css/style.css assets/js/main.js     all animations + AJAX/validation
database.sql          schema
```

## Notes / things to harden before going live

- Switch session cookies to `secure`/`httponly` and use HTTPS.
- Add rate-limiting to login/register/forgot-password to deter brute force.
- Hook `forgot-password.php` up to real SMTP (PHPMailer) instead of displaying the reset link.
- Add pagination to the homepage and admin tables once data grows.
