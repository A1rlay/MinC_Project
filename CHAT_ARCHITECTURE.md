# Chat System Architecture & User Flow

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        MINC AUTO SUPPLY                         │
│                       CHAT SYSTEM v1.0                          │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────┐          ┌──────────────────────────┐
│   CUSTOMER INTERFACE     │          │    ADMIN INTERFACE       │
├──────────────────────────┤          ├──────────────────────────┤
│                          │          │                          │
│  Home Page               │          │  Dashboard               │
│  └─ Chat Bubble          │          │  └─ Sidebar              │
│     ├─ Normal View       │◄────────►│     └─ Customer Messages │
│     └─ Expanded Modal    │          │        ├─ Conversation   │
│                          │          │        │   List           │
│  Features:              │          │        └─ Message Thread  │
│  • Send Messages         │          │                          │
│  • View History          │          │  Features:               │
│  • Real-time Updates     │          │  • View All Messages     │
│  • Expandable View       │          │  • Respond to Customers  │
│  • Session Persistence   │          │  • Track Unread Count    │
│                          │          │  • Message History       │
└──────────────────────────┘          └──────────────────────────┘
         │                                      │
         └──────────────┬──────────────────────┘
                        │
            ┌───────────▼───────────┐
            │   BACKEND API         │
            │ send_message.php      │
            ├───────────────────────┤
            │                       │
            │  POST: Send Message   │
            │  GET: Retrieve Msgs   │
            │  • Session handling   │
            │  • XSS Protection     │
            │  • Validation         │
            └───────────┬───────────┘
                        │
            ┌───────────▼───────────┐
            │   MYSQL DATABASE      │
            │   chat_messages       │
            ├───────────────────────┤
            │                       │
            │  Columns:             │
            │  • message_id         │
            │  • sender_id          │
            │  • sender_name        │
            │  • sender_email       │
            │  • sender_type        │
            │  • message_content    │
            │  • is_read            │
            │  • read_at            │
            │  • created_at         │
            │  • session_id         │
            │                       │
            └───────────────────────┘
```

## User Flow - Customer

```
Customer Opens Home Page
        │
        ▼
See Chat Bubble (bottom-right)
        │
        ├─────► Click Bubble
        │           │
        │           ▼
        │       Compact Chat Opens
        │           │
        │           ├─────► Type Message
        │           │           │
        │           │           ▼
        │           │       Press Enter/Send
        │           │           │
        │           │           ▼
        │           │       Message Sent ✓
        │           │       (to database)
        │           │
        │           ├─────► Click Expand Button
        │           │           │
        │           │           ▼
        │           │       Full-Screen Modal
        │           │           │
        │           │           └─ Better for mobile
        │           │
        │           └─────► Click Close
        │                       │
        │                       ▼
        │                   Chat Closes
        │
        └─ Auto-Refresh Every 3 Seconds
              (if chat is open)
              │
              ▼
          Check for Admin
          Responses
              │
              ├─ New Messages → Display
              └─ No New → Keep current
```

## User Flow - Admin

```
Admin Logs In
        │
        ▼
Go to Dashboard
        │
        ▼
Click "Customer Messages"
(in Customer Service section)
        │
        ▼
View Conversation List
        │
        ├─ Unread Count Badges ◄─ Red highlights
        │
        ├─ Click Conversation
        │       │
        │       ▼
        │   View Full Thread
        │   (chronological order)
        │       │
        │       ├─ Customer msgs (gray, left)
        │       └─ Admin msgs (teal, right)
        │
        ├─ Type Response
        │       │
        │       ▼
        │   Textarea with Auto-Resize
        │       │
        │       ▼
        │   Click Send
        │       │
        │       ▼
        │   Message Posted ✓
        │   (appears in thread)
        │
        └─ Auto-Refresh Every 5 Seconds
              (whole page reloads)
              │
              ▼
          Check for New Customer
          Messages
              │
              ├─ New Messages → Display
              └─ No New → Keep current
```

## Data Flow Diagram

```
┌─────────────────────────────────────────┐
│      CUSTOMER BROWSER                   │
│  (localStorage)                         │
│  ├─ chat_session_id                     │
│  ├─ customer_name (optional)            │
│  └─ customer_email (optional)           │
└────────────┬────────────────────────────┘
             │
             │ Fetch POST
             │ {
             │   message_content: "...",
             │   sender_name: "John",
             │   sender_email: "john@...",
             │   sender_type: "customer",
             │   session_id: "session_..."
             │ }
             │
             ▼
┌─────────────────────────────────────────┐
│  /backend/chat/send_message.php         │
│  ├─ Validate input                      │
│  ├─ Escape HTML (XSS protection)        │
│  ├─ Generate session ID if new          │
│  └─ Insert into database                │
└────────────┬────────────────────────────┘
             │
             │ SQL INSERT
             │
             ▼
┌─────────────────────────────────────────┐
│      MySQL Database                     │
│      chat_messages Table                │
│  ┌─────────────────────────────────┐   │
│  │ ID │ Name  │ Type     │ Content │   │
│  ├─────────────────────────────────┤   │
│  │ 1  │ John  │ customer │ "Hi..." │   │
│  │ 2  │ Admin │ admin    │ "Hey..."│   │
│  │ 3  │ John  │ customer │ "Thx..."│   │
│  └─────────────────────────────────┘   │
└────────────┬────────────────────────────┘
             │
             │ Fetch GET
             │ /send_message.php
             │ ?type=customer
             │ &session_id=...
             │
             ▼
┌─────────────────────────────────────────┐
│  /backend/chat/send_message.php         │
│  ├─ Query database                      │
│  ├─ Filter by session_id                │
│  ├─ Return array of messages            │
│  └─ JSON response                       │
└────────────┬────────────────────────────┘
             │
             │ Response JSON
             │ {
             │   status: "success",
             │   data: [
             │     {message_id, sender_name,
             │      message_content, ...}
             │   ]
             │ }
             │
             ▼
┌─────────────────────────────────────────┐
│      CUSTOMER BROWSER                   │
│  JavaScript Updates:                    │
│  ├─ Parse JSON response                 │
│  ├─ Create HTML for each message        │
│  ├─ Append to chat container            │
│  ├─ Scroll to bottom                    │
│  └─ Display message                     │
└─────────────────────────────────────────┘

[Similar flow for Admin side, but:
 - Admin panel fetches all conversations
 - Grouped by session_id and sender_email
 - Shows unread counts
 - Allows responding with sender_type: "admin"]
```

## File Structure

```
MinC_Project/
│
├── index.php                           [MODIFIED]
│   └─ Includes chat_bubble.php
│
├── database/
│   └── MinC.sql                        [MODIFIED]
│       └─ Added chat_messages table
│
├── backend/
│   ├── auth.php
│   ├── connect_database.php
│   └── chat/
│       └── send_message.php            [NEW]
│           └─ API for messages
│
├── html/
│   └── components/
│       └── chat_bubble.php             [NEW]
│           └─ Customer chat interface
│
├── app/
│   └── frontend/
│       ├── chat-admin.php              [NEW]
│       │   └─ Admin message panel
│       │
│       └── components/
│           └── sidebar.php             [MODIFIED]
│               └─ Added chat admin link
│
├── setup/
│   └── setup_chat.php                  [NEW]
│       └─ Database initialization
│
└── Documentation/
    ├── CHAT_SYSTEM_README.md           [NEW]
    ├── CHAT_IMPLEMENTATION_SUMMARY.md  [NEW]
    └── CHAT_QUICK_START.md             [NEW]
```

## Message Status Tracking

```
Customer Message Lifecycle:
────────────────────────────

1. Created
   └─ is_read: 0
   └─ read_at: NULL

2. Sent to DB
   └─ created_at: NOW()
   └─ is_read: 0

3. Admin Opens
   └─ is_read: 1
   └─ read_at: NOW()

4. Admin Responds
   └─ New message created
   └─ sender_type: 'admin'
   └─ Created entry visible to customer


Unread Count:
─────────────

SUM(CASE WHEN is_read = 0 AND sender_type = 'customer' THEN 1 ELSE 0 END)

Shows only unread CUSTOMER messages (not admin's own messages)
Resets when admin opens conversation
```

## Security Flow

```
Customer Input
      │
      ▼
┌─────────────────┐
│ Frontend Escape │  htmlspecialchars() in JS
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Send via JSON  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Backend Processing                  │
│ ├─ Validate message not empty       │
│ ├─ htmlspecialchars() on all input  │
│ ├─ Parameterized SQL (PDO)          │
│ ├─ Check session/authorization      │
│ └─ Log action                       │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  Safe Storage   │  Escaped content in DB
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Retrieval & Display                 │
│ ├─ PDO fetch (parameterized)        │
│ ├─ htmlspecialchars() in backend    │
│ ├─ JavaScript escape() on display   │
│ └─ Safe HTML rendering             │
└─────────────────────────────────────┘
```

## Real-Time Update Cycle

```
Customer Browser                Admin Browser
──────────────────              ─────────────

Every 3 seconds:               Every 5 seconds:
├─ If chat open                ├─ Always running
│  └─ Fetch messages           │  └─ Full page reload
│     from database                 (or AJAX fetch)
│                              │
├─ Parse JSON response         ├─ Get all conversations
│                              │
├─ Display new messages        ├─ Update counts
│                              │
└─ Auto-scroll to bottom       └─ Highlight new messages
   (if no user scroll)
```

## Access Control Matrix

```
┌──────────────────┬─────────┬──────────┬──────────┬──────────┐
│ Feature          │ Customer│ Manager  │ IT Staff │ Owner    │
├──────────────────┼─────────┼──────────┼──────────┼──────────┤
│ Chat Bubble      │   ✓     │    ✓     │    ✓     │    ✓     │
│ Send Messages    │   ✓     │    ✓     │    ✓     │    ✓     │
│ View Own Chat    │   ✓     │    ✓     │    ✓     │    ✓     │
├──────────────────┼─────────┼──────────┼──────────┼──────────┤
│ Admin Panel      │   ✗     │    ✗     │    ✓     │    ✓     │
│ View All Chats   │   ✗     │    ✗     │    ✓     │    ✓     │
│ Respond          │   ✗     │    ✗     │    ✓     │    ✓     │
│ Mark as Read     │   Auto  │   Auto   │   Auto   │   Auto   │
└──────────────────┴─────────┴──────────┴──────────┴──────────┘

User Level IDs:
1 = Owner (full access)
2 = Manager (customer side only)
3 = Customer (customer side only)
5 = IT Staff (full admin access)
```

---

This architecture ensures:
✓ Real-time messaging
✓ Data persistence
✓ Security & XSS protection
✓ Scalability
✓ User-friendly experience
✓ Admin visibility
