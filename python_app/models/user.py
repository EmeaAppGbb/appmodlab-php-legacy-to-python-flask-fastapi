"""User ORM model."""

import enum
from datetime import datetime

from sqlalchemy import DateTime, Enum, String, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from python_app.database import Base


class UserRole(str, enum.Enum):
    USER = "user"
    ORGANIZER = "organizer"
    ADMIN = "admin"


class User(Base):
    __tablename__ = "users"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    username: Mapped[str] = mapped_column(String(50), unique=True, nullable=False)
    email: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(
        "password", String(255), nullable=False
    )
    name: Mapped[str] = mapped_column(String(100), nullable=False)
    phone: Mapped[str | None] = mapped_column(String(20))
    role: Mapped[UserRole] = mapped_column(
        Enum(UserRole, values_callable=lambda e: [m.value for m in e]),
        default=UserRole.USER,
        server_default="user",
    )
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now()
    )

    # Relationships
    organizer: Mapped["Organizer | None"] = relationship(
        "Organizer", back_populates="user", uselist=False
    )
    tickets: Mapped[list["Ticket"]] = relationship(
        "Ticket", back_populates="user"
    )
    reviews: Mapped[list["Review"]] = relationship(
        "Review", back_populates="user"
    )
