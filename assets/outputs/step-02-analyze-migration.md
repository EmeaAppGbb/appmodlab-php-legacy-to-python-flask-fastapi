# CityPulse Events — PHP-to-Python/FastAPI Migration Analysis

> **Source:** PHP 5.6 procedural · mysqli · jQuery 1.12.4  
> **Target:** Python 3.12 · FastAPI · SQLAlchemy · Pydantic · Jinja2  
> **Analysis Date:** 2026-04-16

---

## 1. Pattern Migration Map

### 1.1 Database: `mysqli` → SQLAlchemy ORM

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)` | `create_engine("mysql+pymysql://...")` via SQLAlchemy |
| Global `$db` variable passed via `global $db` | SQLAlchemy `SessionLocal` factory + FastAPI `Depends(get_db)` |
| `query($sql)` with raw string interpolation | SQLAlchemy ORM queries: `db.query(Model).filter(...)` |
| `fetch_assoc($result)` → associative array | SQLAlchemy model instance (attribute access) |
| `fetch_all($result)` → array of assoc arrays | `db.query(Model).all()` → list of model instances |
| `escape_string($str)` (defined but unused) | Parameterised queries by default (SQLAlchemy binds) |
| `get_last_insert_id()` | `db.flush()` → `instance.id` (auto-populated by ORM) |
| `die("Query failed: " . mysqli_error($db))` | SQLAlchemy exceptions + FastAPI `HTTPException` |
| Raw SQL `JOIN` chains | SQLAlchemy `relationship()` + eager/lazy loading |
| `mysqli_real_escape_string()` | Not needed — ORM uses parameterised queries |

**Example transformation:**

```php
// PHP — includes/functions.php:18-29
function get_event($event_id) {
    $sql = "SELECT e.*, v.name as venue_name ...
            WHERE e.id = $event_id";         // SQL injection!
    $result = query($sql);
    return fetch_assoc($result);
}
```

```python
# Python — app/crud/events.py
def get_event(db: Session, event_id: int) -> Event | None:
    return (
        db.query(Event)
        .options(joinedload(Event.venue), joinedload(Event.category))
        .filter(Event.id == event_id)
        .first()
    )
```

**SQLAlchemy model for `events` table:**

```python
# app/models/event.py
class Event(Base):
    __tablename__ = "events"

    id: Mapped[int] = mapped_column(primary_key=True)
    title: Mapped[str] = mapped_column(String(200))
    description: Mapped[str] = mapped_column(Text)
    venue_id: Mapped[int | None] = mapped_column(ForeignKey("venues.id"))
    organizer_id: Mapped[int] = mapped_column(ForeignKey("organizers.id"))
    event_date: Mapped[date]
    start_time: Mapped[time]
    end_time: Mapped[time]
    category: Mapped[int | None] = mapped_column(ForeignKey("categories.id"))
    max_capacity: Mapped[int]
    price: Mapped[Decimal] = mapped_column(Numeric(10, 2))
    status: Mapped[str] = mapped_column(String(20), default="active")
    image_path: Mapped[str | None] = mapped_column(String(255))
    created_at: Mapped[datetime] = mapped_column(default=func.now())

    venue: Mapped["Venue"] = relationship(back_populates="events")
    organizer: Mapped["Organizer"] = relationship(back_populates="events")
    tickets: Mapped[list["Ticket"]] = relationship(back_populates="event")
    reviews: Mapped[list["Review"]] = relationship(back_populates="event")
```

---

### 1.2 Authentication: `MD5` → `bcrypt`

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `md5($password)` | `passlib.hash.bcrypt.hash(password)` (or `bcrypt.hashpw()`) |
| `md5($password)` comparison in SQL query | `bcrypt.verify(password, stored_hash)` in Python |
| 32-char MD5 hex string in DB | 60-char bcrypt hash string in DB (extend `VARCHAR(32)` → `VARCHAR(255)`) |
| No salt | bcrypt includes salt automatically |
| Plain-text comparison via SQL `WHERE password = '$hash'` | Fetch user by username, then `verify()` in app code |

**Example transformation:**

```php
// PHP — includes/auth.php:45-62
function login_user($username, $password) {
    $password_hash = md5($password);
    $sql = "SELECT * FROM users WHERE username = '$username'
            AND password = '$password_hash'";
    // ...
}
```

```python
# Python — app/services/auth.py
from passlib.context import CryptContext

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

def authenticate_user(db: Session, username: str, password: str) -> User | None:
    user = db.query(User).filter(User.username == username).first()
    if not user or not pwd_context.verify(password, user.password):
        return None
    return user
```

**Migration note:** Existing MD5 hashes in the database must be migrated. Options:
1. **Bulk re-hash** via a one-time migration script (requires knowing plain-text — not feasible).
2. **Dual-hash strategy:** On login, if hash is 32-char MD5, verify with MD5, then re-hash with bcrypt and update the row. Mark migrated users. Eventually drop MD5 support.

---

### 1.3 Sessions: `$_SESSION` → JWT (JSON Web Tokens)

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `session_start()` | Not needed — JWT is stateless |
| `$_SESSION['user_id'] = $row['id']` | Encode `user_id` in JWT payload (`sub` claim) |
| `$_SESSION['role'] = $row['role']` | Encode `role` in JWT payload (custom claim) |
| `is_logged_in()` → check `$_SESSION['user_id']` | FastAPI dependency: `get_current_user(token: str = Depends(oauth2_scheme))` |
| `require_login()` → redirect to login page | `Depends(get_current_user)` → raises `HTTPException(401)` |
| `require_admin()` → check `$_SESSION['role']` | `Depends(require_role("admin"))` → raises `HTTPException(403)` |
| `require_organizer()` → check role | `Depends(require_role("organizer"))` → raises `HTTPException(403)` |
| `session_destroy()` (logout) | Client deletes token; optionally use token blocklist/revocation |
| `$_SESSION['message']` / `$_SESSION['message_type']` (flash) | Return message in JSON response body; frontend handles display |

**Example transformation:**

```php
// PHP — includes/auth.php:8-15
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['message'] = 'Please login to access this page';
        header('Location: /admin/login.php');
        exit;
    }
}
```

```python
# Python — app/dependencies/auth.py
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import JWTError, jwt

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/api/v1/auth/login")

async def get_current_user(
    token: str = Depends(oauth2_scheme),
    db: Session = Depends(get_db),
) -> User:
    try:
        payload = jwt.decode(token, settings.SECRET_KEY, algorithms=["HS256"])
        user_id: int = payload.get("sub")
    except JWTError:
        raise HTTPException(status_code=401, detail="Invalid token")
    user = db.query(User).get(user_id)
    if not user:
        raise HTTPException(status_code=401, detail="User not found")
    return user

def require_role(*roles: str):
    async def role_checker(user: User = Depends(get_current_user)):
        if user.role not in roles:
            raise HTTPException(status_code=403, detail="Insufficient permissions")
        return user
    return role_checker
```

**JWT token structure:**

```json
{
  "sub": 1,
  "username": "admin",
  "role": "admin",
  "exp": 1714329600
}
```

**Role hierarchy preserved:** `require_role("organizer", "admin")` replaces the PHP pattern where `is_organizer()` returns `true` for admins.

---

### 1.4 Templates: PHP `include` → Jinja2

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `include 'includes/header.php'` | Jinja2 `{% extends "base.html" %}` + `{% block content %}` |
| `include 'includes/footer.php'` | Included in `base.html` template automatically |
| `$page_title` variable set before include | Jinja2 `{% block title %}Page Name{% endblock %}` |
| Inline `<?php echo $var; ?>` | Jinja2 `{{ var }}` (auto-escaped by default — fixes XSS) |
| `<?php foreach ($items as $item): ?>` | Jinja2 `{% for item in items %}` |
| `<?php if (condition): ?>` | Jinja2 `{% if condition %}` |
| `$_SESSION['message']` flash messages | Jinja2 `{{ get_flashed_messages() }}` or pass via template context |
| `<?php echo SITE_URL; ?>` | Jinja2 `{{ url_for('route_name') }}` |
| Mixed PHP/HTML in every file | Clean separation: route handler returns `templates.TemplateResponse(...)` |

**Example transformation:**

```php
<!-- PHP — includes/header.php + index.php -->
<?php $page_title = 'Home'; include 'includes/header.php'; ?>
<h2><?php echo $event['title']; ?></h2>
<?php include 'includes/footer.php'; ?>
```

```html
<!-- Jinja2 — templates/base.html -->
<!DOCTYPE html>
<html>
<head><title>{% block title %}{% endblock %} - CityPulse Events</title></head>
<body>
  {% include "partials/nav.html" %}
  <main>{% block content %}{% endblock %}</main>
  {% include "partials/footer.html" %}
</body>
</html>

<!-- templates/index.html -->
{% extends "base.html" %}
{% block title %}Home{% endblock %}
{% block content %}
  <h2>{{ event.title }}</h2>
{% endblock %}
```

**Key improvement:** Jinja2's auto-escaping eliminates all XSS vulnerabilities present in the PHP version where raw `echo` is used throughout.

---

### 1.5 Request Data: `$_GET` / `$_POST` → Pydantic Models

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `$_POST['username']` | Pydantic `BaseModel` field with type validation |
| `$_GET['id']` | FastAPI path/query parameter: `event_id: int` |
| `$_GET['q']` | `Query(default=None)` parameter |
| `$_GET['page']` / `$_GET['category']` | `Query(default=1, ge=1)` with validation |
| `$_FILES['image']` | `UploadFile` parameter with MIME validation |
| No input validation anywhere | Pydantic validates all input automatically |
| No type coercion | Pydantic auto-coerces `str → int`, `str → date`, etc. |

**Example transformation:**

```php
// PHP — events/create.php:14-33
$title = $_POST['title'];          // No validation
$description = $_POST['description'];
$venue_id = $_POST['venue_id'];    // Could be anything
$price = $_POST['price'];          // Could be negative
```

```python
# Python — app/schemas/event.py
from pydantic import BaseModel, Field
from datetime import date, time
from decimal import Decimal

class EventCreate(BaseModel):
    title: str = Field(..., min_length=1, max_length=200)
    description: str = Field(..., min_length=1)
    venue_id: int = Field(..., gt=0)
    event_date: date
    start_time: time
    end_time: time
    category: int = Field(..., gt=0)
    max_capacity: int = Field(..., gt=0)
    price: Decimal = Field(..., ge=0, decimal_places=2)

class EventUpdate(EventCreate):
    pass  # Same validation for updates

class EventResponse(BaseModel):
    id: int
    title: str
    description: str
    venue_name: str | None = None
    city: str | None = None
    event_date: date
    start_time: time
    end_time: time
    category_name: str | None = None
    price: Decimal
    status: str
    tickets_available: int | None = None
    avg_rating: float | None = None

    model_config = ConfigDict(from_attributes=True)
```

**Pydantic schemas for all form inputs:**

| PHP Form / Input | Pydantic Schema |
|---|---|
| `register.php` POST fields | `UserCreate(username, email, password, name, phone)` |
| `admin/login.php` POST fields | `LoginRequest(username, password)` |
| `events/create.php` POST fields | `EventCreate(title, description, venue_id, ...)` |
| `events/edit.php` POST fields | `EventUpdate(...)` |
| `events/detail.php` review POST | `ReviewCreate(rating: int = Field(ge=1, le=5), comment: str)` |
| `tickets/purchase.php` POST fields | `TicketPurchase(ticket_type: Literal["general","vip"], quantity: int = Field(gt=0))` |
| `organizers/settings.php` POST | `OrganizerSettingsUpdate(company_name, description, website)` |
| `organizers/reports.php` GET params | `ReportFilter(start_date: date, end_date: date)` |
| `events/list.php` GET params | `EventListQuery(category: int \| None, page: int = 1)` |
| `events/search.php` GET param | `SearchQuery(q: str = Query(min_length=1))` |

---

### 1.6 Configuration: `config.php` → `.env` + `pydantic-settings`

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `define('DB_HOST', getenv('DB_HOST') ?: 'db')` | `DB_HOST` in `.env`, loaded by `pydantic-settings` |
| `define('DB_USER', 'citypulse_user')` (hardcoded) | `DB_USER` in `.env` (never committed) |
| `define('DB_PASS', 'citypulse123')` (hardcoded) | `DB_PASS` in `.env` (never committed) |
| `define('DB_NAME', 'citypulse_events')` | `DB_NAME` in `.env` |
| `define('SITE_NAME', 'CityPulse Events')` | `SITE_NAME` in Settings class with default |
| `define('SITE_URL', 'http://localhost:8080')` | `SITE_URL` in `.env` |
| `define('UPLOAD_DIR', 'uploads/')` | `UPLOAD_DIR` in Settings with default |
| `define('MAX_UPLOAD_SIZE', 5242880)` | `MAX_UPLOAD_SIZE` in Settings with default |
| `define('PAYPAL_EMAIL', ...)` (hardcoded) | `PAYPAL_EMAIL` in `.env` |
| `define('PAYPAL_SANDBOX', true)` | `PAYPAL_SANDBOX` in `.env` |
| `ini_set('session.cookie_httponly', 0)` | Not applicable (JWT-based, no cookies) |
| `error_reporting(E_ALL)` | Python `logging` module with configurable level |

**Example transformation:**

```php
// PHP — config.php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', 'citypulse_user');
define('DB_PASS', 'citypulse123');
define('PAYPAL_EMAIL', 'merchant@citypulse.com');
```

```python
# Python — app/core/config.py
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # Database
    DB_HOST: str = "db"
    DB_PORT: int = 3306
    DB_USER: str
    DB_PASS: str
    DB_NAME: str = "citypulse_events"

    # Application
    SITE_NAME: str = "CityPulse Events"
    SITE_URL: str = "http://localhost:8000"
    UPLOAD_DIR: str = "uploads"
    MAX_UPLOAD_SIZE: int = 5_242_880

    # Security
    SECRET_KEY: str          # JWT signing key — required, no default
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 30

    # PayPal
    PAYPAL_EMAIL: str = ""
    PAYPAL_SANDBOX: bool = True

    @property
    def DATABASE_URL(self) -> str:
        return f"mysql+pymysql://{self.DB_USER}:{self.DB_PASS}@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"

    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8")

settings = Settings()
```

```ini
# .env (not committed — listed in .gitignore)
DB_HOST=db
DB_USER=citypulse_user
DB_PASS=citypulse123
DB_NAME=citypulse_events
SECRET_KEY=change-me-in-production
PAYPAL_EMAIL=merchant@citypulse.com
PAYPAL_SANDBOX=true
```

---

### 1.7 Routing: File-Based Routing → `APIRouter`

| PHP Pattern | Python/FastAPI Equivalent |
|---|---|
| `/index.php` → homepage | `@router.get("/")` in `app/routers/pages.py` |
| `/events/list.php` → event listing | `@router.get("/events")` in `app/routers/events.py` |
| `/events/detail.php?id=N` | `@router.get("/events/{event_id}")` |
| `/events/search.php?q=term` | `@router.get("/events/search")` |
| `/events/create.php` GET+POST | `@router.get("/events/create")` + `@router.post("/events")` |
| `/events/edit.php?id=N` GET+POST | `@router.get("/events/{id}/edit")` + `@router.put("/events/{id}")` |
| `/admin/login.php` GET+POST | `@router.get("/auth/login")` + `@router.post("/api/v1/auth/login")` |
| `/register.php` GET+POST | `@router.get("/auth/register")` + `@router.post("/api/v1/auth/register")` |
| `/tickets/purchase.php?event_id=N` | `@router.post("/api/v1/events/{event_id}/tickets")` |
| `/admin/events.php?delete=N` (GET!) | `@router.delete("/api/v1/admin/events/{event_id}")` (proper DELETE) |
| `require_once` chain in every file | `app.include_router(router, prefix=...)` |

**Router organisation:**

```python
# app/main.py
from fastapi import FastAPI
from app.routers import auth, events, tickets, organizers, admin, pages

app = FastAPI(title="CityPulse Events")

# API routes (JSON responses)
app.include_router(auth.router,       prefix="/api/v1/auth",       tags=["auth"])
app.include_router(events.router,     prefix="/api/v1/events",     tags=["events"])
app.include_router(tickets.router,    prefix="/api/v1/tickets",    tags=["tickets"])
app.include_router(organizers.router, prefix="/api/v1/organizers", tags=["organizers"])
app.include_router(admin.router,      prefix="/api/v1/admin",      tags=["admin"])

# Page routes (HTML/Jinja2 responses)
app.include_router(pages.router, tags=["pages"])
```

**PHP file-based routing to FastAPI RESTful mapping:**

| PHP URL | HTTP Method | FastAPI Route | Handler |
|---|---|---|---|
| `/` | GET | `/` | `pages.homepage` |
| `/register.php` | GET | `/auth/register` | `pages.register_page` |
| `/register.php` | POST | `/api/v1/auth/register` | `auth.register` |
| `/admin/login.php` | GET | `/auth/login` | `pages.login_page` |
| `/admin/login.php` | POST | `/api/v1/auth/login` | `auth.login` |
| `/includes/logout.php` | GET | `/api/v1/auth/logout` | `auth.logout` |
| `/events/list.php` | GET | `/events` | `pages.event_list` |
| `/events/detail.php?id=N` | GET | `/events/{id}` | `pages.event_detail` |
| `/events/search.php?q=` | GET | `/events/search` | `pages.event_search` |
| `/events/create.php` | GET | `/events/create` | `pages.event_create_page` |
| `/events/create.php` | POST | `/api/v1/events` | `events.create_event` |
| `/events/edit.php?id=N` | GET | `/events/{id}/edit` | `pages.event_edit_page` |
| `/events/edit.php?id=N` | POST | `/api/v1/events/{id}` | `events.update_event` |
| `/tickets/purchase.php?event_id=N` | GET | `/events/{id}/purchase` | `pages.purchase_page` |
| `/tickets/purchase.php` | POST | `/api/v1/events/{id}/tickets` | `tickets.purchase` |
| `/tickets/checkout.php?ticket_id=N` | GET | `/tickets/{id}/checkout` | `pages.checkout_page` |
| `/tickets/confirm.php?ticket_id=N` | GET | `/tickets/{id}/confirm` | `pages.confirm_page` |
| `/tickets/confirm.php` | POST | `/api/v1/tickets/ipn` | `tickets.paypal_ipn` |
| `/tickets/my-tickets.php` | GET | `/my-tickets` | `pages.my_tickets` |
| `/organizers/dashboard.php` | GET | `/organizer/dashboard` | `pages.organizer_dashboard` |
| `/organizers/reports.php` | GET | `/organizer/reports` | `pages.organizer_reports` |
| `/organizers/settings.php` | GET | `/organizer/settings` | `pages.organizer_settings` |
| `/organizers/settings.php` | POST | `/api/v1/organizer/settings` | `organizers.update_settings` |
| `/admin/events.php` | GET | `/admin/events` | `pages.admin_events` |
| `/admin/events.php?delete=N` | GET→DELETE | `/api/v1/admin/events/{id}` | `admin.cancel_event` |
| `/admin/users.php` | GET | `/admin/users` | `pages.admin_users` |

---

### 1.8 Raw SQL → ORM Queries

| PHP Function (functions.php) | SQLAlchemy ORM Equivalent |
|---|---|
| `get_all_events($limit)` | `db.query(Event).options(joinedload(...)).order_by(Event.event_date.desc()).limit(limit).all()` |
| `get_event($event_id)` | `db.query(Event).options(joinedload(Event.venue)).filter(Event.id == event_id).first()` |
| `get_upcoming_events()` | `db.query(Event).filter(Event.event_date >= date.today(), Event.status == "active").order_by(Event.event_date).limit(10).all()` |
| `search_events($term)` | `db.query(Event).filter(Event.title.ilike(f"%{term}%") \| Event.description.ilike(f"%{term}%")).all()` |
| `get_events_by_category($id)` | `db.query(Event).filter(Event.category == id, Event.status == "active").all()` |
| `create_event(...)` | `event = Event(**data); db.add(event); db.commit()` |
| `update_event(...)` | `db.query(Event).filter(Event.id == id).update(data); db.commit()` |
| `delete_event($id)` | `event.status = "cancelled"; db.commit()` (soft delete) |
| `get_tickets_sold($event_id)` | `db.query(func.count(Ticket.id)).filter(Ticket.event_id == id, Ticket.payment_status == "completed").scalar()` |
| `get_all_venues()` | `db.query(Venue).order_by(Venue.name).all()` |
| `get_user_tickets($user_id)` | `db.query(Ticket).options(joinedload(Ticket.event)).filter(Ticket.user_id == id).all()` |
| `create_ticket(...)` | `ticket = Ticket(**data); db.add(ticket); db.commit()` |
| `update_ticket_payment(...)` | `ticket.payment_status = status; ticket.paypal_txn_id = txn; db.commit()` |
| `get_all_categories()` | `db.query(Category).order_by(Category.name).all()` |
| `get_organizer_by_user($uid)` | `db.query(Organizer).filter(Organizer.user_id == uid).first()` |
| `get_organizer_events($oid)` | `db.query(Event).filter(Event.organizer_id == oid).order_by(Event.event_date.desc()).all()` |
| `get_organizer_revenue($oid)` | `db.query(func.sum(Ticket.price)).join(Event).filter(...).scalar()` |
| `get_event_reviews($eid)` | `db.query(Review).options(joinedload(Review.user)).filter(Review.event_id == eid).all()` |
| `create_review(...)` | `review = Review(**data); db.add(review); db.commit()` |
| `get_event_average_rating($eid)` | `db.query(func.avg(Review.rating)).filter(Review.event_id == eid).scalar()` |

**Key ORM benefits over raw SQL:**
- **SQL injection eliminated** — all queries use bound parameters.
- **Type safety** — model fields enforce column types at the Python level.
- **Relationship traversal** — `event.venue.name` instead of manual JOINs everywhere.
- **Migrations** — Alembic auto-generates schema migration scripts from model changes.

---

## 2. PHP File → Python Module Mapping

### 2.1 Infrastructure / Configuration

| PHP File | Python Module | Purpose | Notes |
|---|---|---|---|
| `config.php` | `app/core/config.py` + `.env` | App configuration | `define()` constants → `pydantic-settings` `Settings` class; secrets in `.env` |
| `includes/db.php` | `app/core/database.py` | DB connection & session | `mysqli_connect` → `create_engine()` + `SessionLocal` factory; `get_db()` dependency |
| `includes/auth.php` | `app/dependencies/auth.py` | Auth guards & login | `$_SESSION` checks → JWT decode + FastAPI dependencies |
| `includes/functions.php` | Split into multiple modules (see below) | Business logic | Monolithic 360-line file decomposed by domain |
| `includes/header.php` | `templates/base.html` | HTML head + nav | Jinja2 base template with `{% block %}` system |
| `includes/footer.php` | `templates/base.html` | HTML footer | Merged into base template |
| `includes/logout.php` | `app/routers/auth.py::logout()` | Session destroy | `POST /api/v1/auth/logout` — client deletes JWT |

### 2.2 `functions.php` Decomposition

The monolithic `includes/functions.php` (360 lines) is split into focused modules:

| PHP Function Group | Python Module | Functions Migrated |
|---|---|---|
| Event functions (lines 7–90) | `app/crud/events.py` | `get_all_events`, `get_event`, `get_upcoming_events`, `search_events`, `get_events_by_category`, `create_event`, `update_event`, `delete_event`, `get_tickets_sold`, `is_event_sold_out` |
| Venue functions (lines 107–124) | `app/crud/venues.py` | `get_all_venues`, `get_venue`, `create_venue` |
| Ticket functions (lines 128–160) | `app/crud/tickets.py` | `get_user_tickets`, `create_ticket`, `update_ticket_payment`, `get_ticket`, `generate_qr_code` |
| Category functions (lines 164–174) | `app/crud/categories.py` | `get_all_categories`, `get_category` |
| Organizer functions (lines 178–209) | `app/crud/organizers.py` | `get_organizer_by_user`, `get_organizer_events`, `create_organizer`, `get_organizer_revenue` |
| Review functions (lines 213–235) | `app/crud/reviews.py` | `get_event_reviews`, `create_review`, `get_event_average_rating` |
| File upload (lines 239–251) | `app/services/uploads.py` | `upload_event_image` — with MIME validation, size limits |
| Utility functions (lines 255–291) | `app/utils/formatting.py` | `format_currency`, `format_date`, `format_time`, `truncate_text` — Jinja2 filters |
| Email functions (lines 295–315) | `app/services/email.py` | `send_email`, `send_ticket_confirmation` — use `fastapi-mail` or SMTP |
| PayPal functions (lines 319–328) | `app/services/payment.py` | `verify_paypal_ipn`, `process_paypal_payment` — with actual verification |
| Pagination (lines 332–342) | `app/utils/pagination.py` | `paginate()` — or use FastAPI `Query(skip, limit)` pattern |
| Validation (lines 346–353) | Eliminated | Replaced entirely by Pydantic schema validation |

### 2.3 Page Files → Routers

| PHP File | Python Router | API Endpoints | Page Endpoints |
|---|---|---|---|
| `index.php` | `app/routers/pages.py` | — | `GET /` |
| `register.php` | `app/routers/auth.py` + `pages.py` | `POST /api/v1/auth/register` | `GET /auth/register` |
| `admin/login.php` | `app/routers/auth.py` + `pages.py` | `POST /api/v1/auth/login` | `GET /auth/login` |
| `events/list.php` | `app/routers/events.py` + `pages.py` | `GET /api/v1/events` | `GET /events` |
| `events/detail.php` | `app/routers/events.py` + `pages.py` | `GET /api/v1/events/{id}` | `GET /events/{id}` |
| `events/search.php` | `app/routers/events.py` + `pages.py` | `GET /api/v1/events/search` | `GET /events/search` |
| `events/create.php` | `app/routers/events.py` + `pages.py` | `POST /api/v1/events` | `GET /events/create` |
| `events/edit.php` | `app/routers/events.py` + `pages.py` | `PUT /api/v1/events/{id}` | `GET /events/{id}/edit` |
| `tickets/purchase.php` | `app/routers/tickets.py` + `pages.py` | `POST /api/v1/events/{id}/tickets` | `GET /events/{id}/purchase` |
| `tickets/checkout.php` | `app/routers/tickets.py` + `pages.py` | — | `GET /tickets/{id}/checkout` |
| `tickets/confirm.php` | `app/routers/tickets.py` + `pages.py` | `POST /api/v1/tickets/ipn` | `GET /tickets/{id}/confirm` |
| `tickets/my-tickets.php` | `app/routers/tickets.py` + `pages.py` | `GET /api/v1/tickets/me` | `GET /my-tickets` |
| `organizers/dashboard.php` | `app/routers/organizers.py` + `pages.py` | `GET /api/v1/organizer/dashboard` | `GET /organizer/dashboard` |
| `organizers/reports.php` | `app/routers/organizers.py` + `pages.py` | `GET /api/v1/organizer/reports` | `GET /organizer/reports` |
| `organizers/settings.php` | `app/routers/organizers.py` + `pages.py` | `PUT /api/v1/organizer/settings` | `GET /organizer/settings` |
| `admin/events.php` | `app/routers/admin.py` + `pages.py` | `DELETE /api/v1/admin/events/{id}` | `GET /admin/events` |
| `admin/users.php` | `app/routers/admin.py` + `pages.py` | `GET /api/v1/admin/users` | `GET /admin/users` |

---

## 3. Proposed Python Project Structure

```
citypulse-fastapi/
├── .env                          # Secrets (gitignored)
├── .env.example                  # Template for .env
├── requirements.txt
├── alembic.ini                   # DB migration config
├── alembic/                      # Migration scripts
│   └── versions/
│
├── app/
│   ├── __init__.py
│   ├── main.py                   # FastAPI app factory, router registration
│   │
│   ├── core/
│   │   ├── config.py             # pydantic-settings Settings class
│   │   ├── database.py           # SQLAlchemy engine, SessionLocal, Base
│   │   └── security.py           # JWT create/decode, password hashing
│   │
│   ├── models/                   # SQLAlchemy ORM models (1 file per table)
│   │   ├── __init__.py           # Re-export all models
│   │   ├── user.py
│   │   ├── venue.py
│   │   ├── category.py
│   │   ├── organizer.py
│   │   ├── event.py
│   │   ├── ticket.py
│   │   └── review.py
│   │
│   ├── schemas/                  # Pydantic request/response schemas
│   │   ├── auth.py               # LoginRequest, TokenResponse, UserCreate
│   │   ├── event.py              # EventCreate, EventUpdate, EventResponse
│   │   ├── ticket.py             # TicketPurchase, TicketResponse
│   │   ├── review.py             # ReviewCreate, ReviewResponse
│   │   ├── organizer.py          # OrganizerSettings, DashboardResponse
│   │   └── common.py             # Pagination, MessageResponse
│   │
│   ├── crud/                     # Database operations (replaces functions.php)
│   │   ├── events.py
│   │   ├── venues.py
│   │   ├── tickets.py
│   │   ├── categories.py
│   │   ├── organizers.py
│   │   ├── reviews.py
│   │   └── users.py
│   │
│   ├── routers/                  # FastAPI route handlers (replaces PHP page files)
│   │   ├── auth.py               # /api/v1/auth/*
│   │   ├── events.py             # /api/v1/events/*
│   │   ├── tickets.py            # /api/v1/tickets/*
│   │   ├── organizers.py         # /api/v1/organizers/*
│   │   ├── admin.py              # /api/v1/admin/*
│   │   └── pages.py              # HTML page routes (Jinja2 templates)
│   │
│   ├── services/                 # Business logic services
│   │   ├── auth.py               # authenticate_user, create_token
│   │   ├── payment.py            # PayPal IPN verification
│   │   ├── email.py              # Email sending
│   │   └── uploads.py            # File upload with validation
│   │
│   ├── dependencies/             # FastAPI Depends() callables
│   │   ├── auth.py               # get_current_user, require_role
│   │   └── database.py           # get_db session dependency
│   │
│   └── utils/
│       ├── formatting.py         # format_currency, format_date (Jinja2 filters)
│       └── pagination.py         # Pagination helper
│
├── templates/                    # Jinja2 templates (replaces inline PHP/HTML)
│   ├── base.html
│   ├── partials/
│   │   ├── nav.html
│   │   └── footer.html
│   ├── index.html
│   ├── auth/
│   │   ├── login.html
│   │   └── register.html
│   ├── events/
│   │   ├── list.html
│   │   ├── detail.html
│   │   ├── search.html
│   │   ├── create.html
│   │   └── edit.html
│   ├── tickets/
│   │   ├── purchase.html
│   │   ├── checkout.html
│   │   ├── confirm.html
│   │   └── my_tickets.html
│   ├── organizer/
│   │   ├── dashboard.html
│   │   ├── reports.html
│   │   └── settings.html
│   └── admin/
│       ├── events.html
│       └── users.html
│
├── static/                       # Static assets (replaces css/, js/, uploads/)
│   ├── css/
│   ├── js/
│   └── uploads/
│
└── tests/
    ├── conftest.py
    ├── test_auth.py
    ├── test_events.py
    ├── test_tickets.py
    └── test_organizers.py
```

---

## 4. Security Fixes Summary

Every critical vulnerability from the PHP codebase is addressed by the migration:

| PHP Vulnerability | Severity | Python/FastAPI Fix |
|---|---|---|
| MD5 password hashing | 🔴 CRITICAL | bcrypt via `passlib` — salted, computationally expensive |
| SQL injection (25+ points) | 🔴 CRITICAL | SQLAlchemy ORM — parameterised queries everywhere |
| No CSRF protection | 🔴 CRITICAL | JWT-based auth (no cookies to forge); API uses `Authorization` header |
| Unrestricted file upload | 🔴 CRITICAL | MIME-type whitelist, extension whitelist, size limit in `uploads.py` |
| No input validation | 🟠 HIGH | Pydantic models validate all input with type checking and constraints |
| XSS (raw echo) | 🟠 HIGH | Jinja2 auto-escaping enabled by default |
| Insecure sessions | 🟠 HIGH | JWT tokens — no server-side session state to hijack |
| Hardcoded credentials | 🟡 MEDIUM | `.env` file + `pydantic-settings` — secrets never in source code |
| Verbose error display | 🟡 MEDIUM | FastAPI exception handlers — structured error responses, no stack traces |
| Broken PayPal IPN | 🟡 MEDIUM | Proper IPN signature verification in `payment.py` |
| Event delete via GET | 🟡 MEDIUM | `DELETE` HTTP method with auth dependency |
| No rate limiting | 🟡 MEDIUM | `slowapi` or custom middleware for rate limiting |

---

## 5. Key Python Dependencies

```
# requirements.txt
fastapi>=0.111.0
uvicorn[standard]>=0.30.0
sqlalchemy>=2.0
pymysql>=1.1.0
alembic>=1.13.0
pydantic>=2.7
pydantic-settings>=2.3
python-jose[cryptography]>=3.3.0
passlib[bcrypt]>=1.7.4
python-multipart>=0.0.9
jinja2>=3.1.4
python-dotenv>=1.0.1
httpx>=0.27.0              # For PayPal IPN verification
```
