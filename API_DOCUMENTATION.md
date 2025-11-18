# Rehearsal App API Documentation

## Overview

This is the public API for the Rehearsal Space Reservation system. All endpoints are located under `/api/v1/`.

**Base URL:** `https://your-domain.com/api/v1`

**Authentication:** Token-based (Sanctum) for protected routes. Tokens must be sent in the `Authorization` header as a Bearer token.

**Rate Limiting:**
- General endpoints: 60 requests per minute per IP
- Access validation: 60 requests per minute per IP (with optional IP whitelist configuration)

---

## Endpoints

### 1. List All Rooms

**GET** `/api/v1/rooms`

Returns a list of all available rooms in the system.

**Authentication:** Not required

**Query Parameters:**
- None

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "name": "Studio A",
      "description": "Main rehearsal studio",
      "capacity": 20,
      "has_projector": true,
      "shelly_ip": "192.168.1.100",
      "created_at": "2025-01-01T10:00:00Z",
      "updated_at": "2025-01-01T10:00:00Z"
    },
    {
      "id": 2,
      "name": "Studio B",
      "description": "Smaller rehearsal room",
      "capacity": 10,
      "has_projector": false,
      "shelly_ip": "192.168.1.101",
      "created_at": "2025-01-01T10:00:00Z",
      "updated_at": "2025-01-01T10:00:00Z"
    }
  ]
}
```

---

### 2. Check Room Availability

**GET** `/api/v1/rooms/{room_id}/availability`

Check if a room is available for a specific date range.

**Authentication:** Not required

**Path Parameters:**
- `room_id` (integer): ID of the room

**Query Parameters:**
- `start_at` (string, ISO 8601): Start date and time (e.g., `2025-01-15T10:00:00Z`)
- `end_at` (string, ISO 8601): End date and time (e.g., `2025-01-15T12:00:00Z`)

**Response:** `200 OK`

```json
{
  "room_id": 1,
  "start_at": "2025-01-15T10:00:00Z",
  "end_at": "2025-01-15T12:00:00Z",
  "is_available": true,
  "duration_minutes": 120,
  "conflicting_reservations": []
}
```

**Response (if conflicts exist):** `200 OK`

```json
{
  "room_id": 1,
  "start_at": "2025-01-15T10:00:00Z",
  "end_at": "2025-01-15T12:00:00Z",
  "is_available": false,
  "duration_minutes": 120,
  "conflicting_reservations": [
    {
      "id": 5,
      "user_id": 2,
      "start_at": "2025-01-15T10:30:00Z",
      "end_at": "2025-01-15T11:30:00Z"
    }
  ]
}
```

**Error Responses:**
- `404 Not Found`: Room does not exist
- `422 Unprocessable Entity`: Invalid date parameters

---

### 3. Validate Access Token

**POST** `/api/v1/access/validate`

Validate an access token for room entry. The system will:
1. Verify the token exists and hasn't been used
2. Check if the reservation is currently active
3. Mark the token as used by recording the timestamp
4. Log the access attempt for audit purposes

**Authentication:** Not required

**Request Body:**

```json
{
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f"
}
```

**Response:** `200 OK`

```json
{
  "valid": true,
  "reservation_id": 3,
  "room_id": 1,
  "room_name": "Studio A",
  "user_name": "John Doe",
  "start_at": "2025-01-15T14:00:00Z",
  "end_at": "2025-01-15T16:00:00Z",
  "message": "Access granted"
}
```

**Error Responses:**

- `400 Bad Request`: Invalid token format

```json
{
  "error": "Invalid token format",
  "message": "Token must be a 64-character hexadecimal string"
}
```

- `404 Not Found`: Token does not exist

```json
{
  "error": "Token not found",
  "message": "The provided token is not valid or has already been used"
}
```

- `403 Forbidden`: Reservation is not currently active

```json
{
  "error": "Reservation not active",
  "message": "This reservation has ended or has not yet started"
}
```

- `429 Too Many Requests`: Rate limit exceeded

```json
{
  "error": "Rate limit exceeded",
  "message": "Maximum 60 requests per minute. Please try again later."
}
```

---

### 4. Create Reservation

**POST** `/api/v1/reservations`

Create a new room reservation. Automatically generates an access token.

**Authentication:** Required (Bearer token)

**Request Body:**

```json
{
  "room_id": 1,
  "start_at": "2025-01-20T10:00:00Z",
  "end_at": "2025-01-20T12:00:00Z",
  "notes": "Band rehearsal - please ensure PA system is functional"
}
```

**Response:** `201 Created`

```json
{
  "id": 7,
  "user_id": 1,
  "room_id": 1,
  "room_name": "Studio A",
  "start_at": "2025-01-20T10:00:00Z",
  "end_at": "2025-01-20T12:00:00Z",
  "duration_minutes": 120,
  "notes": "Band rehearsal - please ensure PA system is functional",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f",
  "status": "active",
  "created_at": "2025-01-19T15:30:00Z",
  "updated_at": "2025-01-19T15:30:00Z"
}
```

**Validation Rules:**
- `room_id` (required): Must be a valid room ID
- `start_at` (required): ISO 8601 timestamp, must be in the future
- `end_at` (required): ISO 8601 timestamp, must be after `start_at`
- Duration must be at least 15 minutes (configurable via `config('reservations.min_duration_minutes')`)
- Time slot must not conflict with existing reservations
- `notes` (optional): Maximum 1000 characters

**Error Responses:**

- `401 Unauthorized`: No valid authentication token

```json
{
  "error": "Unauthenticated",
  "message": "Missing or invalid authentication token"
}
```

- `422 Unprocessable Entity`: Validation failed

```json
{
  "errors": {
    "room_id": ["The room_id field is required"],
    "start_at": ["The start_at must be in the future"],
    "end_at": ["The duration must be at least 15 minutes"],
    "time_conflict": ["The selected time overlaps with existing reservation(s)"]
  }
}
```

---

### 5. Get User's Reservations

**GET** `/api/v1/reservations`

Retrieve all reservations for the authenticated user.

**Authentication:** Required (Bearer token)

**Query Parameters:**
- `status` (string, optional): Filter by status (`active`, `past`, `upcoming`) - defaults to all
- `limit` (integer, optional): Maximum number of results (default: 20, max: 100)
- `offset` (integer, optional): Pagination offset (default: 0)

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 7,
      "room_id": 1,
      "room_name": "Studio A",
      "start_at": "2025-01-20T10:00:00Z",
      "end_at": "2025-01-20T12:00:00Z",
      "duration_minutes": 120,
      "notes": "Band rehearsal",
      "status": "upcoming",
      "token_used_at": null,
      "created_at": "2025-01-19T15:30:00Z"
    },
    {
      "id": 6,
      "room_id": 2,
      "room_name": "Studio B",
      "start_at": "2025-01-15T14:00:00Z",
      "end_at": "2025-01-15T16:00:00Z",
      "duration_minutes": 120,
      "notes": "Solo practice",
      "status": "past",
      "token_used_at": "2025-01-15T14:05:00Z",
      "created_at": "2025-01-14T10:00:00Z"
    }
  ],
  "meta": {
    "total": 2,
    "limit": 20,
    "offset": 0
  }
}
```

---

### 6. Get Reservation Details

**GET** `/api/v1/reservations/{reservation_id}`

Retrieve details for a specific reservation.

**Authentication:** Required (Bearer token)

**Path Parameters:**
- `reservation_id` (integer): ID of the reservation

**Response:** `200 OK`

```json
{
  "id": 7,
  "user_id": 1,
  "user_name": "John Doe",
  "room_id": 1,
  "room_name": "Studio A",
  "start_at": "2025-01-20T10:00:00Z",
  "end_at": "2025-01-20T12:00:00Z",
  "duration_minutes": 120,
  "notes": "Band rehearsal - please ensure PA system is functional",
  "status": "upcoming",
  "token_used_at": null,
  "created_at": "2025-01-19T15:30:00Z",
  "updated_at": "2025-01-19T15:30:00Z"
}
```

**Error Responses:**
- `403 Forbidden`: User not authorized to view this reservation
- `404 Not Found`: Reservation does not exist

---

### 7. Update Reservation

**PATCH** `/api/v1/reservations/{reservation_id}`

Update a reservation (only allowed before token is used or reservation starts).

**Authentication:** Required (Bearer token)

**Path Parameters:**
- `reservation_id` (integer): ID of the reservation

**Request Body:**

```json
{
  "start_at": "2025-01-20T11:00:00Z",
  "end_at": "2025-01-20T13:00:00Z",
  "notes": "Updated notes"
}
```

**Response:** `200 OK` (same structure as Get Reservation Details)

**Error Responses:**
- `403 Forbidden`: Cannot update - token already used or reservation has started
- `404 Not Found`: Reservation does not exist
- `422 Unprocessable Entity`: Validation failed

---

### 8. Cancel Reservation

**DELETE** `/api/v1/reservations/{reservation_id}`

Cancel (soft delete) a reservation.

**Authentication:** Required (Bearer token)

**Path Parameters:**
- `reservation_id` (integer): ID of the reservation

**Response:** `204 No Content`

**Error Responses:**
- `403 Forbidden`: Cannot cancel - token already used or reservation has started
- `404 Not Found`: Reservation does not exist

---

## Authentication

### Obtaining an Authentication Token

Tokens are issued via the authentication endpoints (managed by Laravel Breeze):

**POST** `/api/login`

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

Response:

```json
{
  "token": "1|abc123def456ghi789jkl..."
}
```

### Using Authentication Token

Include the token in the `Authorization` header for all protected requests:

```
Authorization: Bearer 1|abc123def456ghi789jkl...
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created successfully |
| 204 | No Content - Request succeeded (no response body) |
| 400 | Bad Request - Invalid request parameters |
| 401 | Unauthorized - Missing or invalid authentication |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

---

## Error Format

All error responses follow this format:

```json
{
  "error": "Error type",
  "message": "Detailed error message"
}
```

For validation errors:

```json
{
  "errors": {
    "field_name": ["Error message for this field"],
    "other_field": ["Multiple errors possible", "Second error"]
  }
}
```

---

## Rate Limiting Headers

All responses include rate limit information in headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1705694400
```

---

## Webhook Events (Future)

The following events are available for webhook subscriptions (contact support to configure):

- `reservation.created` - A new reservation was created
- `reservation.cancelled` - A reservation was cancelled
- `access.validated` - An access token was successfully validated
- `access.denied` - An access token validation failed

---

## API Changelog

### Version 1.0 (Current)

- Initial public API release
- Endpoints for rooms, reservations, and access validation
- Token-based authentication
- Audit logging for all access attempts

---

## Support

For API issues or questions, please contact the development team.

For a detailed list of internal audit logging and administrative endpoints, see `API_ADMIN.md`.
