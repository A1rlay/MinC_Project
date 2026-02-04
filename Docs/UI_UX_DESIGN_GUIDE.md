# MinC Computer Parts - UI/UX Design Guide

## Overview
This document outlines the unified design system implemented across all MinC web pages. The design uses modern web technologies with consistent branding, typography, colors, and components.

---

## Color Palette

### Primary Colors
- **Primary Blue** - `#08415c` (Dark Teal) - Used for main CTAs, headings, and accents
- **Secondary Blue** - `#0a5273` (Medium Teal) - Used for gradients and hover states
- **Accent Gradient** - `linear-gradient(135deg, #08415c 0%, #0a5273 100%)` - Used for buttons and visual emphasis

### Neutral Colors
- **Background** - `#f9fafb` or `#f3f4f6` (Light Gray)
- **Card Background** - `#ffffff` (White)
- **Text Primary** - `#111827` or `#1f2937` (Dark Gray)
- **Text Secondary** - `#6b7280` or `#9ca3af` (Medium Gray)
- **Border** - `#e5e7eb` (Light Gray)

### Status Colors
- **Success** - `#10b981` (Green)
- **Error** - `#ef4444` (Red)
- **Warning** - `#f59e0b` (Amber)
- **Info** - `#3b82f6` (Blue)

---

## Typography

### Font Family
- **Primary Font** - `'Inter', sans-serif` (Google Fonts)
  - Weights: 300, 400, 500, 600, 700, 800

### Font Sizes & Hierarchy

| Element | Size | Weight | Line Height |
|---------|------|--------|------------|
| Heading 1 (Page Title) | 48px / 3rem | 700-800 | 1.2 |
| Heading 2 (Section) | 36px / 2.25rem | 700 | 1.2 |
| Heading 3 | 28px / 1.75rem | 600-700 | 1.3 |
| Heading 4 | 20px / 1.25rem | 600 | 1.4 |
| Body Text | 16px / 1rem | 400-500 | 1.6 |
| Small Text | 14px / 0.875rem | 400 | 1.5 |
| Extra Small | 12px / 0.75rem | 400 | 1.4 |

---

## Components

### Navigation Bar (`components/navbar.php`)

**Features:**
- Fixed position at top of page (z-index: 50)
- Height: 80px (20 units)
- White background with shadow
- Responsive design with mobile menu
- Dynamic login/logout buttons

**States:**
- Default: Gray text
- Hover: Teal color with animated underline
- Active/Current Page: Teal with underline

**Mobile Breakpoint:** Hidden on `md` (768px) and below, toggle menu appears

---

### Footer Component (`components/footer.php`)

**Features:**
- Dark background (`#111827` - gray-900)
- 4-column layout on desktop, single column on mobile
- Contact information, quick links, and social media
- Copyright notice
- Responsive padding and spacing

**Sections:**
1. Brand & Description
2. Quick Links
3. Contact Information
4. Social Media Links

---

### Buttons

#### Primary Button (`.btn-primary-custom`)
```css
Background: linear-gradient(135deg, #08415c 0%, #0a5273 100%)
Color: white
Padding: 12px 24px (px-6 py-3)
Border Radius: 8px (rounded-lg)
Transition: transform 0.3s ease
Hover: translateY(-2px) with shadow
```

#### Secondary Button
```css
Background: #f3f4f6 (gray-100)
Color: #08415c
Padding: 12px 24px
Border Radius: 8px
Hover: Background to #e5e7eb (gray-200)
```

---

### Cards

#### Standard Card
```css
Background: white
Border Radius: 12px (rounded-xl)
Box Shadow: 0 10px 15px rgba(0, 0, 0, 0.1)
Padding: 24px (p-6)
Transition: All effects on hover
```

#### Product Card (Product Listing)
- Image container with 192px height (h-48)
- Product line badge (Category)
- Product name, description, price
- "Add to Cart" button
- Stock indicator (if low stock)

---

### Forms

#### Form Input
```css
Border: 1px solid #e5e7eb
Border Radius: 8px
Padding: 12px 16px (px-4 py-3)
Focus: Ring 2px of #08415c
Font Size: 16px (1rem)
```

#### Form Label
```css
Font Size: 14px (0.875rem)
Font Weight: 500
Color: #374151 (gray-700)
Margin Bottom: 8px
```

---

### Page Sections

#### Hero/Page Header
- Gradient background using primary colors
- Padding: 48px top/bottom (py-12)
- White text
- Breadcrumb navigation
- Page title and description

#### Main Content Area
- Max width: 1280px (max-w-7xl)
- Horizontal padding: 16px (px-4)
- Vertical padding: 64px (py-16)
- Container margin: 0 auto

---

## Layout System

### Grid System
- Uses **Tailwind CSS** grid utilities
- Mobile-first responsive design
- Common breakpoints:
  - `sm`: 640px
  - `md`: 768px
  - `lg`: 1024px
  - `xl`: 1280px

### Common Layout Patterns

#### Two-Column Layout
```html
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
  <!-- Column 1 -->
  <!-- Column 2 -->
</div>
```

#### Three-Column Layout (Products)
```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
  <!-- Card 1 -->
  <!-- Card 2 -->
  <!-- Card 3 -->
</div>
```

### Spacing
- Uses Tailwind spacing scale (multiples of 4px)
- Padding: `p-`, `px-`, `py-`, `pt-`, `pb-`, `pl-`, `pr-`
- Margin: `m-`, `mx-`, `my-`, `mt-`, `mb-`, `ml-`, `mr-`
- Gap: `gap-` (between flex/grid items)

---

## Pages & Implementation

### Navigation Included Pages
All customer-facing pages now include the shared navbar and footer:

1. **index.php** - Landing page with hero, categories, and products
2. **product.php** - Products listing with filters
3. **product_detail.php** - Individual product details
4. **user-cart.php** - Shopping cart and order summary
5. **checkout.php** - Multi-step checkout form
6. **order-success.php** - Order confirmation page
7. **profile.php** - User profile management (logged-in users only)

### File Structure
```
html/
├── components/
│   ├── navbar.php          # Shared navigation component
│   └── footer.php          # Shared footer component
├── product.php             # Products listing page
├── product_detail.php      # Product details page
├── user-cart.php           # Shopping cart page
├── checkout.php            # Checkout page
├── order-success.php       # Order confirmation page
├── profile.php             # User profile page
└── blog.php                # Blog page (Bootstrap-based, separate styling)
```

---

## Responsive Design Guidelines

### Mobile-First Approach
- Design starts at mobile viewport
- Use `hidden` class to hide elements on mobile
- Use responsive prefixes: `sm:`, `md:`, `lg:`, `xl:`, `2xl:`

### Navigation Responsive Behavior
- **Mobile (< 768px):**
  - Logo visible
  - Hamburger menu icon
  - Dropdown menu on toggle
  
- **Desktop (>= 768px):**
  - Full navigation menu visible
  - User section with buttons
  - No hamburger menu

### Common Responsive Patterns
```html
<!-- Hide on mobile, show on desktop -->
<div class="hidden md:flex">...</div>

<!-- Stack on mobile, side-by-side on desktop -->
<div class="grid grid-cols-1 md:grid-cols-2">...</div>

<!-- Text size adjustment -->
<h1 class="text-2xl md:text-4xl">Title</h1>

<!-- Padding adjustment -->
<div class="px-4 md:px-8">...</div>
```

---

## Interactive Elements

### Buttons & Links
- Hover effect: Color change to primary or transform
- Focus state: Ring outline for accessibility
- Disabled state: Reduced opacity

### Dropdown Menus
- Hide by default with `hidden` class
- Show with JavaScript by removing `hidden` class
- Close when clicking outside (event delegation)
- Z-index: 50 for dropdown content

### Modal Dialogs
- Fixed position, full screen overlay
- Background: `rgba(0, 0, 0, 0.5)` (50% opacity)
- Center content vertically and horizontally
- Z-index: 50 or higher
- Close button in top-right corner

### Loading States
- Show/hide with JavaScript
- Spinning animation using CSS
- Center content on page
- Semi-transparent white background

---

## Animations & Transitions

### Transition Classes
```css
/* General transitions */
transition: all 0.3s ease
transition: color 0.3s ease
transition: transform 0.3s ease
transition: box-shadow 0.3s ease

/* Specific durations */
.duration-150 { transition-duration: 150ms; }
.duration-300 { transition-duration: 300ms; }
.duration-500 { transition-duration: 500ms; }
```

### Hover Effects
- **Button:** Transform up + shadow
- **Card:** Transform up + shadow
- **Link:** Color change + underline animation
- **Image:** Scale zoom effect

### Loading Spinner
```css
Animation: spin 1s linear infinite
Border: Thin top border (#08415c)
Size: 64px (h-16 w-16)
```

---

## Accessibility Features

### Color Contrast
- Text to background: Minimum 4.5:1 for normal text
- Primary blue (#08415c) on white: ✓ Passes WCAG AA

### Focus States
- Visible focus ring (2px outline in primary color)
- Applied to interactive elements
- Clear visual indication of current focus

### Semantic HTML
- Use proper heading hierarchy (h1-h6)
- Use semantic tags: `<nav>`, `<main>`, `<section>`, `<article>`
- ARIA labels where necessary

### Mobile Accessibility
- Touch targets minimum 44x44px
- Clear button and link labels
- Sufficient color contrast in all states

---

## Performance Optimization

### Image Optimization
- Use responsive images with srcset (if applicable)
- Optimize image sizes before uploading
- Use placeholder images for missing images
- Lazy load images when possible

### CSS & JavaScript
- Utilize Tailwind CSS for responsive design
- Minimize custom CSS
- Use semantic HTML
- Keep JavaScript modular and efficient

### Loading Time
- Use CDN for external libraries (Tailwind, Font Awesome, etc.)
- Minimize HTTP requests
- Optimize database queries
- Implement pagination for large data sets

---

## Customization & Maintenance

### Adding New Pages
1. Include navbar at top: `<?php include 'components/navbar.php'; ?>`
2. Include footer at bottom: `<?php include 'components/footer.php'; ?>`
3. Add `<?php session_start(); ?>` at the beginning for user detection
4. Use Tailwind classes for styling
5. Follow the color palette and typography guidelines

### Modifying Components
- Edit `components/navbar.php` or `components/footer.php`
- Changes apply to all pages automatically
- Test on both mobile and desktop views

### Adding New Colors
- Extend colors in Tailwind config if needed
- Document in this guide
- Use consistently across all pages

### Creating New Components
- Follow naming convention: `components/component-name.php`
- Include in pages as: `<?php include 'components/component-name.php'; ?>`
- Keep components generic and reusable
- Document purpose and usage

---

## Browser Support

### Tested & Supported
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile Chrome (Android)
- Mobile Safari (iOS)

### Fallbacks
- Graceful degradation for older browsers
- CSS fallbacks for gradients
- JavaScript fallbacks for modern APIs

---

## Future Enhancements

### Planned Improvements
- [ ] Dark mode support
- [ ] Advanced animations
- [ ] PWA functionality
- [ ] Enhanced accessibility features
- [ ] Performance metrics monitoring

### Notes for Developers
- Maintain consistency across all pages
- Test changes on multiple devices
- Document any new patterns or components
- Consider accessibility in all updates
- Keep the design system documented

---

## Questions & Support

For questions about the design system, please refer to:
1. This documentation
2. Component files in `html/components/`
3. Existing implementation in pages (product.php, etc.)

Last Updated: 2025
Version: 1.0
