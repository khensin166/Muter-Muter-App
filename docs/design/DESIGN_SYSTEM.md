# Design System & UI Specification — Muter-Muter Travel Marketplace

- **Document Version:** 1.1
- **Status:** Approved Architecture & Scope Alignment
- **Design Philosophy:** Modern, nature-infused, culturally warm, mobile-first responsive regional travel marketplace (Sumatera Utara / North Sumatra).

---

## 1. Brand Identity & Visual Philosophy

The visual theme for **Muter-Muter App** draws inspiration from the diverse topography and rich cultural heritage of **Sumatera Utara (North Sumatra)** — from the volcanic caldera of Lake Toba, the dense rainforests of Bukit Lawang & Tangkahan, to the cool agricultural highlands of Berastagi:
- **Deep Toba Teal:** Reflects deep volcanic lakes and calm coastal rivers. Conveys trust, reliability, and tranquility.
- **Caldera Terracotta / Coral:** Reflects warm tropical sunrise and vibrant Sumatran cultural warmth. Used for high-priority Call to Action (CTA) buttons, booking triggers, and promotional badges.
- **Highland Emerald / Rainforest Green:** Reflects the Leuser tropical rainforest (Bukit Lawang, Tangkahan) and pine-covered hills. Used for availability badges, eco-tags, and positive confirmation states.
- **Morning Mist & Slate:** High-contrast neutral palette ensuring crisp legibility under outdoor mobile sunlight.

---

## 2. Logo Asset Placement & Navbar Guidelines

### 2.1 File Storage Location Standards
Static brand identity assets must be placed in the public asset directory so they can be referenced without Vite hashing issues across Vue templates, invoices, OpenGraph social sharing meta tags, and favicons:

📂 **`public/images/`**
```
public/
└── images/
    ├── logo.png          # Primary full logo (horizontal mark + typography)
    ├── logo-icon.png     # Square logo icon (app icon, favicon, avatar)
    └── logo-white.png    # Monochrome white logo (for dark surfaces: footer, admin header)
```

### 2.2 Reusable `<ApplicationLogo />` Component Standard
All layouts (`AppLayout`, `AdminLayout`, `PartnerLayout`, and Auth views) MUST consume the logo via `resources/js/Components/ApplicationLogo.vue`:
```vue
<template>
  <div class="flex items-center gap-2.5">
    <img 
      src="/images/logo.png" 
      alt="Muter-Muter" 
      class="h-9 w-auto object-contain" 
      @error="fallbackToText = true"
    />
    <div v-if="fallbackToText" class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center text-white font-black text-xl shadow-xs">
        M
      </div>
      <div class="flex flex-col">
        <span class="text-lg font-black tracking-tight text-primary leading-none">Muter-Muter</span>
        <span class="text-[10px] font-bold text-accent tracking-widest uppercase">Sumatera Utara</span>
      </div>
    </div>
  </div>
</template>
```

### 2.3 Global Brand De-coupling (Removing Restrictive "Lake Toba" Badge)
- **Problem Statement:** Previously, the navbar hardcoded `<span class="uppercase">Lake Toba</span>` under the brand name. This alienates travelers seeking packages in Bukit Lawang, Tangkahan, Berastagi, or Medan.
- **Policy Rule:**
  1. The global navbar brand MUST display **`Muter-Muter`** accompanied by a broader regional tagline: **`Sumatera Utara`** (or **`North Sumatra`**).
  2. "Danau Toba" MUST NOT be hardcoded into global top-level headers, page titles, or portal headers. It must strictly exist as a **Destination Cluster** or **Package Filter**.
  3. Header & Footer copy must highlight the regional variety: *"Jelajahi Sumatera Utara: Danau Toba, Bukit Lawang, Tangkahan, Berastagi, dan Medan."*

---

## 3. Global CSS Variables Architecture

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

