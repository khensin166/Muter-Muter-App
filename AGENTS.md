# AGENTS.md — Development Guidelines for Google Antigravity & AI Assistants

## 1. Project Overview & Identity
- **Project Name:** Lake Toba Travel Marketplace (Muter-Muter App)
- **Product Type:** Travel Marketplace & Trip Management Platform
- **Initial Target Market:** Lake Toba and surrounding areas (North Sumatra, Indonesia)
- **Architecture:** Modular Monolith using Laravel 11, Inertia.js, Vue 3, Tailwind CSS, and PostgreSQL.

## 2. Fundamental Engineering & Business Principles
1. **Never Invent Business Rules:** Treat confirmed decisions in `docs/product/BUSINESS_RULES.md` as strict requirements, recommendations as proposals, and unresolved items as open questions.
2. **Explicit State Separation:**
   - Booking, Payment, and Inventory lifecycles MUST have independent state machines.
   - Never couple payment status directly into a single booking status column.
   - Example valid combination: Booking `CONFIRMED`, Payment `PARTIALLY_PAID`, Inventory `ALLOCATED`.
3. **Immutability via Snapshots:**
   - When a booking is created, the system MUST snapshot the product details, pricing breakdown, cancellation policy, and platform commission percentage into JSONB/dedicated columns.
   - Future updates to a product catalog item must NEVER modify past or active bookings.
4. **Data Types & Integrity:**
   - All primary keys MUST use UUID v4.
   - All monetary amounts MUST use `NUMERIC(14,2)` with IDR currency default.
   - All timestamps MUST use `TIMESTAMPTZ`.
   - Use strict database foreign keys and transaction isolation (`DB::transaction`) for booking, payment, and inventory allocation.
5. **Authorization & Access Control:**
   - Use Laravel Policies and Spatie/RBAC.
   - Strictly forbid hardcoded role strings or inline capability checks across controllers.
   - Partner multi-tenancy isolation: A partner must never view or manipulate data belonging to another partner.

## 3. Tech Stack Conventions
- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 (Composition API `<script setup>`), Inertia.js
- **Styling:** Tailwind CSS (Mobile-first responsive design)
- **Database:** PostgreSQL (with UUID support `pgcrypto`/`uuid-ossp`)
- **Background Tasks:** Synchronous for MVP; Laravel Scheduler (`console.php`) handles routine cron jobs (e.g. hold expiration, SLA reminder).
- **External Notifications:** Modular `WhatsAppService` interacting with the internal Go WhatsApp API Gateway via HTTP client.

## 4. Directory Structure Standard
```
app/
├── Actions/              # Single-responsibility domain actions
├── Enums/                # PHP 8.1+ Enums for statuses and types
├── Http/
│   ├── Controllers/      # Inertia & Web controllers (grouped by namespace: Customer, Partner, Admin)
│   ├── Middleware/
│   └── Requests/         # FormRequest validation classes
├── Models/               # Eloquent models with typed relationships
├── Policies/             # Laravel authorization policies
├── Services/             # External services (WhatsAppService, FinancialService, etc.)
└── Providers/

database/
├── migrations/           # PostgreSQL migrations with UUID PKs
├── seeders/              # Baseline roles, permissions, admin, sample destinations
└── factories/

resources/
├── js/
│   ├── Components/       # Reusable UI components
│   ├── Layouts/          # AppLayout, GuestLayout, PartnerLayout, AdminLayout
│   ├── Pages/            # Inertia page views
│   └── Composables/      # Shared Vue composables
└── css/

routes/
├── web.php               # Public & customer web routes
├── partner.php           # Partner portal routes
├── admin.php             # Admin portal routes
└── console.php           # Scheduler task definitions
```

## 5. Development Workflow & Commands
- **Migrations & Seeding:** `php artisan migrate:fresh --seed`
- **Asset Building:** `npm run dev` or `npm run build`
- **Testing:** `php artisan test` (Pest / PHPUnit)
- **Code Style:** Laravel Pint (`./vendor/bin/pint`)

## 6. Communication & Documentation Linkage
Always refer back to the project documentation located in `docs/`:
- `docs/product/PRD.md` — Product scope and vision
- `docs/product/BUSINESS_RULES.md` — Single source of truth for business rules
- `docs/product/USER_FLOWS.md` — Step-by-step user and transaction flows
- `docs/architecture/DATABASE_ERD.md` — Schema diagrams and relations
- `docs/architecture/DATA_DICTIONARY.md` — Column-level specs
- `docs/development/IMPLEMENTATION_PLAN.md` — Phased roadmap
- `docs/development/TASKS.md` — Actionable checklist

