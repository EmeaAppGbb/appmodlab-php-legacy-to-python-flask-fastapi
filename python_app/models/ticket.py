"""Ticket ORM model."""

import enum
from datetime import datetime
from decimal import Decimal

from sqlalchemy import DateTime, Enum, ForeignKey, Numeric, String, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from python_app.database import Base


class TicketType(str, enum.Enum):
    GENERAL = "general"
    VIP = "vip"


class PaymentStatus(str, enum.Enum):
    PENDING = "pending"
    COMPLETED = "completed"
    FAILED = "failed"


class Ticket(Base):
    __tablename__ = "tickets"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    event_id: Mapped[int] = mapped_column(
        ForeignKey("events.id"), nullable=False
    )
    user_id: Mapped[int] = mapped_column(
        ForeignKey("users.id"), nullable=False
    )
    ticket_type: Mapped[TicketType] = mapped_column(
        Enum(TicketType, values_callable=lambda e: [m.value for m in e]),
        default=TicketType.GENERAL,
        server_default="general",
    )
    price: Mapped[Decimal] = mapped_column(Numeric(10, 2), nullable=False)
    purchase_date: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now()
    )
    payment_status: Mapped[PaymentStatus] = mapped_column(
        Enum(PaymentStatus, values_callable=lambda e: [m.value for m in e]),
        default=PaymentStatus.PENDING,
        server_default="pending",
    )
    paypal_txn_id: Mapped[str | None] = mapped_column(String(100))
    qr_code: Mapped[str | None] = mapped_column(String(100), unique=True)

    # Relationships
    event: Mapped["Event"] = relationship("Event", back_populates="tickets")
    user: Mapped["User"] = relationship("User", back_populates="tickets")
