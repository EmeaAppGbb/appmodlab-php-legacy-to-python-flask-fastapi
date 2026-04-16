"""Event API routes — /api/v1/events/*"""

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import func, select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from python_app.auth import get_current_user_required, require_role
from python_app.database import get_db
from python_app.models import Event, EventStatus, Organizer, PaymentStatus, Ticket, User, UserRole
from python_app.schemas.event import EventCreate, EventResponse, EventUpdate

router = APIRouter()


@router.get("/", response_model=list[EventResponse])
async def list_events(
    category_id: int | None = Query(None, alias="category"),
    skip: int = Query(0, ge=0),
    limit: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
) -> list[Event]:
    """Return paginated list of active events with optional category filter."""
    stmt = select(Event).where(Event.status == EventStatus.ACTIVE)
    if category_id is not None:
        stmt = stmt.where(Event.category_id == category_id)
    stmt = stmt.order_by(Event.event_date.asc()).offset(skip).limit(limit)
    result = await db.execute(stmt)
    return list(result.scalars().all())


@router.get("/search", response_model=list[EventResponse])
async def search_events(
    q: str = Query("", min_length=1),
    skip: int = Query(0, ge=0),
    limit: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
) -> list[Event]:
    """Search active events by title or description."""
    pattern = f"%{q}%"
    stmt = (
        select(Event)
        .where(Event.status == EventStatus.ACTIVE)
        .where(Event.title.ilike(pattern) | Event.description.ilike(pattern))
        .order_by(Event.event_date.asc())
        .offset(skip)
        .limit(limit)
    )
    result = await db.execute(stmt)
    return list(result.scalars().all())


@router.get("/{event_id}", response_model=EventResponse)
async def get_event(
    event_id: int,
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Return a single event by ID with venue, reviews, and organizer."""
    stmt = (
        select(Event)
        .options(
            selectinload(Event.venue),
            selectinload(Event.reviews),
            selectinload(Event.organizer),
            selectinload(Event.category_rel),
        )
        .where(Event.id == event_id)
    )
    result = await db.execute(stmt)
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")
    return event


@router.post("/", response_model=EventResponse, status_code=status.HTTP_201_CREATED)
async def create_event(
    event_data: EventCreate,
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Create a new event (organizer or admin only)."""
    # Get organizer profile
    result = await db.execute(
        select(Organizer).where(Organizer.user_id == current_user.id)
    )
    organizer = result.scalar_one_or_none()
    if organizer is None:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="No organizer profile found. Please create one first.",
        )

    new_event = Event(
        title=event_data.title,
        description=event_data.description,
        venue_id=event_data.venue_id,
        organizer_id=organizer.id,
        event_date=event_data.event_date,
        start_time=event_data.start_time,
        end_time=event_data.end_time,
        category_id=event_data.category_id,
        max_capacity=event_data.max_capacity,
        price=event_data.price,
        image_path=event_data.image_path,
    )
    db.add(new_event)
    await db.flush()
    await db.refresh(new_event)
    return new_event


@router.put("/{event_id}", response_model=EventResponse)
async def update_event(
    event_id: int,
    event_data: EventUpdate,
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Update an existing event. Organizers can update their own; admins can update any."""
    stmt = (
        select(Event)
        .options(selectinload(Event.organizer))
        .where(Event.id == event_id)
    )
    result = await db.execute(stmt)
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")

    # Ownership check: organizers can only edit their own events
    if current_user.role != UserRole.ADMIN:
        org_result = await db.execute(
            select(Organizer).where(Organizer.user_id == current_user.id)
        )
        organizer = org_result.scalar_one_or_none()
        if organizer is None or event.organizer_id != organizer.id:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, detail="Access denied"
            )

    update_fields = event_data.model_dump(exclude_unset=True)
    # Non-admins cannot change event status via this endpoint
    if current_user.role != UserRole.ADMIN:
        update_fields.pop("status", None)

    for field, value in update_fields.items():
        setattr(event, field, value)

    await db.flush()
    await db.refresh(event)
    return event


@router.delete("/{event_id}", response_model=EventResponse)
async def delete_event(
    event_id: int,
    current_user: User = Depends(require_role(UserRole.ORGANIZER, UserRole.ADMIN)),
    db: AsyncSession = Depends(get_db),
) -> Event:
    """Soft-delete (cancel) an event."""
    stmt = select(Event).where(Event.id == event_id)
    result = await db.execute(stmt)
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")

    # Ownership check
    if current_user.role != UserRole.ADMIN:
        org_result = await db.execute(
            select(Organizer).where(Organizer.user_id == current_user.id)
        )
        organizer = org_result.scalar_one_or_none()
        if organizer is None or event.organizer_id != organizer.id:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, detail="Access denied"
            )

    event.status = EventStatus.CANCELLED
    await db.flush()
    await db.refresh(event)
    return event
