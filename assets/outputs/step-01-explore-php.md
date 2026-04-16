# CityPulse Events — PHP Legacy Codebase Analysis

> **Application:** CityPulse Events — an event ticketing platform  
> **Stack:** PHP 5.6 (procedural) · MySQL 5.7 · jQuery 1.12.4 · PayPal Sandbox  
> **Analysis Date:** 2026-04-16

---

## 1. Directory Structure Overview

```
citypulse-events/
├── config.php                  # Global configuration, DB credentials, session setup
├── index.php                   # Homepage — upcoming events, category grid
├── register.php                # User registration form + handler
├── database.sql                # Full schema + seed data (7 tables)
├── docker-compose.yml          # Docker services (PHP + MySQL)
├── Dockerfile                  # PHP runtime container
├── .htaccess                   # Apache rewrite rules
│
├── includes/                   # Shared PHP includes (pseudo-framework)
│   ├── db.php                  # mysqli connection + query/fetch helper functions
│   ├── auth.php                # Authentication: login, register, role checks (MD5!)
│   ├── functions.php           # ~360-line monolithic utility file (all business logic)
│   ├── header.php              # HTML <head>, nav bar, flash messages
│   ├── footer.php              # HTML footer + JS includes
│   └── logout.php              # Session destroy + redirect
│
├── admin/                      # Admin-only pages
│   ├── login.php               # Login form + POST handler
│   ├── events.php              # Admin event management (list, cancel)
│   └── users.php               # Admin user listing
│
├── events/                     # Public + organizer event pages
│   ├── list.php                # Paginated event listing with category filter
│   ├── detail.php              # Single event view + review submission
│   ├── search.php              # Keyword search (GET ?q=)
│   ├── create.php              # Organizer: create new event (with image upload)
│   └── edit.php                # Organizer: edit existing event
│
├── organizers/                 # Organizer dashboard area
│   ├── dashboard.php           # Stats overview, event table, revenue
│   ├── reports.php             # Date-filtered sales/revenue report
│   └── settings.php            # Edit company name, description, website
│
├── tickets/                    # Ticket purchase flow
│   ├── purchase.php            # Select ticket type + quantity
│   ├── checkout.php            # PayPal checkout form (redirect)
│   ├── confirm.php             # PayPal IPN handler + return-URL confirmation
│   └── my-tickets.php          # User's purchased tickets list
│
├── css/                        # Static CSS assets
├── js/                         # Static JS assets (main.js)
├── uploads/                    # User-uploaded event images (no validation)
└── assets/                     # Documentation assets
```

**Total PHP files:** 20  
**Lines of PHP (approx.):** ~1,200 (excluding HTML templates within the same files)

---

## 2. Complete Database Schema (7 Tables)

All tables use **InnoDB** engine. Database name: `citypulse_events`.

### 2.1 `users`

| Column       | Type                                      | Constraints                |
|-------------|-------------------------------------------|----------------------------|
| `id`        | INT AUTO_INCREMENT                        | PRIMARY KEY                |
| `username`  | VARCHAR(50)                               | UNIQUE, NOT NULL           |
| `email`     | VARCHAR(100)                              | UNIQUE, NOT NULL           |
| `password`  | VARCHAR(32)                               | NOT NULL (MD5 hash — 32 chars) |
| `name`      | VARCHAR(100)                              | NOT NULL                   |
| `phone`     | VARCHAR(20)                               | nullable                   |
| `role`      | ENUM('user','organizer','admin')          | DEFAULT 'user'             |
| `created_at`| DATETIME                                  | DEFAULT CURRENT_TIMESTAMP  |

**Seed data:** 6 users (1 admin, 2 organizers, 3 regular users). Passwords: `admin123` → `0192023a7bbd73250516f069df18b500`, `pass123` → `4e6419f9c9d70b3e71071bc2c2e48b04`.

### 2.2 `venues`

| Column          | Type            | Constraints      |
|----------------|-----------------|------------------|
| `id`           | INT AUTO_INCREMENT | PRIMARY KEY   |
| `name`         | VARCHAR(100)    | NOT NULL         |
| `address`      | VARCHAR(255)    | NOT NULL         |
| `city`         | VARCHAR(50)     | NOT NULL         |
| `capacity`     | INT             | NOT NULL         |
| `amenities`    | TEXT            | nullable         |
| `contact_email`| VARCHAR(100)    | nullable         |
| `latitude`     | DECIMAL(10,8)   | nullable         |
| `longitude`    | DECIMAL(11,8)   | nullable         |

**Seed data:** 8 venues across US cities (New York, LA, Chicago, SF, Austin, Seattle, Portland, Denver).

### 2.3 `categories`

| Column | Type         | Constraints          |
|--------|--------------|----------------------|
| `id`   | INT AUTO_INCREMENT | PRIMARY KEY    |
| `name` | VARCHAR(50)  | NOT NULL             |
| `slug` | VARCHAR(50)  | UNIQUE, NOT NULL     |
| `icon` | VARCHAR(10)  | nullable (emoji)     |

**Seed data:** 8 categories — Music, Sports, Arts & Culture, Food & Drink, Technology, Business, Health & Wellness, Education.

### 2.4 `organizers`

| Column           | Type            | Constraints                    |
|-----------------|-----------------|--------------------------------|
| `id`            | INT AUTO_INCREMENT | PRIMARY KEY                 |
| `user_id`       | INT             | NOT NULL, FK → users(id)       |
| `company_name`  | VARCHAR(100)    | NOT NULL                       |
| `description`   | TEXT            | nullable                       |
| `website`       | VARCHAR(255)    | nullable                       |
| `verified`      | TINYINT(1)      | DEFAULT 0                      |
| `commission_rate`| DECIMAL(5,2)   | DEFAULT 10.00                  |

**Seed data:** 2 organizers (Event Masters Inc @ 8%, City Events Co @ 10%).

### 2.5 `events`

| Column         | Type                                        | Constraints                     |
|---------------|---------------------------------------------|---------------------------------|
| `id`          | INT AUTO_INCREMENT                          | PRIMARY KEY                     |
| `title`       | VARCHAR(200)                                | NOT NULL                        |
| `description` | TEXT                                        | NOT NULL                        |
| `venue_id`    | INT                                         | FK → venues(id)                 |
| `organizer_id`| INT                                         | NOT NULL, FK → organizers(id)   |
| `event_date`  | DATE                                        | NOT NULL                        |
| `start_time`  | TIME                                        | NOT NULL                        |
| `end_time`    | TIME                                        | NOT NULL                        |
| `category`    | INT                                         | FK → categories(id)             |
| `max_capacity`| INT                                         | NOT NULL                        |
| `price`       | DECIMAL(10,2)                               | NOT NULL                        |
| `status`      | ENUM('active','cancelled','completed')      | DEFAULT 'active'                |
| `image_path`  | VARCHAR(255)                                | nullable                        |
| `created_at`  | DATETIME                                    | DEFAULT CURRENT_TIMESTAMP       |

**Seed data:** 20 events spanning May–December 2024, prices $0–$299.

### 2.6 `tickets`

| Column          | Type                                      | Constraints                   |
|----------------|-------------------------------------------|-------------------------------|
| `id`           | INT AUTO_INCREMENT                        | PRIMARY KEY                   |
| `event_id`     | INT                                       | NOT NULL, FK → events(id)     |
| `user_id`      | INT                                       | NOT NULL, FK → users(id)      |
| `ticket_type`  | ENUM('general','vip')                     | DEFAULT 'general'             |
| `price`        | DECIMAL(10,2)                             | NOT NULL                      |
| `purchase_date`| DATETIME                                  | DEFAULT CURRENT_TIMESTAMP     |
| `payment_status`| ENUM('pending','completed','failed')     | DEFAULT 'pending'             |
| `paypal_txn_id`| VARCHAR(100)                              | nullable                      |
| `qr_code`      | VARCHAR(100)                              | UNIQUE                        |

**Seed data:** 7 tickets (5 completed, 1 pending, 1 completed).

### 2.7 `reviews`

| Column      | Type     | Constraints                          |
|------------|----------|--------------------------------------|
| `id`       | INT AUTO_INCREMENT | PRIMARY KEY                |
| `event_id` | INT      | NOT NULL, FK → events(id)            |
| `user_id`  | INT      | NOT NULL, FK → users(id)             |
| `rating`   | INT      | NOT NULL, CHECK (1–5)                |
| `comment`  | TEXT     | nullable                             |
| `created_at`| DATETIME| DEFAULT CURRENT_TIMESTAMP            |

**Seed data:** 4 reviews across 3 events.

### Entity-Relationship Summary

```
users 1──N tickets
users 1──N reviews
users 1──1 organizers
organizers 1──N events
events N──1 venues
events N──1 categories
events 1──N tickets
events 1──N reviews
```

---

## 3. All Routes/Pages and What They Do

### 3.1 Public Pages (No Auth Required)

| File                  | URL Pattern                          | Method | Description |
|-----------------------|--------------------------------------|--------|-------------|
| `index.php`           | `/`                                  | GET    | Homepage: displays category grid and up to 10 upcoming active events with links to buy tickets. |
| `register.php`        | `/register.php`                      | GET/POST | User registration form. POST creates a new user with MD5-hashed password, role='user'. |
| `admin/login.php`     | `/admin/login.php`                   | GET/POST | Login form. POST authenticates via MD5 comparison, stores user data in `$_SESSION`. |
| `includes/logout.php` | `/includes/logout.php`               | GET    | Destroys session and redirects to homepage. |
| `events/list.php`     | `/events/list.php?category=N&page=N` | GET    | Browse all events or filter by category. Displays event cards with buy-ticket links. |
| `events/detail.php`   | `/events/detail.php?id=N`            | GET/POST | Single event page: full details, ticket availability, reviews. POST submits a review (requires login). |
| `events/search.php`   | `/events/search.php?q=term`          | GET    | Full-text search on event title and description using SQL LIKE. |

### 3.2 Authenticated User Pages (Login Required)

| File                      | URL Pattern                              | Method | Description |
|---------------------------|------------------------------------------|--------|-------------|
| `tickets/purchase.php`    | `/tickets/purchase.php?event_id=N`       | GET/POST | Ticket purchase form (select type, quantity). POST creates ticket records and redirects to checkout. |
| `tickets/checkout.php`    | `/tickets/checkout.php?ticket_id=N`      | GET    | Displays order summary and PayPal checkout form. Submits to PayPal sandbox. |
| `tickets/confirm.php`     | `/tickets/confirm.php?ticket_id=N`       | GET/POST | GET: return URL after PayPal payment — simulates completion. POST: PayPal IPN callback handler. |
| `tickets/my-tickets.php`  | `/tickets/my-tickets.php`                | GET    | Lists all tickets purchased by the logged-in user with event details and QR codes. |

### 3.3 Organizer Pages (Organizer/Admin Role Required)

| File                      | URL Pattern                     | Method | Description |
|---------------------------|---------------------------------|--------|-------------|
| `events/create.php`       | `/events/create.php`            | GET/POST | Create new event form with image upload. Requires organizer role. |
| `events/edit.php`         | `/events/edit.php?id=N`         | GET/POST | Edit existing event. Checks ownership (organizer's own event) or admin. |
| `organizers/dashboard.php`| `/organizers/dashboard.php`     | GET    | Organizer stats: total events, revenue, verification status. Lists all organizer's events with per-event metrics. |
| `organizers/reports.php`  | `/organizers/reports.php?start_date=&end_date=` | GET | Date-filtered sales report: tickets sold and revenue per event. |
| `organizers/settings.php` | `/organizers/settings.php`      | GET/POST | Edit organizer profile (company name, description, website). |

### 3.4 Admin Pages (Admin Role Required)

| File               | URL Pattern               | Method | Description |
|--------------------|---------------------------|--------|-------------|
| `admin/events.php` | `/admin/events.php`       | GET    | Admin event management table. Can view, edit, or cancel (soft-delete) any event via `?delete=N`. |
| `admin/users.php`  | `/admin/users.php`        | GET    | Admin user management table. Lists all users with roles and registration dates. |

---

## 4. Security Vulnerabilities

### 4.1 🔴 CRITICAL: MD5 Password Hashing

**Files:** `includes/auth.php:47`, `includes/auth.php:67`

```php
$password_hash = md5($password);
```

- MD5 is cryptographically broken and unsuitable for password storage.
- No salt is used — identical passwords produce identical hashes.
- Passwords in `database.sql` are stored as raw MD5 (32-char hex strings).
- **Should use:** `password_hash()` with `PASSWORD_BCRYPT` or `PASSWORD_ARGON2ID`.

### 4.2 🔴 CRITICAL: SQL Injection via String Interpolation

**Pervasive across the entire codebase.** Every SQL query uses direct string interpolation with user-supplied input. No prepared statements are used anywhere.

| Location | Vulnerable Query |
|----------|-----------------|
| `includes/auth.php:50` | `"SELECT * FROM users WHERE username = '$username' AND password = '$password_hash'"` |
| `includes/auth.php:70-71` | `INSERT INTO users` with `$username`, `$email`, `$name`, `$phone` |
| `includes/auth.php:79` | `"SELECT * FROM users WHERE id = $user_id"` |
| `register.php:19` | `"SELECT id FROM users WHERE username = '$username'"` |
| `includes/functions.php:26` | `get_event()` — `WHERE e.id = $event_id` |
| `includes/functions.php:43` | `search_events()` — `LIKE '%$search_term%'` |
| `includes/functions.php:53` | `get_events_by_category()` — `WHERE e.category = $category_id` |
| `includes/functions.php:64-65` | `create_event()` — all parameters interpolated |
| `includes/functions.php:72-84` | `update_event()` — all parameters interpolated |
| `includes/functions.php:88` | `delete_event()` — `WHERE id = $event_id` |
| `includes/functions.php:142` | `create_ticket()` — all parameters interpolated |
| `includes/functions.php:148` | `update_ticket_payment()` — `$payment_status`, `$paypal_txn_id` |
| `includes/functions.php:225` | `create_review()` — `$comment` interpolated |
| `organizers/reports.php:16-22` | Date range query with `$start_date`, `$end_date` |
| `organizers/settings.php:17-21` | UPDATE organizers with `$company_name`, `$description`, `$website` |
| `events/detail.php:8` | `$event_id = $_GET['id']` passed directly to `get_event()` |
| `events/list.php:10` | `$page` from `$_GET['page']` (unused in query but still unsanitized) |
| `events/list.php:14` | `$category_id` from `$_GET['category']` passed to `get_events_by_category()` |

The `escape_string()` function exists in `db.php:31` but is **never called anywhere** in the codebase.

### 4.3 🔴 CRITICAL: No CSRF Protection

No form in the entire application includes a CSRF token. All state-changing operations (login, register, create/edit/delete events, purchase tickets, submit reviews, update settings) are vulnerable to cross-site request forgery.

**Affected forms:**
- `register.php` — Account creation
- `admin/login.php` — Login
- `events/create.php` — Event creation
- `events/edit.php` — Event modification
- `events/detail.php` — Review submission
- `tickets/purchase.php` — Ticket purchase
- `organizers/settings.php` — Profile update
- `admin/events.php` — Event deletion via GET parameter (`?delete=N`)

**Notably:** Event deletion in `admin/events.php` uses a **GET request** (`?delete=N`), making it trivially exploitable via image tags or link prefetching.

### 4.4 🟠 HIGH: No Input Validation or Sanitization

- User inputs from `$_POST` and `$_GET` are used directly without any validation.
- `sanitize_output()` function exists (`functions.php:288`) but is **never called** — all template output uses raw `echo`.
- XSS vulnerabilities exist in every page that renders user data:
  - Event titles/descriptions (`detail.php`, `list.php`, `search.php`)
  - User names (`header.php:26`, `admin/users.php`)
  - Review comments (`detail.php:105`)
  - Search terms reflected in output (`search.php:15, 27`)
  - Flash messages (`header.php:38`)

### 4.5 🟠 HIGH: Insecure Session Configuration

**File:** `config.php:20-21`

```php
ini_set('session.cookie_httponly', 0);  // JS can access session cookie
ini_set('session.cookie_secure', 0);    // Cookie sent over HTTP (not HTTPS-only)
```

- Session cookies are accessible to JavaScript (enables session hijacking via XSS).
- Session cookies are transmitted over plain HTTP.
- No `SameSite` attribute configured.
- No session regeneration after login (session fixation vulnerability).

### 4.6 🟠 HIGH: Unrestricted File Upload

**File:** `includes/functions.php:239-251`

```php
function upload_event_image($file) {
    $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    move_uploaded_file($file["tmp_name"], $target_dir . $new_filename);
}
```

- **No MIME type validation** — any file type can be uploaded.
- **No file extension whitelist** — `.php`, `.phtml`, `.htaccess` files can be uploaded.
- **No file size check** in code (only `MAX_UPLOAD_SIZE` defined but never enforced).
- Uploaded files go to `uploads/` which is web-accessible — **remote code execution** possible.

### 4.7 🟡 MEDIUM: Hardcoded Credentials & Secrets

**File:** `config.php:3-6, 14-16`

```php
define('DB_USER', 'citypulse_user');
define('DB_PASS', 'citypulse123');
define('PAYPAL_EMAIL', 'merchant@citypulse.com');
```

- Database credentials hardcoded (DB_HOST reads from env with fallback).
- PayPal merchant email hardcoded.
- Demo passwords visible in `database.sql` and `admin/login.php:50-53`.

### 4.8 🟡 MEDIUM: Verbose Error Reporting in Production

**File:** `config.php:24-25`

```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

- Full PHP errors displayed to users, leaking file paths, query details, and stack traces.
- `die("Query failed: " . mysqli_error($db))` in `db.php:14` exposes SQL error messages.

### 4.9 🟡 MEDIUM: Insecure PayPal IPN Handling

**File:** `tickets/confirm.php:9-23`, `includes/functions.php:319-323`

```php
function verify_paypal_ipn() {
    return true; // No verification!
}
```

- PayPal IPN callback never verifies the request origin with PayPal.
- Attackers can forge IPN POST requests to mark tickets as paid without actual payment.
- The return URL (`confirm.php` GET) also auto-completes payment without verification.

### 4.10 🟡 MEDIUM: Broken Authorization Checks

- **Event deletion via GET:** `admin/events.php:12-16` — `?delete=N` with no CSRF; any link/image can trigger deletion.
- **Ticket quantity not validated:** `tickets/purchase.php:27` — `$quantity` from POST, could be negative or zero.
- **No duplicate review prevention** — users can submit unlimited reviews for the same event.
- **Ticket ownership check incomplete:** `confirm.php:36` auto-completes any `ticket_id` passed via GET, regardless of who owns it.

### 4.11 🟢 LOW: Deprecated/Outdated Dependencies

- **jQuery 1.12.4** (`header.php:8`) — End of life, known XSS vulnerabilities.
- **PHP `mail()` function** (`functions.php:297`) — No SMTP authentication, easily spoofable headers.
- Uses `md5(uniqid(rand(), true))` for QR codes — weak entropy source.

---

## 5. Architecture Patterns

### 5.1 Overall Architecture: Procedural PHP

The application follows a **procedural PHP** architecture with no framework, no MVC separation, and no OOP:

- **No classes or objects** — all logic is in standalone functions.
- **No autoloading** — every file manually `require_once`'s its dependencies.
- **No routing layer** — each URL maps directly to a `.php` file (file-based routing).
- **No templating engine** — PHP/HTML mixed in the same files (inline `<?php ?>` tags).
- **No dependency management** — no Composer, no `vendor/` directory.

### 5.2 Database Access Layer: Raw mysqli

**File:** `includes/db.php`

- Uses **procedural `mysqli_*` functions** (not PDO, not OOP mysqli).
- Single global `$db` connection variable.
- Helper functions: `query()`, `fetch_assoc()`, `fetch_all()`, `escape_string()`, `get_last_insert_id()`.
- **No prepared statements** — all queries use string concatenation/interpolation.
- `escape_string()` is defined but never used.
- `die()` on connection and query failures (no graceful error handling).

### 5.3 Authentication & Authorization: Session-Based

**File:** `includes/auth.php`

- **Session-based authentication** using PHP's native `$_SESSION`.
- Login stores: `user_id`, `username`, `email`, `role`, `name` in session.
- Three role levels: `user`, `organizer`, `admin`.
- Guard functions: `require_login()`, `require_admin()`, `require_organizer()`.
- Role hierarchy: admin has organizer privileges (`is_organizer()` returns true for admins).
- Redirect to `admin/login.php` on unauthorized access.

### 5.4 Template System: Include-Based

- `includes/header.php` — opens HTML document, `<head>`, navigation, flash messages.
- `includes/footer.php` — closes `<main>`, footer HTML, JS includes.
- Each page sets `$page_title` before including header.
- Flash messages via `$_SESSION['message']` / `$_SESSION['message_type']`.

### 5.5 Business Logic: Monolithic Functions File

**File:** `includes/functions.php` (~360 lines)

All business logic lives in a single file organized by comments into sections:
- **Event functions** — CRUD, search, category filter, sold-out check
- **Venue functions** — list all, get by ID, create
- **Ticket functions** — create, update payment, get user tickets
- **Category functions** — list all, get by ID
- **Organizer functions** — get by user, get events, create, revenue calculation
- **Review functions** — get by event, create, average rating
- **File upload** — `upload_event_image()` (no validation)
- **Utility functions** — formatting (currency, date, time), redirect, flash messages
- **Email functions** — `send_email()` via `mail()`, ticket confirmation
- **PayPal functions** — IPN verification (stubbed), payment processing
- **Pagination** — offset calculation helper
- **Validation** — minimal (`validate_email`, `validate_required`) and never used

### 5.6 Payment Flow

1. User selects ticket type/quantity → `tickets/purchase.php` creates `tickets` rows (status: pending).
2. Redirect to `tickets/checkout.php` — renders PayPal form with hidden fields.
3. Form posts to PayPal sandbox URL.
4. PayPal redirects back to `tickets/confirm.php?ticket_id=N` — auto-completes payment.
5. PayPal IPN callback also hits `tickets/confirm.php` via POST — trusts all data without verification.

### 5.7 Request Lifecycle Pattern

Every page follows the same pattern:

```php
<?php
require_once 'config.php';           // 1. Load config, start session
require_once 'includes/db.php';       // 2. Connect to database
require_once 'includes/auth.php';     // 3. Load auth functions
require_once 'includes/functions.php'; // 4. Load all business logic

require_login();  // or require_admin(), require_organizer()  // 5. Optional: guard

$page_title = 'Page Name';           // 6. Set page title

// 7. Handle POST if form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') { ... }

// 8. Fetch data
$data = some_function();

include 'includes/header.php';        // 9. Render header
?>
<!-- 10. Page-specific HTML with inline PHP -->
<?php include 'includes/footer.php'; ?> <!-- 11. Render footer -->
```

### 5.8 Key Anti-Patterns Summary

| Anti-Pattern | Location | Impact |
|-------------|----------|--------|
| God file (all business logic in one file) | `functions.php` | Unmaintainable, untestable |
| Global state (`global $db`) | `db.php`, all functions | Tight coupling, no DI |
| Mixed concerns (HTML + PHP + SQL in same file) | Every page file | No separation of concerns |
| No error handling (die on failure) | `db.php:6,14` | Poor UX, info leakage |
| Duplicated require chains | Every file's first 4 lines | Fragile include order |
| Hardcoded URLs/paths | `config.php`, templates | Environment inflexibility |
| No tests of any kind | — | Zero test coverage |
| No input validation | All POST handlers | Data integrity issues |
| Dead code (`escape_string`, `sanitize_output`, `validate_*`) | `db.php`, `functions.php` | Misleading safety impression |

---

## 6. Summary Statistics

| Metric | Value |
|--------|-------|
| Total PHP files | 20 |
| Database tables | 7 |
| Seed data rows | ~60 (6 users, 8 venues, 8 categories, 2 organizers, 20 events, 7 tickets, 4 reviews) |
| User roles | 3 (user, organizer, admin) |
| SQL injection points | 25+ |
| Critical vulnerabilities | 4 (MD5 passwords, SQL injection, no CSRF, unrestricted upload) |
| Framework/ORM | None (raw procedural PHP + mysqli) |
| Test coverage | 0% |
| External dependencies | jQuery 1.12.4 (CDN), PayPal Sandbox |
