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
