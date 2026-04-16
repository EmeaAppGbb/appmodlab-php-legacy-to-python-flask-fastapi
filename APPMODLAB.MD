---
title: "PHP to Python Flask/FastAPI"
description: "Migrate legacy PHP apps to Python FastAPI with modern async patterns"
authors: ["marconsilva"]
category: "Code Modernization"
industry: "Cross-Industry"
services: ["Azure Container Apps", "Azure Database for PostgreSQL", "Azure Blob Storage", "Azure Communication Services"]
languages: ["Python"]
frameworks: ["FastAPI", "Flask", "SQLAlchemy"]
modernizationTools: []
agenticTools: []
tags: ["php-migration", "fastapi", "mysql-to-postgresql", "api-first"]
extensions: ["github.copilot"]
thumbnail: ""
video: ""
version: "1.0.0"
---

## Legacy Application Overview

CityPulse Events is a legacy PHP 5.6 event ticketing platform running on a classic LAMP stack (Apache + MySQL 5.7). The application uses procedural PHP with MD5 password hashing, unsanitized user inputs, and session-cookie-based authentication — all intentional legacy vulnerabilities targeted for remediation during migration to Python FastAPI.

### Homepage & Navigation

The legacy application greets users with a hero search bar and category navigation grid covering Arts & Culture, Business, Education, Food & Drink, Health & Wellness, Music, Sports, and Technology.

![Homepage — Legacy CityPulse Events landing page with category grid and upcoming events](assets/screenshots/01-homepage.png)

### Event Browsing & Ticketing

Users browse a full event listing with cards displaying title, date, venue, city, price, and "Buy Tickets" buttons. The database is seeded with 20+ events across all categories.

![Events List — Event cards with details and purchase buttons](assets/screenshots/02-events-list.png)

### Search Functionality

The search feature uses a simple `GET` parameter with no input sanitization — a common legacy vulnerability that will be addressed in the Python migration.

![Search — Results page demonstrating unsanitized GET parameter search](assets/screenshots/03-search.png)

### User Registration & Authentication

The registration form collects Username, Email, Password, Full Name, and Phone fields. Passwords are stored as MD5 hashes, and the login page displays demo credentials with session-cookie-based auth and no CSRF protection.

![Register — Registration form with legacy MD5 password storage](assets/screenshots/04-register.png)

![Admin Login — Login page with demo credentials and no CSRF protection](assets/screenshots/05-admin-login.png)

---

## Initial Application Screenshots

The legacy CityPulse Events PHP application running via Docker (PHP 5.6 + Apache + MySQL 5.7).

### Homepage
![Homepage](assets/screenshots/01-homepage.png)
The main landing page with a hero search bar, category grid (Arts & Culture, Business, Education, Food & Drink, Health & Wellness, Music, Sports, Technology), and an Upcoming Events section.

### Events List
![Events List](assets/screenshots/02-events-list.png)
Full event listing with event cards showing title, date, venue, city, price, and "Buy Tickets" buttons. The database is seeded with ~20+ events across all categories.

### Search
![Search](assets/screenshots/03-search.png)
Search results page showing events matching a query. Uses a simple `GET` parameter with no input sanitization (intentional legacy vulnerability).

### User Registration
![Register](assets/screenshots/04-register.png)
Registration form with Username, Email, Password, Full Name, and Phone fields. Passwords are stored as MD5 hashes (intentional legacy vulnerability).

### Admin Login
![Admin Login](assets/screenshots/05-admin-login.png)
Login page with demo credentials displayed: Admin (admin/admin123), Organizer (organizer1/pass123), User (user1/pass123). Session-cookie-based auth with no CSRF protection.

---

## CLI-Guided Migration Walkthrough

The complete PHP-to-FastAPI migration was performed interactively using **GitHub Copilot CLI** (`gh copilot`) across 8 incremental steps, each captured as a git tag.

| Step | Tag | CLI Command (abbreviated) | What the CLI Produced |
|------|-----|--------------------------|----------------------|
| **1. Explore PHP Codebase** | `step-01-explore-php` | `gh copilot` — "Analyze the legacy PHP codebase…" | Comprehensive analysis document covering file structure, routing conventions, DB schema, auth flow, and legacy vulnerabilities. |
| **2. Analyze Migration** | `step-02-analyze-migration` | `gh copilot` — "Map PHP patterns to Python equivalents…" | Migration mapping document: PHP includes → FastAPI routers, mysqli → SQLAlchemy, MD5 → bcrypt, sessions → JWT, procedural → async. |
| **3. Design FastAPI** | `step-03-design-fastapi` | `gh copilot` — "Scaffold the FastAPI project structure…" | 18 files generated — project layout with `main.py`, routers, models, schemas, templates, config, and test stubs. |
| **4. Data Models** | `step-04-data-models` | `gh copilot` — "Create SQLAlchemy models and Pydantic schemas…" | 7 SQLAlchemy ORM models (User, Event, Ticket, Category, Organizer, Venue, Order) + 8 Pydantic schema files for request/response validation. |
| **5. Migrate Routes** | `step-05-migrate-routes` | `gh copilot` — "Implement all route handlers…" | Full route implementation with JWT authentication, CRUD operations, role-based access control, file uploads, and search. |
| **6. Migrate Templates** | `step-06-migrate-templates` | `gh copilot` — "Convert PHP templates to Jinja2…" | 16 Jinja2 HTML templates converted from PHP — homepage, event list/detail, registration, login, admin dashboard, and more. |
| **7. Add Tests** | `step-07-add-tests` | `gh copilot` — "Generate pytest test suite…" | 41 pytest tests covering auth flows, event CRUD, ticket purchasing, admin operations, and model validation. |
| **8. Build & Run** | `step-08-build-run` | `gh copilot` — "Install dependencies and verify…" | Dependencies installed, all 41 tests pass, FastAPI app boots successfully with interactive docs at `/docs`. |

---

## Key Modernization Improvements

The migration addressed critical security vulnerabilities and architectural debt present in the legacy PHP application:

| Legacy PHP | Modern FastAPI | Impact |
|-----------|---------------|--------|
| **MD5 password hashing** | **bcrypt** with salt rounds | Passwords resistant to rainbow-table and brute-force attacks |
| **Raw SQL with string concatenation** (SQL injection) | **Parameterized queries** via SQLAlchemy ORM | Eliminates SQL injection vulnerabilities entirely |
| **Session-cookie auth** with no CSRF protection | **JWT (JSON Web Tokens)** with expiry and role claims | Stateless, secure authentication suitable for APIs and SPAs |
| **Procedural PHP** with `include`/`require` spaghetti | **Async FastAPI** with dependency injection and routers | Clean separation of concerns, auto-generated OpenAPI docs |
| **mysqli** direct database calls | **SQLAlchemy ORM** with Alembic migrations | Type-safe queries, relationship mapping, schema versioning |
