"""Ticket Pydantic schemas."""

from datetime import datetime
from decimal import Decimal

from pydantic import BaseModel, ConfigDict

from python_app.models.ticket import PaymentStatus, TicketType


class TicketCreate(BaseModel):
    event_id: int
    ticket_type: TicketType = TicketType.GENERAL


class TicketResponse(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    event_id: int
    user_id: int
    ticket_type: TicketType
    price: Decimal
    purchase_date: datetime
    payment_status: PaymentStatus
    paypal_txn_id: str | None = None
    qr_code: str | None = None
