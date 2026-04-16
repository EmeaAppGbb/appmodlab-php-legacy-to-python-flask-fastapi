"""Admin API routes — /api/v1/admin/*"""

from fastapi import APIRouter

router = APIRouter()


@router.get("/users")
async def list_users() -> list[dict]:
    """Return all users (admin only)."""
    ...


@router.delete("/events/{event_id}")
async def cancel_event(event_id: int) -> dict:
    """Cancel (soft-delete) an event."""
    ...
