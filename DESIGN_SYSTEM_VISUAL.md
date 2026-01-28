# MinC Design System - Visual Reference

## Color Palette

### Primary Colors
```
████ #08415c - Primary Blue (Buttons, Headings, Links)
████ #0a5273 - Secondary Blue (Gradients, Hover States)
████ Linear Gradient - Used for buttons and visual emphasis
```

### Neutral Colors
```
████ #ffffff - White (Card backgrounds)
████ #f3f4f6 - Light Gray (Page backgrounds)
████ #e5e7eb - Border Gray (Dividers)
████ #6b7280 - Medium Gray (Secondary text)
████ #111827 - Dark Gray (Footer, primary text)
```

### Status Colors
```
████ #10b981 - Success (Green)
████ #ef4444 - Error (Red)
████ #f59e0b - Warning (Amber)
████ #3b82f6 - Info (Blue)
```

---

## Typography Scale

```
48px │ Page Titles          │ Bold/Extrabold │ Hero sections
36px │ Section Headings     │ Bold           │ Major sections
28px │ Subsection Headers   │ Semibold       │ Category headers
20px │ Card Titles          │ Semibold       │ Component titles
18px │ Larger Text          │ Medium         │ Emphasis text
16px │ Body Text (Default)  │ Regular        │ Main content
14px │ Small Text           │ Regular        │ Helper text, labels
12px │ Extra Small          │ Regular        │ Captions
```

---

## Component Showcase

### Navigation Bar
```
┌────────────────────────────────────────────────────────────┐
│ MinC    About Us  Products  Categories  Contact │ Cart  Login│
└────────────────────────────────────────────────────────────┘
```
- Fixed position at top
- White background with shadow
- Responsive hamburger on mobile
- Blue accent on hover

### Button States
```
Default:    ┌─────────────────┐
            │  Primary Button │ (Blue gradient)
            └─────────────────┘

Hover:      ┌─────────────────┐
            │  Primary Button │ (Lifted, shadow)
            └─────────────────┘

Active:     ┌─────────────────┐
            │  Primary Button │ (Pressed, focused)
            └─────────────────┘

Disabled:   ┌─────────────────┐
            │  Primary Button │ (Faded)
            └─────────────────┘
```

### Product Card
```
┌─────────────────────────────┐
│  [Product Image]    [Badge] │
├─────────────────────────────┤
│ Product Name                │
│ Product description...      │
├─────────────────────────────┤
│ ₱1,999       [Add to Cart]  │
└─────────────────────────────┘
```
- Image: 192px height
- Badge: Category/type
- Hover: Transform up with shadow
- Button: Gradient, interactive

### Form Input
```
Email Address *
┌─────────────────────────────┐
│                             │
└─────────────────────────────┘

Focus State:
┌═════════════════════════════┐ (Blue outline)
│                             │
└═════════════════════════════┘
```
- Rounded corners (8px)
- Focus ring in primary color
- Gray borders
- Proper padding

### Footer
```
┌──────────────────────────────────────────────┐
│ MinC              Contact      Links   Social│
│ Description       Address      Home    f t i │
│                   Phone        About   LinkedIn
│                   Email        Products
└──────────────────────────────────────────────┘
```
- Dark background
- 4 columns on desktop
- Single column on mobile
- Icons included

---

## Layout Patterns

### Single Column (Mobile)
```
┌─────────┐
│ Content │
│         │
│         │
└─────────┘
```

### Two Column (Tablet)
```
┌─────────┬─────────┐
│    1    │    2    │
│         │         │
└─────────┴─────────┘
```

### Three Column (Products)
```
┌────┬────┬────┐
│  1 │  2 │  3 │
├────┼────┼────┤
│  4 │  5 │  6 │
└────┴────┴────┘
```

### Four Column (Footer)
```
┌──────┬──────┬──────┬──────┐
│  1   │  2   │  3   │  4   │
│      │      │      │      │
└──────┴──────┴──────┴──────┘
```

---

## Spacing Scale

```
2px  (0.5 units)  - Minimal spacing
4px  (1 unit)     - Tight spacing
8px  (2 units)    - Default spacing
12px (3 units)    - Comfortable spacing
16px (4 units)    - Component padding
24px (6 units)    - Section spacing
32px (8 units)    - Major spacing
48px (12 units)   - Large sections
64px (16 units)   - Extra large spacing
```

---

## Responsive Breakpoints

```
Mobile      ├─────────────┤  < 640px
            │  Single Col │  Hamburger Menu
            │  Full Width │  Stacked Layout
            └─────────────┘

Tablet      ├─────────────────┤  640px - 1024px
            │  Two Columns    │  Optimized Spacing
            │  Partial Width  │  Menu Visible
            └─────────────────┘

Desktop     ├──────────────────────┤  > 1024px
            │  Multi-Column       │  Full Width
            │  Max 1280px Width   │  Optimal Spacing
            └──────────────────────┘
```

---

## Hover & Interaction Effects

### Link Hover
```
Before:  Text
         ════

After:   Text
         ════════  (Underline expands)
```

### Card Hover
```
Before:  ┌───┐       After:   ┌───┐
         │   │  ──→           │   │  (Lifts up)
         └───┘                └───┘
                              (Shadow appears)
```

### Button Hover
```
Before:  [Button]      After:   [Button]  (Moves up 2px)
                               (Blue shadow appears)
```

---

## Page Structure

### Typical Page Layout
```
┌──────────────────────────────────┐
│     Navigation Bar (Fixed)       │
├──────────────────────────────────┤
│         Hero Section             │
│    (Gradient, Breadcrumb)        │
├──────────────────────────────────┤
│                                  │
│      Main Content Area           │
│   (Max Width 1280px, Centered)   │
│                                  │
├──────────────────────────────────┤
│          Footer                  │
│      (Dark Background)           │
└──────────────────────────────────┘
```

### Hero Section
```
┌───────────────────────────────────┐ Gradient Background
│                                   │ (#08415c to #0a5273)
│  Breadcrumb > Page Name           │
│  Page Title (Large)               │ White Text
│  Page Description (Subtle)        │
│                                   │
└───────────────────────────────────┘
```

---

## Mobile Menu States

### Closed
```
┌────────────────────────┐
│ MinC         ☰ (Menu)  │
└────────────────────────┘
```

### Open
```
┌────────────────────────┐
│ MinC         ✕ (Close) │
├────────────────────────┤
│ About Us               │
│ Products              │
│ Categories            │
│ Contact               │
│ [Login Button]        │
└────────────────────────┘
```

---

## Icon Usage

### Navigation
```
🏠 Home           🛍  Products       📦 Order/Cart
🔐 Profile        👤 Account        🔔 Notifications
```

### Actions
```
✓ Checkmark      ✕ Close          ⚠  Warning
📱 Mobile        🖥  Desktop       📋 Document
```

### Social Media
```
f  Facebook      t  Twitter        📷 Instagram
in LinkedIn      🌐 Website
```

---

## Font Combinations

### Headings + Body
```
Inter Bold       (Page Titles)
Inter Semibold   (Section Titles)
Inter Medium     (Card Titles)
Inter Regular    (Body Text)
```

All weights from same family = consistent appearance

---

## Animation Effects

### Transition Timings
```
Short    150ms  - Quick feedback
Normal   300ms  - Standard interactions
Long     500ms  - Attention-grabbing
```

### Common Animations
```
Color Change       - 300ms ease
Transform/Scale    - 300ms ease
Shadow Effect      - 300ms ease
Opacity Fade       - 300ms ease
Height/Width       - 500ms ease
```

---

## Accessibility Features

### Touch Targets
```
Minimum size: 44 x 44 pixels
Applied to: Buttons, links, form inputs
Ensures: Easy mobile interaction
```

### Color Contrast
```
AAA Level:  7:1 ratio (Excellent)
AA Level:   4.5:1 ratio (Good)
Large Text: 3:1 ratio (Adequate)
```

### Focus Indicators
```
Default:    No visible ring
Focus:      2px ring in primary color
Keyboard:   Tab navigation works
```

---

## File Structure

```
MinC_Project/
├── html/
│   ├── components/
│   │   ├── navbar.php        (Navigation)
│   │   └── footer.php        (Footer)
│   ├── product.php           (Products List)
│   ├── product_detail.php    (Product Detail)
│   ├── user-cart.php         (Shopping Cart)
│   ├── checkout.php          (Checkout)
│   ├── order-success.php     (Order Confirmation)
│   └── profile.php           (User Profile)
├── Assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   ├── json/
│   └── script/
├── backend/                  (API endpoints)
├── database/                 (Database connection)
├── admin/                    (Admin panel)
└── Documentation/
    ├── UI_UX_DESIGN_GUIDE.md
    ├── QUICK_REFERENCE_GUIDE.md
    ├── UI_UX_CONSISTENCY_REPORT.md
    ├── VALIDATION_CHECKLIST.md
    └── IMPLEMENTATION_COMPLETE.md
```

---

## Quick Decision Tree

```
Is it a page heading?
├─ Yes → 48px, Bold, #08415c
└─ No → Is it a section heading?
    ├─ Yes → 28px, Semibold, #08415c
    └─ No → Is it body text?
        ├─ Yes → 16px, Regular, #374151
        └─ No → Use appropriate size based on context

Need a button?
├─ Primary action? → Gradient button with hover effect
└─ Secondary action? → Gray button with hover effect

Is it a card?
├─ Yes → White background, rounded corners, shadow
└─ Use in grid layout with responsive spacing

Mobile or Desktop?
├─ Mobile (< 640px) → Single column, hamburger menu
├─ Tablet (640-1024px) → Two columns, optimized
└─ Desktop (> 1024px) → Multi-column, full features
```

---

## Design System Version

**Version:** 1.0
**Created:** 2025
**Status:** Active & Maintained
**Last Updated:** 2025

---

**This design system ensures consistency, professionalism, and excellent user experience across the entire MinC platform.**

All components work together to create a cohesive, modern web application that customers will enjoy using.

🎨 **Beautiful Design** + 💻 **Clean Code** + 📱 **Responsive** = ✨ **Professional Result**
