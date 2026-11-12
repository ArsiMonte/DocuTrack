# DocuTrack: A Web-Based Document Request Monitoring System for School Registrar Services
### Academic Course Requirement: Web Management System (Identify, Analyze, and Solve a Problem)
*Format: Short Bond Paper Submission Reference*

---

### 1. System Title
**DocuTrack: A Web-Based Document Request Monitoring System for School Registrar Services**

---

### 2. Problem Statement
The School Registrar’s Office is the central hub for managing and releasing official academic records—such as Transcripts of Records (TOR), Certificates of Good Moral Character, Certificates of Grades (COG), and Honorable Dismissal credentials. However, the current manual, walk-in request procedure presents severe operational bottlenecks:
- **Long Queues & Wasted Time:** Students and alumni must travel to campus and endure lengthy lines just to secure a physical request slip and check document availability.
- **Lack of Transparency & Status Uncertainty:** Applicants have no visibility into the progress of their requests, resulting in repeated in-person follow-ups or unattended phone inquiries.
- **Manual Record Tracking Inefficiencies:** Registrar personnel rely on paper logbooks and manual ledger filing, which are prone to misplaced records, miscalculated processing fees, and disorganized claim scheduling.
- **Congested Registrar Counters:** Counter staff spend a disproportionate amount of time answering routine status inquiries rather than verifying, printing, and processing records.

---

### 3. Problem Analysis
#### 3.1 Causes of the Problem
1. **Paper-Based Ingestion:** Reliance on physical request forms and logbooks that cannot be searched or audited quickly.
2. **Disconnected Communication:** Absence of an automated notification or public lookup mechanism for students to check if their records are ready for release.
3. **Physical-First Processing:** Requiring students to visit in person for initial filing and payment submission rather than conducting preliminary steps online.

#### 3.2 Effects of the Problem
1. **Student Frustration & Lost Productivity:** Students miss classes or leave work only to be told their document is "still in process."
2. **Staff Burnout & Overload:** Registrar staff juggle counter inquiries, record validation, document retrieval, and physical filing simultaneously.
3. **Prone to Human Error:** Handwritten request forms lead to misspelled names, lost transaction receipts, and difficulty tracing processing backlogs.

#### 3.3 Current Process & Limitations
Under the existing setup, a student travels to the registrar counter, fills out a paper request form, walks to the cashier to pay, returns the receipt to the registrar, and is verbally told to "come back after 7 to 10 days." If delays occur (e.g., pending clearances or signatory absence), the student only finds out upon arriving back on campus.

#### 3.4 Issues the System Intends to Address
DocuTrack replaces the unmonitored paper cycle with an end-to-end digital request and status workflow. It introduces automated tracking references, clear requirement checklists, transparent fee computation, and a real-time progress pipeline (Pending &rarr; Payment Verification &rarr; Processing &rarr; Ready for Pickup &rarr; Released).

---

### 4. Proposed Solution
DocuTrack is a secure, responsive Web Management System built using PHP, MySQL, HTML5, CSS3, and JavaScript. 
- **Online Request Ingestion:** Students and alumni can submit requests for specific registrar documents 24/7 with predefined requirement guidelines, automated fee computation, and file attachments (e.g. proof of payment or valid IDs).
- **Public & Authenticated Real-Time Tracking:** Each submission generates a unique tracking code (e.g., `DOC-2026-XXXX`) allowing students to track progress via an interactive visual timeline without needing to visit campus.
- **Administrative Registrar Back-Office:** Registrar personnel can filter incoming requests, verify payments, transition requests across distinct workflow stages, assign target release dates, and append remarks visible to the student.
- **Dynamic Catalog Management:** The administration can adjust processing lead times, pricing, and document requirements as institutional policies evolve.

---

### 5. Target Users
1. **Students & Alumni (End Users / Requesters):**
   - Register and manage personal accounts with their student credentials.
   - Browse the catalog of official registrar documents, fees, and prerequisites.
   - Submit document requests and attach supporting documents or receipts.
   - Track request milestones in real-time through their portal or via the public reference lookup.
2. **Registrar Staff & System Administrators (Office Personnel):**
   - Monitor real-time request counts and pipeline statistics via an interactive dashboard.
   - Review submitted student details and attached proof of payment.
   - Update transaction statuses (*Pending*, *Payment Verification*, *Processing*, *Ready for Pickup*, *Released*, *Rejected*).
   - Enter remarks, set expected release dates, and keep an auditable activity history.
   - Configure document offerings, fees, and estimated processing timelines.

---

### 6. System Objectives
#### 6.1 General Objective
To design and develop a web-based document request and monitoring system that automates the application, tracking, and management of school registrar credentials to improve operational efficiency and student satisfaction.

#### 6.2 Specific Objectives
1. **To provide a convenient self-service portal** where students can request official documents, view clear guidelines, and calculate fees anytime, anywhere.
2. **To establish a real-time tracking mechanism** utilizing unique tracking codes and a visual progress timeline to keep requesters informed of every milestone.
3. **To equip Registrar staff with a centralized management dashboard** to filter, verify, update, and manage the lifecycle of requests without paper logbooks.
4. **To maintain an automated audit trail (status history)** that records who updated each document status and when, ensuring accountability and preventing lost records.

---

### 7. System Features / Modules
| Feature / Module | Purpose |
| :--- | :--- |
| **1. User Authentication & Role Management** | Secure login and registration for students with password hashing (`bcrypt`), and role-guarded access for registrar personnel. |
| **2. Document Catalog & Pricing Module** | Centralized list of credentials (TOR, Good Moral, Certifications) showing real-time price calculation and prerequisite lists. |
| **3. Request Submission & Upload Module** | Form allowing students to select documents, specify purpose, specify copy quantity, and attach payment receipts or clearance slips. |
| **4. Real-Time Tracking & Progress Stepper** | Visual timeline indicator displaying the current stage (*Pending &rarr; Payment Verification &rarr; Processing &rarr; Ready for Pickup &rarr; Released*). |
| **5. Registrar Administrative Dashboard** | KPI overview showing pending, processing, and ready-to-claim counts with rapid review shortcuts. |
| **6. Request Processing & Status Workflow** | Registrar tool to review documents, approve/reject applications, set pickup dates, and append student-facing remarks. |
| **7. Activity History & Audit Trail** | Database-backed log tracking every status transition, timestamp, and the staff member who authorized the change. |
| **8. Catalog Administration Module** | Allows administrators to create, edit, activate, or disable document types, prices, and lead times. |
