"""Event Pydantic schemas."""

from datetime import date, datetime, time
from decimal import Decimal

from pydantic import BaseModel, ConfigDict, Field

from python_app.models.event import EventStatus


class EventCreate(BaseModel):
    title: str = Field(..., min_length=1, max_length=200)
    description: str = Field(..., min_length=1)
    venue_id: int | None = None
    event_date: date
    start_time: time
    end_time: time
    category_id: int | None = None
    max_capacity: int = Field(..., gt=0)
    price: Decimal = Field(..., ge=0)
    image_path: str | None = None


class EventUpdate(BaseModel):
    title: str | None = Field(None, min_length=1, max_length=200)
    description: str | None = Field(None, min_length=1)
    venue_id: int | None = None
    event_date: date | None = None
    start_time: time | None = None
    end_time: time | None = None
    category_id: int | None = None
    max_capacity: int | None = Field(None, gt=0)
    price: Decimal | None = Field(None, ge=0)
    status: EventStatus | None = None
    image_path: str | None = None


class EventResponse(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    title: str
    description: str
    venue_id: int | None = None
    organizer_id: int
    event_date: date
    start_time: time
    end_time: time
    category_id: int | None = None
    max_capacity: int
    price: Decimal
    status: EventStatus
    image_path: str | None = None
    created_at: datetime
