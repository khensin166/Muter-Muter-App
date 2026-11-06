# Development Task Checklist — Lake Toba Travel Marketplace

- **Status Legend:**
  - `[x]` Completed
  - `[/]` In Progress
  - `[ ]` Pending Execution

---

## Phase 0: Requirements, Grooming & Architecture Alignment
- [x] Consolidate Foundation v1.0 specifications into workspace.
- [x] Resolve and confirm high-impact business decisions:
  - [x] Vendor SLA (24h) and timeout handling (Admin manual intervention).
  - [x] Single vendor approval door for payment release.
  - [x] Minimum lead time: H-1 (24h) Tour, H-12h Vehicle.
  - [x] Down Payment default 30%, admin-configurable.
  - [x] Semi-automated payment flow (Web + WhatsApp notification + Admin bank verification).
  - [x] Baseline refund schedule: >7d 100%, 3-7d 50%, <3d 0% on amount paid.
  - [x] Ferry crossing bundled into tour package itinerary.
  - [x] Go WhatsApp Gateway client integration.
  - [x] Partner multi-staff roles (`Partner Owner` & `Partner Staff`).
  - [x] Partner KYC documentation requirements.
- [x] Generate Core Documentation Suite:
  - [x] `AGENTS.md`
  - [x] `README.md`
  - [x] `docs/product/PRD.md`
  - [x] `docs/product/BUSINESS_RULES.md`
  - [x] `docs/product/USER_STORIES.md`
  - [x] `docs/product/USER_FLOWS.md`
  - [x] `docs/product/OPEN_QUESTIONS.md`
  - [x] `docs/architecture/SYSTEM_ARCHITECTURE.md`
  - [x] `docs/architecture/DATABASE_ERD.md`
  - [x] `docs/architecture/DATA_DICTIONARY.md`
  - [x] `docs/development/IMPLEMENTATION_PLAN.md`
  - [x] `docs/development/TASKS.md`

---

## Phase 1: Environment Setup & Project Scaffolding
- [x] Initialize Laravel 11 application with PHP 8.2+.
- [x] Install and configure Inertia.js (Vue 3 adapter).
- [x] Install and configure Tailwind CSS 3.x and Tailwind Typography / Forms plugins.
- [x] Setup global CSS variables & color theme in `app.css` & `tailwind.config.js`.
- [x] Setup database drivers (PostgreSQL + SQLite testing) in PHP configuration.
- [x] Configure Vite build pipeline and verify production asset bundling.
- [x] Create base UI layouts:
  - [x] `AppLayout.vue` (Customer / Public marketplace navbar & footer).
  - [x] `PartnerLayout.vue` (Vendor portal sidebar & header).
  - [x] `AdminLayout.vue` (Operations control center).
- [x] Verify Pest test suite execution (25 passing tests).

---

## Phase 2: Database Migrations, Enums & Seeders
- [ ] Define PHP 8.2+ Backed Enums:
  - [ ] `BookingStatus`
  - [ ] `PaymentStatus`
  - [ ] `InventoryStatus`
  - [ ] `ProductType`
  - [ ] `OwnershipType`
  - [ ] `ApprovalStatus`
  - [ ] `PartnerRole`
- [ ] Create database migrations with UUID primary keys:
  - [ ] `users`, `password_resets`, `sessions`
  - [ ] `partners`, `partner_members`, `partner_documents`
  - [ ] `destination_categories`, `destinations`, `destination_media`
  - [ ] `products`, `tour_packages`, `vehicles`, `product_media`
  - [ ] `tour_itinerary_days`, `tour_itinerary_stops`, `tour_departures`
  - [ ] `bookings`, `booking_status_histories`
  - [ ] `inventory_holds`, `vehicle_unavailabilities`
  - [ ] `payments`, `payment_proofs`
  - [ ] `refunds`, `vendor_settlements`, `audit_logs`
- [ ] Implement database seeders:
  - [ ] Roles and Super Admin account.
  - [ ] Lake Toba destination clusters (Parapat, Samosir East, Samosir West, Merek).
  - [ ] Demo partner profiles and test products.

---

## Phase 3: Auth, RBAC & Partner Onboarding
- [ ] Setup Authentication system (Customer, Partner, Admin).
- [ ] Create Partner Registration page with document upload (KTP, NIB, Photos).
- [ ] Create Admin Partner Review interface (`Approve` / `Reject` with reason).
- [ ] Implement Partner Multi-Staff invitation and role assignment (`Owner` vs `Staff`).
- [ ] Create Laravel Policies for Partner isolation (Partners can only view their own records).

---

## Phase 4: Product Catalog & Discovery
- [ ] Build Public Destination Listing & Filter by cluster.
- [ ] Build Public Destination Detail view with map coordinates and ticket info.
- [ ] Build Partner Product Creation wizard:
  - [ ] Tour Package form (Itinerary, inclusions, ferry note, pricing).
  - [ ] Vehicle Rental form (Brand, capacity, transmission, with-driver toggle).
- [ ] Build Admin Product Moderation queue (`Approve` / `Reject`).
- [ ] Build Public Tour Package detail view.
- [ ] Build Public Vehicle Rental detail view.

---

## Phase 5: Booking Engine & SLA Monitoring
- [ ] Build Checkout wizard with guest-to-login modal.
- [ ] Enforce lead-time validation (H-1 tour, H-12h vehicle).
- [ ] Implement booking creation action with JSONB product/pricing snapshots.
- [ ] Implement 24-hour vendor SLA timer calculation.
- [ ] Build Partner Booking Request inbox with 1-click `Setujui Ketersediaan` / `Tolak`.
- [ ] Build Laravel Scheduler task to elevate SLA timeouts (>24h) to `MANUAL_REVIEW`.
- [ ] Build Admin Manual Review queue for unresponded bookings.

---

## Phase 6: Inventory Hold & Semi-Automated Payment
- [ ] Implement 24-hour temporary inventory hold creation upon vendor approval.
- [ ] Implement Down Payment bill generation (default 30%).
- [ ] Build Customer Payment view with platform bank account instructions.
- [ ] Build Customer Payment Proof upload form (image/PDF validation).
- [ ] Build Admin Payment Verification dashboard:
  - [ ] Preview transfer proof.
  - [ ] Approve $\rightarrow$ Status: `CONFIRMED`, Inventory: `ALLOCATED`.
  - [ ] Reject $\rightarrow$ Status: `AWAITING_PAYMENT` with rejection feedback.
- [ ] Build Scheduler task to release expired inventory holds (after 24h unpaid).

---

## Phase 7: WhatsApp Notification Integration
- [ ] Build `WhatsAppService` class with HTTP client and mock mode for testing.
- [ ] Configure environment variables in `.env` (`WHATSAPP_GATEWAY_URL`, `WHATSAPP_TOKEN`).
- [ ] Implement dispatch trigger for:
  - [ ] New booking alert to Partner PIC.
  - [ ] Booking approval & DP payment instructions to Customer.
  - [ ] Payment confirmation & voucher to Customer.
  - [ ] Hold expiration warning.

---

## Phase 8: Cancellation, Refund & Partner Settlement
- [ ] Build Customer Cancellation modal with real-time refund calculator (>7d: 100%, 3-7d: 50%, <3d: 0% on amount paid).
- [ ] Implement inventory release upon cancellation.
- [ ] Build Admin Refund processing queue.
- [ ] Implement trip completion trigger (`COMPLETED`).
- [ ] Build Partner Settlement ledger calculating net payout (`Gross - 10% Platform Commission`).
- [ ] Build Admin Payout execution interface with bank reference logging.

---

## Phase 9: Testing & Launch Readiness
- [ ] Write Feature tests for Booking State Machine.
- [ ] Write Feature tests for 24h Hold Expiration.
- [ ] Write Feature tests for Lead-time validation.
- [ ] Perform mobile responsive UX audit.
- [ ] Prepare deployment documentation and environment checklist.

