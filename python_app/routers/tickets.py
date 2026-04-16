"""Ticket API routes — /api/v1/tickets/*"""

from fastapi import APIRouter

router = APIRouter()


@router.post("/events/{event_id}/purchase")
async def purchase_ticket(event_id: int) -> dict:
    """Purchase tickets for an event."""
    ...


@router.get("/me")
async def my_tickets() -> list[dict]:
    """Return tickets owned by the current user."""
    ...


@router.post("/ipn")
async def paypal_ipn() -> dict:
    """Handle PayPal IPN callback."""
    ...
