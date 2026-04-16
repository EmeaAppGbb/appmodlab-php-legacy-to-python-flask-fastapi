"""Organizer API routes — /api/v1/organizers/*"""

from datetime import date

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import func, select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from python_app.auth import require_role
from python_app.database import get_db
from python_app.models import Event, Organizer, PaymentStatus, Ticket, User, UserRole
from python_app.schemas.organizer import OrganizerCreate, OrganizerResponse

router = APIRouter()


async def _get_organizer(user: User, db: AsyncSession) -> Organizer:
    """Helper to fetch organizer profile for the current user."""
    result = await db.execute(
        select(Organizer).where(Organizer.user_id == user.id)
    )
    organizer = result.scalar_one_or_none()
    if organizer is None:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="No organizer profile found",
        )
    return organizer


@router.get("/dashboard")
async def organizer_dashboard(
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> dict:
    """Return organizer dashboard data: stats and events list."""
    organizer = await _get_organizer(current_user, db)

    # Fetch organizer's events
    events_result = await db.execute(
        select(Event)
        .where(Event.organizer_id == organizer.id)
        .order_by(Event.event_date.desc())
    )
    events = list(events_result.scalars().all())

    # Total revenue from completed tickets across all events
    revenue_result = await db.execute(
        select(func.coalesce(func.sum(Ticket.price), 0)).where(
            Ticket.event_id.in_([e.id for e in events]) if events else False,
            Ticket.payment_status == PaymentStatus.COMPLETED,
        )
    )
    total_revenue = revenue_result.scalar() or 0

    # Build per-event stats
    events_data = []
    for event in events:
        sold_result = await db.execute(
            select(func.count(Ticket.id)).where(
                Ticket.event_id == event.id,
                Ticket.payment_status == PaymentStatus.COMPLETED,
            )
        )
        tickets_sold = sold_result.scalar() or 0

        event_rev_result = await db.execute(
            select(func.coalesce(func.sum(Ticket.price), 0)).where(
                Ticket.event_id == event.id,
                Ticket.payment_status == PaymentStatus.COMPLETED,
            )
        )
        event_revenue = event_rev_result.scalar() or 0

        events_data.append({
            "id": event.id,
            "title": event.title,
            "event_date": str(event.event_date),
            "max_capacity": event.max_capacity,
            "tickets_sold": tickets_sold,
            "revenue": float(event_revenue),
            "status": event.status.value,
        })

    return {
        "organizer": {
            "id": organizer.id,
            "company_name": organizer.company_name,
            "verified": organizer.verified,
            "commission_rate": float(organizer.commission_rate),
        },
        "stats": {
            "total_events": len(events),
            "total_revenue": float(total_revenue),
        },
        "events": events_data,
    }


@router.get("/reports")
async def organizer_reports(
    start_date: date | None = Query(None),
    end_date: date | None = Query(None),
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> dict:
    """Return organizer revenue and ticket reports with optional date filtering."""
    organizer = await _get_organizer(current_user, db)

    # Base query: completed tickets for organizer's events
    stmt = (
        select(
            Event.id.label("event_id"),
            Event.title,
            Event.event_date,
            func.count(Ticket.id).label("tickets_sold"),
            func.coalesce(func.sum(Ticket.price), 0).label("revenue"),
        )
        .join(Ticket, Ticket.event_id == Event.id)
        .where(
            Event.organizer_id == organizer.id,
            Ticket.payment_status == PaymentStatus.COMPLETED,
        )
    )

    if start_date:
        stmt = stmt.where(Ticket.purchase_date >= start_date)
    if end_date:
        stmt = stmt.where(Ticket.purchase_date <= end_date)

    stmt = stmt.group_by(Event.id, Event.title, Event.event_date).order_by(
        func.sum(Ticket.price).desc()
    )

    result = await db.execute(stmt)
    rows = result.all()

    report_data = []
    total_tickets = 0
    total_revenue = 0.0
    for row in rows:
        report_data.append({
            "event_id": row.event_id,
            "title": row.title,
            "event_date": str(row.event_date),
            "tickets_sold": row.tickets_sold,
            "revenue": float(row.revenue),
        })
        total_tickets += row.tickets_sold
        total_revenue += float(row.revenue)

    return {
        "start_date": str(start_date) if start_date else None,
        "end_date": str(end_date) if end_date else None,
        "total_tickets": total_tickets,
        "total_revenue": total_revenue,
        "events": report_data,
    }


@router.put("/settings", response_model=OrganizerResponse)
async def update_settings(
    settings_data: OrganizerCreate,
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Organizer:
    """Update organizer profile settings (company name, description, website)."""
    organizer = await _get_organizer(current_user, db)

    organizer.company_name = settings_data.company_name
    if settings_data.description is not None:
        organizer.description = settings_data.description
    if settings_data.website is not None:
        organizer.website = settings_data.website

    await db.flush()
    await db.refresh(organizer)
    return organizer
