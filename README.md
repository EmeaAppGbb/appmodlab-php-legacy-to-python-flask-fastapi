# 🕹️ PHP → PYTHON FLASK/FASTAPI 🐍

```
█▀█ █ █ █▀█   ▀█▀ █▀█   █▀█ █ █ ▀█▀ █ █ █▀█ █▄ █
█▀▀ █▀█ █▀▀    █  █ █   █▀▀ ▀▄▀  █  █▀█ █ █ █ ▀█
▀   ▀ ▀ ▀      ▀  ▀▀▀   ▀    ▀   ▀  ▀ ▀ ▀▀▀ ▀  ▀

THE GREAT LANGUAGE MIGRATION 🚀 
PROCEDURAL → MODERN | LAMP → CLOUD | 2005 → 2025
```

[![PHP 5.6](https://img.shields.io/badge/PHP-5.6%20Legacy-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Python 3.12](https://img.shields.io/badge/Python-3.12%20Modern-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://www.python.org/)
[![FastAPI](https://img.shields.io/badge/FastAPI-009688?style=for-the-badge&logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com/)
[![Flask](https://img.shields.io/badge/Flask-000000?style=for-the-badge&logo=flask&logoColor=white)](https://flask.palletsprojects.com/)

---

## 🎮 OVERVIEW

Welcome to the **CityPulse Events** migration lab — a journey from the golden age of LAMP stacks to the modern Python cloud era! 🌟

Remember when every app was a collection of `.php` files, MySQL credentials lived in `config.php`, and `$_GET['id']` was how you grabbed parameters? 🐘 This lab brings back that 2005 nostalgia, then shows you how to **level up** to modern Python with FastAPI/Flask, PostgreSQL, JWT auth, and Azure services! ⚡

### 💎 What's This Lab About?

You'll take a **10-year-old procedural PHP event ticketing platform** (complete with MD5 passwords, SQL injection vulnerabilities, and a 2000-line `functions.php` file 😱) and transform it into a **modern, secure, cloud-ready Python API** with auto-generated documentation, async database access, and proper authentication!

**Legacy Stack:**
```
APACHE STARTING... 🔥
MYSQL CONNECTED 🐬  
PHP PARSED <?php 🐘
JQUERY LOADED $ 💫
SESSION STARTED 🍪
MD5 HASHED... (yikes) 🔓
```

**Target Stack:**
```
PYTHON IMPORTED 🐍
FASTAPI INITIALIZED ⚡
POSTGRESQL CONNECTED 🐘
ASYNC ENABLED 🚀
JWT AUTHENTICATED 🔐
OPENAPI DOCUMENTED 📚
```

---

## 🎯 WHAT YOU'LL LEARN

By the end of this lab, you'll master:

✅ **PHP to Python Translation** — Convert procedural PHP code to modern Python OOP patterns  
✅ **Framework Migration** — Choose between FastAPI (async, modern) or Flask (classic, proven)  
✅ **Database Evolution** — Migrate MySQL to PostgreSQL with SQLAlchemy 2.0  
✅ **Security Remediation** — Replace MD5 passwords with bcrypt, fix SQL injection, implement JWT  
✅ **Payment Modernization** — Swap PayPal IPN for Stripe Checkout Sessions  
✅ **Cloud Services** — Integrate Azure Blob Storage, Communication Services, Container Apps  
✅ **API-First Development** — Build self-documenting REST APIs with OpenAPI/Swagger  
✅ **Async Patterns** — Adopt modern async/await for database and HTTP operations  

---

## 📋 PREREQUISITES

Before starting your migration quest, ensure you have:

- 🐍 **Python 3.11+** — The modern runtime
- 🐘 **Basic PHP Reading Knowledge** — You'll need to understand what you're migrating from
- 🐳 **Docker Desktop** — For running both legacy and modern apps
- ☁️ **Azure Subscription** — For cloud deployment (free trial works!)
- 🗄️ **Basic SQL Knowledge** — You'll be migrating databases
- 🤖 **GitHub Copilot CLI** — Your AI coding assistant

**Optional but Helpful:**
- Nostalgia for the LAMP stack era 💜
- Experience with REST APIs and JSON
- Understanding of async/await concepts

---

## 🚀 QUICK START

### Option 1: XAMPP Nostalgia Mode 🕰️

```bash
# For the authentic 2005 experience!
# Install XAMPP and drop the legacy folder into htdocs
# Navigate to http://localhost/citypulse

# But honestly, just use Docker 😉
```

### Option 2: Docker (Recommended) 🐳

```bash
# Clone the repository
git clone https://github.com/EmeaAppGbb/appmodlab-php-legacy-to-python-flask-fastapi.git
cd appmodlab-php-legacy-to-python-flask-fastapi

# Start the legacy PHP app
git checkout legacy
docker-compose up -d

# Watch the magic!
# 🔥 Apache starting on port 8080
# 🐬 MySQL initializing on port 3306
# 🐘 PHP 5.6 parsing your scripts

# Visit the legacy app
open http://localhost:8080
```

### Running the Modern Python Version 🐍

```bash
# FastAPI version (primary)
git checkout solution
docker-compose up -d

# Flask alternative
git checkout solution-flask
docker-compose up -d

# Visit the API docs (FastAPI magic!)
open http://localhost:8000/docs

# Interactive Swagger UI appears! 📚✨
```

---

## 📁 PROJECT STRUCTURE

### Legacy PHP (The "Before" Photo) 🕸️

```
citypulse/                              # The legacy codebase
├── 🏠 index.php                        # Homepage with event listing
├── 🔧 config.php                       # Hardcoded credentials 😬
├── 📂 includes/
│   ├── db.php                          # mysqli_connect() vibes
│   ├── header.php                      # HTML fragments everywhere
│   ├── footer.php                      # Copy-paste templating
│   ├── functions.php                   # 2000 lines of globals 💀
│   └── auth.php                        # Session-based auth
├── 📂 events/
│   ├── list.php                        # Event catalog
│   ├── detail.php?id=X                 # SQL injection waiting to happen
│   ├── create.php                      # Mixed HTML/PHP spaghetti
│   ├── edit.php                        # More of the same
│   └── search.php                      # Raw SQL LIKE queries
├── 📂 tickets/
│   ├── purchase.php                    # Ticket buying flow
│   ├── checkout.php                    # PayPal IPN integration
│   ├── confirm.php                     # Payment callback handler
│   └── my-tickets.php                  # User ticket history
├── 📂 organizers/
│   ├── dashboard.php                   # Event organizer console
│   ├── reports.php                     # HTML table reports
│   └── settings.php                    # Profile management
├── 📂 admin/
│   ├── login.php                       # Admin authentication
│   ├── events.php                      # Event moderation
│   └── users.php                       # User management
├── 📂 uploads/                         # Filesystem uploads 📁
├── 📂 css/                             # Stylesheets
├── 📂 js/                              # jQuery 1.x scripts
└── .htaccess                           # Apache rewrite rules
```

### Modern Python (The "After" Photo) ✨

```
citypulse_api/                          # Clean Python architecture
├── 📄 pyproject.toml                   # Poetry dependency management
├── 📄 Dockerfile                       # Containerization
├── 📂 app/
│   ├── 🎯 main.py                      # FastAPI application entry
│   ├── 📂 api/
│   │   ├── routes/                     # API endpoint handlers
│   │   │   ├── events.py               # Event CRUD operations
│   │   │   ├── tickets.py              # Ticket purchase APIs
│   │   │   ├── auth.py                 # JWT authentication
│   │   │   └── organizers.py           # Organizer endpoints
│   │   └── dependencies.py             # Dependency injection
│   ├── 📂 core/
│   │   ├── config.py                   # Environment-based config
│   │   ├── security.py                 # JWT, bcrypt, OAuth2
│   │   └── database.py                 # Async SQLAlchemy setup
│   ├── 📂 models/
│   │   ├── event.py                    # SQLAlchemy models
│   │   ├── ticket.py                   # Database models
│   │   └── user.py                     # Proper ORM
│   ├── 📂 schemas/
│   │   ├── event.py                    # Pydantic request/response
│   │   ├── ticket.py                   # Type-safe schemas
│   │   └── user.py                     # Input validation
│   ├── 📂 services/
│   │   ├── event_service.py            # Business logic layer
│   │   ├── payment_service.py          # Stripe integration
│   │   ├── storage_service.py          # Azure Blob Storage
│   │   └── email_service.py            # Azure Communication Services
│   └── 📂 repositories/
│       ├── event_repository.py         # Data access layer
│       └── ticket_repository.py        # Async database queries
├── 📂 tests/
│   ├── test_events.py                  # Pytest test suite
│   └── test_auth.py                    # Authentication tests
└── 📂 migrations/
    └── alembic/                        # Database migrations
```

---

## 🏗️ LEGACY STACK (The "Before Times")

### Tech Stack Circa 2005 📼

| Component | Technology | Status |
|-----------|-----------|--------|
| **Language** | PHP 5.6 (procedural) | 🐘 No namespaces, no Composer |
| **Web Server** | Apache + mod_php | 🔥 .htaccess magic |
| **Database** | MySQL 5.7 | 🐬 mysqli_connect() |
| **Authentication** | File-based sessions | 🍪 $_SESSION everywhere |
| **Password Hash** | MD5 | 🔓 **DANGER!** |
| **SQL Queries** | String interpolation | 💉 SQL injection galore |
| **Frontend** | jQuery 1.x | 💫 $(document).ready() |
| **Templating** | PHP echo in HTML | 🍝 Spaghetti code |
| **Email** | PHPMailer (outdated) | 📧 Copied into project |
| **Payments** | PayPal IPN | 💳 No signature verification |
| **File Uploads** | move_uploaded_file() | 📁 No validation |
| **Routing** | .htaccess rewrites | 🔀 Apache-dependent |

### Database Schema 🗄️

```sql
-- MySQL Schema (Legacy)
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    venue_id INT,
    organizer_id INT,
    event_date DATE,
    start_time TIME,
    end_time TIME,
    category VARCHAR(100),
    max_capacity INT,
    price DECIMAL(10,2),
    status ENUM('draft','published','cancelled'),
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    email VARCHAR(255),
    password VARCHAR(32),  -- MD5 hash (32 chars) 😱
    role ENUM('user','organizer','admin'),
    name VARCHAR(255),
    phone VARCHAR(20),
    created_at TIMESTAMP
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    ticket_type VARCHAR(50),
    price DECIMAL(10,2),
    purchase_date TIMESTAMP,
    payment_status ENUM('pending','completed','refunded'),
    paypal_txn_id VARCHAR(100),
    qr_code VARCHAR(255)
);
```

### Anti-Patterns Hall of Shame 😱

```php
<?php
// SQL Injection Paradise
$id = $_GET['id'];
$query = "SELECT * FROM events WHERE id = $id";  // 💀

// MD5 Password "Security"
$password = md5($_POST['password']);  // 🔓 Please don't

// Global State Everywhere
include 'includes/functions.php';
$user = get_current_user();  // From the global 2000-line file

// Mixed HTML and PHP
echo "<div class='event'>";
echo "<h2>" . $row['title'] . "</h2>";  // XSS waiting to happen
echo "</div>";

// Session Authentication
if ($_SESSION['logged_in'] != true) {
    header('Location: login.php');
}

// Error Display in Production
ini_set('display_errors', 1);  // Show all the secrets! 🙈
?>
```

---

## 🎯 TARGET ARCHITECTURE (The Modern Era)

### Python Stack 2025 🚀

| Component | Technology | Benefits |
|-----------|-----------|----------|
| **Language** | Python 3.12 | 🐍 Type hints, async/await, modern syntax |
| **Framework** | FastAPI / Flask | ⚡ Auto docs, async support, dependency injection |
| **ORM** | SQLAlchemy 2.0 | 🔮 Async sessions, type-safe models |
| **Database** | PostgreSQL on Azure | 🐘 JSONB, full-text search, cloud-managed |
| **Auth** | OAuth2 + JWT | 🔐 Stateless, secure tokens |
| **Password Hash** | bcrypt via passlib | 🔒 Industry standard, salted hashing |
| **API Docs** | OpenAPI/Swagger | 📚 Auto-generated, interactive |
| **Validation** | Pydantic | ✅ Type-safe request/response schemas |
| **Email** | Azure Communication | 📧 Cloud-native, scalable |
| **Payments** | Stripe API | 💳 Modern, webhook-based |
| **File Storage** | Azure Blob Storage | ☁️ CDN-backed, geo-redundant |
| **Hosting** | Azure Container Apps | 🐳 Serverless containers, auto-scale |

### Modern Code Patterns ✨

```python
# Type-safe Pydantic models
from pydantic import BaseModel, EmailStr, validator

class EventCreate(BaseModel):
    title: str
    description: str
    venue_id: int
    event_date: date
    max_capacity: int
    price: Decimal

    @validator('price')
    def price_must_be_positive(cls, v):
        if v < 0:
            raise ValueError('Price must be positive')
        return v

# Async database queries with SQLAlchemy
async def get_event(event_id: int, db: AsyncSession) -> Event:
    stmt = select(Event).where(Event.id == event_id)
    result = await db.execute(stmt)
    return result.scalar_one_or_none()

# JWT Authentication with FastAPI Security
from fastapi.security import OAuth2PasswordBearer

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")

async def get_current_user(token: str = Depends(oauth2_scheme)) -> User:
    payload = jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
    user_id = payload.get("sub")
    return await get_user_by_id(user_id)

# Dependency Injection
@router.post("/events", response_model=EventResponse)
async def create_event(
    event: EventCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_user)
):
    return await event_service.create(db, event, current_user)

# Auto-generated OpenAPI docs (FastAPI magic!)
app = FastAPI(
    title="CityPulse Events API",
    description="Modern event management platform",
    version="2.0.0"
)
# Visit /docs for interactive Swagger UI! 📚
```

---

## 🧪 LAB WALKTHROUGH WITH COPILOT CLI

### Step 1: Explore the Legacy App 🕵️

```bash
# Start the legacy PHP application
git checkout legacy
docker-compose up -d

# 🔥 APACHE STARTING...
# 🐬 MYSQL CONNECTED...  
# 🐘 PHP PARSED <?php...

# Use GitHub Copilot CLI to explore
gh copilot suggest "show me the event detail page"
gh copilot suggest "find all SQL queries in the codebase"
gh copilot suggest "identify security vulnerabilities"
```

**Things to notice:**
- 💀 SQL injection in `events/detail.php`
- 🔓 MD5 passwords in `includes/auth.php`
- 🍝 2000-line `includes/functions.php`
- 🎯 No input validation anywhere
- 📁 File uploads with no security checks

### Step 2: Database Migration 🗄️

```bash
# Switch to the database migration branch
git checkout step-1-database-migration

# Convert MySQL to PostgreSQL
gh copilot suggest "convert MySQL schema to PostgreSQL"

# Key changes:
# - AUTO_INCREMENT → SERIAL
# - ENUM → VARCHAR with CHECK constraints
# - TIMESTAMP → TIMESTAMPTZ
# - Add proper indexes and foreign keys
```

### Step 3: Set Up FastAPI Project 🐍

```bash
git checkout step-2-api-layer

# Initialize Python project with Poetry
poetry init
poetry add fastapi uvicorn sqlalchemy asyncpg pydantic

# Create project structure
gh copilot suggest "create FastAPI project structure for event management"

# PYTHON IMPORTED 🐍
# FASTAPI INITIALIZED ⚡
```

### Step 4: Build API Routes 🛤️

```bash
# Translate PHP endpoints to FastAPI routes
gh copilot suggest "convert events/list.php to FastAPI endpoint"
gh copilot suggest "create Pydantic schemas for event models"

# Watch OpenAPI docs auto-generate!
# Visit http://localhost:8000/docs
```

### Step 5: Implement Authentication 🔐

```bash
git checkout step-3-auth-and-security

# Replace sessions with JWT
gh copilot suggest "implement JWT authentication with FastAPI"
gh copilot suggest "migrate MD5 passwords to bcrypt"

# JWT AUTHENTICATED 🔐
# BCRYPT HASHED 🔒
```

### Step 6: Stripe Integration 💳

```bash
git checkout step-4-payment-and-services

# Replace PayPal IPN with Stripe
gh copilot suggest "integrate Stripe Checkout for ticket purchases"
gh copilot suggest "set up Stripe webhook handler"

# STRIPE CONNECTED 💳
# WEBHOOKS CONFIGURED 🪝
```

### Step 7: Azure Services ☁️

```bash
# Add cloud services
gh copilot suggest "integrate Azure Blob Storage for file uploads"
gh copilot suggest "set up Azure Communication Services for email"

# AZURE CONNECTED ☁️
# BLOB STORAGE READY 📦
```

### Step 8: Deploy to Azure 🚀

```bash
git checkout step-5-deploy

# Containerize and deploy
gh copilot suggest "create Dockerfile for FastAPI app"
gh copilot suggest "deploy to Azure Container Apps"

# DOCKER BUILT 🐳
# AZURE DEPLOYED ☁️
# LIVE IN PRODUCTION 🎉
```

---

## ⏱️ DURATION

**Total Lab Time:** 4–6 hours

| Phase | Duration | What You'll Do |
|-------|----------|----------------|
| 🕵️ **Legacy Exploration** | 30 mins | Run PHP app, identify anti-patterns |
| 🗄️ **Database Migration** | 45 mins | MySQL → PostgreSQL schema conversion |
| 🐍 **FastAPI Setup** | 45 mins | Project structure, dependencies, ORM |
| 🛤️ **API Development** | 90 mins | Build routes, models, schemas |
| 🔐 **Auth & Security** | 60 mins | JWT, bcrypt, input validation |
| 💳 **Integrations** | 60 mins | Stripe, Azure services |
| 🚀 **Deployment** | 45 mins | Docker, Azure Container Apps |
| 🧪 **Testing** | 30 mins | Pytest, API testing |

---

## 📚 RESOURCES

### Official Documentation

- 📖 [FastAPI Documentation](https://fastapi.tiangolo.com/) — Modern Python web framework
- 📖 [Flask Documentation](https://flask.palletsprojects.com/) — Classic Python web framework
- 📖 [SQLAlchemy 2.0](https://docs.sqlalchemy.org/) — Python SQL toolkit and ORM
- 📖 [Pydantic](https://docs.pydantic.dev/) — Data validation using Python type hints
- 📖 [Azure Container Apps](https://learn.microsoft.com/azure/container-apps/) — Serverless containers

### Migration Guides

- 🔄 [PHP to Python Translation Guide](./docs/php-to-python.md)
- 🔒 [Security Remediation Checklist](./docs/security-fixes.md)
- ⚡ [FastAPI vs Flask Comparison](./docs/framework-comparison.md)
- 🗄️ [MySQL to PostgreSQL Migration](./docs/database-migration.md)

### Helpful Tools

- 🤖 [GitHub Copilot CLI](https://githubnext.com/projects/copilot-cli) — AI coding assistant
- 🐳 [Docker Desktop](https://www.docker.com/products/docker-desktop) — Containerization platform
- 📮 [Postman](https://www.postman.com/) — API testing (or use Swagger UI!)
- 🧪 [pytest](https://docs.pytest.org/) — Python testing framework

---

## 🎮 ACHIEVEMENT UNLOCKED

Complete this lab to earn:

- ✅ **LAMP Stack Archaeologist** — Successfully ran a legacy PHP application
- ✅ **Python Modernizer** — Migrated procedural PHP to modern Python
- ✅ **Security Fixer** — Remediated MD5 passwords and SQL injection
- ✅ **API Architect** — Built auto-documented REST APIs
- ✅ **Cloud Native Developer** — Deployed to Azure Container Apps

---

## 🤝 CONTRIBUTING

Found a bug? Want to improve the lab? Contributions welcome!

```bash
# Fork the repository
gh repo fork EmeaAppGbb/appmodlab-php-legacy-to-python-flask-fastapi

# Create a feature branch
git checkout -b feature/amazing-improvement

# Make your changes and commit
git commit -m "Add amazing improvement"

# Push and create a pull request
git push origin feature/amazing-improvement
gh pr create
```

---

## 📜 LICENSE

This project is licensed under the MIT License — see [LICENSE](LICENSE) for details.

---

## 🙏 ACKNOWLEDGMENTS

Built with 💜 by the **EmeaAppGbb Team**

Special thanks to:
- The PHP community for building the web (circa 2005) 🐘
- The Python community for modernizing it (2025 edition) 🐍
- Everyone who survived the LAMP stack era 🔥
- Developers who still maintain legacy PHP apps (you're heroes!) 🦸

---

<div align="center">

```
▀█▀ █ █ █▀█ █▄ █ █▄▀   █ █ █▀█ █ █   █▀▀ █▀█ █▀█ 
 █  █▀█ █▀█ █ ▀█ █ █   ▀▄▀ █ █ █ █   █▀  █ █ █▀▄ 
 ▀  ▀ ▀ ▀ ▀ ▀  ▀ ▀ ▀    ▀  ▀▀▀ ▀▀▀   ▀   ▀▀▀ ▀ ▀ 
 
 █▀▄▀█ █ █▀▀ █▀█ █▀█ ▀█▀ █ █▄ █ █▀▀   ▐ 
 █ ▀ █ █ █▄█ █▀▄ █▀█  █  █ █ ▀█ █▄█   ▐ 
```

**Happy Migrating! May your SQL be parameterized and your passwords be hashed! 🚀**

</div>
