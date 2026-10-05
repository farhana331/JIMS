# JIMS - Job & Internship Management System

JIMS (Job & Internship Management System) is a web-based platform designed to connect students with companies and simplify the process of job and internship recruitment.

The system provides separate functionalities for **Students, Companies, and Administrators**, allowing each role to manage recruitment-related activities efficiently.

## Overview

JIMS provides a centralized platform where:

* Students can browse available job and internship opportunities and apply for suitable positions.
* Companies can create job and internship posts, manage applications, and conduct recruitment activities.
* Administrators can manage users, companies, job posts, and overall system activities.

## User Roles

### Student

* Create and manage student profile
* Manage skills
* Upload CV
* Browse approved jobs and internships
* Search and filter opportunities
* View job details
* Apply for jobs and internships
* Track application status
* View recruitment information

### Company

* Register as a company
* Request company approval
* Manage company profile
* Post jobs and internships
* Edit and manage job posts
* View applicants
* Review submitted CVs
* Shortlist candidates
* Schedule interviews
* Update interview status
* Select or reject candidates

### Administrator

* Manage students and companies
* Approve or reject company registrations
* Manage job and internship posts
* Monitor applications
* Manage system users
* Monitor recruitment activities
* Control overall system operations

## Key Features

* Role-based authentication and authorization
* Student registration and profile management
* Company registration and approval system
* Job and internship posting
* Job search and filtering
* CV upload and management
* Online job application system
* Application status tracking
* Candidate shortlisting
* Interview scheduling
* Candidate selection and rejection
* Vacancy management
* Automatic vacancy tracking
* Admin management dashboard
* Secure password storage
* Database-driven recruitment management

## Recruitment Workflow

```text
Company Registration
        |
        v
Admin Approval
        |
        v
Company Posts Job / Internship
        |
        v
Student Applies
        |
        v
Application Review
        |
        v
Shortlisted
        |
        v
Interview Scheduled
        |
        v
Interview Completed
        |
        +------------------+
        |                  |
        v                  v
     Selected           Rejected
```

## Technology Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Server:** Apache
* **Development Environment:** XAMPP

## Database

JIMS uses a MySQL relational database to manage users, students, companies, jobs, applications, interviews, skills, and other recruitment-related information.

The system uses relationships between different entities to maintain data consistency and support the complete recruitment workflow.

## Security

The system includes several security measures, including:

* Role-based access control
* Password hashing
* Session-based authentication
* Input validation
* Prepared SQL statements
* Access restrictions for protected pages
* File upload validation for CVs

## Vacancy Management

JIMS includes vacancy tracking for job and internship posts.

The system keeps track of:

* Total available positions
* Selected candidates
* Remaining vacancies
* Application status

When the available positions are filled, the opportunity can automatically become unavailable for further applications.

## Project Structure

```text
job_internship_system/
│
├── admin/
├── auth/
├── company/
├── student/
├── config/
├── uploads/
├── index.php
└── ...
```

## Local Setup

To run the project locally:

1. Install **XAMPP**.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Place the project inside:

```text
C:\xampp\htdocs\
```

4. Create the required MySQL database.
5. Import the project database.
6. Configure the database connection.
7. Open the project in a browser:

```text
http://localhost/job_internship_system/
```

> Note: The above URL is a local development URL and is not publicly accessible.

## Project Purpose

The main objective of JIMS is to provide an organized recruitment platform where students can discover employment opportunities and companies can manage the hiring process digitally.

The system reduces manual recruitment activities and provides a structured workflow from job posting to candidate selection.

## Future Improvements

* Email notifications
* Online interview integration
* Advanced search and filtering
* Resume/CV parsing
* Real-time notifications
* Cloud deployment
* Analytics and recruitment reports

## Project

**JIMS - Job & Internship Management System**

A web-based recruitment management system developed as an academic project.
