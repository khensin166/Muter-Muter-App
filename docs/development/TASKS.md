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
- [x] **Execution (Database, Services, UI & Forms):**
  - [x] Create PostgreSQL migration to convert translatable columns to `JSONB` with B-Tree expression indexes and GIN full-text indexes.
  - [x] Implement `App\Traits\HasTranslations` trait for translatable Eloquent models.
  - [x] Implement `App\Services\TranslationService` with self-hosted LibreTranslate client (`https://translate.kenantomfie.com`), `X-API-Key` auth, and mock mode fallback.
  - [x] Implement `SetLocaleMiddleware`, `POST /locale` switcher route, and session/cookie persistence.
  - [x] Build `resources/js/Components/LanguageSwitcher.vue` in `AppLayout.vue`, `PartnerLayout.vue`, and `AdminLayout.vue`.
  - [x] Build dynamic `ID` / `EN` toggle button & Auto-Translate button in `PartnerProductController` forms (`CreateTour.vue`, `CreateVehicle.vue`, and itinerary modals).
  - [x] Write automated tests for locale persistence, JSONB queries, and translation fallback (`MultilingualTest.php`).

---

## Phase 13: Platform-Curated Tours, Dispatching Engine, Partner Fleet Role, Review System & Anti-Disintermediation Engine
- [x] **Grooming & Architecture Documentation Update:**
  - [x] Update `docs/product/BUSINESS_RULES.md` with Two-Tier Role Matrix (`SUPER_ADMIN`, `PARTNER [OWNER, STAFF]`, `CUSTOMER`).
  - [x] Document Platform-Curated Tour Model (`OwnershipType::INTERNAL`) with North Sumatra preset inclusions (BBM PP, Driver/Guide, Makan & Rokok Supir, Foto/Drone, Tiket Ferry).
  - [x] Document Booking Dispatching & Partner Assignment rules and 24h SLA fallback.
  - [x] Document Partner Lifetime Trip History (`completed_trips_count`) and Customer Review & Rating Engine (`average_rating`, 1-5 stars).
  - [x] Document **Anti-Disintermediation & Win-Win Protection Engine (4 Pilar)**:
    - [x] Pilar 1: Driver Loyalty & Priority Dispatching Engine (Mitra untung & dilindungi, kompensasi pembatalan < 3 hari).
    - [x] Pilar 2: Traveler Protection Locks & Value Guarantee (Garansi armada pengganti 1-2 jam di rute kaldera, include makan/rokok supir & ferry).
    - [x] Pilar 3: Masked & Timed Contact Release (Kontak disamarkan, baru dibuka otomatis pada H-1 keberangkatan untuk sinkronisasi penjemputan bandara KNO/Silangit).
    - [x] Pilar 4: Fair & Transparent Platform Commission (10% platform, 90% mitra, sifat opsional/configurable bilateral) & Pakta Integritas Mitra.
  - [x] Document Reference Standard Itineraries: 6D5N Famtrip North Sumatra (KNO - Bukit Lawang - Medan - Parapat - Samosir) and 1-Day Tour Eksotis Danau Toba & Samosir (Open Trip 250k-270k / Private 1.500k).
  - [x] Create `docs/architecture/UI_UX_DESIGN_SYSTEM.md`:
    - [x] Strict ban on raw emoji icons in management menus/actions; enforce semantic SVG icons with tinted containers.
    - [x] Enterprise SaaS elevation hierarchy (`bg-white` surface, `border-slate-200/80`, `shadow-xs`).
    - [x] Standardized Status Pills with animated/solid dot indicators.
    - [x] Tabular data typography and empty state illustrations.
  - [x] Document in `docs/product/BUSINESS_RULES.md` (Section 12 & 13):
    - [x] Section 12: Destination-to-Tour Package Relational Data Binding (`tour_itinerary_stops.destination_id` -> `destinations.id`) and Auto-Aggregated Photo Gallery.
    - [x] Section 13: Clean Vehicle Standard (Standar Kebersihan, Kenyamanan, dan Kelaikan Teknis Armada Mitra).
  - [x] Update `docs/product/PRD.md` with Admin Portal Content Overview (Destinations, Curated Tours, Fleet) and 5 Golden Guarantees.
  - [x] Fix Mermaid diagram syntax preview in `docs/architecture/DATABASE_ERD.md` (remove non-standard UK markers & quote string comments).
  - [x] Fix destination dynamic gallery & `day()` relation alias on `TourItineraryStop` in `app/Models/TourItineraryStop.php` & `Show.vue`.
  - [x] Update `docs/product/USER_FLOWS.md`, `docs/product/USER_STORIES.md`, `docs/architecture/DATABASE_ERD.md`, and `docs/architecture/DATA_DICTIONARY.md`.

---

## Phase 14: Execution — Admin Enterprise Redesign, Curated Tour Creator, Destination Binding & Homepage Guarantees
- [x] **1. Admin Layout & Dashboard Redesign:**
  - [x] Refactor `resources/js/Layouts/AdminLayout.vue`: replace all raw emojis with semantic SVG icons, crisp white surface, modern active link pills, and clean user profile header.
  - [x] Redesign `resources/js/Pages/Admin/Dashboard.vue`:
    - [x] Add Content & Catalog Overview cards: Total Master Destinations per cluster, Curated Tour Packages status, Active Partner Fleet count.
    - [x] Add 1-Click Quick Action buttons: `+ Buat Paket Wisata Baru`, `+ Kelola Destinasi`.
    - [x] Add Dispatch Queue modal & overview for platform-curated tour orders.
- [x] **2. Destination-to-Package Photo Auto-Binding:**
  - [x] Update `TourPackage` model and `Public/TourController.php` to eager-load `itineraryDays.stops.destination.media`.
  - [x] Update `resources/js/Pages/Tours/Show.vue`:
    - [x] Render verified thumbnail photos on each itinerary stop timeline.
    - [x] Add clickable links to `/destinations/{slug}` on itinerary stops.
    - [x] Implement auto-aggregated photo gallery combining package cover with destination media.
- [x] **3. Admin Tour Package Creator & Master Destinations Management:**
  - [x] Build `resources/js/Pages/Admin/Destinations/Index.vue` and `AdminDestinationController.php`.
  - [x] Build `resources/js/Pages/Admin/Products/CreateTour.vue`:
    - [x] Multi-day itinerary builder with searchable Master Destination dropdown.
    - [x] Auto-preview destination photo, description, and estimated duration upon stop selection.
    - [x] Preset North Sumatra inclusions checklist (BBM, Driver/Guide, Makan & Rokok Supir, Foto/Drone, Tiket Ferry).
    - [x] 1-Click Template Loaders (`6D5N Famtrip Sumut` & `1D Danau Toba`).
    - [x] Bilingual `ID` / `EN` toggle with Auto-Translate.
  - [x] Add routes `GET /admin/tours/create`, `POST /admin/tours`, and `GET/POST /admin/destinations` in `routes/admin.php`.
- [x] **4. Homepage 5 Golden Guarantees Showcase:**
  - [x] Update `resources/js/Pages/Welcome.vue` with dedicated "5 Standar Emas & Jaminan Ekosistem Muter-Muter" banner & Win-Win Agreement section:
    - [x] 1. Armada Bersih & Prima (Kabin wangi, AC dingin, bebas asap rokok).
    - [x] 2. Garansi Unit Pengganti 1–2 Jam (Cadangan sigap di rute kaldera).
    - [x] 3. Driver Bersahabat & Uang Makan/Rokok Supir Included (Bebas pungli tips canggung).
    - [x] 4. Tiket Ferry Danau Toba PP Terjadwal Resmi.
    - [x] 5. Transparansi DP 30% Kunci Unit & Invoice Resmi.
- [x] **5. Database & Dispatching Engine:**
  - [x] Migration for `reviews` table and partner rating aggregate columns (`2026_11_10_200000_create_reviews_and_add_partner_reputation_columns.php`).
  - [x] `Review` Eloquent model with vehicle cleanliness rating score (`vehicle_rating`).
  - [x] Admin Booking Dispatch modal & endpoint `POST /admin/bookings/{id}/dispatch`.
- [x] **6. Quality Assurance & Testing:**
  - [x] Run Pest test suite (`106 passed, 382 assertions`).
  - [x] Run Vite production build (`npm run build` succeeded).

---

## Phase 15: Concurrency, Performance Indexing & Database Deployment Documentation
- [x] **1. Database Concurrency & Race Condition Hardening:**
  - [x] Implement pessimistic locking (`lockForUpdate()`) in `AdminPaymentController::approve()` to guard against simultaneous approval and cron expiration.
  - [x] Harden `ReleaseExpiredInventoryHolds` with `lockForUpdate()` on `bookings` to prevent releasing holds of orders verified concurrently.
  - [x] Atomic partner completed trips metric incrementation (`Partner::increment('completed_trips_count')`) in `MarkCompletedTripsCommand`.
- [x] **2. High-Performance Database Indexes:**
  - [x] Create migration `2026_11_10_210000_add_performance_and_concurrency_indexes.php`.
  - [x] Add composite indexes on `partners(status, average_rating)`, `partners(completed_trips_count)`, `products(ownership_type, status, approval_status)`.
  - [x] Add composite indexes on `bookings(booking_status, service_end_date)` and `bookings(booking_status, service_start_date, contact_unmasked)`.
  - [x] Add composite indexes on `inventory_holds(vehicle_id, status, expires_at)` and `inventory_holds(tour_departure_id, status, expires_at)`.
  - [x] Add explicit Foreign Key indexes on `tour_itinerary_stops(destination_id)` and `destination_media(destination_id, is_primary, sort_order)`.
- [x] **3. Documentation Suite:**
  - [x] Create `docs/architecture/DATABASE_MIGRATION_AND_SEEDING_GUIDE.md`:
    - [x] Fresh setup instructions (`php artisan migrate:fresh --seed`).
    - [x] Incremental migration guide (`php artisan migrate --force`).
    - [x] Multilingual translation backfill command.
    - [x] Environment configuration & PostgreSQL troubleshooting.
  - [x] Create `docs/architecture/DATABASE_CONCURRENCY_AND_INDEXING_SPEC.md`:
    - [x] Concurrency race condition scenarios (Hold Expiration, Double Approval, Fleet Overbooking).
    - [x] Complete PostgreSQL index registry table.
  - [x] Update `docs/architecture/DATABASE_ERD.md` with complete clean Mermaid syntax and entity definitions.
  - [x] Update `docs/architecture/DATA_DICTIONARY.md` with new columns, reviews table, and high-performance index registry.

---

## Phase 16: Bug Fixes, Beta Tudia Printable PDF Itinerary & Customer Sharing
- [x] **1. Admin Dashboard Bug Fix (`is_active` Column):**
  - [x] Add migration `2026_11_10_220000_add_is_active_to_destinations_table.php` to add `is_active` boolean column with index.
  - [x] Update `Destination` Eloquent model `$fillable` and `$casts`.
  - [x] Fix media lookup to `is_primary` across `AdminDashboardController`, `AdminDestinationController`, and `AdminTourController`.
- [x] **2. Beta Tudia Printable PDF Itinerary Document:**
  - [x] Create route `GET /tours/{slug}/itinerary/print` and controller action `TourController::printItinerary`.
  - [x] Create `resources/js/Pages/Tours/ItineraryPrint.vue` with:
    - [x] Authentic North Sumatra Gorga Batak header accent & "Beta Tudia — Official Tour Itinerary" branding.
    - [x] Clean A4 page layout with automatic `@media print` CSS rules.
    - [x] Day-by-day timetable & activities breakdown, Inclusions & Exclusions grid, and 5 Golden Guarantees.
    - [x] 1-Click "Cetak / Simpan ke PDF" button.
- [x] **3. Interactive Customer Share Features:**
  - [x] Add "Unduh Itinerary PDF" and "Bagikan Paket" action buttons on `resources/js/Pages/Tours/Show.vue`.
  - [x] Web Share API integration with fallback to interactive Share Modal:
    - [x] Direct WhatsApp Group/Chat invitation link with pre-filled message.
    - [x] 1-Click Copy Link with animated "Disalin! ✓" feedback.
    - [x] Social share shortcuts (Facebook, X / Twitter).
- [x] **4. Quality Assurance:**
  - [x] Automated test for Admin Dashboard metrics loading & Beta Tudia printable document (`AdminCuratedTourAndDestinationTest`).
  - [x] Full test suite verified (`108 passed, 412 assertions`).
  - [x] Production asset build verified (`npm.cmd run build` in 4.42s).

---

## Phase 17: Partner Vendor Header Profile Dropdown, Sidebar Badge & Tab Favicon
- [x] **1. Partner Portal Header Refinement (`PartnerLayout.vue`):**
  - [x] Retain `ApplicationLogo` + "Portal Operasional Mitra Wisata" (`partner.portal_title`) cleanly in header.
  - [x] Relocate raw badge `Mitra Vendor` from top header to top of sidebar with green verified dot and clean pill.
  - [x] Convert static user name & logout button into modern Profile Avatar Dropdown Menu:
    - [x] Trigger button with initials avatar circle, truncated full name, and rotating chevron.
    - [x] Floating dropdown menu showing user name, email, verified partner badge, quick links (Dashboard, Staff, Settlements, Profile Settings), and logout button.
    - [x] Click-outside listener auto-closes dropdown.
- [x] **2. Browser Tab Icon (Favicon):**
  - [x] Add SVG vector favicon (`/images/logo-icon.svg`), PNG 32x32, and Apple Touch Icon to `resources/views/app.blade.php`.
  - [x] Generate fallback `public/favicon.svg` and `public/favicon.png` for root icon requests.
- [x] **3. Quality Assurance:**
  - [x] Verified partner tests (`22 passed, 66 assertions`).
  - [x] Verified frontend asset bundling (`npm.cmd run build` succeeded in 4.70s).

---

## Phase 18: Strict Role Boundaries, Fleet Focus for Partners, Universal Header Dropdown & Customer Profile Card
- [x] **1. Strict Role Boundary & Scope Elimination for Partner Portal:**
  - [x] Tour Packages and Itineraries confirmed as strictly **Super Admin Platform-Curated** (`AdminTourController`, `AdminTourTest`).
  - [x] Decommissioned partner tour creation (`tours/create`, `POST tours`) from `routes/partner.php`.
  - [x] Removed `createTour` and `storeTour` from `PartnerProductController.php`.
  - [x] Removed orphan file `resources/js/Pages/Partner/Products/CreateTour.vue`.
  - [x] Replaced Partner sidebar group `Katalog Produk` (`Semua Produk`, `Paket Wisata`, `Armada Rental`) in `PartnerLayout.vue` with focused **`Armada & Transportasi`** (`Daftar Unit Armada`, `Tambah Unit Armada`).
  - [x] Updated `Partner/Products/Index.vue`: removed `+ Tambah Paket Tour` button and `Paket Tour` filter tab; refocused strictly on vehicle fleet management.
  - [x] Added translation keys `partner.section_fleet`, `partner.menu_fleet_list`, and `partner.menu_add_vehicle` to `lang/id.json` and `lang/en.json`.
- [x] **2. Universal Profile Avatar Dropdown Menu across All Layouts:**
  - [x] Implemented Profile Avatar Dropdown in `AppLayout.vue` (Customer / Marketplace Header) replacing raw text links (`Dashboard`, `Logout`, `Admin Panel`, `Portal Mitra`).
  - [x] Implemented Profile Avatar Dropdown in `AdminLayout.vue` (Super Admin Console Header) replacing static chip and separate button.
  - [x] Provided consistent user initials avatar circle, full name, role badge, navigation links, and styled POST logout.
  - [x] Added click-outside listener to automatically close popovers on all layouts.
- [x] **3. Customer Travel Hub Dashboard Profile & Personal Data Display:**
  - [x] Enhanced `UserDashboardController.php` to pass structured `userProfile` (name, email, phone, role, joined date, email verification status).
  - [x] Built dedicated **Kartu Data Diri & Profil Wisatawan** in `resources/js/Pages/Dashboard.vue`:
    - [x] Prominent rounded-2xl avatar with initials and online status indicator.
    - [x] Full Name, Email with verification status pill, WhatsApp/Phone number with +62 badge, and member since date.
    - [x] Quick action button linking to `/profile` for profile and password editing.
  - [x] Updated `ProfileUpdateRequest.php` and `UpdateProfileInformationForm.vue` to support updating WhatsApp / phone number.
- [x] **4. Quality Assurance:**
  - [x] Verified full test suite (`107 passed, 405 assertions`).
  - [x] Verified production frontend build (`npm.cmd run build` in 4.03s).

---

## Phase 19: Reusable Formatting Composable (`useFormat.js`) & Interactive Google Maps Itinerary (Option A)
- [x] **1. Centralized Reusable Composable (`resources/js/Composables/useFormat.js`):**
  - [x] Extracted `formatRupiah`, `getInitials`, `formatDate`, and `formatDuration` into a single source of truth.
  - [x] Refactored 26 Vue pages and 3 layouts (`AppLayout`, `PartnerLayout`, `AdminLayout`) to eliminate duplicate `formatRupiah` and `userInitials` implementations.
- [x] **2. Google Maps API Integration (`useGoogleMaps.js`):**
  - [x] Verified Google Maps API Key (`Maps JS v3`, `Geocoding`, `Directions`, `Places` all `200 OK`).
  - [x] Configured `VITE_GOOGLE_MAPS_API_KEY` in `.env` and `.env.example`.
  - [x] Created lazy async loader `resources/js/Composables/useGoogleMaps.js` with custom nature map styling (`MAP_STYLE_NATURE`).
- [x] **3. Interactive Itinerary Route Map — Option A (`ItineraryMap.vue` & `Tours/Show.vue`):**
  - [x] Built `resources/js/Components/ItineraryMap.vue` with day filter tabs (`Semua Hari`, `Hari 1`, `Hari 2`, ...), numbered circle pins per stop, dashed route polylines per day, and rich info windows (thumbnail photo, stop badge, detail link, "Buka di Google Maps").
  - [x] Implemented **Option A** split layout in `resources/js/Pages/Tours/Show.vue` (timeline on the left, sticky interactive map on the right) with two-way sync (click stop in timeline $\leftrightarrow$ pan/open info window on map; click marker on map $\leftrightarrow$ smooth scroll & highlight stop card).
  - [x] Added embedded destination map on `resources/js/Pages/Destinations/Show.vue`.
  - [x] Enriched `TourController::show` with `latitude` and `longitude` per itinerary stop and populated WGS84 coordinates in `DestinationSeeder.php`.
- [x] **4. Quality Assurance:**
  - [x] Seeded destination coordinates into PostgreSQL (`php artisan db:seed --class=DestinationSeeder`).
  - [x] Verified full test suite (`107 passed, 405 assertions`).
  - [x] Verified production frontend build (`npm.cmd run build` in 5.42s).

---

## Phase 20: Full Destination CRUD & Upload Fix, Role-Aware Profile Layout, Sticky Sidebar & Shared UI Composables
- [x] **1. Full CRUD & Image Upload Fix for Master Destinations (`AdminDestinationController.php` & `Admin/Destinations/Index.vue`):**
  - [x] Add `update(Request $request, string $id)` method with validation, multilingual auto-translation, photo replacement/addition, and coordinates update.
  - [x] Add `destroy(string $id)` method with safety check (forbid deletion if destination is actively bound to published tour itinerary stops; allow archive/deactivate instead).
  - [x] Add `toggleStatus(string $id)` for 1-click active/inactive toggling.
  - [x] Add routes in `routes/admin.php`:
    - `POST destinations/{id}` $\rightarrow$ `AdminDestinationController::update` (multipart form support)
    - `DELETE destinations/{id}` $\rightarrow$ `AdminDestinationController::destroy`
    - `PATCH destinations/{id}/toggle` $\rightarrow$ `AdminDestinationController::toggleStatus`
  - [x] Fix Image Upload & Broken Image Path:
    - [x] Created directory junction `public/storage` pointing to `storage/app/public`.
    - [x] Fixed `DestinationMedia::getMediaUrlAttribute` and `ProductMedia::getMediaUrlAttribute` to handle `/storage/` prefix properly without generating double `/storage/storage/` URLs.
    - [x] Ensure `FileSecurityService::validateAndStore` safely stores files and returns clean relative path.
  - [x] Modernize `Admin/Destinations/Index.vue`:
    - [x] Add Edit Modal with pre-filled fields (name, cluster, category, descriptions, tickets, operating hours, coordinates, existing media thumbnails, radio primary, delete checkboxes, and new file upload).
    - [x] Add Delete Confirmation Modal with safety warning.
    - [x] Add 1-click Toggle Status button (`🟢 Aktif` / `⚪ Nonaktif`).
    - [x] Add instant image preview for newly selected upload files.
- [x] **2. Sticky Anchored Sidebar Standards for Admin Console & Partner Portal:**
  - [x] Refactor `AdminLayout.vue` sidebar to be sticky (`md:sticky md:top-16 md:h-[calc(100vh-4rem)] md:overflow-y-auto`) so navigation remains permanently visible and stationary while main dashboard/table content scrolls.
  - [x] Apply the same sticky anchored standard to `PartnerLayout.vue`.
- [x] **3. Role-Aware Profile Page & Seamless Dashboard Integration:**
  - [x] Refactor `resources/js/Pages/Profile/Edit.vue` to dynamically adopt the user's role-based layout:
    - Admin $\rightarrow$ Wrapped in `AdminLayout.vue` (preserves sidebar, header, dark enterprise theme).
    - Partner $\rightarrow$ Wrapped in `PartnerLayout.vue` (preserves sidebar, header, verified partner theme).
    - Customer $\rightarrow$ Wrapped in `AppLayout.vue` (preserves marketplace navigation, footer, Beta Tudia theme).
  - [x] Removed disjointed vanilla Breeze `AuthenticatedLayout.vue` styling from user profile experience.
  - [x] Customer dashboard in `Dashboard.vue` provides seamless identity card and direct access to profile management without losing layout context.
- [x] **4. Additional Reusable Composables Extraction:**
  - [x] Created `resources/js/Composables/useStatusBadge.js` to standardize booking, payment, and approval status badge styles and labels across all dashboards.
  - [x] Created `resources/js/Composables/useClipboard.js` for clean copy-to-clipboard actions with animated feedback.
- [x] **5. Quality Assurance:**
  - [x] Verified destination update and delete via feature tests (`AdminCuratedTourAndDestinationTest`).
  - [x] Verified image upload and public retrieval via storage junction.
  - [x] Verified frontend build (`npm run build` in 4.85s) and full test suite (`109 passed, 417 assertions`).

---

## Phase 21: Customer Dashboard Bugfix, Dynamic Destination Clusters CRUD, Destination Map Polishing, Centralized Vehicle Pricing, Header/Sidebar Unification, Disk Space Cleanup & Design Consistency
- [x] **1. Customer Dashboard Login Bugfix (`UserDashboardController.php`):**
  - [x] Fix invalid enum reference `PaymentStatus::DOWN_PAYMENT_PENDING` $\rightarrow$ replace with `PaymentStatus::PAYMENT_REVIEW` on lines 45 & 56.
  - [x] Audit all enum references across `app/`, `database/`, `routes/`, `tests/`, and `useStatusBadge.js` to ensure zero invalid enum constants exist.
  - [x] Verify customer login and `/dashboard` metrics render without 500 error.
- [x] **2. Full CRUD for Dynamic Destination Clusters (`/admin/destination-clusters`):**
  - [x] Create migration for `destination_clusters` table (`id` UUID, `code` string unique, `name` JSONB translatable, `description` JSONB translatable, `province`, `is_active`, `sort_order`, `timestamps`).
  - [x] Create `App\Models\DestinationCluster` Eloquent model with HasTranslations, HasUuids, `activeOptions()`, `resolveLabel()`, and `destinations()` relation.
  - [x] Create `App\Casts\DestinationClusterCast` so `Destination::$cluster` remains backward-compatible with `->value` and `->label()` while supporting dynamic database clusters.
  - [x] Create `database/seeders/DestinationClusterSeeder.php` seeding the 5 core clusters + expansion zones (`BUKIT_LAWANG`, `BERASTAGI`, `TANGKAHAN`).
  - [x] Create `AdminDestinationClusterController.php` with `index`, `store`, `update`, `destroy`, `toggleStatus` methods and multilingual translation.
  - [x] Add routes in `routes/admin.php` for `admin.destination-clusters.*`.
  - [x] Build `Admin/DestinationClusters/Index.vue` with list, add modal, edit modal, delete protection (cannot delete if destinations are bound), and 1-click active toggle.
  - [x] Update `AdminLayout.vue` sidebar to include "Klaster Wilayah" navigation link.
  - [x] Connect `AdminDestinationController.php`, `DestinationController.php`, and `HomeController.php` to fetch cluster options dynamically from `DestinationCluster` table instead of hardcoded enum.
- [x] **3. Destination Single Location Map Mode (`ItineraryMap.vue` & `Destinations/Show.vue`):**
  - [x] Add `isSingleDestination` prop and auto-detect `itinerary.length === 1 && stops.length === 1` in `ItineraryMap.vue`.
  - [x] Completely remove the `Semua Hari | Hari 1` filter bar when viewing a single destination.
  - [x] Remove `HARI 1 • STOP 1` label from the map popup InfoWindow (display only clean destination name, cluster, photo, and detail links).
- [x] **4. Centralized Platform Vehicle Pricing (Admin Governed):**
  - [x] In `Partner/Products/CreateVehicle.vue`, update pricing field: Partner registers fleet details (brand, model, year, capacity, transmission, driver option, photos), while pricing shows standard platform tier info ("Harga distandarisasi terpusat oleh Admin Platform").
  - [x] In `Admin/Products/Show.vue` and `ProductModerationController.php`: Allow Admin to view, adjust, and assign the approved base price and DP percentage upon approving or managing partner vehicle listings.
  - [x] Ensure consistent pricing across all partners to prevent unfair price competition.
- [x] **5. Admin & Partner Header/Sidebar Refinements:**
  - [x] Move "Admin Console / Platform Operations Console" from `AdminLayout.vue` top header into the top of the Admin sidebar.
  - [x] In `PartnerLayout.vue` (and all user roles), refactor the identity/verification badge from horizontal single-row to stacked vertical layout (`flex-col gap-1.5`) so text like "Mitra Vendor / Status: Terverifikasi" is never clipped/truncated on narrower sidebars.
  - [x] Clean up Admin header to mirror modern minimalist aesthetics (Logo on left, Language Switcher & Profile Dropdown on right).
- [x] **6. Design System Token & Color Consistency:**
  - [x] Purge arbitrary ad-hoc colors (e.g. arbitrary purples, untokenized blues) in Admin and Partner consoles.
  - [x] Strictly use project CSS variable tokens (`bg-primary`, `bg-surface`, `border-border`, `text-text-main`, `text-text-muted`, `accent`) defined in `UI_UX_DESIGN_SYSTEM.md`.
- [x] **7. Destination Multiple Image Edit, Responsive Modal & Disk Cleanup:**
  - [x] Fix multiple image upload during Edit mode in `Admin/Destinations/Index.vue` and controller validation (`photos` array and multiple file append with individual preview removal).
  - [x] Make Destination Edit & Create modals compact and responsive: `max-w-2xl max-h-[88vh] flex flex-col`, internal scrollable body, and backdrop dismiss via `@click.self`.
  - [x] Enhance storage deletion in `AdminDestinationController::destroy` and media removal: ensure all physical files under `storage/app/public` are deleted from disk regardless of URL prefix format, freeing up server disk storage.
- [x] **8. Quality Assurance & Regression Suite:**
  - [x] Verify test suite passes with updated vehicle pricing policies, cluster CRUD, customer login, and destination media cleanup.
  - [x] Verify Vite build bundling.








