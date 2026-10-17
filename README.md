# Logbook Digital IQC Portal

> **Incoming Quality Control (IQC) Digital Logbook & Inspection Management System**  
> Developed for precision manufacturing, part verification, tolerance checking, and supervisor disposition workflow.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)

---

## 🌟 Key Features

1. **⚡ Quick GRN Scanner**:
   - Barcode / QR scanner integration on the Inspector Dashboard.
   - Instantly finds incoming Good Receipt Notes (GRN) and routes inspectors directly to the synchronized inspection form.
   - Alerts with friendly modal dialogs if an unknown GRN is scanned or if the GRN has already been inspected.

2. **🔒 Locked Specifications & Audit Integrity**:
   - When opened via GRN Quick Scanner or Queue, the **Lot Information & Component Specifications** are securely locked (`readonly` and `disabled` with hidden integrity inputs).
   - Prevents accidental tampering of part drawing specs, GRN numbers, lot numbers, and arrival quantities.

3. **📏 Real-Time Dimension Measurement & Auto-Evaluation**:
   - Dynamic AQL sample measurement table with real-time deviation calculations against nominal drawings.
   - Automatic `PASS` or `OUT OF SPEC` badge evaluation per sample.
   - Instant pass/fail tally bar.

4. **🛡️ Poka-Yoke & Role-Based Access Control**:
   - **Inspector (`INS-001`)**: Can register GRNs, scan incoming shipments, input physical measurements, and submit reports.
   - **Supervisor (`SPV-001`)**: Exclusive authority to review and approve or reject out-of-spec inspection lots with engineering disposition notes.
   - **Administrator (`ADM-001`)**: Full control over Master Parts, Measurement Instruments, User Management, and System Settings.

5. **📊 History, Reports & Audit Trail**:
   - Filterable inspection logbook archive with full measurement data sheets, timestamps, and inspector/supervisor digital sign-offs.

---

## 💻 Tech Stack

- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Styling**: Tailwind CSS + Google Material Symbols
- **Database**: SQLite (default zero-config) or MySQL / PostgreSQL
- **Architecture**: MVC with Blade templating

---

## 🚀 Quick Start Guide

### 1. Prerequisites
Ensure you have the following installed on your system:
- **PHP 8.2** or higher (with `php-sqlite3`, `php-mbstring`, `php-xml`, `php-curl` extensions)
- **Composer** (PHP dependency manager)
- **Git**

---

### 2. Clone the Repository

```bash
git clone https://github.com/SerlinYolanda/logbook_digital_iqc.git
cd logbook_digital_iqc
```

---

### 3. Installation & Setup

Navigate into the Laravel backend directory:

```bash
cd app-backend
```

Install Composer dependencies:

```bash
composer install
```

Set up the environment configuration file:

```bash
# On Windows (Command Prompt / PowerShell):
copy .env.example .env

# On Linux / macOS:
cp .env.example .env
```

Generate the application encryption key:

```bash
php artisan key:generate
```

Prepare the database and seed initial test data (Parts, Instruments, Users, Incoming Materials):

```bash
# Create SQLite file if using SQLite (default):
# Windows PowerShell:
New-Item -ItemType File -Path database\database.sqlite -Force

# Linux / macOS:
touch database/database.sqlite

# Run database migrations and seeders:
php artisan migrate:fresh --seed
```

---

### 4. Running the Development Server

Start the Laravel local server:

```bash
php artisan serve --port=8000
```

The application will be accessible at:  
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

> **Windows One-Click Shortcut:**  
> You can also double-click [`start_laravel.bat`](start_laravel.bat) in the project root to start the server and open the browser automatically.

---

## 🔑 Default Login Credentials

| Role | NIK | Password | Description |
|---|---|---|---|
| **Administrator** | `ADM-001` | `password123` | Full administrative access (Master Parts, Users, Instruments) |
| **Inspector** | `INS-001` | `password123` | Quick Scanner, IQC Testing Form, Logbook History |
| **Supervisor** | `SPV-001` | `password123` | Quality Review, Disposition, Pass/Reject Approval |

---

## 📁 Project Directory Structure

```text
logbook_digital_iqc/
├── app-backend/                    # Complete Laravel application
│   ├── app/
│   │   ├── Http/Controllers/       # IncomingMaterial, Inspection, MasterData, etc.
│   │   ├── Models/                 # Part, Inspection, Defect, Instrument, User
│   │   └── Middleware/             # RoleMiddleware (Poka-Yoke)
│   ├── database/
│   │   ├── migrations/             # Schema definitions
│   │   └── seeders/                # Default parts, instruments & accounts
│   ├── resources/
│   │   └── views/                  # Blade templates (Dashboard, Inspections, etc.)
│   └── routes/
│       └── web.php                 # Application routes & middleware guards
├── .gitignore                      # Git ignore rules
├── deploy_optimize.bat             # Production cache & optimization script
├── README.md                       # Documentation & run instructions
└── start_laravel.bat               # Windows one-click launcher
```

---

## 📄 License

This software is released under the [MIT License](LICENSE).
