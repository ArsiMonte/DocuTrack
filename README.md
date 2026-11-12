# DocuTrack: School Registrar Document Request & Monitoring System

A responsive, web-based management system designed for school registrar offices to streamline document applications, eliminate physical queues, and provide real-time status tracking for students and alumni.

---

## 🚀 Key Features

- **Public & Authenticated Status Tracking:** Instant lookup via tracking reference code (e.g. `DOC-2026-XXXX`) with a visual milestone progress bar.
- **Student Portal:** Document catalog browsing, automated fee calculations, purpose specification, and receipt upload.
- **Registrar Dashboard:** Request counter KPIs (*Pending*, *Verification*, *Processing*, *Ready for Pickup*, *Released*).
- **Workflow & Audit Logging:** Status updates are timestamped and logged with staff remarks.
- **Catalog Management:** Add/edit documents, fees, turnaround lead times, and prerequisite requirements.

---

## 💻 Tech Stack

- **Backend:** PHP (PDO with prepared statements)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript, FontAwesome Icons
- **Local Server:** XAMPP (Apache & MySQL)
- **Version Control:** Git

---

## 🛠️ Step-by-Step Installation Guide (XAMPP)

### Step 1: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**. Both should show green status.

### Step 2: Set Up the Database in phpMyAdmin
1. Open your browser and navigate to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click the **Databases** tab or click **New** on the left sidebar.
3. In the "Database name" field, type: `docutrack_db` and click **Create**.
4. With `docutrack_db` selected, click on the **Import** tab at the top.
5. Click **Choose File** and select:
   ```
   C:\xampp\htdocs\DocuTrack\database\docutrack.sql
   ```
6. Click **Import** (or **Go**) at the bottom.
   > The schema will create all tables and populate default demo accounts and common document types.

### Step 3: Access the Application
Open your web browser and visit:
```
http://localhost/DocuTrack/
```

---

## 🔑 Default Demo Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Registrar Admin** | `admin@docutrack.edu` | `admin123` |
| **Student** | `student@docutrack.edu` | `student123` |

*(You can also register a brand new student account directly via the Register link.)*

---

## 📦 Git Setup & Workflow Guide

If you are new to Git, follow these exact steps to track your code and push it to GitHub:

### 1. Initialize Git (Already done for this directory)
```bash
git init
```

### 2. Check the Status
```bash
git status
```

### 3. Stage and Commit Your Files
```bash
git add .
git commit -m "Initial commit: Complete DocuTrack system architecture and submission documentation"
```

### 4. Link to a Remote GitHub Repository
1. Go to [https://github.com](https://github.com) and click **New Repository**.
2. Name your repo (e.g. `DocuTrack`).
3. Leave "Initialize this repository with a README" **unchecked** (we already have one).
4. Copy the repository URL and run in your terminal:
```bash
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/DocuTrack.git
git push -u origin main
```

### 5. Daily Git Commands for Ongoing Work
- After making code changes:
  ```bash
  git add .
  git commit -m "Describe what changes you made"
  git push
  ```

---

## 📂 Project Structure

```
DocuTrack/
├── .gitignore               # Ignored files (uploads, cache, local configs)
├── README.md                # Project documentation and setup guide
├── PROJECT_DOCUMENT.md      # Ready-to-print academic document (7 required sections)
├── index.php                # Landing page with quick tracking lookup & feature cards
├── track.php                # Public / student tracking page with visual progress bar
├── login.php                # Secure role-based login
├── register.php             # Student registration form
├── logout.php               # Session destruction & redirect
├── config/
│   └── db.php               # PDO database connection
├── database/
│   └── docutrack.sql        # Database schema, foreign keys, and seed data
├── includes/
│   ├── auth.php             # Role verification and session helpers
│   ├── header.php           # Common navigation and styling head
│   └── footer.php           # Reusable footer and script loading
├── assets/
│   ├── css/
│   │   └── style.css        # Responsive, modern styling
│   └── js/
│       └── main.js          # Dynamic fee calculation, search filters
├── student/
│   ├── dashboard.php        # Student request overview & status cards
│   └── new_request.php      # Document request submission form
├── admin/
│   ├── dashboard.php        # Registrar overview and request statistics
│   ├── requests.php         # Request management, review, and status update
│   └── documents.php        # Document catalog management (pricing, lead times)
└── uploads/                 # Storage for attached payment receipts / valid IDs
```
