"""Event ORM model."""

import enum
from datetime import date, datetime, time
from decimal import Decimal

from sqlalchemy import Date, DateTime, Enum, ForeignKey, Numeric, String, Text, Time, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from python_app.database import Base


class EventStatus(str, enum.Enum):
    ACTIVE = "active"
    CANCELLED = "cancelled"
    COMPLETED = "completed"


class Event(Base):
    __tablename__ = "events"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    title: Mapped[str] = mapped_column(String(200), nullable=False)
    description: Mapped[str] = mapped_column(Text, nullable=False)
    venue_id: Mapped[int | None] = mapped_column(ForeignKey("venues.id"))
    organizer_id: Mapped[int] = mapped_column(
        ForeignKey("organizers.id"), nullable=False
    )
    event_date: Mapped[date] = mapped_column(Date, nullable=False)
    start_time: Mapped[time] = mapped_column(Time, nullable=False)
    end_time: Mapped[time] = mapped_column(Time, nullable=False)
    category_id: Mapped[int | None] = mapped_column(
        "category", ForeignKey("categories.id")
    )
    max_capacity: Mapped[int] = mapped_column(nullable=False)
    price: Mapped[Decimal] = mapped_column(Numeric(10, 2), nullable=False)
    status: Mapped[EventStatus] = mapped_column(
        Enum(EventStatus, values_callable=lambda e: [m.value for m in e]),
        default=EventStatus.ACTIVE,
        server_default="active",
    )
    image_path: Mapped[str | None] = mapped_column(String(255))
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now()
    )

    # Relationships
    venue: Mapped["Venue | None"] = relationship("Venue", back_populates="events")
    organizer: Mapped["Organizer"] = relationship(
        "Organizer", back_populates="events"
    )
    category_rel: Mapped["Category | None"] = relationship(
        "Category", back_populates="events"
    )
    tickets: Mapped[list["Ticket"]] = relationship(
        "Ticket", back_populates="event"
    )
    reviews: Mapped[list["Review"]] = relationship(
        "Review", back_populates="event"
    )
