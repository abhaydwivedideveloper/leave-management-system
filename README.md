# Staff Leave Management System

A production-style internal web application built with **Laravel 10**, **MySQL**, and **Blade + Alpine.js**. Employees apply for leave; managers approve or reject team requests; admins manage users, leave types, entitlements, and organization-wide reporting.

## Features

- **Role-based access control** (Employee, Manager, Admin) with middleware returning HTTP 403 for unauthorized access
- **Leave workflow**: Pending → Approved / Rejected (admin can override status)
- **Validation**: valid date ranges, no past start dates, no overlapping approved leave, entitlement balance checks
- **Employee dashboard**: applications list, filters, yearly balance summary
- **Manager dashboard**: pending queue with inline review, processed history
- **Admin panel**: user CRUD, manager assignment, leave types & entitlements, global leave view, CSV export
- **Bonus**: email notifications on status change, leave balance tracking, JSON API (`/api/leave-applications`), feature tests

## Requirements

- PHP 8.1+
- Composer
- MySQL 5.7+ / MariaDB
- Node.js 18+ (for frontend assets)

## Setup

```bash
# Clone and install dependencies
composer install
npm install && npm run build

# Environment
cp .env.example .env
php artisan key:generate

# Configure .env database credentials, then:
php artisan migrate --seed

# If tables exist but users table is empty, run:
php artisan db:seed
```

> **Important:** The app uses the database named in `.env` (`DB_DATABASE`, default: `leave_management`).  
> In phpMyAdmin, select that database — not `laravel` or another schema — when checking the `users` table.

### Database (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Mail (optional)

For local development, use the log driver:

```env
MAIL_MAILER=log
```

Or configure Mailpit/SMTP for real email delivery.

## Test credentials

| Role     | Email                   | Password  |
|----------|-------------------------|-----------|
| Admin    | admin@leavems.test      | password  |
| Manager  | manager@leavems.test    | password  |
| Employee | employee@leavems.test   | password  |

Additional seeded users: `manager2@leavems.test`, `sarah@leavems.test` (password: `password`).

## Running the application

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` and log in with any credential above.

## API

Authenticated via Sanctum (session cookie for same-origin requests):

```
GET /api/leave-applications?status=pending
```

## Tests

```bash
php artisan test
```

Includes tests for login, leave application creation, and role-based access control.

## Architecture notes

- **Services**: `LeaveApplicationService` centralizes validation (overlap, balance, date rules)
- **Policies**: `LeaveApplicationPolicy` guards view, cancel, review, and admin override actions
- **Enums**: `UserRole` and `LeaveStatus` for type-safe role/status handling
- Auth scaffolding provided by **Laravel Breeze** (Blade stack)

## License

MIT
