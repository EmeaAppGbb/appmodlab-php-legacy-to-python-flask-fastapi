"""Tests for event endpoints — /api/v1/events/*"""

import pytest

from tests.conftest import TEST_PASSWORD, auth_header


class TestListEvents:
    async def test_list_events_empty(self, client):
        resp = await client.get("/api/v1/events/")
        assert resp.status_code == 200
        assert resp.json() == []

    async def test_list_events_with_data(self, client, sample_event):
        resp = await client.get("/api/v1/events/")
        assert resp.status_code == 200
        data = resp.json()
        assert len(data) >= 1
        assert data[0]["title"] == "Test Concert"


class TestGetEvent:
    async def test_get_single_event(self, client, sample_event):
        resp = await client.get(f"/api/v1/events/{sample_event.id}")
        assert resp.status_code == 200
        data = resp.json()
        assert data["id"] == sample_event.id
        assert data["title"] == "Test Concert"

    async def test_get_event_not_found(self, client):
        resp = await client.get("/api/v1/events/99999")
        assert resp.status_code == 404


class TestSearchEvents:
    async def test_search_events(self, client, sample_event):
        resp = await client.get("/api/v1/events/search", params={"q": "Concert"})
        assert resp.status_code == 200
        data = resp.json()
        assert len(data) >= 1
        assert "Concert" in data[0]["title"]

    async def test_search_events_no_results(self, client, sample_event):
        resp = await client.get(
            "/api/v1/events/search", params={"q": "nonexistent_xyz"}
        )
        assert resp.status_code == 200
        assert resp.json() == []


class TestCreateEvent:
    EVENT_PAYLOAD = {
        "title": "New Festival",
        "description": "An amazing festival",
        "event_date": "2026-08-01",
        "start_time": "14:00:00",
        "end_time": "22:00:00",
        "max_capacity": 500,
        "price": "15.00",
    }

    async def test_create_event_as_organizer(self, client, organizer_token):
        resp = await client.post(
            "/api/v1/events/",
            json=self.EVENT_PAYLOAD,
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 201
        data = resp.json()
        assert data["title"] == "New Festival"
        assert data["max_capacity"] == 500

    async def test_create_event_unauthorized_regular_user(
        self, client, user_token
    ):
        resp = await client.post(
            "/api/v1/events/",
            json=self.EVENT_PAYLOAD,
            headers=auth_header(user_token),
        )
        assert resp.status_code == 403

    async def test_create_event_no_auth(self, client):
        resp = await client.post("/api/v1/events/", json=self.EVENT_PAYLOAD)
        assert resp.status_code == 401


class TestUpdateEvent:
    async def test_update_event(self, client, sample_event, organizer_token):
        resp = await client.put(
            f"/api/v1/events/{sample_event.id}",
            json={"title": "Updated Concert"},
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 200
        assert resp.json()["title"] == "Updated Concert"

    async def test_update_event_not_found(self, client, organizer_token):
        resp = await client.put(
            "/api/v1/events/99999",
            json={"title": "Nope"},
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 404


class TestDeleteEvent:
    async def test_delete_event_soft_cancel(
        self, client, sample_event, organizer_token
    ):
        resp = await client.delete(
            f"/api/v1/events/{sample_event.id}",
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 200
        assert resp.json()["status"] == "cancelled"

    async def test_delete_event_not_found(self, client, organizer_token):
        resp = await client.delete(
            "/api/v1/events/99999",
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 404
