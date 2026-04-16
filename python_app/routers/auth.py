"""Authentication API routes — /api/v1/auth/*"""

from fastapi import APIRouter

router = APIRouter()


@router.post("/login")
async def login() -> dict[str, str]:
    """Authenticate user and return JWT token."""
    ...


@router.post("/register")
async def register() -> dict[str, str]:
    """Register a new user account."""
    ...


@router.post("/logout")
async def logout() -> dict[str, str]:
    """Log out the current user (client discards token)."""
    return {"message": "Logged out successfully"}
