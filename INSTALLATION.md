# ONS SOUND — Local Installation & Configuration Guide

This guide covers installing and running **ONS SOUND** on a local development environment such as **XAMPP**, **WampServer**, **MAMP**, or standard Apache/PHP/MySQL stacks.

---

## 💻 System Prerequisites

| Requirement | Supported Version | Notes |
|-------------|-------------------|-------|
| **PHP** | 8.0, 8.1, 8.2, or 8.3 | Required |
| **Web Server** | Apache 2.4+ / Nginx | `mod_rewrite` enabled for clean URLs |
| **Database** | MySQL 5.7+ / 8.0+ or MariaDB 10.4+ | InnoDB engine support |
| **PHP Extensions** | `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo` | Standard with XAMPP/WAMP |

---

## 🛠️ Step-by-Step Installation

### Step 1: Place Files in Webroot
Copy or clone the project directory into your web server's document root:
- **XAMPP (Windows)**: `C:\xampp\htdocs\ONSsounds\`
- **WAMP (Windows)**: `C:\wamp64\www\ONSsounds\`
- **LAMP (Linux)**: `/var/www/html/ONSsounds/`
- **MAMP (macOS)**: `/Applications/MAMP/htdocs/ONSsounds/`

---

### Step 2: Database Creation & Migration
Ensure your MySQL service is running. Then run the automated database installer:

1. Open your terminal in the project directory:
   ```bash
   cd e:\Xammp\htdocs\ONSsounds
   php database/install.php
   ```
2. *Alternative via Web Browser*: Open `http://localhost/ONSsounds/database/install.php`.
3. *Alternative via phpMyAdmin*: Import `database/onssound.sql` directly into a database named `onssound_db`.

---

### Step 3: Verify Environment Settings (`.env`)
The project utilizes a root-level `.env` configuration file loaded automatically by `config/config.php`. Verify the database connection values in `.env`:

```ini
APP_NAME="ONS SOUND"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/ONSsounds
APP_TIMEZONE=Asia/Kolkata

DB_HOST=localhost
DB_PORT=3306
DB_NAME=onssound_db
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=theartspace44@gmail.com
SMTP_PASS=dgqbgjjlmxiilrfu
SMTP_ENCRYPTION=tls
STORE_EMAIL=aryan.n.shah.04@gmail.com
STORE_NAME="ONS SOUND"
```

> **Note on SMTP**: The project uses Gmail SMTP via PHPMailer. If you change email credentials, make sure to generate an **App Password** in your Google Account security settings.

---

### Step 4: Verify Folder Permissions
Ensure the web server has write permissions for uploads and logs:
- `assets/uploads/` (Stores uploaded product gallery images, category thumbnails, and banners)
- `error.log` (Application error logging)

On Linux/macOS:
```bash
chmod -R 775 assets/uploads
chmod 664 error.log
```

---

### Step 5: Access the Website

Open your web browser and navigate to:

- 🛍️ **Storefront**: `http://localhost/ONSsounds/`
- 🎛️ **Admin Panel**: `http://localhost/ONSsounds/admin/`

#### Default Credentials:
- **Admin Email**: `admin@onssound.com`
- **Admin Password**: `Admin@12345`

- **Demo Customer Email**: `aryan.n.shah.04@gmail.com`
- **Demo Customer Password**: `password` (or register a new customer account with OTP verification)

---

## 🔧 Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| **Database connection error** | MySQL is stopped or credentials mismatch | Open XAMPP Control Panel and start MySQL. Check `DB_NAME` and `DB_PORT` in `.env`. |
| **Email OTP not arriving** | SMTP credentials invalid or blocked by Google | Check `SMTP_USER` and `SMTP_PASS` in `.env`. Use an App Password if 2FA is active. |
| **404 on clean URLs** | Apache `mod_rewrite` is disabled | Enable `rewrite_module` in Apache `httpd.conf` and restart Apache. |
| **Uploaded images don't display** | Missing write permissions on `assets/uploads/` | Ensure `assets/uploads/` is writable by the PHP process. |
