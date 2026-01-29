# Chat System - Quick Start Guide

## 🚀 Getting Started in 3 Steps

### Step 1: Initialize Database
Visit this URL in your browser (one-time setup):
```
http://localhost/pages/MinC_Project/setup/setup_chat.php
```
This will create the necessary database table. You should see a success message.

### Step 2: Test as Customer
1. Go to the home page: `http://localhost/pages/MinC_Project/`
2. Look for the **teal chat bubble** in the bottom-right corner
3. Click it to open the chat window
4. Type a message and press Enter or click Send
5. You'll see your message appear in blue on the right side

### Step 3: View as Admin
1. Login to the dashboard (as Owner or IT Staff)
2. From the sidebar, click **"Customer Messages"** under "Customer Service"
3. You'll see your conversation in the list
4. Click on it to view the full conversation
5. Type a response in the textarea and click Send
6. Go back to the home page and you'll see the admin's response!

## 💡 Key Features

### For Customers
- 💬 Chat bubble in bottom-right corner
- 📱 Expandable to full-screen on mobile
- ✨ Real-time message updates
- 💾 Message history persists in browser

### For Admin
- 📋 See all customer conversations
- 🔔 Unread message counts
- ⚡ Quick response interface
- 👤 Customer name and email tracking

## 🎯 What You Can Do

**Customer Side:**
- Send messages from anywhere on the home page
- See responses from the business owner
- Expand chat to full-screen for easier typing
- Close chat and return to shopping

**Admin Side:**
- See all active conversations
- View unread message counts
- Respond to customer inquiries
- Track conversation history

## 📍 Where to Find Everything

| Feature | Location |
|---------|----------|
| Chat Bubble | Bottom-right of home page |
| Admin Panel | Dashboard → Customer Messages |
| Database Table | `chat_messages` in MinC database |
| Setup Tool | `/setup/setup_chat.php` |
| Component Code | `/html/components/chat_bubble.php` |
| Admin Interface | `/app/frontend/chat-admin.php` |
| API Backend | `/backend/chat/send_message.php` |

## ❓ Common Questions

**Q: How do messages get stored?**
A: Messages are saved in the `chat_messages` database table. Customer messages are grouped by session ID (stored in localStorage).

**Q: How often do messages refresh?**
A: Every 3 seconds on customer side, every 5 seconds on admin side.

**Q: Can I customize the chat bubble appearance?**
A: Yes! Edit the color, size, and position in `/html/components/chat_bubble.php`

**Q: Who can access the admin chat panel?**
A: Only the Owner (level 1) or IT Staff (level 5) can see and respond to messages.

**Q: Where do customer emails get stored?**
A: If you set a customer email in localStorage (key: `customer_email`), it gets saved with their messages.

## 🎨 Customization Tips

### Change Chat Bubble Color
Open `/html/components/chat_bubble.php` and find lines with:
```
from-[#08415c] to-[#0a5273]
```
Replace with your desired hex colors.

### Change Chat Bubble Position
Find in `/html/components/chat_bubble.php`:
```html
<div id="chat-bubble" class="fixed bottom-6 right-6 z-40">
```
- `bottom-6` = distance from bottom (adjust: bottom-4, bottom-8, etc.)
- `right-6` = distance from right edge (adjust: right-4, right-8, etc.)

### Change Refresh Rate
**Customer side** - Find in `/html/components/chat_bubble.php`:
```javascript
setInterval(function() { ... }, 3000); // 3000ms = 3 seconds
```

**Admin side** - Find in `/app/frontend/chat-admin.php`:
```javascript
setInterval(function() { location.reload(); }, 5000); // 5 seconds
```

## 🔍 Testing Checklist

- [ ] Database table created successfully
- [ ] Chat bubble appears on home page
- [ ] Can send message as customer
- [ ] Message appears in chat window
- [ ] Admin panel shows the conversation
- [ ] Can respond from admin panel
- [ ] Response appears on home page
- [ ] Unread count shows correctly
- [ ] Chat window closes/opens properly
- [ ] Expand button works
- [ ] Messages refresh automatically
- [ ] Works on mobile browser
- [ ] Works on desktop browser

## 📞 Need Help?

Check these resources:
1. **Full Documentation**: `/CHAT_SYSTEM_README.md`
2. **Implementation Summary**: `/CHAT_IMPLEMENTATION_SUMMARY.md`
3. **Browser Console**: Look for JavaScript errors (F12)
4. **Database**: Verify `chat_messages` table exists

## 🎯 Next Steps

Now that the chat system is running:

1. **Customize it** to match your brand colors
2. **Test thoroughly** with different browsers/devices
3. **Train staff** on how to use the admin panel
4. **Inform customers** that support is available via chat
5. **Monitor messages** regularly for inquiries

## 🚀 You're All Set!

The chat system is now ready to handle customer inquiries. Customers can easily reach out via the chat bubble, and you can respond through the admin panel.

Happy chatting! 💬

---

**Need more details?** See the full documentation in `CHAT_SYSTEM_README.md`
