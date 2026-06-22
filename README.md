# 🍳 Tasty Tales — Animated Recipe Blog

A full-stack recipe-sharing platform built with **PHP** and **MySQLi**, featuring user/admin authentication, Google Sign-In, likes & comments, a full admin dashboard, and a modern animated dark-theme UI.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)

---

## ✨ Features

- 🏠 **Public home page** — browse all recipes without logging in
- 🔐 **Authentication** — register, login, forgot/reset password, all with server + client-side validation
- 🔵 **Google Sign-In** — OAuth login alongside traditional email/password
- ❤️ **Likes & 💬 Comments** — unlocked only for logged-in users
- 🍴 **Recipe details** — ingredients, instructions, and tips rendered as clean bullet lists
- 🛠️ **Admin dashboard** — add/edit/delete recipes, manage users (block/unblock/delete), moderate comments
- 🎨 **Modern dark theme** — glassmorphism navbar, glowing accents, animated background blobs, scroll-reveal animations
- 🔒 **Security basics** — CSRF tokens, prepared statements, password hashing, input sanitization

---

## 🖥️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (MySQLi, procedural) |
| Database | MySQL |
| Frontend | HTML5, CSS3 (custom, no framework), vanilla JavaScript |
| Auth | Session-based + Google OAuth 2.0 |

---

## 📂 Project Structure

```
recipe-blog/
├── index.php                  # Public home page
├── blog.php                   # Single recipe page (like/comment)
├── ajax_like.php              # AJAX endpoint for likes
├── register.php / login.php / forgot-password.php / reset-password.php
├── google-login.php / google-callback.php
├── logout.php
├── admin/
│   ├── dashboard.php          # Stats overview
│   ├── blogs.php               # List/delete recipes
│   ├── blog-add.php            # Add new recipe
│   ├── blog-edit.php           # Edit recipe
│   ├── users.php               # Manage users
│   ├── comments.php            # Moderate comments
│   └── includes/sidebar.php
├── includes/
│   ├── config.php              # DB + site config (gitignored — see setup)
│   ├── config.example.php      # Template for config.php
│   ├── functions.php           # Helper functions
│   ├── header.php / footer.php
├── assets/
│   ├── css/style.css           # Dark theme + animations
│   └── js/main.js              # AJAX like, validation, cursor glow
├── uploads/                    # Recipe images (gitignored contents)
└── database.sql                # Full schema
```

---

## 🚀 Setup

### 1. Requirements
- PHP 7.4+ with MySQLi extension
- MySQL/MariaDB
- A local server stack (XAMPP, WAMP, MAMP, or similar)

### 2. Clone the repo
```bash
git clone https://github.com/YOUR_USERNAME/recipe-blog.git
```
Place it inside your server's web root (e.g. `htdocs/`).

### 3. Import the database
Import `database.sql` via phpMyAdmin or:
```bash
mysql -u root -p < database.sql
```

### 4. Configure the app
```bash
cp includes/config.example.php includes/config.php
```
Edit `includes/config.php` with your real database credentials and site URL:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'recipe_blog');
define('SITE_URL', 'http://localhost/recipe-blog');
```

### 5. Create the admin account
Visit `setup-admin.php` once in your browser:
```
http://localhost/recipe-blog/setup-admin.php
```
Default login created: `admin@example.com` / `Admin@123`

**Delete `setup-admin.php` immediately after running it once.**

### 6. (Optional) Enable Google Sign-In
1. Create OAuth credentials at [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Add this as an authorized redirect URI:
   ```
   http://localhost/recipe-blog/google-callback.php
   ```
3. Add the Client ID/Secret to `includes/config.php`

### 7. Make sure `uploads/` is writable
Admin needs to upload recipe images here.

---

## 🔑 How Roles Work

- Anyone can browse recipes on `index.php` / `blog.php` without an account.
- Logging in (same form for everyone) unlocks liking & commenting.
- The `role` column on the `users` table (`user` or `admin`) determines whether login redirects to the home page or the admin dashboard.

---

## ⚠️ Before Going to Production

- [ ] Change the default admin password
- [ ] Delete `setup-admin.php`
- [ ] Wire up real SMTP/PHPMailer for `forgot-password.php` (currently displays the reset link on-screen for local testing)
- [ ] Switch session cookies to `secure` + `httponly`, serve over HTTPS
- [ ] Add rate-limiting to login/register/forgot-password forms

---

## 📄 License

This project is open-sourced for learning purposes. Feel free to fork and adapt it.
