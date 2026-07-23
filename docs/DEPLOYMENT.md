# Deployment checklist

## Shared hosting / cPanel

1. Create a MySQL database and user.
2. Import `database/schema.sql`.
3. Upload project files into `public_html/taskflow-pro`.
4. Edit `config/database.php` or set environment variables:
   - DB_HOST
   - DB_NAME
   - DB_USER
   - DB_PASS
5. Confirm PHP 8.1+.
6. Enable PDO MySQL.
7. Visit `/auth/register.php` and create your own account.
8. Change your role to `admin` from phpMyAdmin if needed.
9. Delete or change the demo user before public production use.
10. Use HTTPS.

## Portfolio links

- Live Demo
- GitHub Repository
- Case Study
