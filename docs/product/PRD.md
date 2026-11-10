# Product Requirements Document (PRD) — Muter-Muter Travel Marketplace

- **Document Version:** 1.1
- **Status:** Approved Architecture & Scope Alignment
- **Product Type:** Regional Travel Marketplace & Trip Management Platform
- **Initial Target Market:** North Sumatra (Sumatera Utara), Indonesia — covering Lake Toba, Bukit Lawang, Tangkahan, Berastagi, and Medan Hub

---

## 1. Executive Summary & Vision

### 1.1 Product Vision
> *"One platform to discover, plan, and experience North Sumatra."*

**Muter-Muter App** is an integrated regional tourism marketplace connecting travelers, local tour operators, and vehicle rental providers across North Sumatra. Rather than being restricted to a single attraction, Muter-Muter encompasses the entire North Sumatra travel corridor.

**Danau Toba (Lake Toba)** is positioned as one of the flagship destination clusters alongside **Bukit Lawang** (orangutan jungle trekking), **Tangkahan** (elephant sanctuary & river tubing), **Berastagi** (volcanoes & highlands), and **Medan** (cultural & transit hub).

### 1.2 Core Objectives
1. **Discoverability:** Provide comprehensive, curated listings across major North Sumatra travel corridors.
2. **Integrated Commerce:** Offer direct booking for Tour Packages and Vehicle Rentals under a unified platform.
3. **Local Partner Empowerment:** Provide local operators (tour guides, transport providers) with digital storefronts, availability management, and transparent financial settlement.
4. **Reliable Fulfillment:** Ensure booking confirmations, inventory holds, and customer notifications are clear, predictable, and supported by local customer service.
5. **Brand Flexibility & Scalability:** Establish a scalable, regional brand identity that can seamlessly onboard partners across all regencies of North Sumatra.

---

## 2. Target Market & User Personas

### 2.1 Target Personas
- **Primary MVP Persona:** Domestic tourists from outside North Sumatra (Jakarta, Java, Riau, Batam), typically traveling in small private groups of 2–6 people (couples, families, close friend groups) seeking seamless logistical coordination (transport + curated stops across North Sumatra).
- **Secondary Persona:** Local North Sumatran weekenders seeking spontaneous vehicle rentals or 1-day weekend open trips.
- **International Eco-Tourists:** Backpackers and wildlife enthusiasts seeking certified guides for Bukit Lawang jungle treks and Tangkahan elephant experiences.
- **Supply-Side Personas:**
  - *Local Tour Operator (Partner):* Small-to-medium operators offering guided excursions and cultural experiences.
  - *Transport Provider (Partner):* Car rental owners, minibus drivers, and private transfer operators.

### 2.2 Geographic Clusters
1. **Danau Toba Caldera (Parapat, Samosir, Merek/Tongging):** Volcanic lake, Batak cultural heritage, waterfalls, and scenic viewpoints.
2. **Bukit Lawang & Gunung Leuser (Langkat):** Tropical rainforest exploration, wild orangutan trekking, and river activities.
3. **Tangkahan Eco-Tourism (Langkat):** Hidden jungle paradise, elephant washing & patrol, and natural hot springs.
4. **Berastagi & Karo Highlands:** Highland agriculture, fruit markets, Mount Sibayak, and Mount Sinabung views.
5. **Medan City Hub:** Primary arrival and departure center (Kualanamu International Airport, railway station, heritage landmarks, and culinary tourism).

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
- **Destination Photo & Media Auto-Binding:** Every itinerary stop references a master `Destination`. The tour package detail view dynamically aggregates photos from all visited destination stops into an auto-generated visual route showcase.
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

### 3.6 Partner Management Portal (Fleet & Driver Fulfillment)
- Self-registration with business documentation upload (KTP, NIB/Business Permit, Unit Photos).
- Multi-staff access: **Partner Owner** (business profile, payout bank info, staff management, fleet CRUD, settlement ledgers) and **Partner Staff / Driver** (daily dispatch schedule, traveler manifest, pickup times; zero financial/bank access).
- **Fleet & Driver Availability:** Partners register vehicle units (Avanza, Innova Reborn, HiAce, Alphard) and driver availability. Partners are freed from complex marketing copywriting, focusing on reliable transport execution.
- **Dispatch Fulfillment:** 1-click confirmation for dispatched tour bookings and vehicle requests within 24-hour SLA.
- Performance visibility: lifetime completed trips count, average star rating, and customer reviews.

### 3.7 Administration & Operations Portal
- **Enterprise-Grade UI/UX:** Styled to modern Tier-1 SaaS standards (Stripe/Linear/Airbnb style) with semantic SVG icons, crisp surface elevation, and zero developer-prototype emojis.
- **Master Content & Catalog Dashboard:** Overview of Master Destinations per cluster, Curated Tour Packages status, and ready-to-dispatch partner vehicle fleet with 1-click shortcut actions (`+ Buat Paket Wisata`, `+ Tambah Destinasi`).
- **Platform-Curated Tour Creator:** Super Admin directly designs, publishes, and maintains standardized Master Tour Packages (e.g., *6D5N Famtrip North Sumatra*, *1D Keliling Samosir*, *Open Trip Danau Toba*) with multi-day itineraries, North Sumatra preset inclusions (BBM PP, Driver & Guide, Makan & Uang Rokok Supir, Foto/Video/Drone, Tiket Ferry), and destination auto-photo binding.
- **Order Dispatching Engine:** Interface to assign incoming customer bookings to verified Partners based on vehicle capacity, location cluster, and partner reputation.
- **Partner KYC & Finance:** Partner document verification, manual bank transfer verification, centralized booking oversight with SLA timeouts, commission tracking (baseline 10% snapshot), and partner settlement ledger.

### 3.8 Partner Performance & Customer Review Engine
- Post-completion traveler reviews: 1 to 5 star overall rating, sub-scores for vehicle comfort and driver hospitality/safety, and optional feedback comment.
- Real-time aggregation of `average_rating` and `completed_trips_count` per partner.
- Metrics surfaced to Admin for dispatch decision-making and displayed as transparency badges on public package cards and checkout.

### 3.9 Anti-Disintermediation & Ecosystem Protection Engine
- **Strategic Win-Win Approach:** Eliminates off-platform leakage ("transaksi jalur belakang") not through hostility, but by locking in superior mutual value for both travelers and partner drivers:
  - **Pilar 1 (Mitra Diuntungkan):** Lifetime `completed_trips_count` and verified star ratings (`average_rating`) exist exclusively on-platform. High-rated operators receive Priority Dispatch on high-margin bookings (e.g. 6D5N Famtrip, international private tours). Guaranteed compensation on late customer cancellations (< 3 days).
  - **Pilar 2 (Wisatawan Diuntungkan & Tenang):** Emergency Vehicle Replacement SLA guarantees replacement car and driver within 1–2 hours in case of mechanical breakdown on challenging mountain/caldera roads (Tele, Sibea-bea, Tongging, Bukit Lawang). Includes bundled scheduled Lake Toba ferry tickets, transparent invoices, driver meals & cigarettes, and **Clean Vehicle Standard (kabin wangi, AC dingin, bebas asap rokok)**.
  - **Pilar 3 (Masked & Timed Contact Release):** Direct driver/customer phone numbers are strictly masked during booking and checkout; released automatically at **H-1 before departure** via WhatsApp E-Voucher for flight arrival sync at Kualanamu (KNO) / Silangit (DTB).
  - **Pilar 4 (Fair 10% Commission & Partner Code of Conduct):** Highly competitive, friendly take-rate (90% partner net, 10% platform), with partner integrity pact prohibiting off-platform solicitation.
- **Reference Standard Packages:** Standardized catalog templates covering flagship North Sumatra corridors (6D5N Famtrip KNO - Bukit Lawang - Medan - Parapat - Samosir and 1D Danau Toba & Samosir Open/Private Trips).

---

## 4. MVP Priority Matrix (MoSCoW)

### Must Have (Phase 1 MVP)
- Public destination catalog, tour package catalog, and vehicle catalog.
- User authentication and Two-Tier RBAC (`SUPER_ADMIN`, `PARTNER [OWNER, STAFF]`, `CUSTOMER`).
- Platform-curated tour creator for Super Admin with multi-day itineraries & North Sumatra preset inclusions (BBM PP, Driver/Guide, Makan & Rokok Supir, Foto/Drone, Tiket Ferry).
- Partner fleet management (vehicle units, capacities, driver assignment).
- Booking dispatching engine for Super Admin to assign tour orders to local partners based on capacity, cluster, and ratings.
- Partner performance metrics: lifetime completed trip history counter and customer review/rating engine (1-5 stars).
- Anti-Disintermediation Engine: Masked contact release at H-1 departure and Emergency Vehicle Replacement SLA protocol.
- Semi-automated booking flow with 24h partner SLA and admin manual fallback.
- 24-Hour temporary inventory hold upon approval.
- Down payment (DP) calculation (default 30%, admin configurable) and manual bank transfer proof verification.
- Automated WhatsApp notifications via Go WA Gateway.
- Partner onboarding, document upload, and admin approval.
- Cancellation and refund policy engine (>7d 100%, 3-7d 50%, <3d 0%).
- Configurable platform commission snapshot (baseline 10%) and vendor settlement ledger upon trip completion.
- Bilingual localization (ID & EN) for all public, checkout, dashboard, and portal views.

### Should Have (Phase 1.5)
- Customer wishlist and saved favorites.
- Interactive Leaflet/Mapbox destination maps.
- Promo codes and discount vouchers.
- Automated payment gateway integration (Midtrans/Xendit).

### Could Have (Future Phases)
- AI-driven itinerary generator.
- Multi-product single-cart checkout.
- Multi-currency support for international travelers.
- Direct live API integration with regional ferry operators.

