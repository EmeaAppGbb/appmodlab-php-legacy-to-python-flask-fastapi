"""Organizer ORM model."""

from decimal import Decimal

from sqlalchemy import ForeignKey, Numeric, String, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from python_app.database import Base


class Organizer(Base):
    __tablename__ = "organizers"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False)
    company_name: Mapped[str] = mapped_column(String(100), nullable=False)
    description: Mapped[str | None] = mapped_column(Text)
    website: Mapped[str | None] = mapped_column(String(255))
    verified: Mapped[bool] = mapped_column(default=False, server_default="0")
    commission_rate: Mapped[Decimal] = mapped_column(
        Numeric(5, 2), default=Decimal("10.00"), server_default="10.00"
    )

    # Relationships
    user: Mapped["User"] = relationship("User", back_populates="organizer")
    events: Mapped[list["Event"]] = relationship(
        "Event", back_populates="organizer"
    )
