# API Endpoints - Discord Bot Integration

This document describes the API endpoints for Discord bot integration. The system is compatible with our [Discord Bot](https://github.com/Vatsim-Scandinavia/discord-bot).

## Overview

- **All staffing bookings happen through the Discord bot** (no frontend booking)
- Events communicates with your bot's FastAPI server for Discord message management
- Bot communicates back to Events API for booking operations
- Control Center bookings are created/deleted automatically

## Authentication

All API requests to Events from the bot require token authentication in the request body or headers.

## Bot → Events Endpoints

These endpoints are called by your Discord bot.

### 1. Get All Events

**GET** `/api/events`

**Query Parameters:**
- `upcoming` (boolean, default: true) - Only future events
- `staffing` (boolean, default: false) - Only events with staffing

**Response:**
```json
[
  {
    "id": 4,
    "name": "Welcome to HEL",
    "short_description": "Event at Helsinki",
    "description": "Full description markdown...",
    "date": "2026-01-25",
    "start_time": "09:00",
    "end_time": "21:00",
    "start_datetime": "2026-01-25T09:00:00.000000Z",
    "end_datetime": "2026-01-25T21:00:00.000000Z",
    "airports": ["EFHK"],
    "banner": "http://your-domain.com/storage/banners/event.jpg",
    "url": "http://your-domain.com/events/4",
    "discord_staffing_channel_id": "1460774578170368020",
    "discord_staffing_message_id": "1462162109759619225",
    "has_staffing": true
  }
]
```

### 2. Get Event Staffing

**GET** `/api/events/{id}/staffing`

Returns complete staffing data for the event including all sections and positions.

**Response:**
```json
{
  "event_id": 4,
  "title": "Welcome to HEL",
  "description": "Staffing description in markdown...",
  "channel_id": "1460774578170368020",
  "message_id": "1462162109759619225",
  "start_date": "2026-01-25 09:00:00",
  "end_date": "2026-01-25 21:00:00",
  "staffing": [
    {
      "id": 3,
      "name": "Early Shift",
      "positions": [
        {
          "id": 11,
          "position": "EKCH_F_APP",
          "name": "Kastrup Final",
          "start_time": "2026-01-25 09:00:00",
          "end_time": "2026-01-25 12:00:00",
          "booked": false,
          "user": null
        },
        {
          "id": 12,
          "position": "EKCH_TWR",
          "name": "Kastrup Tower",
          "start_time": "2026-01-25 09:00:00",
          "end_time": "2026-01-25 12:00:00",
          "booked": true,
          "user": {
            "id": 1234567,
            "name": "CID 1234567"
          }
        }
      ]
    }
  ]
}
```

**Notes:**
- `position` field contains the callsign (e.g., `EKCH_TWR`)
- Times are in UTC/Zulu
- `user.id` is the VATSIM CID for Discord bookings
- `user.name` is "CID {cid}" for Discord bookings

### 3. Book Position

**POST** `/api/staffing`

Books a position for a Discord user.

**Request Body:**
```json
{
  "cid": 1234567,
  "discord_user_id": "123456789012345678",
  "position": "EKCH_TWR",
  "message_id": "1462162109759619225"
}
```

**Response:**
```json
{
  "message": "Position booked successfully",
  "position_id": 12
}
```

**Behavior:**
- Creates Control Center booking automatically
- Stores `vatsim_cid` and `discord_user_id` on the position
- Returns error if position already booked

### 4. Unbook Position

**DELETE** `/api/staffing`

Unbooks a position.

**Request Body:**
```json
{
  "discord_user_id": "123456789012345678",
  "message_id": "1462162109759619225",
  "position": "EKCH_TWR"
}
```

**Response:**
```json
{
  "message": "Position unbooked successfully"
}
```

**Behavior:**
- Deletes Control Center booking automatically
- Clears `vatsim_cid`, `discord_user_id`, `booked_by_user_id` fields

### 5. Get Staffing by ID

**GET** `/api/staffings/{staffing_id}`

Get specific staffing section (useful for direct lookups).

**Response:**
```json
{
  "id": 3,
  "event_id": 4,
  "title": "Welcome to HEL",
  "name": "Early Shift",
  "positions": [
    {
      "id": 11,
      "position": "EKCH_TWR",
      "name": "Kastrup Tower",
      "start_time": "2026-01-25 09:00:00",
      "end_time": "2026-01-25 12:00:00",
      "booked": true,
      "user": {
        "id": 1234567,
        "name": "CID 1234567"
      }
    }
  ]
}
```

### 6. Reset Staffing (Manual)

**POST** `/api/staffings/{staffing_id}/reset`

Manually resets all bookings for a staffing (clears all positions, deletes CC bookings).

**Request Body:**
```json
{}
```

**Response:**
```json
{
  "message": "Staffing reset successfully"
}
```

**Behavior:**
- Clears all bookings from all positions in the event
- Deletes all Control Center bookings
- Triggers Discord message update

## Events → Bot Endpoints

These endpoints must be implemented on your bot's FastAPI server. Events calls these to manage Discord messages.

### 1. Setup Staffing Message

**POST** `{DISCORD_BOT_API_URL}/staffings/setup`

**Headers:**
```
Authorization: Bearer {DISCORD_BOT_API_TOKEN}
Content-Type: application/x-www-form-urlencoded
```

**Request Body:**
```
id=3
```

**Your Bot Should:**
1. Call `GET /api/events/{event_id}/staffing` to fetch data
2. Create Discord embed
3. Post to configured Discord channel
4. Store `message_id` internally for updates

**Response:**
```json
{
  "success": true
}
```

### 2. Update Staffing Message

**POST** `{DISCORD_BOT_API_URL}/staffings/update`

**Headers:**
```
Authorization: Bearer {DISCORD_BOT_API_TOKEN}
Content-Type: application/x-www-form-urlencoded
```

**Request Body:**
```
id=3
```

**Your Bot Should:**
1. Call `GET /api/events/{event_id}/staffing` to fetch updated data
2. Update the existing Discord message
3. Refresh button states (show which positions are booked)

**Response:**
```json
{
  "success": true
}
```

## Workflow Examples

### Event Creation & Discord Setup

1. **Admin creates event** with recurring rule
2. **Admin adds staffing sections and positions**
3. **Admin clicks "Setup in Discord"**
4. **Events** → Bot: `POST /staffings/setup` with `id`
5. **Bot** → Events: `GET /api/events/{id}/staffing`
6. **Bot** → Discord API: Posts interactive message
7. **Bot** stores `message_id` for future updates

### User Books Position via Discord

1. **User uses /book command** in Discord
2. **Discord** → Bot: Command interaction event
3. **Bot** → Events: `POST /api/staffing` with cid, discord_user_id, position, message_id
4. **Events** creates booking and Control Center entry
5. **Events** → Bot: `POST /staffings/update` with `id`
6. **Bot** → Events: `GET /api/events/{id}/staffing`
7. **Bot** → Discord API: Updates message to show position as booked

### User Unbooks Position via Discord

1. **User uses /unbook command** in Discord
2. **Discord** → Bot: Command interaction event
3. **Bot** → Evenets: `DELETE /api/staffing` with discord_user_id, position, message_id
4. **Events** deletes booking and Control Center entry
5. **Events** → Bot: `POST /staffings/update` with `id`
6. **Bot** → Events: `GET /api/events/{id}/staffing`
7. **Bot** → Discord API: Updates message to show position as available

### Moderator Unbooks via Web

1. **Moderator clicks "Unbook"** on position in web interface
2. **Events** deletes booking and Control Center entry
3. **Events** → Bot: `POST /staffings/update` with `id`
4. **Bot** → Events: `GET /api/events/{id}/staffing`
5. **Bot** → Discord API: Updates message

### Manual Staffing Reset (Bot Triggered)

1. **Bot command** `/manreset {staffing_id}`
2. **Bot** → Events: `POST /api/staffings/{id}/reset`
3. **Events** clears all bookings and Control Center entries
4. **Events** → Bot: `POST /staffings/update` with `id`
5. **Bot** → Events: `GET /api/events/{id}/staffing`
6. **Bot** → Discord API: Updates message
7. **Bot** → Discord: Purges channel messages (optional)

### Automatic Staffing Reset (Recurring Events)

1. **Hourly job** checks for completed event occurrences
2. **Events** resets staffing for next occurrence
3. **Events** clears all bookings and Control Center entries
4. **Events** → Bot: `POST /staffings/update` with `id`
5. **Bot** → Discord API: Updates message for next occurrence

## Configuration

### Event system `.env`

```env
# Your bot's FastAPI server
DISCORD_BOT_API_URL=
DISCORD_BOT_API_TOKEN=

# Discord bot token (for fetching channels)
DISCORD_BOT_TOKEN=

# Discord server to restrict channel fetching
DISCORD_GUILD_ID=

# Role to mention for event notifications
DISCORD_MENTION_ROLE_ID=

# Control Center API
CONTROL_CENTER_API_URL=
CONTROL_CENTER_API_TOKEN=

# Discord webhook for event notifications (optional)
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...
```

### Bot Configuration

Your bot needs:
- Events systems API URL
- Shared secret token (matching `DISCORD_BOT_API_TOKEN`)
- Discord bot token
- Discord channel ID for staffing messages

## Important Notes

### Control Center Integration

- Events automatically creates CC bookings when positions are booked
- Events automatically deletes CC bookings when positions are unbooked
- Booking format: `{cid, date (d/m/Y), position (callsign), start_at (H:i), end_at (H:i), tag: 3, source}`
- CC booking ID is stored on `staffing_positions.control_center_booking_id`

### UTC Times

All times in the API are in UTC (Zulu time). The bot should display them as such.

### Discord Channels

- Events fetches Discord channels directly using `DISCORD_BOT_TOKEN`
- Only channels containing "staffing" in their name are shown
- Channels are filtered by `DISCORD_GUILD_ID` if configured
- Same channel cannot be used for multiple events

### Staffing Restrictions

- Only recurring events can have staffing
- Staffing sections can only be created for events with a `recurrence_rule`

### Pre-Event Reminders

The Events system automatically sends Discord notifications 2 hours before event start (with role mention).

## Discord Bot

This Events system is build with support for our [Official Discord Bot](https://github.com/Vatsim-Scandinavia/discord-bot). 

Your existing Discord bot code will work immediately!

## Error Handling

### Bot Server Unreachable

- Events logs error and continues
- Discord message won't update
- Check `storage/logs/laravel.log`

### Control Center API Error

- Events logs error
- Booking in Events succeeds, but not in CC
- Tech can manually fix in Control Center

### Position Already Booked

- API returns 422 with error message
- Bot should display error to user

### Invalid Position/Message ID

- API returns 404 Not Found
- Check that message_id and position callsign are correct
