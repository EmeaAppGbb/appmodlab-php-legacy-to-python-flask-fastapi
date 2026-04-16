"""CityPulse Events — FastAPI application entry point."""

from contextlib import asynccontextmanager
from collections.abc import AsyncIterator
from pathlib import Path

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates

from python_app.config import settings
from python_app.routers import admin, auth, events, organizers, pages, tickets

BASE_DIR = Path(__file__).resolve().parent


@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncIterator[None]:
    """Application lifespan: startup and shutdown hooks."""
    # Startup — add shared resources to app.state if needed
    yield
    # Shutdown — cleanup resources here if needed


app = FastAPI(
    title=settings.SITE_NAME,
    version="1.0.0",
    description="CityPulse Events — community event management platform",
    lifespan=lifespan,
)

# ---------------------------------------------------------------------------
# CORS middleware
# ---------------------------------------------------------------------------
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # tighten in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ---------------------------------------------------------------------------
# Jinja2 templates (available to page routers via request.app.state.templates)
# ---------------------------------------------------------------------------
templates = Jinja2Templates(directory=str(BASE_DIR / "templates"))
app.state.templates = templates

# ---------------------------------------------------------------------------
# Static files mount
# ---------------------------------------------------------------------------
app.mount("/static", StaticFiles(directory=str(BASE_DIR / "static")), name="static")

# ---------------------------------------------------------------------------
# API routers (JSON responses)
# ---------------------------------------------------------------------------
app.include_router(auth.router, prefix="/api/v1/auth", tags=["auth"])
app.include_router(events.router, prefix="/api/v1/events", tags=["events"])
app.include_router(tickets.router, prefix="/api/v1/tickets", tags=["tickets"])
app.include_router(organizers.router, prefix="/api/v1/organizers", tags=["organizers"])
app.include_router(admin.router, prefix="/api/v1/admin", tags=["admin"])

# ---------------------------------------------------------------------------
# Page routers (HTML / Jinja2 responses)
# ---------------------------------------------------------------------------
app.include_router(pages.router, tags=["pages"])
