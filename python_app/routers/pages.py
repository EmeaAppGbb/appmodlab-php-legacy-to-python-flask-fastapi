"""HTML page routes — serves Jinja2 templates for browser clients."""

from datetime import date

from fastapi import APIRouter, Depends, Query, Request
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from python_app.auth import get_current_user
from python_app.database import get_db
from python_app.models import Category, Event, EventStatus, User

router = APIRouter()


@router.get("/")
async def homepage(
    request: Request,
    db: AsyncSession = Depends(get_db),
    current_user: User | None = Depends(get_current_user),
):
    """Render the homepage with upcoming events and categories."""
    # Upcoming active events
    events_result = await db.execute(
        select(Event)
        .options(selectinload(Event.venue), selectinload(Event.category_rel))
        .where(Event.status == EventStatus.ACTIVE, Event.event_date >= date.today())
        .order_by(Event.event_date.asc())
        .limit(10)
    )
    events = list(events_result.scalars().all())

    # All categories
    cats_result = await db.execute(select(Category).order_by(Category.name))
    categories = list(cats_result.scalars().all())

    templates = request.app.state.templates
    return templates.TemplateResponse(
        request,
        "index.html",
        {
            "page_title": "Home",
            "events": events,
            "categories": categories,
            "current_user": current_user,
        },
    )


@router.get("/events")
async def events_page(
    request: Request,
    category: int | None = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user: User | None = Depends(get_current_user),
):
    """Render event listing page with optional category filter."""
    stmt = (
        select(Event)
        .options(selectinload(Event.venue), selectinload(Event.category_rel))
        .where(Event.status == EventStatus.ACTIVE)
    )
    if category is not None:
        stmt = stmt.where(Event.category_id == category)
    stmt = stmt.order_by(Event.event_date.asc())
    events_result = await db.execute(stmt)
    events = list(events_result.scalars().all())

    cats_result = await db.execute(select(Category).order_by(Category.name))
    categories = list(cats_result.scalars().all())

    templates = request.app.state.templates
    return templates.TemplateResponse(
        request,
        "events_list.html",
        {
            "page_title": "Events",
            "events": events,
            "categories": categories,
            "selected_category": category,
            "current_user": current_user,
        },
    )


@router.get("/events/{event_id}")
async def event_detail_page(
    request: Request,
    event_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User | None = Depends(get_current_user),
):
    """Render event detail page with reviews and venue info."""
    stmt = (
        select(Event)
        .options(
            selectinload(Event.venue),
            selectinload(Event.reviews),
            selectinload(Event.organizer),
            selectinload(Event.category_rel),
        )
        .where(Event.id == event_id)
    )
    result = await db.execute(stmt)
    event = result.scalar_one_or_none()

    templates = request.app.state.templates
    if event is None:
        return templates.TemplateResponse(
            request,
            "404.html",
            {"page_title": "Not Found", "current_user": current_user},
            status_code=404,
        )

    return templates.TemplateResponse(
        request,
        "event_detail.html",
        {
            "page_title": event.title,
            "event": event,
            "current_user": current_user,
        },
    )


@router.get("/login")
async def login_page(request: Request):
    """Render the login page."""
    templates = request.app.state.templates
    return templates.TemplateResponse(
        request, "login.html", {"page_title": "Login"}
    )


@router.get("/register")
async def register_page(request: Request):
    """Render the registration page."""
    templates = request.app.state.templates
    return templates.TemplateResponse(
        request, "register.html", {"page_title": "Register"}
    )
