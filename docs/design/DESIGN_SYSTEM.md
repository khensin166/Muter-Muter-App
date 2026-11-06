# Design System & UI Specification — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Approved Post-Grooming
- **Design Philosophy:** Modern, nature-infused, culturally warm, mobile-first responsive travel marketplace.

---

## 1. Brand Identity & Visual Philosophy

The visual theme for Lake Toba Travel Marketplace (Muter-Muter App) draws inspiration from the natural grandeur and cultural warmth of the Lake Toba region:
- **Deep Toba Teal:** Reflects the vast, calm, and mystic volcanic waters of Lake Toba. Conveys reliability, cleanliness, and tranquility.
- **Caldera Terracotta / Coral:** Reflects the warm sunrise over the caldera cliffs and Batak cultural warmth. Used for high-priority Call to Action (CTA) buttons, booking triggers, and promotional badges.
- **Highland Emerald / Pine:** Reflects the surrounding pine hills (Bukit Holbung, Tele, Sipiso-piso). Used for availability badges, eco-tags, and positive confirmation states.
- **Morning Mist & Slate:** High-contrast neutral palette ensuring crisp legibility under outdoor mobile sunlight.

---

## 2. Global CSS Variables Architecture

Colors are defined as CSS variables at `:root` inside `resources/css/app.css` (or `resources/css/variables.css`). This enables effortless white-labeling, seasonal theming, and centralized color adjustments.

```css
:root {
  /* =========================================================================
     1. Brand Colors (Lake Toba Theme)
     ========================================================================= */
  --color-primary: #0D5C75;          /* Deep Toba Teal */
  --color-primary-light: #1995AD;    /* Lake Surface Blue */
  --color-primary-dark: #083B4C;     /* Abyssal Blue */

  --color-accent: #E76F51;           /* Caldera Coral / Terracotta */
  --color-accent-hover: #D45D3F;     /* Deep Terracotta */

  --color-secondary: #2A9D8F;        /* Highland Emerald / Pine */
  --color-secondary-light: #E8F5F3;  /* Soft Pine Tint */

  /* =========================================================================
     2. Surface & Background
     ========================================================================= */
  --color-background: #F8FAFC;       /* Morning Mist Slate 50 */
  --color-surface: #FFFFFF;          /* Pure White Card */
  --color-surface-muted: #F1F5F9;    /* Light Slate 100 */
  --color-border: #E2E8F0;           /* Border Slate 200 */

  /* =========================================================================
     3. Typography
     ========================================================================= */
  --color-text-main: #0F172A;        /* Deep Slate 900 */
  --color-text-muted: #64748B;       /* Cool Slate 500 */
  --color-text-inverted: #FFFFFF;    /* Pure White */

  /* =========================================================================
     4. Semantic Feedback
     ========================================================================= */
  --color-success: #10B981;          /* Emerald 500 */
  --color-warning: #F59E0B;          /* Amber 500 */
  --color-danger: #EF4444;           /* Rose/Red 500 */
  --color-info: #0284C7;             /* Sky 600 */
}
```

---

## 3. Tailwind CSS Configuration Extension

In `tailwind.config.js`, the CSS variables are registered into the Tailwind color palette:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: 'var(--color-primary)',
          light: 'var(--color-primary-light)',
          dark: 'var(--color-primary-dark)',
        },
        accent: {
          DEFAULT: 'var(--color-accent)',
          hover: 'var(--color-accent-hover)',
        },
        secondary: {
          DEFAULT: 'var(--color-secondary)',
          light: 'var(--color-secondary-light)',
        },
        background: 'var(--color-background)',
        surface: {
          DEFAULT: 'var(--color-surface)',
          muted: 'var(--color-surface-muted)',
        },
        border: 'var(--color-border)',
        'text-main': 'var(--color-text-main)',
        'text-muted': 'var(--color-text-muted)',
        'text-inverted': 'var(--color-text-inverted)',
        success: 'var(--color-success)',
        warning: 'var(--color-warning)',
        danger: 'var(--color-danger)',
        info: 'var(--color-info)',
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        'xl': '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
      },
      boxShadow: {
        'card': '0 2px 10px rgba(13, 92, 117, 0.06)',
        'card-hover': '0 10px 25px rgba(13, 92, 117, 0.12)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
```

---

## 4. UI Component Design Rules

### 4.1 Buttons
- **Primary CTA ("Pesan Sekarang", "Bayar DP"):**
  `bg-accent hover:bg-accent-hover text-white font-semibold py-2.5 px-5 rounded-xl shadow-sm transition duration-150`
- **Secondary Action ("Lihat Detail", "Hubungi CS"):**
  `bg-primary hover:bg-primary-dark text-white font-medium py-2.5 px-4 rounded-xl transition duration-150`
- **Tertiary / Outline ("Batal", "Filter"):**
  `border border-border text-text-main hover:bg-surface-muted font-medium py-2 px-3 rounded-lg transition`

### 4.2 Status Badges
- **Booking Confirmed / Paid:** `bg-emerald-50 text-emerald-700 border border-emerald-200`
- **Awaiting Payment / Hold Active:** `bg-amber-50 text-amber-700 border border-amber-200`
- **Pending Partner Review:** `bg-sky-50 text-sky-700 border border-sky-200`
- **Manual Admin Review:** `bg-purple-50 text-purple-700 border border-purple-200`
- **Expired / Cancelled:** `bg-rose-50 text-rose-700 border border-rose-200`

### 4.3 Typography Scale
- **Display Heading:** `text-3xl md:text-4xl font-extrabold text-text-main tracking-tight`
- **Section Heading:** `text-xl md:text-2xl font-bold text-text-main`
- **Product Title:** `text-lg font-bold text-text-main line-clamp-2`
- **Body Text:** `text-sm text-text-muted leading-relaxed`
- **Price Tag:** `text-lg font-extrabold text-accent`

