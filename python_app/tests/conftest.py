"""Shared pytest fixtures for the CityPulse Events test suite."""

from datetime import date, time
from decimal import Decimal

import pytest
import pytest_asyncio
from httpx import ASGITransport, AsyncClient
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession, async_sessionmaker, create_async_engine
from sqlalchemy.pool import StaticPool

from python_app.auth import create_access_token, hash_password
from python_app.database import Base, get_db
from python_app.models import (
    Category,
    Event,
    EventStatus,
    Organizer,
    User,
    UserRole,
    Venue,
)

TEST_PASSWORD = "testpass123"


def auth_header(token: str) -> dict[str, str]:
    """Build an Authorization header dict from a bearer token."""
    return {"Authorization": f"Bearer {token}"}


@pytest_asyncio.fixture
async def db_engine():
    """Create an async in-memory SQLite engine for testing."""
    engine = create_async_engine(
        "sqlite+aiosqlite://",
        connect_args={"check_same_thread": False},
        poolclass=StaticPool,
    )
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)
    yield engine
    await engine.dispose()


@pytest_asyncio.fixture
async def db_session(db_engine):
    """Provide an async session bound to the test engine."""
    factory = async_sessionmaker(
        db_engine, class_=AsyncSession, expire_on_commit=False
    )
    async with factory() as session:
        yield session


@pytest_asyncio.fixture
async def client(db_engine):
    """Provide an httpx AsyncClient wired to the FastAPI app with DB override."""
    factory = async_sessionmaker(
        db_engine, class_=AsyncSession, expire_on_commit=False
    )

    async def override_get_db():
        async with factory() as session:
            try:
                yield session
                await session.commit()
            except Exception:
                await session.rollback()
                raise

    from python_app.main import app

    app.dependency_overrides[get_db] = override_get_db
    async with AsyncClient(
        transport=ASGITransport(app=app), base_url="http://test"
    ) as ac:
        yield ac
    app.dependency_overrides.clear()


# ── User fixtures ──────────────────────────────────────────────────────────


@pytest_asyncio.fixture
async def regular_user(db_session):
    """Create and return a regular user."""
    user = User(
        username="testuser",
        email="testuser@example.com",
        password_hash=hash_password(TEST_PASSWORD),
        name="Test User",
        role=UserRole.USER,
    )
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)
    return user


@pytest_asyncio.fixture
async def organizer_user(db_session):
    """Create and return an organizer user with an organizer profile."""
    user = User(
        username="orguser",
        email="orguser@example.com",
        password_hash=hash_password(TEST_PASSWORD),
        name="Organizer User",
        role=UserRole.ORGANIZER,
    )
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)

    org = Organizer(
        user_id=user.id,
        company_name="Test Events Co",
        description="A test organizer",
    )
    db_session.add(org)
    await db_session.commit()
    await db_session.refresh(org)
    return user


@pytest_asyncio.fixture
async def admin_user(db_session):
    """Create and return an admin user."""
    user = User(
        username="adminuser",
        email="admin@example.com",
        password_hash=hash_password(TEST_PASSWORD),
        name="Admin User",
        role=UserRole.ADMIN,
    )
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)
    return user


# ── Token fixtures ─────────────────────────────────────────────────────────


@pytest.fixture
def user_token(regular_user) -> str:
    return create_access_token(data={"sub": str(regular_user.id)})


@pytest.fixture
def organizer_token(organizer_user) -> str:
    return create_access_token(data={"sub": str(organizer_user.id)})


@pytest.fixture
def admin_token(admin_user) -> str:
    return create_access_token(data={"sub": str(admin_user.id)})


# ── Seed-data fixtures ────────────────────────────────────────────────────


@pytest_asyncio.fixture
async def sample_event(db_session, organizer_user):
    """Create and return a sample active event."""
    result = await db_session.execute(
        select(Organizer).where(Organizer.user_id == organizer_user.id)
    )
    org = result.scalar_one()

    event = Event(
        title="Test Concert",
        description="A great test concert",
        organizer_id=org.id,
        event_date=date(2026, 6, 15),
        start_time=time(19, 0),
        end_time=time(22, 0),
        max_capacity=100,
        price=Decimal("25.00"),
        status=EventStatus.ACTIVE,
    )
    db_session.add(event)
    await db_session.commit()
    await db_session.refresh(event)
    return event


@pytest_asyncio.fixture
async def sold_out_event(db_session, organizer_user, regular_user):
    """Create an event with max_capacity=1 and one existing ticket."""
    from python_app.models import PaymentStatus, Ticket

    result = await db_session.execute(
        select(Organizer).where(Organizer.user_id == organizer_user.id)
    )
    org = result.scalar_one()

    event = Event(
        title="Sold Out Show",
        description="This event is sold out",
        organizer_id=org.id,
        event_date=date(2026, 7, 1),
        start_time=time(20, 0),
        end_time=time(23, 0),
        max_capacity=1,
        price=Decimal("50.00"),
        status=EventStatus.ACTIVE,
    )
    db_session.add(event)
    await db_session.commit()
    await db_session.refresh(event)

    ticket = Ticket(
        event_id=event.id,
        user_id=regular_user.id,
        price=event.price,
        payment_status=PaymentStatus.COMPLETED,
        qr_code="QR-EXISTING0001",
    )
    db_session.add(ticket)
    await db_session.commit()
    return event
