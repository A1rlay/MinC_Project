# Chat System Documentation

## Overview
The MinC Auto Supply chat system enables customers to communicate with the business owner/admin through a simple bubble interface on the home page. The owner can manage all conversations through an admin panel.

## Features

### Customer Side
- **Chat Bubble**: Small floating bubble (bottom-right) on the home page
- **Expandable Chat Window**: Click bubble to open compact chat (with expand button)
- **Full-Screen Chat Modal**: Expanded version for better visibility on mobile/desktop
- **Real-time Updates**: Messages refresh every 3 seconds
- **Session Persistence**: Chat history stored per browser session (localStorage)
- **Message History**: Customers can see conversation history and admin responses

### Admin/Owner Side
- **Chat Admin Panel**: Dedicated panel to view all customer conversations
- **Conversation List**: View all active conversations with:
  - Customer name and email
  - Number of messages
  - Last message timestamp
  - Unread message count
  - Unread indicator badge
- **Message Management**: View full conversation thread and respond
- **Auto-refresh**: Panel refreshes every 5 seconds to show new messages
- **Quick Response**: Easy interface to respond to customer inquiries

## File Structure

```
MinC_Project/
├── database/
│   └── MinC.sql (includes chat_messages table)
├── backend/
│   └── chat/
│       └── send_message.php (API for sending/receiving messages)
├── html/
│   └── components/
│       └── chat_bubble.php (Customer-facing chat component)
├── app/
│   ├── frontend/
│   │   ├── chat-admin.php (Admin/Owner chat management panel)
│   │   └── components/
│   │       └── sidebar.php (Updated with chat admin link)
└── index.php (Home page with chat bubble)
```

## Database Schema

### chat_messages Table
```sql
CREATE TABLE `chat_messages` (
  `message_id` bigint UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sender_id` bigint UNSIGNED NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NULL,
  `sender_type` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `message_content` longtext NOT NULL,
  `is_read` tinyint DEFAULT 0,
  `read_at` timestamp NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `session_id` varchar(255) NULL,
  KEY `sender_id` (`sender_id`),
  KEY `sender_type` (`sender_type`),
  KEY `created_at` (`created_at`),
  KEY `is_read` (`is_read`)
)
```

## API Endpoints

### Send/Receive Messages
**Endpoint**: `/backend/chat/send_message.php`

#### POST - Send Message
```json
Request:
{
  "message_content": "Hello, I have a question...",
  "sender_name": "John Doe",
  "sender_email": "john@example.com",
  "sender_type": "customer" | "admin",
  "session_id": "optional"
}

Response:
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "message_id": 1,
    "sender_id": null,
    "sender_name": "John Doe",
    "sender_email": "john@example.com",
    "sender_type": "customer",
    "message_content": "Hello, I have a question...",
    "is_read": 0,
    "created_at": "2026-01-28 12:34:56",
    "session_id": "session_..."
  }
}
```

#### GET - Retrieve Messages
```
URL: /backend/chat/send_message.php?type=customer&session_id=session_123

Response:
{
  "status": "success",
  "data": [
    {
      "message_id": 1,
      "sender_name": "John Doe",
      "sender_type": "customer",
      "message_content": "Hello!",
      "created_at": "2026-01-28 12:34:56",
      ...
    }
  ],
  "session_id": "session_123"
}
```

## Usage

### For Customers
1. Visit the home page (index.php)
2. Click the chat bubble in the bottom-right corner
3. Type your message in the input field
4. Click "Send" or press Enter
5. Optionally click the expand button to view in full-screen modal

### For Admin/Owner
1. Login to the dashboard
2. Navigate to "Customer Messages" under "Customer Service" section
3. Select a conversation from the list
4. View the full conversation thread
5. Type a response in the message input area
6. Click "Send" to respond

## Features Details

### Session Management
- Each customer browser session gets a unique session ID
- Stored in localStorage as `chat_session_id`
- Enables message history retrieval across page visits

### Message Status
- `is_read`: Tracks whether customer has read admin messages
- `read_at`: Timestamp when customer marked as read
- Admin panel shows unread count per conversation

### Real-time Updates
- **Customer side**: Refreshes every 3 seconds when chat is open
- **Admin side**: Refreshes every 5 seconds to show new messages

### Responsive Design
- **Desktop**: Compact chat bubble + expandable modal (optimized for 2xl width)
- **Tablet**: Full-screen modal with improved spacing
- **Mobile**: Full-screen chat interface with better touch targets

## Security Features

1. **XSS Prevention**:
   - All user input sanitized with `htmlspecialchars()` in backend
   - HTML escaping on frontend before display

2. **Access Control**:
   - Chat admin panel restricted to IT Staff and Owner (user_level_id 1 or 5)
   - Session validation for message retrieval

3. **Data Validation**:
   - Message content validation (non-empty)
   - Email format validation where applicable
   - Input length checks

## Customization

### Color Scheme
The chat system uses the teal theme (#08415c, #0a5273) matching the rest of the application.

To customize:
1. Update color values in `chat_bubble.php` (lines with `from-[#08415c]` etc.)
2. Update colors in `chat-admin.php` for the admin interface

### Message Refresh Rate
- **Customer**: Change interval in `chat_bubble.php` (line ~350): `setInterval(..., 3000)`
- **Admin**: Change interval in `chat-admin.php` (line ~290): `setInterval(..., 5000)`

### Appearance
- Chat bubble size: Modify width/height classes in `chat_bubble.php` (#chat-toggle-btn)
- Modal dimensions: Update `w-96 h-96` (or `sm:w-2xl`) classes in `chat_bubble.php`
- Styling: Customize Tailwind classes throughout both components

## Troubleshooting

### Messages Not Sending
1. Check browser console for JavaScript errors
2. Verify `/backend/chat/send_message.php` exists and is accessible
3. Ensure database table `chat_messages` exists and is properly created

### Messages Not Loading
1. Check that localStorage is enabled in browser
2. Verify database connection in `backend/chat/send_message.php`
3. Check browser console for fetch errors

### Admin Panel Access Issues
1. Verify user is logged in as Owner or IT Staff
2. Check user_level_id is 1 (Owner) or 5 (IT Staff)
3. Ensure sidebar link is visible for authorized users

## Future Enhancements

- WebSocket integration for true real-time messaging
- Typing indicators
- File/image upload support
- Message search functionality
- Conversation archiving
- Custom chatbot responses
- Email notifications for new messages
- Chat rating/feedback system
