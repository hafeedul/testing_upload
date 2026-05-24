# 🩸 Blood Donation System — Setup Guide

## Files Structure
```
blood_bank/
├── index.php           ← Public homepage (Request + Track only)
├── login.php           ← Admin login (hidden from public)
├── logout.php          ← Admin logout
├── dashboard.php       ← Admin dashboard
├── donor_form.php      ← Add donor (admin only)
├── view_donors.php     ← View/manage donors (admin only)
├── view_requests.php   ← View/edit requests (admin only)
├── request_form.php    ← Public blood request form
├── public_status.php   ← Public track by CNIC
├── css/style.css       ← All styles
├── includes/
│   ├── db.php          ← Database connection + table creation
│   └── auth.php        ← Login session helper
└── uploads/            ← Doctor sheets (auto-created)
```

## Setup Steps

### 1. Requirements
- PHP 7.4+ with MySQLi
- MySQL / MariaDB
- Apache or Nginx (XAMPP / WAMP / LAMP)

### 2. Database
- Open `includes/db.php`
- Set your DB credentials:
  ```php
  define('DB_USER', 'root');
  define('DB_PASS', '');       // your MySQL password
  define('DB_NAME', 'blood_bank');
  ```
- Tables are created **automatically** on first run

### 3. Place Files
- Copy the `blood_bank/` folder to `htdocs/` (XAMPP) or `www/` (WAMP)

### 4. Access
- Public: `http://localhost/blood_bank/`
- Admin: `http://localhost/blood_bank/login.php`
  - Username: `admin`
  - Password: `admin123`
  - ⚠️ Change password after first login!

## Features

### Public (No Login Required)
- ✅ Request blood with full form including doctor sheet upload
- ✅ Track request status using CNIC
- ❌ Admin Login button hidden from homepage

### Admin Only
- ✅ Dashboard with stats
- ✅ Add / manage donors
- ✅ View ALL request details (CNIC, doctor sheet, etc.)
- ✅ Approve / Reject requests with note
- ✅ Filter & search requests

## Security Notes
- Admin login is at `/login.php` — not linked from homepage
- Doctor sheets stored in `/uploads/` folder
- All admin pages protected by session check
- CNIC uniqueness enforced per request
