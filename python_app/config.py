"""Application configuration via pydantic-settings, loaded from .env file."""

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """CityPulse Events application settings.

    All values can be overridden via environment variables or a .env file
    located in the python_app/ directory.
    """

    # Database
    DB_HOST: str = "db"
    DB_PORT: int = 3306
    DB_USER: str = "citypulse_user"
    DB_PASS: str = "citypulse123"
    DB_NAME: str = "citypulse_events"

    # Application
    SITE_NAME: str = "CityPulse Events"
    SITE_URL: str = "http://localhost:8000"
    UPLOAD_DIR: str = "uploads"
    MAX_UPLOAD_SIZE: int = 5_242_880  # 5 MB

    # Security
    SECRET_KEY: str = "change-me-in-production"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 30
    ALGORITHM: str = "HS256"

    # PayPal
    PAYPAL_EMAIL: str = ""
    PAYPAL_SANDBOX: bool = True

    # Logging
    LOG_LEVEL: str = "INFO"

    @property
    def DATABASE_URL(self) -> str:
        """Build async MySQL connection string for SQLAlchemy."""
        return (
            f"mysql+aiomysql://{self.DB_USER}:{self.DB_PASS}"
            f"@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"
        )

    @property
    def DATABASE_URL_SYNC(self) -> str:
        """Build sync MySQL connection string (used by Alembic migrations)."""
        return (
            f"mysql+pymysql://{self.DB_USER}:{self.DB_PASS}"
            f"@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"
        )

    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )


settings = Settings()
