# ScholaSys – Graduate Data Management System

ScholaSys is a complete web‑based **Graduate Data Management System** built with PHP (vanilla) and MySQL, running on XAMPP. It helps educational institutions track graduates, manage employment data, conduct tracer surveys, organize alumni events, and more.

The system includes advanced features like user roles, 2FA, email reminders, PDF reports, document uploads, and a public alumni directory.

## 📋 Requirements

* **XAMPP/Laragon:** Apache, MySQL, PHP 7.4 or higher (Tested on PHP 8.5.5 latest version)
* **Composer:** Required for dependency management
* **Browser:** Chrome, Firefox, or Edge

---

## 🚀 Installation

1. **Clone or download** this repository into your XAMPP `htdocs` folder:

    ```bash
    C:\xampp\htdocs\scholasys\
    ```

2. **Start XAMPP** and enable both **Apache** and **MySQL**.

3. **Create a database** named `scholasys` in phpMyAdmin.

4. **Import the database schema:**
    Open phpMyAdmin, select the `scholasys` database, and import the file `scholasys.sql` located in the root folder.

5. **Install PHP dependencies:**
    Open a terminal in the project folder and run:

    ```bash
    composer install
    ```

    *Note: This installs PHPMailer, Dompdf, and TwoFactorAuth. If you prefer manual installation, use:*

    ```bash
    composer require phpmailer/phpmailer dompdf/dompdf robthree/twofactorauth
    ```

6. **Set file permissions:** Ensure the `assets/uploads/` folder is writable.

7. **Update SMTP settings:** Edit `send_survey_reminders.php`, `send_anniversary_reminders.php`, and `pages/newsletter.php` with your email credentials.

8. **Access the system** in your browser:

    ```text
    http://localhost/scholasys/public/
    ```

9. **Log in** with the default administrator account:
    * **Username:** `admin`
    * **Password:** `admin123`

    > **⚠️ Important:** Change the password immediately after your first login.

    **⚠️ Reminder**: You have to update an smtp credential your own gmail account because they are not allowed to public from gitguardian my gmail so I decided to use a default smtp to modified yourself

---

## ✨ Features

### Core Management

* **Graduate Records:** Add, edit, search, filter, and export data to CSV.
* **Employment Tracking:** Manage current job details and complete employment history.
* **Tracer Surveys:** Public forms with unique token links for automated response recording.
* **Reporting:** Employment rates by program, top employers, and PDF downloads.
* **Dashboard:** Real-time statistics, employment trend charts, and industry distribution.

### Alumni Engagement

* **Events Management:** Public registration and attendance tracking.
* **Announcements:** Post news updates visible to all users.
* **Public Directory:** Opt-in directory with advanced search and filtering.

### User & Security

* **Multi-role Access:** Admin (full control) and Staff (management only) roles.
* **Security:** Optional Two-Factor Authentication (2FA) via Google Authenticator.
* **Activity Logs:** Track system changes and user actions.

---

## 📁 File Structure

```text
scholasys/
├── assets/           # CSS, JS, and uploaded files
├── includes/         # Config, header, and footer files
├── pages/            # Protected application pages
├── public/           # Publicly accessible pages (Login, Survey, 2FA)
├── vendor/           # Composer dependencies
├── send_*.php        # Automation/Cron scripts
├── scholasys.sql     # Database schema dump
└── README.md         # Documentation
```

---

## 🔧 Cron Jobs (Windows)

To automate emails, create two scheduled tasks in **Windows Task Scheduler** to run daily:

* **Survey Reminders:**
    `php C:\xampp\htdocs\scholasys\send_survey_reminders.php`
* **Anniversary Reminders:**
    `php C:\xampp\htdocs\scholasys\send_anniversary_reminders.php`

---

## 🛠️ Troubleshooting

| Issue | Solution |
| :--- | :--- |
| **Login Fails** | Ensure the password hash in the DB is correct or use a reset script. |
| **PDF Errors** | Verify `dompdf` is in the `vendor/` folder and `autoload.php` is included. |
| **404 Errors** | Check directory mapping (e.g., `/public/` vs `/pages/`). |
| **Composer Errors** | Ensure PHP 8.2+. Try `composer install --ignore-platform-reqs` for compatibility. |

---

## 📝 License

This project is open‑source and free for educational use. No warranty is provided.

**Enjoy using ScholaSys!** 🎓
