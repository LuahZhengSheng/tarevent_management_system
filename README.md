================================================================================
0. How to Run This Laravel Project (Local Setup)
Prerequisites:

PHP 8.2+

Composer

Node.js + npm

A database (MySQL / MariaDB / PostgreSQL / SQLite)

0.1 Clone and install dependencies
Steps:

Clone the repository

Install PHP dependencies:

composer install

Install frontend dependencies (Vite):

npm install

0.2 Environment configuration
Steps:

Copy environment file:

cp .env.example .env

Configure your .env:

APP_URL (e.g. http://127.0.0.1:8000)

DB_CONNECTION / DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD

Generate application key:

php artisan key:generate
​

0.3 Database setup
Steps:

Run migrations:

php artisan migrate
​

(Optional) If the project provides seeders:

php artisan db:seed

0.4 Storage symlink (for uploaded images/files)
If the project uses public file uploads:

php artisan storage:link
​

0.5 Run the application
You need TWO terminals (backend + frontend):

Terminal A (Laravel server):

php artisan serve
​

Terminal B (Vite dev server):

npm run dev
​

Then open in browser:

http://127.0.0.1:8000

0.6 Production build (optional)
For production deployment, build assets:

npm run build
​

Notes:

If you see blank styles/scripts in local dev, ensure npm run dev is running (Vite).
​

If images under /storage cannot load, re-run php artisan storage:link.
​



================================================================================
                    TAREvent User Management Module Guide
                         (User Management Module)
================================================================================

1. Module Overview
================================================================================

The User Management Module is one of the core modules of the TAREvent system.  
It is responsible for managing all user accounts within the system.

This module allows administrators to create, view, edit, and manage different
types of user accounts.

Main features include:
- Student user management
- Club user management
- Administrator account management
- Permission management

================================================================================
2. User Role Descriptions
================================================================================

There are four types of user roles in the system:

1. Student
   - Regular users who can register and participate in events
   - Email verification is required before login
   - Can browse and register for events
   - Redirected to the event homepage (/events) after login

2. Club
   - Club administrators who can create and manage events
   - Email verification is required before login
   - Redirected to the club dashboard (/club/dashboard) after login

3. Admin
   - System administrators who can manage users, events, and clubs
   - Email is automatically verified upon creation (no manual verification required)
   - Redirected to the admin dashboard (/admin/dashboard) after login
   - Permissions are assigned by the Super Admin

4. Super Admin
   - Highest-privilege administrator
   - Has full permissions, including managing other administrators’ permissions
   - Can edit all system content
   - Redirected to the admin dashboard (/admin/dashboard) after login

================================================================================
3. Access Paths
================================================================================

Prerequisites:
- Must be logged in with an admin or super_admin role
- After login, access: http://localhost:8000/admin/dashboard

Main page routes:

1. User Management (Students and Clubs)
   - User list: /admin/users
   - Create user: /admin/users/create
   - View details: /admin/users/{userID}
   - Edit user: /admin/users/{userID}/edit

2. Administrator Management
   - Administrator list: /admin/administrators
   - Create administrator: /admin/administrators/create
   - View details: /admin/administrators/{adminID}
   - Edit administrator: /admin/administrators/{adminID}/edit

3. Permission Management (Super Admin only)
   - Permission list: /admin/permissions
   - Edit permissions: /admin/permissions/{adminID}/edit

================================================================================
4. Feature Usage Guide
================================================================================

4.1 Creating a New User (Student / Club)

Steps:
1. Log in as an administrator
2. Click "User Management" → "All Users" in the sidebar
3. Click the "Create User" button
4. Fill in user information:
   - Name (required)
   - Email (required, must be unique)
   - Student ID (required, student only)
   - Phone number (optional)
   - Program (required, selected from dropdown)
   - Role (Student or Club)
   - Password (optional; auto-generated if omitted)
5. Click "Create User"

Result:
- A password is automatically generated if not provided
- A welcome email containing login information is sent
- Email verification is required before login
- Redirects to the user detail page upon success

Notes:
- Phone number is optional
- Auto-generated passwords are 12 characters long
- Student and Club users must verify their email before logging in

================================================================================
4.2 Viewing the User List
================================================================================

Steps:
1. Log in as an administrator
2. Navigate to "User Management" → "All Users"

Available features:
- Search by name, email, or student ID
- Filter by role and status
- Pagination support
- Sorting by different fields

================================================================================
4.3 Viewing User Details
================================================================================

Steps:
1. Click on a user name or the "View" button in the user list
2. The system navigates to the user detail page

Displayed information:
- Basic details such as name, email, student ID, phone number, and program
- Account status (active / inactive)
- Profile avatar (if available)
- Club memberships loaded dynamically via API
- Action buttons such as Edit and Status Toggle

================================================================================
4.4 Editing User Information
================================================================================

Steps:
1. Click "Edit" on the user detail page
   or
2. Click "Edit" directly from the user list
3. Update the required fields
4. Click "Save Changes"

Editable fields:
- Name
- Phone number
- Avatar (upload new image)

Non-editable fields:
- Email
- Student ID
- Program

================================================================================
4.5 Toggling User Status (Activate / Deactivate)
================================================================================

Steps:
1. Locate the status toggle button on the user list or detail page
2. Click "Activate" or "Deactivate"

Notes:
- Deactivated users cannot log in
- Activated users can use the system normally
- Status changes take effect immediately

================================================================================
4.6 Creating an Administrator Account
================================================================================

Steps:
1. Log in with an account that has create_administrator permission
2. Navigate to "User Management" → "Administrators"
3. Click "Create Administrator"
4. Enter administrator details:
   - Name (required)
   - Email (required, must be unique)
   - Password (optional; auto-generated if omitted)
5. Click "Create Administrator"

Result:
- Password is auto-generated if not provided
- Welcome email is sent
- Email is automatically verified
- Redirects to the administrator detail page

Notes:
- Only admin or super_admin can create administrators
- Newly created admins have no permissions by default
- Super Admin must assign permissions manually

================================================================================
4.7 Managing Administrator Permissions (Super Admin Only)
================================================================================

Steps:
1. Log in as Super Admin
2. Navigate to "Permission Management" → "Manage Permissions"
3. Select an administrator
4. Click "Edit Permissions"
5. Assign required permissions:
   - User Management
   - Administrator Management
   - Event Management
   - Club Management
   - Reports
   - System Settings
6. Click "Save Changes"

Permission notes:
- Each permission controls access to specific features
- Administrators can only access granted permissions
- Super Admin has all permissions by default

================================================================================
5. User Registration and Login Flow
================================================================================

5.1 Student Self-Registration

Steps:
1. Visit the registration page: /register
2. Fill in registration details such as name, email, password, student ID, and program
3. Submit the registration form
4. Email verification is sent
5. Click the verification link
6. Log in after successful verification

Characteristics:
- User sets their own password
- Email verification is mandatory
- No automatic login after registration
- Redirects to the login page

================================================================================
5.2 Administrator-Created Users
================================================================================

Steps:
1. Administrator creates the user from the admin panel
2. System generates a password if not provided
3. Welcome email is sent
4. User verifies email
5. User logs in

Characteristics:
- Password may be system-generated
- Login credentials are delivered via email
- Email verification is required

================================================================================
5.3 Login Process
================================================================================

Steps:
1. Visit /login
2. Enter email and password
3. Click "Sign in"

Login checks:
- Email and password validation
- Account suspension status
- Account activation status
- Email verification (Student / Club only)

Post-login redirection:
- Student → /events
- Club → /club/dashboard
- Admin / Super Admin → /admin/dashboard

================================================================================
5.4 Bearer Token Mechanism
================================================================================

Description:
- An API token is generated upon successful login
- The token is stored in the browser’s localStorage
- Used for authenticated API requests

Usage:
- Token is stored automatically
- Can be retrieved in JavaScript:
  const token = localStorage.getItem('api_token');

Logout behavior:
- Token is removed automatically on logout
- Or manually via:
  localStorage.removeItem('api_token');

================================================================================
6. Permission System Overview
================================================================================

6.1 Permission Types

The system uses fine-grained permission control.

User management permissions:
- view_users
- create_user
- update_user
- delete_user
- view_user_details
- toggle_user_status

Administrator management permissions:
- view_administrators
- create_administrator
- update_administrator
- delete_administrator
- view_administrator_details
- toggle_administrator_status
- manage_permissions (Super Admin only)

Other permissions:
- manage_events
- manage_clubs
- view_reports
- manage_settings

================================================================================
                             End of Document
================================================================================
