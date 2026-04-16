"""Ticket API routes — /api/v1/tickets/*"""

import uuid

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import func, select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from python_app.auth import get_current_user_required
from python_app.database import get_db
from python_app.models import Event, EventStatus, PaymentStatus, Ticket, User
from python_app.schemas.ticket import TicketCreate, TicketResponse

router = APIRouter()


def _generate_qr_code() -> str:
    return f"QR-{uuid.uuid4().hex[:20].upper()}"


@router.post("/events/{event_id}/purchase", response_model=TicketResponse, status_code=status.HTTP_201_CREATED)
async def purchase_ticket(
    event_id: int,
    ticket_data: TicketCreate,
    current_user: User = Depends(get_current_user_required),
    db: AsyncSession = Depends(get_db),
) -> Ticket:
    """Purchase a ticket for an event."""
    # Fetch event
    result = await db.execute(select(Event).where(Event.id == event_id))
    event = result.scalar_one_or_none()
    if event is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Event not found")
    if event.status != EventStatus.ACTIVE:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Event is not active"
        )

    # Check capacity (count pending + completed tickets)
    sold_result = await db.execute(
        select(func.count(Ticket.id)).where(
            Ticket.event_id == event_id,
            Ticket.payment_status.in_([PaymentStatus.PENDING, PaymentStatus.COMPLETED]),
        )
    )
    tickets_sold = sold_result.scalar() or 0
    if tickets_sold >= event.max_capacity:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Event is sold out"
        )

    ticket = Ticket(
        event_id=event_id,
        user_id=current_user.id,
        ticket_type=ticket_data.ticket_type,
        price=event.price,
        payment_status=PaymentStatus.PENDING,
        qr_code=_generate_qr_code(),
    )
    db.add(ticket)
    await db.flush()
    await db.refresh(ticket)
    return ticket


@router.get("/me", response_model=list[TicketResponse])
async def my_tickets(
    current_user: User = Depends(get_current_user_required),
    db: AsyncSession = Depends(get_db),
) -> list[Ticket]:
    """Return tickets owned by the current user."""
    result = await db.execute(
        select(Ticket)
        .options(selectinload(Ticket.event))
        .where(Ticket.user_id == current_user.id)
        .order_by(Ticket.purchase_date.desc())
    )
    return list(result.scalars().all())


@router.get("/{ticket_id}", response_model=TicketResponse)
async def get_ticket(
    ticket_id: int,
    current_user: User = Depends(get_current_user_required),
    db: AsyncSession = Depends(get_db),
) -> Ticket:
    """Return a single ticket. Users can only view their own tickets."""
    result = await db.execute(
        select(Ticket)
        .options(selectinload(Ticket.event))
        .where(Ticket.id == ticket_id)
    )
    ticket = result.scalar_one_or_none()
    if ticket is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Ticket not found")
    if ticket.user_id != current_user.id:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Access denied")
    return ticket


@router.post("/ipn")
async def paypal_ipn() -> dict[str, str]:
    """Handle PayPal IPN callback (placeholder)."""
    return {"status": "received"}
