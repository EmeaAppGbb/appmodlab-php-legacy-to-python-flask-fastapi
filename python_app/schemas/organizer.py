"""Organizer Pydantic schemas."""

from decimal import Decimal

from pydantic import BaseModel, ConfigDict, Field, HttpUrl


class OrganizerCreate(BaseModel):
    company_name: str = Field(..., min_length=1, max_length=100)
    description: str | None = None
    website: str | None = Field(None, max_length=255)


class OrganizerResponse(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    company_name: str
    description: str | None = None
    website: str | None = None
    verified: bool
    commission_rate: Decimal
