# MinC UI/UX System - Quick Reference Guide

## 🎨 Color Palette

```css
/* Primary Colors */
--primary-blue: #08415c;
--secondary-blue: #0a5273;
--primary-gradient: linear-gradient(135deg, #08415c 0%, #0a5273 100%);

/* Neutral Colors */
--white: #ffffff;
--dark-gray: #111827;
--light-gray: #f3f4f6;
--gray-100: #f9fafb;
--gray-200: #e5e7eb;
--gray-600: #4b5563;
--gray-700: #374151;

/* Status Colors */
--success: #10b981;
--error: #ef4444;
--warning: #f59e0b;
--info: #3b82f6;
```

---

## 📝 Typography

### Font Family
```css
font-family: 'Inter', sans-serif;
```

### Quick Size Reference
| Class | Size | Usage |
|-------|------|-------|
| `text-xs` | 12px | Labels, captions |
| `text-sm` | 14px | Small text, helper |
| `text-base` | 16px | Body text (default) |
| `text-lg` | 18px | Larger text |
| `text-xl` | 20px | Section headers |
| `text-2xl` | 24px | Medium headers |
| `text-3xl` | 30px | Page sections |
| `text-4xl` | 36px | Page titles |
| `text-5xl` | 48px | Hero titles |

### Weight Classes
| Class | Weight | Usage |
|-------|--------|-------|
| `font-light` | 300 | Subtle text |
| `font-normal` | 400 | Body text |
| `font-medium` | 500 | Emphasis |
| `font-semibold` | 600 | Strong emphasis |
| `font-bold` | 700 | Headings |
| `font-extrabold` | 800 | Major headings |

---

## 🎯 Common Components

### Buttons

#### Primary Button
```html
<button class="btn-primary-custom text-white px-6 py-3 rounded-lg font-semibold">
    Click Me
</button>
```

**CSS:**
```css
.btn-primary-custom {
    background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
    transition: all 0.3s ease;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
}
```

#### Secondary Button
```html
<button class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
    Click Me
</button>
```

---

### Cards

#### Standard Card
```html
<div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-xl font-bold text-gray-900 mb-4">Card Title</h3>
    <p class="text-gray-600">Card content goes here...</p>
</div>
```

#### Product Card
```html
<div class="bg-white rounded-xl shadow-lg overflow-hidden product-card">
    <div class="relative h-48 bg-gray-100">
        <img src="image.jpg" alt="Product" class="w-full h-full object-cover">
        <div class="absolute top-3 right-3 bg-[#08415c] text-white px-3 py-1 rounded-full text-sm font-semibold">
            Badge
        </div>
    </div>
    <div class="p-6">
        <h3 class="text-xl font-bold text-[#08415c] mb-2">Product Name</h3>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">Description...</p>
        <div class="flex justify-between items-center">
            <span class="text-2xl font-bold text-[#08415c]">₱1,999</span>
            <button class="btn-primary-custom text-white px-4 py-2 rounded-lg font-semibold">
                Add
            </button>
        </div>
    </div>
</div>
```

---

### Forms

#### Input Field
```html
<div>
    <label class="block text-gray-700 font-medium mb-2">Label Text</label>
    <input type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
</div>
```

#### Textarea
```html
<div>
    <label class="block text-gray-700 font-medium mb-2">Label Text</label>
    <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c] resize-none"></textarea>
</div>
```

#### Select Dropdown
```html
<select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#08415c]">
    <option>Choose Option</option>
    <option>Option 1</option>
    <option>Option 2</option>
</select>
```

---

### Navigation

#### Include in Page
```php
<?php include 'components/navbar.php'; ?>
```

**Features:**
- Automatic session detection
- Shows/hides login/logout buttons
- Mobile responsive menu
- Fixed position with shadow

#### Custom Links in Navbar
Edit `html/components/navbar.php` to add custom links.

---

### Footer

#### Include in Page
```php
<?php include 'components/footer.php'; ?>
```

**Features:**
- 4-column layout on desktop
- Single column on mobile
- Contact information
- Social media links
- Copyright notice

---

## 📱 Responsive Breakpoints

```tailwind
sm:  640px   (small devices)
md:  768px   (tablets)
lg:  1024px  (desktops)
xl:  1280px  (large desktops)
2xl: 1536px  (extra large)
```

### Common Patterns

#### Hide on Mobile
```html
<div class="hidden md:flex">
    Visible on desktop only
</div>
```

#### Stack on Mobile
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <div>Column 1</div>
    <div>Column 2</div>
    <div>Column 3</div>
</div>
```

#### Responsive Text Size
```html
<h1 class="text-2xl md:text-4xl lg:text-5xl font-bold">
    Responsive Heading
</h1>
```

#### Responsive Padding
```html
<div class="px-4 md:px-8 lg:px-12 py-8 md:py-12 lg:py-16">
    Content
</div>
```

---

## 🎨 Flexbox & Grid

### Flex Container
```html
<div class="flex items-center justify-between gap-4">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

**Common Classes:**
- `flex` - Enable flex
- `items-center` - Vertical center
- `items-start` - Top align
- `items-end` - Bottom align
- `justify-center` - Horizontal center
- `justify-between` - Space between
- `justify-around` - Space around
- `gap-4` - Space between items

### Grid Container
```html
<div class="grid grid-cols-3 gap-8">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>
```

**Common Classes:**
- `grid` - Enable grid
- `grid-cols-1` - 1 column
- `grid-cols-2` - 2 columns
- `grid-cols-3` - 3 columns
- `grid-cols-4` - 4 columns
- `gap-4` - Spacing between items

---

## 🔄 States & Effects

### Hover Effects
```html
<!-- Color change -->
<a href="#" class="text-gray-700 hover:text-[#08415c] transition">
    Link
</a>

<!-- Background change -->
<button class="bg-gray-100 hover:bg-gray-200 transition">
    Button
</button>

<!-- Transform -->
<div class="hover:scale-105 hover:shadow-lg transition">
    Card
</div>
```

### Focus States
```html
<input class="focus:outline-none focus:ring-2 focus:ring-[#08415c]">
```

### Disabled State
```html
<button disabled class="opacity-50 cursor-not-allowed">
    Disabled Button
</button>
```

---

## 📊 Spacing Scale

| Class | Size |
|-------|------|
| `p-2` `m-2` | 8px |
| `p-3` `m-3` | 12px |
| `p-4` `m-4` | 16px |
| `p-6` `m-6` | 24px |
| `p-8` `m-8` | 32px |
| `p-12` `m-12` | 48px |
| `p-16` `m-16` | 64px |

### Directional Padding
- `px-4` - Horizontal padding
- `py-3` - Vertical padding
- `pt-4` - Top padding
- `pb-4` - Bottom padding
- `pl-4` - Left padding
- `pr-4` - Right padding

---

## 🎭 Shadows & Borders

### Shadows
```html
<!-- Light shadow -->
<div class="shadow"></div>

<!-- Medium shadow -->
<div class="shadow-lg"></div>

<!-- Large shadow -->
<div class="shadow-xl"></div>

<!-- Extra large shadow -->
<div class="shadow-2xl"></div>
```

### Borders
```html
<!-- Border -->
<div class="border border-gray-300">Box</div>

<!-- Rounded corners -->
<div class="rounded-lg">Box</div>
<div class="rounded-xl">Box</div>

<!-- Border colors -->
<div class="border-2 border-[#08415c]">Box</div>
```

---

## 💡 Useful Tips

### Using Custom Colors
```html
<!-- Primary blue -->
<div class="text-[#08415c]">Text</div>

<!-- Background -->
<div class="bg-[#08415c]">Background</div>

<!-- Border -->
<div class="border-[#08415c]">Border</div>
```

### Max Width Container
```html
<div class="max-w-7xl mx-auto">
    <!-- Content constrained to 1280px, centered -->
</div>
```

### Utility Classes
- `w-full` - Full width
- `h-screen` - Full height
- `mx-auto` - Horizontal centering
- `text-center` - Center text
- `whitespace-nowrap` - Prevent wrapping
- `line-clamp-2` - Limit to 2 lines
- `truncate` - Truncate text

---

## 🚀 Creating a New Page

### Template
```php
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - MinC Computer Parts</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation Component -->
    <?php include 'components/navbar.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-16 mt-20">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Page Title</h1>
        <p class="text-gray-600 text-lg mb-8">Page description goes here...</p>
        
        <!-- Your content -->
    </div>

    <!-- Footer Component -->
    <?php include 'components/footer.php'; ?>

</body>
</html>
```

---

## 📱 Mobile Menu Usage

```php
<button onclick="toggleMobileMenu()" class="md:hidden">
    Menu Button
</button>
```

The navbar component includes automatic mobile menu handling.

---

## 🔍 Form Validation Classes

```html
<!-- Success state -->
<input class="border-green-500">

<!-- Error state -->
<input class="border-red-500">

<!-- Disabled state -->
<input disabled class="bg-gray-100 cursor-not-allowed">
```

---

## 💾 Session & Login

```php
<?php
// Check if logged in
if (isset($_SESSION['user_id'])) {
    echo "User is logged in";
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
```

The navbar automatically handles login/logout display.

---

## 🐛 Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Navbar not showing | Add `<?php session_start(); ?>` at top of page |
| Footer missing | Include `<?php include 'components/footer.php'; ?>` |
| Colors not showing | Use correct hex codes with `#` or use Tailwind color names |
| Responsive not working | Check correct breakpoint prefixes (md:, lg:, etc.) |
| Styles conflicting | Remove inline styles, use Tailwind classes only |
| Mobile menu stuck | Check JavaScript `toggleMobileMenu()` function |

---

## 📚 Resources

- **Tailwind CSS:** https://tailwindcss.com
- **Font Awesome:** https://fontawesome.com
- **Google Fonts:** https://fonts.google.com
- **Design Guide:** See `UI_UX_DESIGN_GUIDE.md`
- **Implementation Report:** See `UI_UX_CONSISTENCY_REPORT.md`

---

## ✨ Best Practices

1. ✅ Use Tailwind classes whenever possible
2. ✅ Keep custom CSS minimal
3. ✅ Use semantic HTML tags
4. ✅ Test on mobile and desktop
5. ✅ Follow the color palette
6. ✅ Maintain consistent spacing
7. ✅ Use shared components
8. ✅ Document new patterns
9. ✅ Keep files organized
10. ✅ Test all responsive breakpoints

---

## 📞 Quick Links

| File | Purpose |
|------|---------|
| `html/components/navbar.php` | Navigation bar |
| `html/components/footer.php` | Footer |
| `UI_UX_DESIGN_GUIDE.md` | Complete design specifications |
| `UI_UX_CONSISTENCY_REPORT.md` | Implementation summary |

---

**Last Updated:** 2025
**Version:** 1.0
