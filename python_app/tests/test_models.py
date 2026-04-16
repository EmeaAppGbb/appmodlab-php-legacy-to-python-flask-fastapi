"""Tests for ORM model creation and relationships."""

from datetime import date, time
from decimal import Decimal

import pytest
from sqlalchemy import select
from sqlalchemy.orm import selectinload

from python_app.models import (
    Category,
    Event,
    EventStatus,
    Organizer,
    Review,
    Ticket,
    TicketType,
    PaymentStatus,
    User,
    UserRole,
    Venue,
)
from python_app.auth import hash_password


class TestUserModel:
    async def test_create_user(self, db_session):
        user = User(
            username="modeltest",
            email="modeltest@example.com",
            password_hash=hash_password("pass123"),
            name="Model Test",
            role=UserRole.USER,
        )
        db_session.add(user)
        await db_session.commit()
        await db_session.refresh(user)

        assert user.id is not None
        assert user.username == "modeltest"
        assert user.role == UserRole.USER

    async def test_user_role_enum(self, db_session):
        for role in UserRole:
            user = User(
                username=f"role_{role.value}",
                email=f"{role.value}@example.com",
                password_hash="hash",
                name=f"Role {role.value}",
                role=role,
            )
            db_session.add(user)
        await db_session.commit()

        result = await db_session.execute(select(User))
        users = result.scalars().all()
        assert len(users) == 3


class TestEventModel:
    async def test_create_event(self, db_session, organizer_user):
        result = await db_session.execute(
            select(Organizer).where(Organizer.user_id == organizer_user.id)
        )
        org = result.scalar_one()

        event = Event(
            title="Model Event",
            description="Testing event model",
            organizer_id=org.id,
            event_date=date(2026, 12, 25),
            start_time=time(10, 0),
            end_time=time(18, 0),
            max_capacity=50,
            price=Decimal("10.00"),
        )
        db_session.add(event)
        await db_session.commit()
        await db_session.refresh(event)

        assert event.id is not None
        assert event.title == "Model Event"
        assert event.status == EventStatus.ACTIVE


class TestTicketModel:
    async def test_create_ticket(self, db_session, sample_event, regular_user):
        ticket = Ticket(
            event_id=sample_event.id,
            user_id=regular_user.id,
            ticket_type=TicketType.VIP,
            price=Decimal("50.00"),
            payment_status=PaymentStatus.COMPLETED,
            qr_code="QR-MODEL-TEST-001",
        )
        db_session.add(ticket)
        await db_session.commit()
        await db_session.refresh(ticket)

        assert ticket.id is not None
        assert ticket.ticket_type == TicketType.VIP
        assert ticket.payment_status == PaymentStatus.COMPLETED


class TestRelationships:
    async def test_organizer_user_relationship(self, db_session, organizer_user):
        result = await db_session.execute(
            select(Organizer)
            .options(selectinload(Organizer.user))
            .where(Organizer.user_id == organizer_user.id)
        )
        org = result.scalar_one()
        assert org.user.id == organizer_user.id
        assert org.company_name == "Test Events Co"

    async def test_event_organizer_relationship(self, db_session, sample_event):
        result = await db_session.execute(
            select(Event)
            .options(selectinload(Event.organizer))
            .where(Event.id == sample_event.id)
        )
        event = result.scalar_one()
        assert event.organizer is not None
        assert event.organizer.company_name == "Test Events Co"

    async def test_ticket_event_user_relationships(
        self, db_session, sample_event, regular_user
    ):
        ticket = Ticket(
            event_id=sample_event.id,
            user_id=regular_user.id,
            price=Decimal("25.00"),
            payment_status=PaymentStatus.PENDING,
            qr_code="QR-REL-TEST-001",
        )
        db_session.add(ticket)
        await db_session.commit()

        result = await db_session.execute(
            select(Ticket)
            .options(selectinload(Ticket.event), selectinload(Ticket.user))
            .where(Ticket.id == ticket.id)
        )
        t = result.scalar_one()
        assert t.event.title == "Test Concert"
        assert t.user.username == "testuser"
