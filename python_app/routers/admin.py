"""Admin API routes — /api/v1/admin/*"""

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from python_app.auth import require_role
from python_app.database import get_db
from python_app.models import Event, EventStatus, User, UserRole
from python_app.schemas.event import EventResponse, EventUpdate
from python_app.schemas.user import UserResponse

router = APIRouter()


@router.get("/users", response_model=list[UserResponse])
async def list_users(
    skip: int = Query(0, ge=0),
    limit: int = Query(50, ge=1, le=100),
    _admin: User = Depends(require_role(UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> list[User]:
    """Return all users (admin only)."""
    result = await db.execute(
        select(User).order_by(User.created_at.desc()).offset(skip).limit(limit)
    )
    return list(result.scalars().all())


@router.get("/events", response_model=list[EventResponse])
async def list_all_events(
    skip: int = Query(0, ge=0),
    limit: int = Query(50, ge=1, le=100),
    _admin: User = Depends(require_role(UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> list[Event]:
    """Return all events regardless of status (admin only)."""
    result = await db.execute(
        select(Event).order_by(Event.created_at.desc()).offset(skip).limit(limit)
    )
    return list(result.scalars().all())


@router.put("/events/{event_id}/status", response_model=EventResponse)
async def update_event_status(
    event_id: int,
    event_data: EventUpdate,
    _admin: User = Depends(require_role(UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Update event status (admin only)."""
    result = await db.execute(select(Event).where(Event.id == event_id))
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")

    if event_data.status is not None:
        event.status = event_data.status

    await db.flush()
    await db.refresh(event)
    return event


@router.delete("/events/{event_id}", response_model=EventResponse)
async def cancel_event(
    event_id: int,
    _admin: User = Depends(require_role(UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Cancel (soft-delete) an event (admin only)."""
    result = await db.execute(select(Event).where(Event.id == event_id))
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")

    event.status = EventStatus.CANCELLED
    await db.flush()
    await db.refresh(event)
    return event
