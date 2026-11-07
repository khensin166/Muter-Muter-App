# System Architecture & Technical Design — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Approved Post-Grooming
- **Architecture Style:** Modular Monolith (Laravel 11 + Inertia.js + Vue 3 + PostgreSQL)

---

## 1. Architectural Philosophy & Overview

The system is designed as a **Modular Monolith** in a single repository. It provides high developer productivity, straightforward transactional integrity, and zero distributed-system overhead for the MVP stage.

```mermaid
graph TD
    Client["Client Browser (Desktop & Mobile Responsive)"]
    
    subgraph Frontend["Vue 3 + Tailwind CSS (SPA experience via Inertia.js)"]
        UI_Cust["Customer UI Pages"]
        UI_Partner["Partner Portal Pages"]
        UI_Admin["Admin Operations Pages"]
    end
    
    subgraph Backend["Laravel 11 Modular Monolith"]
        Router["Web & Inertia Routing (web.php, partner.php, admin.php)"]
        Middleware["Auth & RBAC Middleware"]
        Controllers["Inertia Controllers"]
        
        subgraph DomainActions["Domain Actions & Services"]
            ActionBooking["BookingAction / StateMachine"]
            ActionInventory["InventoryHoldService"]
            ActionPayment["PaymentVerificationService"]
            ActionSettlement["SettlementService"]
            ServiceWA["WhatsAppService (Go WA Gateway Client)"]
        end
        
        Scheduler["Laravel Scheduler (Cron / console.php)"]
    end
    
    subgraph StorageLayer["Data & Persistence Layer"]
        Postgres[(PostgreSQL 15+ Database)]
        FileStore["Storage (Proofs & Media)"]
    end
    
    subgraph ExternalServices["External Ecosystem"]
        GoWA["Internal Go WhatsApp Gateway"]
    end

    Client --> Frontend
    Frontend <==>|Inertia JSON Protocol| Router
    Router --> Middleware --> Controllers
    Controllers --> DomainActions
    DomainActions --> Postgres
    DomainActions --> FileStore
    ServiceWA -->|HTTP REST| GoWA
    Scheduler -->|Every Minute Check| ActionInventory
    Scheduler -->|Hourly SLA Check| ActionBooking
```

---

## 2. Key Architectural Decisions

| Decision | Choice | Rationale |
| :--- | :--- | :--- |
| **Monolith vs Microservices** | Modular Monolith | Microservices introduce unnecessary network latency, distributed transaction complexity (SAGA), and high operational cost. Monolith gives ACID transactions out of the box. |
| **Inertia.js vs Pure REST API** | Inertia.js + Vue 3 | Eliminates the need to maintain duplicate API schemas and client-side routing. Controllers return Inertia views with typed props directly. |
| **Primary Keys** | UUID v4 | Prevents enumeration attacks on booking codes, partner documents, and invoice IDs. Safe for future sharding. |
| **Financial Representation** | `NUMERIC(14,2)` | Avoids floating-point precision loss in currency calculations and commission percentages. |
| **Background Processing** | Synchronous + Scheduler | No Redis or RabbitMQ required for MVP. Scheduler periodically evaluates hold expiration and SLA breaches. |
| **External Communication** | Go WhatsApp API | Modular service wrapper isolates HTTP communication with fallback logging for local test environments. |

---

## 3. Domain Boundaries & Module Structure

The application is structured into clearly separated business domains:

1. **Identity & Access Control Domain (`app/Models/User`, `app/Policies`):**
   - RBAC using Spatie Laravel-Permission or native policies.
   - Distinct roles: `Customer`, `Partner Owner`, `Partner Staff`, `Admin`, `Super Admin`.
2. **Partner Domain (`app/Models/Partner`, `app/Models/PartnerMember`):**
   - Partner profile, KYC documents verification, multi-staff relationship, payout bank credentials.
3. **Destination & Catalog Domain (`app/Models/Destination`, `app/Models/Product`):**
   - Destinations, categories, media, tour packages, vehicles, departures.
   - Catalog lifecycle (`DRAFT`, `PENDING_REVIEW`, `APPROVED`, `PUBLISHED`).
4. **Inventory & Availability Domain (`app/Models/InventoryHold`, `app/Models/VehicleAvailability`):**
   - Seat quota tracking, vehicle booking date collision detection, 24h temporary hold release.
5. **Booking & Order Domain (`app/Models/Booking`, `app/Models/BookingStatusHistory`):**
   - Independent state management, product JSON snapshotting, lead-time validation.
6. **Payment & Finance Domain (`app/Models/Payment`, `app/Models/Refund`, `app/Models/VendorSettlement`):**
   - Manual bank transfer verification, DP tracking, 10% commission calculation, completed trip payout ledger.
7. **Communication Domain (`app/Services/WhatsAppService`):**
   - Dispatches transactional WhatsApp alerts (booking approved, hold reminder, payment confirmed).

---

## 4. Concurrency & Transactional Safety

To eliminate race conditions, double bookings, and inventory overselling:
- **Database Transactions:** Every state transition involving Booking, Inventory, or Payment must run inside `DB::transaction(function () { ... })`.
- **Row-Level Locking:** Use `lockForUpdate()` during inventory hold acquisition to prevent simultaneous checkouts from reserving the same vehicle or oversubscribing open trip quotas.
- **Idempotency:** Payment verification actions must check current status before mutating state to prevent duplicate confirmations or double payouts.

---

## 5. Security & Compliance Architecture

- **CSRF Protection:** Native Laravel CSRF tokens validated on all mutation requests.
- **SQL Injection Immunity:** 100% Eloquent ORM & PDO prepared statements with strict PostgreSQL UUID v4 type enforcement; zero unparameterized raw SQL queries.
- **4-Layer File Upload Hardening (`FileSecurityService`):**
  1. *Magic Bytes & Binary Inspection:* Real MIME verification via `finfo`, strict rejection of `.svg`/`.php`/executable extensions, pixel dimension bounds (`<= 4096x4096`), and binary payload scanning against embedded `<?php` or `<script>` tags.
  2. *Image Re-Encoding & EXIF Stripping:* Re-renders uploaded raster images via PHP GD to destroy hidden EXIF metadata and polyglot webshell payloads.
  3. *UUID Filename Sanitization:* Original user filenames are discarded and replaced with cryptographic UUID v4 names.
  4. *Web Server Execution Lock (`Caddyfile`):* `/storage/*` is strictly isolated from `php_server` execution and served with `X-Content-Type-Options: nosniff`.
  - See full specification in [`docs/architecture/SECURITY_AND_UPLOAD_SPEC.md`](SECURITY_AND_UPLOAD_SPEC.md).
- **Structured Logging & Audit Trail:** Unified JSON logging (`daily` + Docker `stderr` stack) with automatic PII masking and immutable database `audit_logs`. See [`docs/architecture/LOGGING_AND_AUDIT_SPEC.md`](LOGGING_AND_AUDIT_SPEC.md).
- **Authorization Enforcement:** Every controller endpoint MUST execute `$this->authorize(...)` or RBAC policy validation.
- **Data Privacy:** Customer phone numbers and bank account numbers are only accessible to authorized Admins and assigned fulfillment Partners.

---

## 6. Multilingual Architecture & Localization (i18n)

- **Bilingual Core Support:** Native support for Bahasa Indonesia (`id` - default) and English (`en` - international tourists).
- **PostgreSQL JSONB Dynamic Content:** Translatable catalog attributes (`name`, `description`, `inclusions`, `exclusions`, `itinerary`) are persisted as `JSONB` objects (`{"id": "...", "en": "..."}`).
- **Performance Indexing:** Dual PostgreSQL indexes — B-Tree Expression indexes for fast locale sorting (`(name->>'en')`) and GIN Full-Text Search indexes for multilingual search.
- **Translate-on-Save Service:** Integrated `GoogleTranslationService` translates Indonesian inputs into English asynchronously, reducing runtime latency and external API cost.
- **Dynamic Partner/Admin Form UX:** Forms default to Indonesian with dynamic `ID` / `EN` toggle buttons and 1-click Auto-Translate triggers.
- See full specification in [`docs/architecture/LOCALIZATION_I18N_SPEC.md`](LOCALIZATION_I18N_SPEC.md).

