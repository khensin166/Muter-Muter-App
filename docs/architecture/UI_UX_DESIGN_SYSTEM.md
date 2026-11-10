# UI/UX Design System & Management Portal Standards — Muter-Muter Platform

- **Document Version:** 1.0
- **Status:** Approved Architecture Specification
- **Scope:** Canonical UI/UX standard for Admin Operations Console, Partner Portal, and Marketplace Front-end.

---

## 1. Executive Philosophy: Eliminating "Vibe Coding"

### 1.1 The Problem: "Vibe Coding" / Developer Prototype Aesthetic
Early prototypes often rely on developer shortcuts that diminish user trust and perceived platform maturity:
- **Raw Emoji Menus & Badges:** Using `📈`, `🚨`, `💳`, `🏢`, `🏷️`, `💸`, `📍` directly in sidebar navigation, headers, or data tables gives an unpolished, informal impression.
- **Harsh Alert Tones:** Using pure saturated reds (`#FF0000`), greens (`#00FF00`), or blues with heavy black text.
- **Unstructured Layouts:** Cards without subtle borders, misaligned tabular metrics, raw database timestamp strings, and unstyled fallback empty states.

### 1.2 The Standard: Enterprise SaaS & Modern Travel Management (Stripe/Linear/Airbnb Tier)
All management portals (Admin Console and Partner Portal) must adhere to **Tier-1 SaaS design standards**:
1. **Professional Iconography:** 100% semantic SVG icons (Heroicons/Lucide inspired) wrapped in softly tinted geometric containers.
2. **Neutral Elevation & Surface Hierarchy:** Crisp white surfaces (`bg-white` / `bg-surface`) paired with subtle slate borders (`border-slate-200/80` or `border-border`) and micro-shadows (`shadow-xs` / `shadow-sm`).
3. **Structured Status Pills:** Statuses must use rounded pill badges (`rounded-full`) with a pulsating/solid dot indicator, pastel background tint, and high-contrast readable typography.
4. **Tabular Data Clarity:** Monospace or tabular figure fonts (`tabular-nums`) for currency, timestamps, booking codes, and license plates.
5. **Purposeful Micro-Interactions:** Smooth hover transitions (`transition-all duration-150`), visible focus rings, and clear active states.

---

## 2. Iconography & Visual Language Rules

### 2.1 SVG Icon Standard
- **STRICT PROHIBITION:** Raw system emojis (`🚨`, `💳`, `🏢`, `🏷️`, etc.) are **strictly forbidden** in:
  - Sidebar navigation links.
  - Header title bars.
  - Primary, secondary, and destructive action buttons.
  - Data table column headers and status badges.
- **Approved Icon Format:** Inline SVG or dedicated Vue Icon components using standard 24x24 or 20x20 viewBox with `stroke="currentColor"` and `stroke-width="1.75"` or `"2"`.
- **Icon Container Tokens:**
  - Standard Nav: `w-5 h-5 text-slate-500 group-hover:text-primary transition-colors`
  - Active Nav: `w-5 h-5 text-primary`
  - Metric Card Icon Badge: `w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center`

| Menu Item | Prohibited Emoji | Standard SVG Metaphor |
| :--- | :---: | :--- |
| **Dashboard** | `📈` | `LayoutDashboard` / Grid of 4 rounded rectangles |
| **SLA Monitoring** | `🚨` | `AlertTriangle` / Shield Alert with subtle pulse |
| **Verifikasi DP** | `💳` | `CreditCard` / Bank Receipt with checkmark |
| **Mitra Vendor** | `🏢` | `BuildingStorefront` / Users Group |
| **Katalog Produk** | `🏷️` | `Tag` / Compass navigation |
| **Paket Wisata** | `🎒` | `MapPinPlus` / Route Path Itinerary |
| **Destinasi Wisata**| `📍` | `MapPin` / Mountain Panorama |
| **Settlement Payout**| `💸` | `Banknotes` / Wallet Transfer |
| **Refunds** | `🔄` | `ArrowPath` / Currency exchange |

---

## 3. Color Tokens & Surface Hierarchy

```
[ Canvas Background: #F8FAFC (slate-50) ]
       │
       ▼
[ Card Surface: #FFFFFF (white) + border border-slate-200/80 + shadow-xs ]
       │
       ▼
[ Elevated Element / Modal: #FFFFFF + shadow-xl + ring-1 ring-black/5 ]
```

### 3.1 Status Pill Standards
Every lifecycle status in Admin and Partner portals must render using the standard status badge format:

```html
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
  Terkonfirmasi
</span>
```

| Domain Status | Badge Container Style | Dot Indicator |
| :--- | :--- | :--- |
| `CONFIRMED`, `PAID`, `APPROVED`, `PUBLISHED` | `bg-emerald-50 text-emerald-700 border-emerald-200/60` | `bg-emerald-500` (solid) |
| `AWAITING_PAYMENT`, `PAYMENT_REVIEW`, `PENDING_REVIEW` | `bg-amber-50 text-amber-800 border-amber-200/60` | `bg-amber-500` (subtle pulse) |
| `MANUAL_REVIEW`, `OVERDUE` | `bg-rose-50 text-rose-800 border-rose-200/60` | `bg-rose-500` (active pulse) |
| `REJECTED`, `CANCELLED`, `SUSPENDED` | `bg-slate-100 text-slate-700 border-slate-200` | `bg-slate-400` |
| `DRAFT`, `NONE` | `bg-slate-50 text-slate-600 border-slate-200` | `bg-slate-400` |

---

## 4. Admin Navigation & Dashboard Architecture

### 4.1 Admin Layout Hierarchy
1. **Header Bar (Top):**
   - Height: 64px (`h-16`).
   - Clean white background (`bg-white`) with bottom border (`border-b border-slate-200`).
   - Left: Platform Logo + subtle badge `"ADMIN CONSOLE"` in muted slate pill (`bg-slate-100 text-slate-700 text-[11px] font-bold tracking-wider`).
   - Right: Quick stats (System status), Bilingual Switcher (`LanguageSwitcher.vue`), Admin User Avatar with initials, and Logout dropdown.
2. **Sidebar (Left):**
   - Width: 260px (`w-64`), crisp white with right border (`border-r border-slate-200`).
   - Grouped into 4 explicit sections:
     - **OPERASIONAL:** Dashboard, SLA Monitoring (with manual count pill), Booking Dispatch Queue.
     - **VERIFIKASI & KYC:** Verifikasi DP (Review pill), Mitra Vendor, Moderasi Produk.
     - **KATALOG MASTER:** Destinasi Wisata, Paket Wisata Curated.
     - **FINANSIAL:** Settlement Mitra, Refund Wisatawan.
   - Active Link State: `bg-primary/5 text-primary font-bold border-l-4 border-primary`.

### 4.2 Dashboard Functional Modules
The Admin Dashboard (`Admin/Dashboard.vue`) must serve as an operational flight deck:
1. **Row 1: Operational Vital Metrics (4 Cards):**
   - Verifikasi Bukti DP (Pending count + currency total).
   - Intervensi Manual SLA (Breached count with red highlight).
   - Verifikasi Mitra KYC (Pending partner accounts).
   - Komisi Platform Terkumpul (Cumulative 10% net platform revenue).
2. **Row 2: Content & Marketplace Asset Overview (3 Cards):**
   - **Destinasi Wisata Master:** Total destinasi aktif per klaster (Toba, Bukit Lawang, Medan, Berastagi) + Quick Button `+ Tambah Destinasi`.
   - **Paket Wisata Curated:** Total paket aktif (Published) + Quick Button `+ Buat Paket Wisata Baru`.
   - **Armada Mitra Siap Jalan:** Total unit kendaraan terdaftar (HiAce, Innova, Avanza).
3. **Row 3: Actionable Queues (Two Columns):**
   - Left: Antrean Pembayaran DP Terbaru (Review & 1-click verify modal).
   - Right: Antrean Dispatch Pesanan Baru (Curated tour bookings awaiting partner assignment).

---

## 5. Homepage Ecosystem Presentation Standards (`Welcome.vue`)

To build traveler trust and counter off-platform disintermediation, the Homepage must prominently feature the **5 Golden Guarantees of Muter-Muter**:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                  5 STANDAR EMAS & JAMINAN EKOSISTEM MUTER-MUTER              │
├──────────────┬──────────────┬──────────────┬──────────────────┬──────────────┤
│ 🚐 ARMADA    │ 🛠️ GARANSI   │ ☕ MAKAN &   │ 🚢 TIKET FERRY   │ 🛡️ DP 30%    │
│    BERSIH    │    PENGGANTI │    ROKOK     │    DANAU TOBA    │    KUNCI     │
│    & PRIMA   │    1-2 JAM   │    SUPIR     │    TERJADWAL     │    UNIT      │
│              │              │    INCLUDED  │                  │              │
│ Kabin wangi, │ Unit cadangan│ Bebas pungli │ Tiket penyebe-   │ Hold 24 jam  │
│ AC dingin,   │ sigap jika   │ dan canggung,│ rangan resmi     │ aman, invoice│
│ bebas asap   │ kendala di   │ driver lokal │ tanpa antre liar │ legal instan │
│ rokok.       │ rute kaldera.│ bersahabat.  │ di pelabuhan.    │ terverifikasi│
└──────────────┴──────────────┴──────────────┴──────────────────┴──────────────┘
```

### 5.1 Presentation Rules:
- Displayed immediately below the Hero Search card or between Featured Clusters and Tour Packages.
- Card Design: Clean white surface cards with colored SVG icons, bold title, and concise two-line guarantee explanation.
- Micro-badge: *"Standar Kualitas Resmi Sumatera Utara"* with a verified checkmark.

---

## 6. Context-Aware Profile Management & Shell Continuity

### 6.1 The Disjointed Experience Problem
Vanilla auth scaffolding (such as default Laravel Breeze) often wraps `/profile` in a separate, isolated layout (`AuthenticatedLayout.vue`). When a user navigates to their profile from an enterprise portal (Admin Console) or a Partner Portal, the sidebar and header abruptly vanish, giving the appearance of being kicked into a different, incomplete application.

### 6.2 The Shell Continuity Standard
1. **Dynamic Role-Based Layout Binding:**
   - The user profile view (`Profile/Edit.vue`) must dynamically inherit the active user's environment:
     - **Admin Users:** Rendered inside `<AdminLayout>` with full admin sidebar, breadcrumb, and topbar intact.
     - **Partner Users:** Rendered inside `<PartnerLayout>` with partner sidebar and operational status intact.
     - **Customers:** Rendered inside `<AppLayout>` with customer navigation, brand accents, and footer intact.
2. **Sticky Anchored Sidebar Architecture:**
   - Sidebars in both the Admin Console (`AdminLayout.vue`) and Partner Portal (`PartnerLayout.vue`) must be anchored statically (`md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:overflow-y-auto`).
   - When the main operational dashboard or data tables are scrolled vertically, the navigation sidebar must remain permanently stationary and accessible without scrolling off-screen.
3. **Dashboard Context Preservation:**
   - In customer dashboards (`Dashboard.vue`), profile updates should be accessible directly within the dashboard tab/modal structure without breaking context or navigating to an unbranded page.
4. **Enterprise Destination CRUD Experience:**
   - Master data management for Destinations in the Admin Console must provide full lifecycle operations (Create, Read, Update, Delete, and 1-Click Status Toggle) within the table/grid view without page jumping, accompanied by instant photo upload previews and storage link verification.


