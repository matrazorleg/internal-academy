# Internal Academy

Laravel 13 + Vue 3 + Inertia application to manage internal workshops and employee registrations.

## Features

- Role-based auth with two roles:
  - `admin`: workshop management and statistics dashboard
  - `employee`: upcoming workshops and one-click registration
- Admin workshop CRUD:
  - title, description, start/end date-time, capacity
- Employee registration flow:
  - confirmed registration when seats are available
  - waiting list when workshop is full
  - cancellation that frees seats immediately
- Waiting list FIFO auto-promotion:
  - when a confirmed participant cancels, first waiting user is promoted
- Overlap protection:
  - user cannot register in workshops that overlap in time
- Reminder command:
  - `php artisan academy:remind` sends emails to confirmed participants of tomorrow workshops
- Admin statistics:
  - most popular workshop
  - live counters (polling every 5 seconds)

## Tech Stack

- Backend: Laravel 13
- Frontend: Vue 3
- Integration: Inertia.js
- Database: MySQL

## Setup

1. Install dependencies

```bash
composer install
npm install
```

2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

3. Create MySQL database

```bash
mysql -u root -p -e "CREATE DATABASE internal_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

4. Verify DB variables in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=internal_academy
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. Run migrations + seed demo data

```bash
php artisan migrate:fresh --seed
```

6. Start application

```bash
php artisan serve
npm run dev
```

## Demo Credentials

- Admin:
  - Email: `admin@academy.local`
  - Password: `password`
- Employee:
  - Seeded employees use password `password`

## Reminder Command

Send reminder emails to confirmed users for workshops scheduled tomorrow:

```bash
php artisan academy:remind
```

Set mail driver in `.env` (for local testing, `MAIL_MAILER=log` is enough).

## .env Documentation (Project-Oriented)

These variables map directly to the challenge requirements:

- `APP_NAME`
  - App label used in UI and email sender name.
  - Recommended value: `Internal Academy`.
- `APP_URL`
  - Base URL used by Laravel and links in notifications/emails.
- `DB_*`
  - Storage for all core data: users with roles, workshops, registrations, waiting list states.
  - For this implementation use MySQL (`DB_CONNECTION=mysql`).
- `SESSION_DRIVER=database`
  - Supports authenticated sessions and role-based interfaces (`admin` vs `employee`).
  - Requires session table migration (already covered by default migrations).
- `CACHE_STORE=database`
  - Centralized cache store; safe default in local/dev team setups.
- `QUEUE_CONNECTION=database`
  - Prepared for queue-based execution if reminder emails are moved async later.
  - Current `academy:remind` command sends synchronously.
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_*`
  - Used by `php artisan academy:remind` to send reminder emails for next-day workshops.
  - Local dev: `MAIL_MAILER=log`.
  - Real env: configure SMTP and a real `MAIL_FROM_ADDRESS`.
- `BROADCAST_CONNECTION`
  - Real-time admin counters are implemented with polling, so no websocket provider is required.
  - Can remain `log` unless you later switch to Reverb/websockets.

## API/Route Overview

- Auth:
  - `GET /login`
  - `POST /login`
  - `POST /logout`
- Employee:
  - `GET /dashboard`
  - `POST /workshops/{workshop}/registrations`
  - `DELETE /workshops/{workshop}/registrations`
- Admin:
  - `GET /admin/workshops`
  - `GET /admin/workshops/create`
  - `POST /admin/workshops`
  - `GET /admin/workshops/{workshop}/edit`
  - `PUT /admin/workshops/{workshop}`
  - `DELETE /admin/workshops/{workshop}`
  - `GET /admin/workshops/stats`

## Architectural Notes

- Core business logic for registration/cancellation sits in `WorkshopRegistrationService`.
- Waitlist promotion is transactional and FIFO.
- Role access is enforced by a dedicated `role` middleware alias.
- Inertia shared props expose authenticated user and flash messages globally.
