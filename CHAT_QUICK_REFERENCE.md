# Chat System - Quick Reference Card

## 🚀 Quick Start (3 Steps)

1. **Initialize Database**: Visit `http://localhost/pages/MinC_Project/setup/setup_chat.php`
2. **Test as Customer**: Go to home page, click chat bubble (bottom-right)
3. **Manage as Admin**: Dashboard → "Customer Messages"

---

## 📍 Key Files & Locations

| File | Purpose | Access |
|------|---------|--------|
| `/html/components/chat_bubble.php` | Customer chat interface | Home page |
| `/app/frontend/chat-admin.php` | Admin message panel | Dashboard (Owner/IT Staff) |
| `/backend/chat/send_message.php` | Message API | Internal API |
| `/database/MinC.sql` | Database schema | DB initialization |
| `/setup/setup_chat.php` | Setup tool | Browser |

---

## 💬 Customer Interface

```
┌─ Chat Bubble (bottom-right)
│  └─ Click to open
│     ├─ Normal view (384×384px)
│     └─ Click expand for full-screen
│
└─ Features:
   • Send/receive messages
   • View conversation history
   • Timestamps on messages
   • Auto-refresh every 3 seconds
```

---

## 👨‍💼 Admin Interface

```
Dashboard → Customer Messages

┌─ Left Pane: Conversation List
│  ├─ All customer conversations
│  ├─ Unread count badges
│  ├─ Customer name & email
│  └─ Last message time
│
└─ Right Pane: Message Thread
   ├─ Full conversation
   ├─ Chronological order
   ├─ Message composer
   └─ Send button
```

---

## 🔑 Key Shortcuts

| Action | Command |
|--------|---------|
| Send Message | Enter key or Send button |
| Expand Chat | Click expand icon in bubble |
| Close Chat | Click X or outside modal |
| View Chats (Admin) | Dashboard → Customer Messages |
| Refresh Messages | Auto-refresh every 3-5 seconds |

---

## 🎯 Message Flow

```
Customer Types → Clicks Send
        ↓
API /backend/chat/send_message.php
        ↓
Stores in Database
        ↓
Admin Panel Auto-refreshes
        ↓
Admin Responds
        ↓
Customer Sees Response (3-second refresh)
```

---

## 🔐 Access Control

| Role | Can Chat | Can Respond | Can See All |
|------|----------|-------------|------------|
| Customer | ✅ | ❌ | ❌ |
| Manager | ✅ | ❌ | ❌ |
| IT Staff | ✅ | ✅ | ✅ |
| Owner | ✅ | ✅ | ✅ |

---

## 🛠️ Customization

### Change Bubble Color
Edit `/html/components/chat_bubble.php`
```css
from-[#08415c] to-[#0a5273]  /* Change these hex codes */
```

### Change Refresh Rate (Customer)
Edit `/html/components/chat_bubble.php` (line ~350)
```javascript
setInterval(function() {...}, 3000);  /* 3000ms = 3 seconds */
```

### Change Refresh Rate (Admin)
Edit `/app/frontend/chat-admin.php` (line ~290)
```javascript
setInterval(function() {...}, 5000);  /* 5000ms = 5 seconds */
```

### Move Chat Bubble
Edit `/html/components/chat_bubble.php` (line ~2)
```html
class="fixed bottom-6 right-6"  /* Adjust: bottom-4, right-4, etc. */
```

---

## 📊 Database Query Examples

### Get All Messages for a Customer
```sql
SELECT * FROM chat_messages 
WHERE session_id = 'session_xxx'
ORDER BY created_at ASC;
```

### Get Unread Count
```sql
SELECT COUNT(*) as unread 
FROM chat_messages 
WHERE is_read = 0 AND sender_type = 'customer';
```

### Get Conversations Summary
```sql
SELECT session_id, sender_name, sender_email,
       COUNT(*) as msg_count,
       MAX(created_at) as last_msg
FROM chat_messages
WHERE sender_type = 'customer'
GROUP BY session_id, sender_email
ORDER BY last_msg DESC;
```

### Mark Messages as Read
```sql
UPDATE chat_messages 
SET is_read = 1, read_at = NOW() 
WHERE session_id = 'session_xxx' AND sender_type = 'customer';
```

---

## 🔗 API Endpoints

### Send Message (POST)
```
URL: /backend/chat/send_message.php
Method: POST
Content-Type: application/json

Body:
{
  "message_content": "Hello!",
  "sender_name": "John Doe",
  "sender_email": "john@example.com",
  "sender_type": "customer"
}
```

### Get Messages (GET)
```
URL: /backend/chat/send_message.php?type=customer&session_id=session_xxx
Method: GET

Response: Array of message objects
```

---

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Chat bubble not showing | Check index.php includes chat_bubble.php |
| Messages not sending | Verify database table exists (run setup) |
| Admin panel not loading | Check user is Owner/IT Staff (level 1 or 5) |
| Auto-refresh not working | Check browser console for JS errors |
| Database error | Verify MinC.sql was executed or run setup_chat.php |

---

## 📞 Documentation Links

- **Full Guide**: `CHAT_SYSTEM_README.md`
- **Quick Start**: `CHAT_QUICK_START.md`
- **Architecture**: `CHAT_ARCHITECTURE.md`
- **Implementation**: `CHAT_IMPLEMENTATION_SUMMARY.md`
- **Completion**: `CHAT_COMPLETION_REPORT.md`

---

## ✅ Verification Checklist

- [ ] Database table created
- [ ] Chat bubble visible on home page
- [ ] Can send message as customer
- [ ] Message appears in chat window
- [ ] Admin panel accessible
- [ ] Conversation shows in admin list
- [ ] Can respond from admin panel
- [ ] Response visible to customer
- [ ] Unread counts accurate
- [ ] Auto-refresh working

---

## 💡 Pro Tips

1. **Store Customer Info**: Set `localStorage.setItem('customer_name', 'John')` on login
2. **Email Notifications**: Plan for future with email_config.php
3. **Monitor Performance**: Check database size regularly
4. **Backup Messages**: SQL dump chat_messages weekly
5. **Train Staff**: Show admin team the admin panel

---

## 🎨 Color Reference

| Use | Color | Hex |
|-----|-------|-----|
| Primary | Teal | #08415c |
| Secondary | Navy | #0a5273 |
| Tertiary | Light Teal | #1a6d9e |
| Customer Msg | Dark Teal | #08415c |
| Admin Msg | Gray | #e5e7eb |
| Badge | Red | #ef4444 |

---

## 📱 Device Support

- ✅ iPhone/iPad (iOS)
- ✅ Android phones
- ✅ Tablets (7"+ screens)
- ✅ Desktop (Windows/Mac/Linux)
- ✅ Tablets in landscape
- ✅ Small phones (320px+)

---

**Last Updated**: January 28, 2026  
**Version**: 1.0  
**Status**: Production Ready ✅

For detailed information, see the full documentation files.
