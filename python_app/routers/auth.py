"""Authentication API routes — /api/v1/auth/*"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.ext.asyncio import AsyncSession

from python_app.auth import (
    create_access_token,
    get_current_user_required,
    hash_password,
    is_legacy_hash,
    verify_password,
)
from python_app.database import get_db
from python_app.models.user import User
from python_app.schemas.token import Token
from python_app.schemas.user import UserCreate, UserLogin, UserResponse

router = APIRouter()


@router.post("/login", response_model=Token)
async def login(
    credentials: UserLogin,
    db: AsyncSession = Depends(get_db),
) -> Token:
    """Authenticate user and return JWT token."""
    result = await db.execute(
        select(User).where(User.username == credentials.username)
    )
    user = result.scalar_one_or_none()

    if user is None or not verify_password(credentials.password, user.password_hash):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )

    # Migrate legacy MD5 hash to bcrypt on successful login
    if is_legacy_hash(user.password_hash):
        user.password_hash = hash_password(credentials.password)

    access_token = create_access_token(data={"sub": str(user.id)})
    return Token(access_token=access_token)


@router.post("/register", response_model=UserResponse, status_code=status.HTTP_201_CREATED)
async def register(
    user_data: UserCreate,
    db: AsyncSession = Depends(get_db),
) -> User:
    """Register a new user account."""
    new_user = User(
        username=user_data.username,
        email=user_data.email,
        password_hash=hash_password(user_data.password),
        name=user_data.name,
        phone=user_data.phone,
    )
    db.add(new_user)
    try:
        await db.flush()
    except IntegrityError:
        await db.rollback()
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail="Username or email already exists",
        )
    await db.refresh(new_user)
    return new_user


@router.get("/me", response_model=UserResponse)
async def me(
    current_user: User = Depends(get_current_user_required),
) -> User:
    """Return the current authenticated user."""
    return current_user


@router.post("/logout")
async def logout() -> dict[str, str]:
    """Log out the current user (client discards token)."""
    return {"message": "Logged out successfully"}
