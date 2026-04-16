"""Venue Pydantic schemas."""

from decimal import Decimal

from pydantic import BaseModel, ConfigDict, EmailStr, Field


class VenueCreate(BaseModel):
    name: str = Field(..., min_length=1, max_length=100)
    address: str = Field(..., min_length=1, max_length=255)
    city: str = Field(..., min_length=1, max_length=50)
    capacity: int = Field(..., gt=0)
    amenities: str | None = None
    contact_email: EmailStr | None = None
    latitude: Decimal | None = None
    longitude: Decimal | None = None


class VenueResponse(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
    address: str
    city: str
    capacity: int
    amenities: str | None = None
    contact_email: str | None = None
    latitude: Decimal | None = None
    longitude: Decimal | None = None
