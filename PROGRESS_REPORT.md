# STUDENT DAILY PROGRESS REPORT / ACCOMPLISHMENT LOG
**Course / Subject:** Web Management Systems / Systems Development  
**Academic Requirement:** 5-Day Project Progress & Attendance Verification  
**System Title:** DocuTrack: A Web-Based Document Request Monitoring System for School Registrar Services  

---

### Student Information
- **Student Name:** [Insert Your Name Here]
- **Student ID:** [Insert Student ID / e.g. 2023-10045]
- **Course, Year & Section:** [e.g. BS Information Technology 3-A]
- **Instructor / Professor:** [Insert Instructor Name]
- **Submission Date:** November 12, 2026
- **Reporting Period:** Day 1 to Day 5

---

## Daily Accomplishment Summary Matrix

| Day | Date | Focus Area | Status | Hours Logged |
| :---: | :---: | :--- | :---: | :---: |
| **Day 1** | Nov 8, 2026 | Problem Identification, Scope Definition & Requirements Gathering | Completed | 4.5 hrs |
| **Day 2** | Nov 9, 2026 | Database Modeling, Schema Normalization & Git Initialization | Completed | 5.0 hrs |
| **Day 3** | Nov 10, 2026 | UI/UX Layout, Modular Design & Authentication System | Completed | 5.5 hrs |
| **Day 4** | Nov 11, 2026 | Student Portal, Catalog Engine & Dynamic Fee Computation | Completed | 6.0 hrs |
| **Day 5** | Nov 12, 2026 | Registrar Admin Workflow, Timeline Auditing & End-to-End Testing | Completed | 6.5 hrs |
| **Total** | | | | **27.5 hrs** |

---

## Detailed Daily Accomplishment Reports

### Day 1: Problem Identification, Scope Definition & Requirements Gathering
* **Target Objective:** Analyze the operational challenges in school registrar document handling and establish clear project boundaries and functional specifications.
* **Tasks Performed:**
  1. Identified real-world pain points regarding registrar services: long student queues, paper logbooks, lack of tracking visibility, and repeated walk-in inquiries.
  2. Identified primary users: **Students/Alumni** (Requesters) and **Registrar Staff/Admins** (Processors).
  3. Formulated the system's 1 general objective and 4 specific objectives based on the course rubric.
  4. Surveyed and itemized the top official documents handled by school registrars:
     - Official Transcript of Records (TOR)
     - Certificate of Good Moral Character
     - Certificate of Grades (COG)
     - Certificate of Registration (COR)
     - Honorable Dismissal / Transfer Credentials
     - Certified True Copy of Diploma
  5. Drafted the 7-section Project Proposal Document (`PROJECT_DOCUMENT.md`).
* **Outputs / Deliverables:**
  - Complete project brief, problem analysis statement, and functional requirements list.
* **Challenges & Resolutions:**
  - *Challenge:* Balancing feature scope so that it remains realistic to finish using core web technologies (HTML, CSS, JS, PHP, MySQL).
  - *Resolution:* Focused on a streamlined document lifecycle (*Pending &rarr; Payment Verification &rarr; Processing &rarr; Ready for Pickup &rarr; Released*).

---

### Day 2: Database Modeling, Schema Normalization & Git Initialization
* **Target Objective:** Design a normalized MySQL database structure, seed initial records, and configure the local Git version control environment.
* **Tasks Performed:**
  1. Initialized local Git repository inside `c:\xampp\htdocs\DocuTrack` on branch `main`.
  2. Configured `.gitignore` to protect sensitive local configs and isolate user-uploaded attachments.
  3. Designed relational database tables in `database/docutrack.sql`:
     - `users`: stores student ID, name, email, hashed passwords, contact info, and roles.
     - `document_types`: maintains available documents, pricing, lead times, and prerequisites.
     - `document_requests`: stores transactions, tracking codes, copy counts, total fee, status, and remarks.
     - `status_history`: maintains an audit trail of every status transition with timestamps and user IDs.
  4. Configured secure PDO connection in `config/db.php` with `PDO::ERRMODE_EXCEPTION` and prepared statements.
  5. Seeded sample registrar catalog and demo accounts with secure `bcrypt` hashes.
* **Outputs / Deliverables:**
  - Working MySQL schema file (`database/docutrack.sql`) and database connector (`config/db.php`).
* **Challenges & Resolutions:**
  - *Challenge:* Generating collision-free tracking codes for students without revealing internal database sequential IDs.
  - *Resolution:* Devised a format `DOC-YYYY-XXXXXX` using year and randomized cryptographic hex strings.

---

### Day 3: UI/UX Layout, Modular Design & Authentication System
* **Target Objective:** Establish responsive UI styling, reusable header/footer layouts, and role-based login and registration guards.
* **Tasks Performed:**
  1. Developed global stylesheet (`assets/css/style.css`) using CSS variables, custom card grids, typography, and status badge color-coding.
  2. Built modular PHP layout includes:
     - `includes/header.php`: dynamic navbar displaying contextual options based on login state.
     - `includes/footer.php`: uniform footer with copyright and script imports.
  3. Developed `includes/auth.php` providing session verification helpers: `isLoggedIn()`, `isAdmin()`, `isStudent()`, and route-protection functions.
  4. Built `register.php` for students with validation against duplicate student numbers and emails.
  5. Built `login.php` using PHP `password_verify()` to seamlessly route admins to the registrar dashboard and students to the student portal.
  6. Implemented secure `logout.php` to invalidate session cookies.
* **Outputs / Deliverables:**
  - Fully functional, secure user authentication flow with role separation.
* **Challenges & Resolutions:**
  - *Challenge:* Unauthorized users accessing admin links via direct URL input.
  - *Resolution:* Implemented `requireAdmin()` and `requireStudent()` guards at the top of protected pages.

---

### Day 4: Student Portal, Catalog Engine & Dynamic Fee Computation
* **Target Objective:** Construct the student request dashboard and request submission form with dynamic JavaScript calculation and file attachment handling.
* **Tasks Performed:**
  1. Built the public landing page (`index.php`) featuring a quick tracking lookup bar and feature overview cards.
  2. Created the Student Dashboard (`student/dashboard.php`) with KPI cards (Total Requests, In Progress, Ready for Pickup, Completed).
  3. Developed the New Request form (`student/new_request.php`):
     - Dynamic dropdown populating active registrar document types from the database.
     - Requirement checklist panel appearing dynamically when a document is selected.
  4. Programmed JavaScript logic (`assets/js/main.js`):
     - Automatically recalculates total fees in real-time as the number of copies changes.
     - Built live client-side table search filtering.
  5. Implemented file upload logic storing payment receipts and valid IDs into `uploads/` with sanitized filenames.
* **Outputs / Deliverables:**
  - Interactive student request pipeline with dynamic pricing and attachment uploads.
* **Challenges & Resolutions:**
  - *Challenge:* Preventing invalid quantities or zero-cost submission bugs.
  - *Resolution:* Added both client-side constraints (`min="1"`) and server-side validation re-fetching base prices directly from the database.

---

### Day 5: Registrar Admin Workflow, Timeline Auditing & End-to-End Testing
* **Target Objective:** Develop the Registrar Admin Back-Office, status transition tools, public tracking visual stepper, and complete system testing.
* **Tasks Performed:**
  1. Developed Registrar Admin Dashboard (`admin/dashboard.php`) displaying real-time metrics across all active request statuses.
  2. Built `admin/requests.php`:
     - Filter incoming requests by status (*Pending, Verification, Processing, Ready for Pickup, Released, Rejected*).
     - Search by student ID, tracking code, or name.
     - Form to review attachments, assign target release dates, add student-facing notes, and change status.
  3. Programmed automatic audit logging into `status_history` on every administrative status modification.
  4. Developed `admin/documents.php` enabling registrar staff to manage document offerings, update fees, and toggle availability.
  5. Developed public tracking interface (`track.php`) featuring a visual multi-step progress bar and historical audit timeline.
  6. Conducted end-to-end integration testing (Student registration &rarr; Request creation &rarr; Admin review &rarr; Status change &rarr; Public tracking verification).
  7. Formatted all project documentation and created git commits on `main`.
* **Outputs / Deliverables:**
  - Working Registrar Admin Portal, Public Tracking Page, and finalized Git repository.
* **Challenges & Resolutions:**
  - *Challenge:* Ensuring students understand why a request was delayed or rejected without needing to call the office.
  - *Resolution:* Added an `admin_remarks` and `rejection_reason` field displayed clearly on the tracking page.

---

## Summary of Accomplishments to Date
- [x] Functional, database-backed web application running on local XAMPP environment.
- [x] Zero syntax errors verified across all PHP modules.
- [x] Two distinct user roles implemented: Student/Alumni vs. Registrar Administrator.
- [x] 8 core functional modules operational:
  1. Authentication & Role-Based Access Control
  2. Document Catalog & Pricing
  3. Student Request Submission with File Upload
  4. Unique Tracking Code Generation
  5. Visual Progress Stepper & Timeline Tracking
  6. Registrar Dashboard & Queue Processing
  7. Audit Logging & Status History Trail
  8. Administrative Catalog Management
- [x] Clean Git repository initialized with detailed README and documentation.

---

## Student Signature & Certification

I hereby certify that the above daily progress report reflects the actual development activities, system architecture design, and coding accomplished for the **DocuTrack** project during this period.

<br>

_____________________________________________  
**Student Name & Signature**  
Date: November 12, 2026

<br>

_____________________________________________  
**Instructor / Evaluator Signature**  
Date: ________________________
