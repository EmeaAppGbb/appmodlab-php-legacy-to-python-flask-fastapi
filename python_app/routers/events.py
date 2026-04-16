"""Event API routes — /api/v1/events/*"""

from fastapi import APIRouter

router = APIRouter()


@router.get("/")
async def list_events() -> list[dict]:
    """Return paginated list of events."""
    ...


@router.get("/search")
async def search_events(q: str = "") -> list[dict]:
    """Search events by title or description."""
    ...


@router.get("/{event_id}")
async def get_event(event_id: int) -> dict:
    """Return a single event by ID."""
    ...


@router.post("/")
async def create_event() -> dict:
    """Create a new event."""
    ...


@router.put("/{event_id}")
async def update_event(event_id: int) -> dict:
    """Update an existing event."""
    ...
