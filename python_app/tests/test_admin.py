"""Tests for admin endpoints — /api/v1/admin/*"""

import pytest

from tests.conftest import auth_header


class TestAdminListUsers:
    async def test_list_users_as_admin(self, client, admin_token, regular_user):
        resp = await client.get(
            "/api/v1/admin/users", headers=auth_header(admin_token)
        )
        assert resp.status_code == 200
        data = resp.json()
        assert isinstance(data, list)
        assert len(data) >= 1

    async def test_list_users_as_non_admin(self, client, user_token):
        resp = await client.get(
            "/api/v1/admin/users", headers=auth_header(user_token)
        )
        assert resp.status_code == 403

    async def test_list_users_no_auth(self, client):
        resp = await client.get("/api/v1/admin/users")
        assert resp.status_code == 401


class TestAdminManageEvents:
    async def test_admin_list_all_events(
        self, client, admin_token, sample_event
    ):
        resp = await client.get(
            "/api/v1/admin/events", headers=auth_header(admin_token)
        )
        assert resp.status_code == 200
        data = resp.json()
        assert len(data) >= 1

    async def test_admin_update_event_status(
        self, client, admin_token, sample_event
    ):
        resp = await client.put(
            f"/api/v1/admin/events/{sample_event.id}/status",
            json={"status": "cancelled"},
            headers=auth_header(admin_token),
        )
        assert resp.status_code == 200
        assert resp.json()["status"] == "cancelled"

    async def test_admin_cancel_event(self, client, admin_token, sample_event):
        resp = await client.delete(
            f"/api/v1/admin/events/{sample_event.id}",
            headers=auth_header(admin_token),
        )
        assert resp.status_code == 200
        assert resp.json()["status"] == "cancelled"

    async def test_admin_events_as_regular_user(self, client, user_token):
        resp = await client.get(
            "/api/v1/admin/events", headers=auth_header(user_token)
        )
        assert resp.status_code == 403
