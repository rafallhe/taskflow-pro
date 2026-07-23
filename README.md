# TaskFlow Pro Enterprise

A portfolio-ready full-stack task management system built with PHP, MySQL, HTML, CSS and JavaScript.

## Main features

- Registration, login and logout
- Password hashing and secure sessions
- CSRF protection
- Role-based access: Admin, Manager, Member
- Professional dashboard
- Task CRUD
- Search and filters
- Categories, priorities, due dates and progress
- Notifications
- Activity logs
- Profile management
- User administration
- REST-style JSON API
- API token authentication
- Responsive dark/light UI
- Prepared database schema and demo data

## Requirements

- PHP 7.4+
- MySQL 8+ or MariaDB 10.5+
- PDO MySQL
- Apache recommended

## XAMPP setup

1. Extract the folder into:
   `C:\xampp\htdocs\taskflow-pro-enterprise`

2. Start Apache and MySQL.

3. Open phpMyAdmin:
   `http://localhost/phpmyadmin`

4. Import:
   `database/schema.sql`

5. Open:
   `http://localhost/taskflow-pro-enterprise/auth/login.php`

## Demo credentials

- Email: `demo@taskflow.test`
- Password: `Demo123!`

If the seeded password is incompatible with your PHP environment, register a new account and manually change its role to `admin` in phpMyAdmin.

## API usage

Endpoint:
`GET /api/tasks.php`

Header:
`X-API-TOKEN: your_token`

The token is visible on the Profile page.

POST JSON example:

```json
{
  "title": "Prepare proposal",
  "description": "Create client proposal",
  "priority": "high",
  "status": "todo",
  "category": "Sales",
  "progress": 0
}
```

## Security

- PDO prepared statements
- Output escaping
- CSRF validation
- Session regeneration
- Password hashing
- Ownership checks
- Role authorization
- Basic security response headers

## GitHub repository name

`taskflow-pro-enterprise`


## Subfolder installation fix
This edition automatically detects the project folder. CSS, JavaScript, links and redirects work under localhost subfolders.

If you imported the older database, import `database/fix_demo_login.sql` once.

Open `diagnostics.php`; every check should show PASS.


## Final compatibility fixes

- Dashboard statistics query rewritten with portable `CASE WHEN` expressions for MySQL and MariaDB.
- Compatible with PHP 7.4 and newer.
- Works from a subfolder inside XAMPP.
- Demo login repair script included.


## Stable build fix

This build avoids the MariaDB/MySQL reserved keyword `HIGH_PRIORITY` as a SQL alias. The dashboard counters now use the safe internal alias `high_count`.

## Version 2 upgrade

New portfolio features:

- Drag-and-drop Kanban board
- Instant task status updates
- Task detail page
- Interactive checklist
- Task comments/discussion
- Improved task navigation

### Upgrade an existing installation

Import once:

`database/upgrade_v2.sql`

Then open:

`http://localhost/taskflow-pro-v2/tasks/kanban.php`

## Version 3

Added:

- Monthly task calendar
- Date-range productivity reports
- CSV export compatible with Excel
- Secure task attachments
- Task tags
- Attachment download and deletion
- Additional responsive interface improvements

### Upgrade

Import once:

`database/upgrade_v3.sql`

Maximum attachment size is 5 MB. Uploaded files are stored in:

`storage/uploads`


## Version 3.1 final fix

- Fixed CSS and JavaScript paths on the Reports page.
- Added `/reports/` to automatic project base URL detection.
- No database migration is required when upgrading from V3.

## Version 4 — Portfolio Polish

Added:

- Interactive Chart.js dashboard
- Weekly, monthly and priority analytics
- Password change workflow
- Recent sign-in history
- Login audit table
- Automatic overdue notifications
- Additional motion and hover polish

### Upgrade from V3.1

Import once:

`database/upgrade_v4.sql`

Then refresh the project.

## Version 5 — Final Portfolio Edition

Major additions:

- Professional SaaS landing page
- Multi-team workspaces
- Team members and roles
- Task assignment to team members
- Invitation workflow
- Real-time notification polling
- English / Arabic layout toggle
- Progressive Web App manifest and service worker
- Installable app icons
- Final portfolio documentation

### Upgrade from V4

Import once:

`database/upgrade_v5.sql`

Then open the project root to view the new landing page.

## Version 6 — Production Toolkit

- Global search
- Automatic checklist progress
- Admin console and audit view
- Printable PDF reports
- Full REST API CRUD
- Dockerfile and Docker Compose

Upgrade by importing `database/upgrade_v6.sql`.
Run Docker with `docker compose up --build`, then open `http://localhost:8080`.


## Version 7 — Portfolio Ready

Final additions:

- Consolidated `fresh_install.sql` for clean installations
- Built-in API documentation page
- Drag-and-drop attachment interface
- GitHub Actions PHP lint workflow
- `.gitignore` and MIT license
- Portfolio-ready project description and resume bullets

### Recommended clean installation

For a fresh database, import only:

`database/fresh_install.sql`

Then open the project root and use:

- Email: `demo@taskflow.test`
- Password: `Demo123!`

For an existing V6.1 database, no new migration is required.


## Version 8 — Enterprise Final

This release consolidates all planned portfolio features:

- Teams with editable identity, descriptions, colors and deletion
- Admin / Manager / Member authorization
- User and role administration
- Task assignment to team members
- Comments, checklists and automatic progress
- Secure file uploads with drag and drop
- Activity and login audit logs
- Email invitation queue
- Interactive analytics dashboard
- Global search across tasks, teams, people, comments and files
- Complete REST API CRUD and local API documentation
- Calendar, Kanban and reports
- CSV and printable PDF reporting
- Dark/light theme, responsive design and RTL layout
- PWA, Docker and GitHub Actions
- Consolidated clean database installer
- Production diagnostics

### Existing database upgrade

Import once:

`database/upgrade_v8.sql`

### Clean installation

Import only:

`database/fresh_install.sql`

### Final verification

Open:

`diagnostics.php`

All checks should display PASS.
