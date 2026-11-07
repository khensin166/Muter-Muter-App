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
- [x] Define PHP 8.2+ Backed Enums:
  - [x] `BookingStatus` (12 lifecycle statuses)
  - [x] `PaymentStatus` (8 independent statuses)
  - [x] `InventoryStatus` (4 orthogonal statuses)
  - [x] `ProductType`, `OwnershipType`, `ApprovalStatus`, `ProductStatus`, `PricingType`
  - [x] `PartnerRole` (OWNER, STAFF) & `PartnerStatus`
  - [x] `TourType`, `VehicleType`, `TransmissionType`
  - [x] `SettlementStatus`, `RefundStatus`, `DestinationCluster`, `UserRole`
- [x] Create database migrations with UUID primary keys & constraints:
  - [x] `users` (UUID PK), `password_reset_tokens`, `sessions` (UUID FK)
  - [x] `partners`, `partner_members`, `partner_documents`
  - [x] `destination_categories`, `destinations`, `destination_media`
  - [x] `products`, `tour_packages`, `vehicles`, `product_media`, `product_approval_logs`
  - [x] `tour_itinerary_days`, `tour_itinerary_stops`, `tour_departures`
  - [x] `bookings` (Snapshots JSONB, SLA timestamp), `booking_tour_details`, `booking_vehicle_details`, `booking_status_histories`
  - [x] `inventory_holds` (24h hold index), `vehicle_unavailabilities`
  - [x] `payments`, `payment_proofs`
  - [x] `refunds`, `vendor_settlements`, `audit_logs`
- [x] Create 25 Eloquent Models with typed relations, UUID traits, and JSON casts.
- [x] Implement database seeders:
  - [x] Super Admin (`admin@mutermuter.com`), Partner Owner, Partner Staff, Customer.
  - [x] 4 Lake Toba clusters (Parapat, Samosir East, Samosir West, Merek/Tongging).
  - [x] Curated tour packages (with ferry inclusion) & verified vehicle units.
- [x] Execute migration & seeding on PostgreSQL (`muter_muter`). All 25 Pest tests passing.

---

## Phase 3: Auth, RBAC & Partner Onboarding
- [x] Setup Authentication system (Customer, Partner, Admin).
- [x] Create Partner Registration page with document upload (KTP, NIB, Photos).
- [x] Create Admin Partner Review interface (`Approve` / `Reject` with reason).
- [x] Implement Partner Multi-Staff invitation and role assignment (`Owner` vs `Staff`).
- [x] Create Laravel Policies for Partner isolation (Partners can only view their own records).
- [x] 35 Automated tests passing for Auth, RBAC, KYC, and Multi-Staff isolation.

---

## Phase 4: Product Catalog & Discovery
- [x] Build Public Destination Listing & Filter by cluster.
- [x] Build Public Destination Detail view with map coordinates and ticket info.
- [x] Build Partner Product Creation wizard:
  - [x] Tour Package form (Itinerary, inclusions, ferry note, pricing).
  - [x] Vehicle Rental form (Brand, capacity, transmission, with-driver toggle).
- [x] Build Admin Product Moderation queue (`Approve` / `Reject`).
- [x] Build Public Tour Package detail view.
- [x] Build Public Vehicle Rental detail view.
- [x] 18 Automated tests passing for Catalog Discovery, Partner Products, and Admin Moderation (53 total suite tests).

---

## Phase 5: Booking Engine & SLA Monitoring
- [x] Build Checkout wizard with guest-to-login modal.
- [x] Enforce lead-time validation (H-1 tour, H-12h vehicle).
- [x] Implement booking creation action with JSONB product/pricing snapshots.
- [x] Implement 24-hour vendor SLA timer calculation.
- [x] Build Partner Booking Request inbox with 1-click `Setujui Ketersediaan` / `Tolak`.
- [x] Build Laravel Scheduler task to elevate SLA timeouts (>24h) to `MANUAL_REVIEW`.
- [x] Build Admin Manual Review queue for unresponded bookings.
- [x] 12 Automated tests passing for Booking Creation, Partner SLA, and Timeout Escalation (65 total suite tests).

---

## Phase 6: Inventory Hold & Semi-Automated Payment
- [x] Implement 24-hour temporary inventory hold creation upon vendor approval.
- [x] Implement Down Payment bill generation (default 30%).
- [x] Build Customer Payment view with platform bank account instructions.
- [x] Build Customer Payment Proof upload form (image/PDF validation).
- [x] Build Admin Payment Verification dashboard:
  - [x] Preview transfer proof.
  - [x] Approve $\rightarrow$ Status: `CONFIRMED`, Inventory: `ALLOCATED`.
  - [x] Reject $\rightarrow$ Status: `AWAITING_PAYMENT` with rejection feedback.
- [x] Build Scheduler task to release expired inventory holds (after 24h unpaid).
- [x] 6 Automated tests passing for Payment Proof, Verification & Hold Expiration (71 total suite tests).

---

## Phase 7: WhatsApp Notification Integration
- [x] Build `WhatsAppService` class with HTTP client and mock mode for testing.
- [x] Configure environment variables in `.env` (`WHATSAPP_GATEWAY_URL`, `WHATSAPP_TOKEN`).
- [x] Implement dispatch trigger for:
  - [x] New booking alert to Partner PIC (`notifyVendorNewBooking`).
  - [x] Booking approval & DP payment instructions to Customer (`notifyCustomerBookingApproved`).
  - [x] Payment confirmation & voucher to Customer (`notifyCustomerPaymentConfirmed`).
- [x] 5 Automated tests passing for WhatsApp notification service & triggers (76 total suite tests).

---

## Phase 8: Cancellation, Refund & Partner Settlement
- [x] Build Customer Cancellation modal with real-time refund calculator (>7d: 100%, 3-7d: 50%, <3d: 0% on amount paid).
- [x] Implement inventory release upon cancellation.
- [x] Build Admin Refund processing queue (`/admin/refunds`, `/admin/refunds/{id}`).
- [x] Implement trip completion trigger (`php artisan trips:mark-completed`).
- [x] Build Partner Settlement ledger calculating net payout (`Gross - 10% Platform Commission`).
- [x] Build Admin Payout execution interface with bank reference logging (`POST /admin/settlements/{id}/payout`).
- [x] 8 Automated tests passing for Cancellation, Refund & Settlements (84 total suite tests).

---

## Phase 9: Testing & Launch Readiness
- [x] Write Feature tests for Booking State Machine (Creation, SLA, Timeout, Hold, Payment, Refund).
- [x] Write Feature tests for 24h Hold Expiration.
- [x] Write Feature tests for Lead-time validation (H-1 tour, H-12h vehicle).
- [x] Perform mobile responsive UX audit (tested breakpoints `sm:`, `md:`, `lg:` with responsive navigation).
- [x] Prepare deployment documentation and environment checklist (`docs/deployment/DEPLOYMENT_GUIDE.md`).

---

## Phase 10: Brand Generalization (North Sumatra) & Logo Asset Integration
- [x] **Documentation & Planning:**
  - [x] Update `docs/product/PRD.md` with multi-cluster regional vision (Danau Toba, Bukit Lawang, Tangkahan, Berastagi, Medan).
  - [x] Update `docs/design/DESIGN_SYSTEM.md` with logo storage standards (`public/images/`) and navbar brand guidelines.
  - [x] Update `AGENTS.md` identity specification.
- [x] **Execution (Code & UI):**
  - [x] Setup image asset directory: `public/images/` and place BetaTudia logo assets (`logo.png`, `logo-white.png`, `logo-icon.svg`).
  - [x] Implement robust `<ApplicationLogo />` Vue component with brand typography **BetaTudia** alongside the logo emblem.
  - [x] Refactor `resources/js/Layouts/AppLayout.vue`: replace hardcoded "Lake Toba" navbar badge with "BetaTudia", update footer categories.
  - [x] Refactor `resources/js/Pages/Welcome.vue`: generalize hero titles and promotional copies to Sumatera Utara.
  - [x] Refactor `resources/js/Pages/Dashboard.vue`: update Customer Travel Hub title and greetings.
  - [x] Refactor `resources/js/Layouts/AdminLayout.vue` & `PartnerLayout.vue`: generalize portal headers.

---

## Phase 11: Security Hardening & 4-Layer File Upload Sanitization
- [x] **Documentation & Architecture Specification:**
  - [x] Create `docs/architecture/SECURITY_AND_UPLOAD_SPEC.md` defining SQL Injection protection, Storage volume persistence, and 4-Layer File Upload Defense.
  - [x] Update `docs/architecture/SYSTEM_ARCHITECTURE.md` and `docs/development/TASKS.md`.
- [x] **Execution (Security Service, Controllers & Web Server):**
  - [x] Implement `App\Services\FileSecurityService` (Magic bytes validation, binary payload scanning for `<?php`/`<script>`, pixel dimension cap `4096x4096`, PHP GD image re-encoding & EXIF stripping, UUID v4 filename generation).
  - [x] Integrate `FileSecurityService` into `PaymentProofController` (`proof_file`).
  - [x] Integrate `FileSecurityService` into `PartnerRegisterController` (`ktp_file`, `nib_file`, `fleet_photos`).
  - [x] Integrate `FileSecurityService` into `PartnerProductController` (`photos` for Tour & Vehicle).
  - [x] Harden `docker/Caddyfile` to block PHP execution inside `/storage/*` and enforce `X-Content-Type-Options: nosniff`.
  - [x] Write automated security tests (`tests/Feature/Security/FileUploadSecurityTest.php`) verifying rejection of spoofed PHP files, polyglot webshells, SVG XSS files, and verifying clean image re-encoding.

---

## Phase 12: Multilingual Architecture (i18n), PostgreSQL JSONB & Translation Integration
- [x] **Documentation & Architecture Specification:**
  - [x] Create `docs/architecture/LOCALIZATION_I18N_SPEC.md` defining bilingual schema (`id` & `en`), PostgreSQL JSONB columns, Expression & GIN Indexing, and Google Cloud Translation Service.
  - [x] Update `docs/architecture/SYSTEM_ARCHITECTURE.md`, `docs/development/TASKS.md`, and `AGENTS.md`.
- [ ] **Execution (Database, Services, UI & Forms):**
  - [ ] Create PostgreSQL migration to convert translatable columns to `JSONB` with B-Tree expression indexes and GIN full-text indexes.
  - [ ] Implement `App\Traits\HasTranslations` trait for translatable Eloquent models.
  - [ ] Implement `App\Services\GoogleTranslationService` with HTTP client and mock mode.
  - [ ] Implement `SetLocaleMiddleware`, `POST /locale` switcher route, and `lang/id.json` & `lang/en.json` dictionaries.
  - [ ] Build `resources/js/Components/LanguageSwitcher.vue` in `AppLayout.vue` navbar.
  - [ ] Build dynamic `ID` / `EN` toggle button & Auto-Translate button in `PartnerProductController` forms (`CreateTour.vue`, `CreateVehicle.vue`, and itinerary modals).
  - [ ] Write automated tests for locale persistence, JSONB queries, and translation fallback.

