# TaskFlow Pro Architecture

## Application layers

- Presentation: PHP templates, responsive CSS, JavaScript
- Application: authentication, authorization, task workflows, reporting
- Data: MySQL relational schema with foreign keys and indexes
- Integration: JSON API and periodic real-time polling
- Offline support: service worker and web app manifest

## Core entities

- Users
- Teams
- Team members
- Team invitations
- Tasks
- Comments
- Checklists
- Attachments
- Notifications
- Activity logs
- Login logs

## Security controls

- Password hashing
- CSRF tokens
- PDO prepared statements
- HTML output escaping
- Role checks
- Team membership checks
- File MIME validation
- Session regeneration
- Login audit logging
