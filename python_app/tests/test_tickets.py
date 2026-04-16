"""Tests for ticket endpoints — /api/v1/tickets/*"""

import pytest


class TestPurchaseTicket:
    async def test_purchase_ticket(self, client, sample_event, user_token, auth_header):
        resp = await client.post(
            f"/api/v1/tickets/events/{sample_event.id}/purchase",
            json={"event_id": sample_event.id, "ticket_type": "general"},
            headers=auth_header(user_token),
        )
        assert resp.status_code == 201
        data = resp.json()
        assert data["event_id"] == sample_event.id
        assert data["payment_status"] == "pending"
        assert data["qr_code"] is not None

    async def test_purchase_ticket_no_auth(self, client, sample_event):
        resp = await client.post(
            f"/api/v1/tickets/events/{sample_event.id}/purchase",
            json={"event_id": sample_event.id, "ticket_type": "general"},
        )
        assert resp.status_code == 401


class TestMyTickets:
    async def test_get_my_tickets(self, client, sample_event, user_token, auth_header):
        # Purchase a ticket first
        await client.post(
            f"/api/v1/tickets/events/{sample_event.id}/purchase",
            json={"event_id": sample_event.id, "ticket_type": "general"},
            headers=auth_header(user_token),
        )

        resp = await client.get(
            "/api/v1/tickets/me", headers=auth_header(user_token)
        )
        assert resp.status_code == 200
        data = resp.json()
        assert len(data) >= 1
        assert data[0]["event_id"] == sample_event.id

    async def test_get_my_tickets_empty(self, client, user_token, auth_header):
        resp = await client.get(
            "/api/v1/tickets/me", headers=auth_header(user_token)
        )
        assert resp.status_code == 200
        assert resp.json() == []


class TestSoldOut:
    async def test_purchase_sold_out_event(
        self, client, sold_out_event, organizer_token, auth_header
    ):
        """Organizer tries to buy a ticket for a sold-out event (capacity 1, already sold)."""
        resp = await client.post(
            f"/api/v1/tickets/events/{sold_out_event.id}/purchase",
            json={"event_id": sold_out_event.id, "ticket_type": "general"},
            headers=auth_header(organizer_token),
        )
        assert resp.status_code == 400
        assert "sold out" in resp.json()["detail"].lower()
