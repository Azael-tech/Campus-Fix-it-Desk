# Campus Fix-It Desk (Campus Maintenance Reporting System)

A Laravel + MySQL app where students, teachers, staff, and parents report campus problems
(broken lights, leaks, damaged furniture) and the maintenance team tracks the fix.

## Features

- **CRUD:** create, view, edit, delete reports, with search, filters, and pagination
- **Staff login:** anyone can send and view reports; only logged-in staff can edit, delete, change status, or open the monthly summary
- **Photo upload:** attach a picture (JPG, PNG, WEBP, up to 2 MB) with a live preview
- **Email when fixed:** if the reporter gave an email, they get a message when the report is marked Resolved
- **Monthly summary:** printable one-page report with totals, bar charts, and a table (Print or Save as PDF)

## Files in this folder

These go on top of a fresh Laravel project (same folder names).

```
app/Models/MaintenanceReport.php
app/Mail/ReportResolved.php
app/Http/Controllers/MaintenanceReportController.php
app/Http/Controllers/AuthController.php
database/migrations/2026_09_30_000000_create_maintenance_reports_table.php
database/migrations/2026_09_30_100000_add_photo_and_email_to_maintenance_reports_table.php
database/seeders/MaintenanceReportSeeder.php
routes/web.php
resources/views/layouts/app.blade.php
resources/views/auth/login.blade.php
resources/views/emails/report-resolved.blade.php
resources/views/reports/{index,create,edit,show,summary,_form,_bars}.blade.php
resources/views/pagination/custom.blade.php
public/css/style.css
public/js/app.js
```

## Setup

1. Copy `app`, `database`, `public`, `resources`, `routes` from this folder into the Laravel project
   root (next to `artisan`). Choose "Replace the files in the destination".
2. Set your database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=campus_maintenance
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Make sure `database/seeders/DatabaseSeeder.php` has this inside `run()`:
   ```php
   $this->call(MaintenanceReportSeeder::class);
   ```
4. In a terminal inside the project:
   ```bash
   php artisan storage:link
   php artisan migrate:fresh --seed
   php artisan serve
   ```
   `migrate:fresh` rebuilds all tables from zero (fine while developing).
   If `storage:link` says a permission error on Windows, open the terminal as Administrator and run it again.
5. Open http://127.0.0.1:8000

## Staff login

| Email             | Password    |
|-------------------|-------------|
| staff@school.test | password123 |

Change this password before using the app for real.

## Email notifications

By default Laravel writes emails to `storage/logs/laravel.log` instead of sending them, so you can
test without any mail account. To send real email, set the `MAIL_` lines in `.env`
(for example a free Mailtrap inbox for testing, or Gmail SMTP with an app password):

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS="fixit@school.test"
MAIL_FROM_NAME="Campus Fix-It Desk"
```

To try it: submit a report with an email, log in, open it, and click Resolved.

## Routes

| Method | URL                   | Who         | What it does              |
|--------|-----------------------|-------------|---------------------------|
| GET    | /reports              | Everyone    | List, search, filter      |
| GET    | /reports/create       | Everyone    | New report form           |
| POST   | /reports              | Everyone    | Save new report           |
| GET    | /reports/{id}         | Everyone    | Report details            |
| GET    | /login                | Guests      | Staff login               |
| GET    | /reports/{id}/edit    | Staff       | Edit form                 |
| PUT    | /reports/{id}         | Staff       | Save changes              |
| PATCH  | /reports/{id}/status  | Staff       | Quick status change       |
| DELETE | /reports/{id}         | Staff       | Delete report             |
| GET    | /reports/summary      | Staff       | Monthly summary           |

## Not included yet

- **SMS notifications:** needs a paid SMS provider account (for example Semaphore or Twilio) and an API key.
