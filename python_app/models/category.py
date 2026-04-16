"""Category ORM model."""

from sqlalchemy import String
from sqlalchemy.orm import Mapped, mapped_column, relationship

from python_app.database import Base


class Category(Base):
    __tablename__ = "categories"

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(50), nullable=False)
    slug: Mapped[str] = mapped_column(String(50), unique=True, nullable=False)
    icon: Mapped[str | None] = mapped_column(String(10))

    # Relationships
    events: Mapped[list["Event"]] = relationship(
        "Event", back_populates="category_rel"
    )
