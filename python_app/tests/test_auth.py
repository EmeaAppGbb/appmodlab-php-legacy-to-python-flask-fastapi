"""Tests for authentication endpoints — /api/v1/auth/*"""

import pytest


class TestLogin:
    async def test_login_valid_credentials(self, client, regular_user, test_password):
        resp = await client.post(
            "/api/v1/auth/login",
            json={"username": regular_user.username, "password": test_password},
        )
        assert resp.status_code == 200
        data = resp.json()
        assert "access_token" in data
        assert data["token_type"] == "bearer"

    async def test_login_invalid_password(self, client, regular_user):
        resp = await client.post(
            "/api/v1/auth/login",
            json={"username": regular_user.username, "password": "wrongpass"},
        )
        assert resp.status_code == 401
        assert "Invalid" in resp.json()["detail"]

    async def test_login_nonexistent_user(self, client):
        resp = await client.post(
            "/api/v1/auth/login",
            json={"username": "ghost", "password": "nope"},
        )
        assert resp.status_code == 401


class TestRegister:
    async def test_register_new_user(self, client):
        resp = await client.post(
            "/api/v1/auth/register",
            json={
                "username": "newuser",
                "email": "new@example.com",
                "password": "securepass",
                "name": "New User",
            },
        )
        assert resp.status_code == 201
        data = resp.json()
        assert data["username"] == "newuser"
        assert data["email"] == "new@example.com"
        assert data["role"] == "user"

    async def test_register_duplicate_username(self, client, regular_user):
        resp = await client.post(
            "/api/v1/auth/register",
            json={
                "username": regular_user.username,
                "email": "other@example.com",
                "password": "securepass",
                "name": "Duplicate",
            },
        )
        assert resp.status_code == 409
        assert "already exists" in resp.json()["detail"]


class TestCurrentUser:
    async def test_get_current_user(self, client, regular_user, user_token, auth_header):
        resp = await client.get(
            "/api/v1/auth/me", headers=auth_header(user_token)
        )
        assert resp.status_code == 200
        data = resp.json()
        assert data["username"] == regular_user.username
        assert data["id"] == regular_user.id

    async def test_get_current_user_no_token(self, client):
        resp = await client.get("/api/v1/auth/me")
        assert resp.status_code == 401

    async def test_get_current_user_invalid_token(self, client, auth_header):
        resp = await client.get(
            "/api/v1/auth/me",
            headers=auth_header("invalid.jwt.token"),
        )
        assert resp.status_code == 401

    async def test_jwt_token_from_login_works(self, client, regular_user, auth_header, test_password):
        """Login, grab the token, and use it to hit /me."""
        login_resp = await client.post(
            "/api/v1/auth/login",
            json={"username": regular_user.username, "password": test_password},
        )
        token = login_resp.json()["access_token"]

        me_resp = await client.get(
            "/api/v1/auth/me", headers=auth_header(token)
        )
        assert me_resp.status_code == 200
        assert me_resp.json()["id"] == regular_user.id
