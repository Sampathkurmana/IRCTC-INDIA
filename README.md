# 🚆 IRCTC — Indian Railway Reservation System

A full-stack Railway Ticket Booking System built with **PHP, MySQL, HTML, CSS & JavaScript** for XAMPP/phpMyAdmin.

---

## ✅ Features

### User Side
- Register & Login with hashed passwords
- Search trains by source, destination & date
- Book tickets with passenger details (up to 6 per booking)
- Auto-generated PNR numbers
- View all bookings with passenger details
- PNR Status check
- Cancel tickets (seats returned to pool)
- Responsive design — works on mobile & desktop

### Admin Panel
- Secure admin login (separate session)
- Dashboard with live stats (trains, users, revenue, bookings)
- Add / Edit / Delete trains
- View & cancel any booking
- Search & filter all users
- Search & filter all bookings

---

## 🛠 Tech Stack

| Layer      | Technology      |
|------------|-----------------|
| Frontend   | HTML5, CSS3, JavaScript (vanilla) |
| Backend    | PHP 7.4+        |
| Database   | MySQL 5.7+ / MariaDB |
| Server     | XAMPP (Apache)  |
| Font       | Google Fonts (Sora + DM Sans) |

---

## 🚀 Installation Steps

### Step 1 — Set Up XAMPP
1. Download & install **XAMPP** from https://www.apachefriends.org
2. Start **Apache** and **MySQL** from XAMPP Control Panel

### Step 2 — Copy Project Files
1. Copy the entire `railway-system` folder into:
   - **Windows:** `C:\xampp\htdocs\`
   - **Mac/Linux:** `/opt/lampp/htdocs/`

### Step 3 — Create the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Create a database named: `railway_system`
4. Click **Import** tab
5. Choose the file: `railway-system/sql/railway.sql`
6. Click **Go** — all tables and sample data will be imported

### Step 4 — Configure Database (if needed)
If your MySQL uses a different username/password, edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // your MySQL username
define('DB_PASS', '');         // your MySQL password (empty by default in XAMPP)
define('DB_NAME', 'railway_system');
```

### Step 5 — Open the Project
Visit: **http://localhost/railway-system/**

---

## 🔐 Default Login Credentials

### Admin Account
| Field    | Value                  |
|----------|------------------------|
| Email    | admin@railway.com      |
| Password | password               |
| URL      | /admin/login.php       |

> To change admin password: Login to phpMyAdmin → `users` table → update the `password` field with a new `password_hash()` value.

### Test User
Register a new account at `/register.php` — or create one directly in phpMyAdmin.

---

## 📁 Project Structure

```
railway-system/
│
├── index.php               ← Homepage with search
├── login.php               ← User login
├── register.php            ← User registration
├── logout.php              ← Session destroy
├── search.php              ← Train search results
├── book_ticket.php         ← Booking form + logic
├── my_bookings.php         ← User booking history
├── pnr_status.php          ← PNR lookup + ticket view
├── cancel_ticket.php       ← Ticket cancellation
│
├── admin/
│   ├── login.php           ← Admin login
│   ├── logout.php          ← Admin logout
│   ├── dashboard.php       ← Admin overview + stats
│   ├── add_train.php       ← Add new train
│   ├── edit_train.php      ← Edit existing train
│   ├── manage_trains.php   ← List + delete trains
│   ├── view_bookings.php   ← All bookings + admin cancel
│   ├── manage_users.php    ← User list
│   ├── admin_header.php    ← Admin layout header
│   └── admin_footer.php    ← Admin layout footer
│
├── config/
│   └── database.php        ← DB connection + helpers
│
├── includes/
│   ├── header.php          ← Site navbar + flash messages
│   ├── footer.php          ← Site footer + JS include
│   └── auth_check.php      ← User auth guard
│
├── css/
│   └── style.css           ← Full design system
│
├── js/
│   └── script.js           ← Client-side interactions
│
└── sql/
    └── railway.sql         ← DB schema + 20 sample trains
```

---

## 🔒 Security Features

- Passwords hashed with `password_hash()` (bcrypt)
- Prepared statements throughout — SQL injection prevention
- `htmlspecialchars()` on all output — XSS prevention
- Session-based auth with separate admin sessions
- Input sanitization on all form data
- CSRF-safe forms (POST for mutations)
- Date validation — prevents past journey bookings

---

## 🚂 Sample Trains Included

20 real Indian routes pre-loaded:
- Howrah Rajdhani (12301/12302)
- Mumbai Rajdhani (12951/12952)
- Bhopal Shatabdi (12001/12002)
- Karnataka Express (12627/12628)
- Duronto Express (12259/12260)
- And 10 more...

---

## ⚡ Quick Fixes

**Site shows blank page?**  
→ Enable error display in `index.php`: add `ini_set('display_errors', 1);` at top

**Database error?**  
→ Check XAMPP MySQL is running. Verify credentials in `config/database.php`

**Admin password not working?**  
→ The default hash in `railway.sql` is for the word `password`. If it fails, run this in phpMyAdmin SQL tab:
```sql
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email='admin@railway.com';
```

**Search returns no results?**  
→ Station names must partially match. Try "Delhi" instead of "New Delhi"

---

## 📖 Database Tables

| Table       | Purpose                              |
|-------------|--------------------------------------|
| `users`     | Registered users + admin accounts   |
| `trains`    | Train schedule & seat availability  |
| `bookings`  | All ticket bookings with PNR        |
| `passengers`| Passenger details per booking       |
