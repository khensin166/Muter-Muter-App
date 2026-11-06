# Product Requirements Document (PRD) — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Approved Post-Grooming
- **Product Type:** Travel Marketplace & Trip Management Platform
- **Initial Target Market:** Lake Toba and surrounding regions, North Sumatra, Indonesia

---

## 1. Executive Summary & Vision

### 1.1 Product Vision
> *"One platform to discover, plan, and experience North Sumatra."*

The Lake Toba Travel Marketplace is an integrated digital ecosystem designed to connect travelers, local tourism operators, and vehicle rental providers. It empowers tourists to discover regional attractions, book curated tour packages, rent vehicles with or without drivers, and consult on custom itineraries with transparent pricing and centralized booking management.

### 1.2 Core Objectives
1. **Discoverability:** Provide comprehensive, curated listings of destinations in the Lake Toba region.
2. **Integrated Commerce:** Offer direct booking for Tour Packages and Vehicle Rentals under a unified platform.
3. **Local Partner Empowerment:** Provide local operators with digital storefronts, availability management, and transparent financial settlement.
4. **Reliable Fulfillment:** Ensure booking confirmations, inventory holds, and customer notifications are clear, predictable, and supported by local customer service.
5. **Extensibility:** Build a scalable modular architecture that can seamlessly expand to other regions in North Sumatra (e.g., Berastagi, Bukit Lawang, Nias).

---

## 2. Target Market & User Personas

### 2.1 Target Personas
- **Primary MVP Persona:** Domestic tourists from outside North Sumatra (Jakarta, Java, Riau, Batam), typically traveling in small private groups of 2–6 people (couples, families, close friend groups) seeking seamless logistical coordination (transport + curated stops).
- **Secondary Persona:** Local North Sumatran weekenders seeking spontaneous vehicle rentals or 1-day weekend open trips.
- **Supply-Side Personas:**
  - *Local Tour Operator (Partner):* Small-to-medium operators offering guided excursions and cultural experiences.
  - *Transport Provider (Partner):* Car rental owners, minibus drivers, and private transfer operators.

### 2.2 Geographic Clusters (MVP)
1. **Parapat:** Main mainland harbor and commercial gateway to Lake Toba.
2. **Samosir East:** Core cultural tourism strip (Tomok, Ambarita, Tuktuk).
3. **Samosir West:** Scenic landscapes and hot springs (Pangururan, Tele viewpoint).
4. **Merek / Tongging:** Northern Lake Toba highland attractions (Sipiso-piso Waterfall, Taman Simalem area).
5. **Medan Hub:** Primary arrival/departure transit center (Kualanamu Airport, Medan Train Station).

---

## 3. Product Modules & Functional Scope

### 3.1 Destination Discovery
- Curated destination directory with categories (Nature, Culture, Culinary, Viewpoint, Water Activities).
- Detailed destination profiles: descriptions, high-resolution media, operating hours, ticket price guides, map locations, and nearby attractions.
- Public search, keyword filtering, and category browsing without mandatory registration.

### 3.2 Tour Packages Catalog
- **Tour Types:**
  - *One Day Trip:* Short excursions covering single clusters.
  - *Multi-Day Trip:* 2D1N to 4D3N comprehensive itineraries.
  - *Open Trip:* Scheduled group departures with seat-based quotas.
  - *Private Trip:* Custom-booked private groups.
- **Package Details:** Overview, daily itinerary with stops, inclusions & exclusions, minimum & maximum participants, departure points, cancellation policy snapshot, and transparent price calculation.
- **Lake Ferry Handling:** Lake crossing ferry tickets/services are bundled as part of package inclusions and itineraries, not sold as independent unbundled ferry tickets.

### 3.3 Transportation & Vehicle Rentals
- **Vehicle Types:** Standard passenger cars (Avanza, Innova), Minibuses (HiAce, Elf), Tourist Buses, and Private Airport Transfer services.
- **Vehicle Specs:** Brand, model, year, passenger capacity, luggage capacity, transmission type, with/without driver option, and operational coverage area.
- Availability calendar and conflict prevention based on rental date/time.

### 3.4 Custom Trip Planner
- Interactive inquiry module where travelers select desired destinations, dates, participant count, and transportation preferences.
- Generates a structured quotation and trip inquiry routed to platform operators for bespoke itinerary consultation via WhatsApp.

### 3.5 Booking & Order Management (Semi-Automated MVP)
- Customer initiates booking on web (authenticated or prompts login at checkout).
- 24-Hour SLA for Partner availability verification.
- Partner confirmation triggers temporary 24-hour inventory hold and issues Down Payment (DP) bill.
- Automated WhatsApp notification dispatched via internal Go WhatsApp API Gateway.
- Customer uploads bank transfer proof on the web portal (or confirms with admin via WhatsApp).
- Admin verifies payment and promotes booking to `CONFIRMED`.
- Independent status lifecycles for Booking, Payment, and Inventory.

### 3.6 Partner Management Portal
- Self-registration with business documentation upload (KTP, NIB/Business Permit, Unit Photos).
- Multi-staff access: **Partner Owner** (business profile, payout bank info, staff management, catalog) and **Partner Staff** (availability verification, daily dispatch view).
- Product management with mandatory Admin review before publishing.
- Booking verification queue, operational schedule calendar, and settlement history.

### 3.7 Administration & Operations Portal
- Partner verification and document review.
- Product catalog moderation and pricing approvals.
- Centralized booking oversight with manual intervention queues for SLA timeouts.
- Manual bank transfer verification and refund processing.
- Commission tracking (default 10%) and partner payout settlement ledger.

---

## 4. MVP Priority Matrix (MoSCoW)

### Must Have (Phase 1 MVP)
- Public destination catalog, tour package catalog, and vehicle catalog.
- User authentication (Customer, Partner Owner/Staff, Admin).
- Semi-automated booking flow with 24h partner SLA and admin manual fallback.
- 24-Hour temporary inventory hold upon approval.
- Down payment (DP) calculation (default 30%, admin configurable) and manual bank transfer proof verification.
- Automated WhatsApp notifications via Go WA Gateway.
- Partner onboarding, document upload, and admin approval.
- Cancellation and refund policy engine (>7d 100%, 3-7d 50%, <3d 0%).
- Platform commission snapshot (default 10%) and vendor settlement ledger upon trip completion.

### Should Have (Phase 1.5)
- Customer reviews and ratings post-completion.
- Customer wishlist and saved favorites.
- Interactive Leaflet/Mapbox destination maps.
- Promo codes and discount vouchers.
- Automated payment gateway integration (Midtrans/Xendit).

### Could Have (Future Phases)
- AI-driven itinerary generator.
- Multi-product single-cart checkout.
- Multi-currency and multi-language support for international travelers.
- Direct live API integration with regional ferry operators.

