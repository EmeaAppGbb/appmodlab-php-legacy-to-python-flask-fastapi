"""HTML page routes — serves Jinja2 templates for browser clients."""

from fastapi import APIRouter, Request

router = APIRouter()


@router.get("/")
async def homepage(request: Request):
    """Render the homepage with upcoming events."""
    templates = request.app.state.templates
    return templates.TemplateResponse(request, "index.html", {"page_title": "Home"})
