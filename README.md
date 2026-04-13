# 📝 Feedback Management System

A full-stack **Feedback Management System** built on the **LAMP stack** (Linux, Apache, MySQL/MariaDB, PHP), designed to be deployed on an **Amazon Linux 2023 EC2** instance. It features a clean feedback submission form, a public card-grid view, and an admin panel with delete and status management.

---

## 🚀 Live Features

- ✅ Submit feedback with name, email, star rating, and message
- ✅ Duplicate submission prevention (per email, 60-minute window)
- ✅ Public card-grid view with rating filters and pagination
- ✅ Admin panel — delete entries, update status (New / Reviewed / Archived)
- ✅ Search feedback by name, email, or message
- ✅ Prepared statements — fully protected against SQL injection
- ✅ Server-side validation with user-friendly error messages
- ✅ Responsive UI — works on desktop and mobile

---

## 🗂️ Project Structure

```
feedback/
├── index.php          # Feedback submission form
├── submit.php         # Form processor (POST handler)
├── view.php           # Public card-grid view of all feedback
├── db.php             # Database connection (MySQLi)
├── helpers.php        # Shared utility functions (sanitise, stars, timeAgo, badges)
├── css/
│   └── style.css      # Full responsive stylesheet
└── admin/
    └── index.php      # Admin panel (delete, status update, search)
```

---

## 🛠️ Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| OS         | Amazon Linux 2023                 |
| Web Server | Apache HTTP Server (httpd) 2.4    |
| Language   | PHP 8+                            |
| Database   | MariaDB 10.5                      |
| Frontend   | HTML5, CSS3 (no frameworks)       |
| Fonts      | Google Fonts — DM Serif Display + DM Sans |

---

## ⚙️ Database Schema

```sql
CREATE DATABASE feedbackdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE feedback (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)  NOT NULL,
    email       VARCHAR(200)  NOT NULL,
    message     TEXT          NOT NULL,
    rating      TINYINT       DEFAULT 5,
    status      ENUM('new','reviewed','archived') DEFAULT 'new',
    ip_address  VARCHAR(45)   DEFAULT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 📦 Quick Deploy (Amazon Linux 2023 EC2)

### Option 1 — Full Auto Deploy (installs everything)

```bash
# Upload and run the full deploy script
sudo bash deploy_feedback.sh
```

This installs Apache, PHP, MariaDB, creates the database, and writes all project files automatically.

### Option 2 — Files Only (if LAMP already installed)

```bash
# Create source files in current directory
bash create_files.sh

# Copy to web root
sudo cp -r feedback /var/www/html/
sudo chown -R apache:apache /var/www/html/feedback
sudo systemctl restart httpd
```

---

## 🔧 Manual Installation Steps

### 1. Install Packages

```bash
sudo dnf update -y
sudo dnf install -y httpd php php-mysqlnd php-mbstring php-xml mariadb105-server mariadb105
```

### 2. Start Services

```bash
sudo systemctl enable --now httpd
sudo systemctl enable --now mariadb
```

### 3. Setup Database

```bash
mysql -u root <<SQL
CREATE DATABASE feedbackdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fbuser'@'localhost' IDENTIFIED BY 'FbSecure@2025!';
GRANT ALL PRIVILEGES ON feedbackdb.* TO 'fbuser'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### 4. Deploy Files

```bash
sudo cp -r feedback/ /var/www/html/
sudo chown -R apache:apache /var/www/html/feedback
sudo find /var/www/html/feedback -type d -exec chmod 755 {} \;
sudo find /var/www/html/feedback -type f -exec chmod 644 {} \;
```

### 5. Restart Apache

```bash
sudo systemctl restart httpd
```

---

## 🌐 Access the Application

Once deployed, open a browser and go to:

| Page         | URL                                        |
|--------------|--------------------------------------------|
| Submit Form  | `http://YOUR_EC2_PUBLIC_IP/feedback/`      |
| View All     | `http://YOUR_EC2_PUBLIC_IP/feedback/view.php` |
| Admin Panel  | `http://YOUR_EC2_PUBLIC_IP/feedback/admin/` |

> Replace `YOUR_EC2_PUBLIC_IP` with your actual EC2 public IP address.

---

## 🔐 EC2 Security Group

Make sure your EC2 Security Group allows **inbound** traffic on:

| Port | Protocol | Source    | Purpose          |
|------|----------|-----------|------------------|
| 22   | TCP      | Your IP   | SSH access       |
| 80   | TCP      | 0.0.0.0/0 | HTTP web access  |
| 443  | TCP      | 0.0.0.0/0 | HTTPS (optional) |

> ⚠️ Do **NOT** open port 3306 (MySQL) to the public internet.

---

## 🗃️ Database Configuration

Stored in `feedback/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'fbuser');
define('DB_PASS', 'FbSecure@2025!');
define('DB_NAME', 'feedbackdb');
define('DB_CHARSET', 'utf8mb4');
```

---

## 📸 Pages Overview

### Submit Form (`index.php`)
- Name, email, star rating (1–5), and message fields
- Client + server-side validation
- Success / error / duplicate flash messages

### View All (`view.php`)
- Responsive card grid with avatar initials
- Filter by star rating
- Pagination (9 per page)
- Shows status badge and relative timestamp

### Admin Panel (`admin/index.php`)
- Full table view of all submissions
- Search by name, email, or message
- Change status (New → Reviewed → Archived)
- Delete individual entries with confirmation prompt
- Summary stats bar (total, new, reviewed, archived)

---

## 🔒 Security Notes

| Feature | Implementation |
|---|---|
| SQL Injection | Prepared statements (`mysqli::prepare`) on all queries |
| XSS Prevention | `htmlspecialchars()` on all output via `h()` helper |
| Duplicate Submissions | Email + 60-minute window check before insert |
| Input Validation | Server-side length, format, and type checks |
| db.php permissions | `chmod 640`, owned by `root:apache` |

> ⚠️ **Before going to production:** Add HTTP Basic Auth or session-based login to `admin/index.php` to prevent public access.

---

## 🧪 Test the Setup

```bash
# Test DB connection from CLI
php -r "
\$c = new mysqli('localhost','fbuser','FbSecure@2025!','feedbackdb');
echo \$c->connect_error ? 'FAIL: '.\$c->connect_error : 'OK - connected';
"

# Test form submission via curl
curl -X POST http://localhost/feedback/submit.php \
  -d "name=Test+User&email=test@example.com&message=This+is+a+test+message&rating=5"

# Check Apache error log
sudo tail -50 /var/log/httpd/error_log

# Check SELinux (if DB works from CLI but not browser)
sudo setsebool -P httpd_can_network_connect_db 1
```

---

## 📁 Scripts Included

| Script | Purpose |
|---|---|
| `deploy_feedback.sh` | Full automated LAMP install + project deploy |
| `create_files.sh` | Creates source files only in current directory |

---

## 🔮 Future Improvements

- [ ] Admin login with session-based authentication
- [ ] Email notifications on new feedback (PHP Mailer / SES)
- [ ] Export feedback to CSV
- [ ] HTTPS with Let's Encrypt (certbot)
- [ ] Dashboard with charts (Chart.js)
- [ ] REST API endpoint for feedback submission

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

## 👤 Author

Built with ❤️ on the LAMP stack for Amazon Linux 2023.  
Deployed on AWS EC2 — Apache 2.4 + PHP 8.5 + MariaDB 10.5.
