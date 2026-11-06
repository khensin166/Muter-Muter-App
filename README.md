# Lake Toba Travel Marketplace (Muter-Muter App)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.x-green.svg)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-1.x-purple.svg)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-3.x-blue.svg)](https://tailwindcss.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-blue.svg)](https://www.postgresql.org)

An integrated travel marketplace and trip management platform connecting tourists, local tour operators, and vehicle rental providers in the Lake Toba and North Sumatra region.

> **Product Vision:** *"One platform to discover, plan, and experience North Sumatra."*

---

## 🗺️ Geographic Scope (MVP)
Initial market focus encompasses the Lake Toba clusters:
1. **Parapat** (Gateway & Lake Harbor)
2. **Samosir East** (Tomok, Ambarita, Tuktuk)
3. **Samosir West** (Pangururan and surrounding natural landscapes)
4. **Merek / Tongging** (Sipiso-piso Waterfall and highland viewpoints)
*Note: Medan serves as the primary arrival and pickup hub.*

---

## 🚀 Core Features & Architecture Highlights
- **Hybrid Marketplace:** Supports platform-managed products (`INTERNAL`) and verified partner products (`PARTNER`).
- **Catalog Modules:** Destination discovery, Tour Packages (One-day, Multi-day, Open Trip, Private Trip), and Vehicle Rentals (Cars, Minibuses, Boats).
- **Semi-Automated Booking Flow:** Customer web booking request $\rightarrow$ Partner availability verification (24h SLA) $\rightarrow$ Down Payment (DP) issuance & temporary 24h inventory hold $\rightarrow$ Go WhatsApp automated notification $\rightarrow$ Bank transfer proof upload & Admin verification.
- **Robust Financial Model:** Independent state lifecycles for Booking, Payment, and Inventory; snapshotting of prices, policies, and commissions (default 10%); post-completion partner settlements.
- **Modern Monolith:** Single repository powered by Laravel 11 and Vue 3 + Inertia.js with PostgreSQL backend.

---

## 📚 Project Documentation
All project specifications, architecture blueprints, and planning documents are maintained inside `docs/`:

### Product Specifications
- [Product Requirements Document (PRD)](file:///docs/product/PRD.md)
- [Business Rules & Policy Engine](file:///docs/product/BUSINESS_RULES.md)
- [User Stories & Acceptance Criteria](file:///docs/product/USER_STORIES.md)
- [End-to-End User Flows](file:///docs/product/USER_FLOWS.md)
- [Open Questions & Future Scope](file:///docs/product/OPEN_QUESTIONS.md)
- [Grooming Summary Notes](file:///GROOMING_NOTES.md)

### Technical Architecture & Design
- [System Architecture](file:///docs/architecture/SYSTEM_ARCHITECTURE.md)
- [Database ERD & Relationships](file:///docs/architecture/DATABASE_ERD.md)
- [Data Dictionary](file:///docs/architecture/DATA_DICTIONARY.md)
- [Design System & Color Variables](file:///docs/design/DESIGN_SYSTEM.md)

### Development & Execution
- [Implementation Plan](file:///docs/development/IMPLEMENTATION_PLAN.md)
- [Granular Task Checklist](file:///docs/development/TASKS.md)
- [AI Assistant Developer Guidelines](file:///AGENTS.md)

---

## 🛠️ Tech Stack & Prerequisites
- **PHP:** 8.2 or higher
- **Composer:** 2.x
- **Node.js:** 20.x or higher & npm
- **Database:** PostgreSQL 15+
- **CSS Framework:** Tailwind CSS 3.x

---

## 📄 License
Proprietary — Internal product for Lake Toba Travel Marketplace.

