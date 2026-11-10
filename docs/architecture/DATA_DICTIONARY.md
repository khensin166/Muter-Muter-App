# Data Dictionary — Lake Toba Travel Marketplace

- **Document Version:** 1.2
- **Database Engine:** PostgreSQL 15+
- **Standard Convention:** UUID v4 primary keys (`id`), Timestamps with timezone (`TIMESTAMPTZ`), IDR Currency (`NUMERIC(14,2)`).

---

## 1. Core Domain Tables

### 1.1 `users`
Stores user credentials for all personas (Customers, Partner Staff, Platform Admins).

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `name` | `VARCHAR(255)` | No | - | Full legal or display name |
| `email` | `VARCHAR(255)` | No | - | Unique email address |
| `phone` | `VARCHAR(30)` | Yes | `NULL` | WhatsApp compatible phone number (E.164 e.g. 62812...) |
| `password` | `VARCHAR(255)` | No | - | Bcrypt hashed password |
| `role` | `VARCHAR(50)` | No | `'CUSTOMER'` | High-level role: `CUSTOMER`, `PARTNER`, `ADMIN`, `SUPER_ADMIN` |
| `email_verified_at` | `TIMESTAMPTZ` | Yes | `NULL` | Verification timestamp |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.2 `partners`
Stores registered vendor business entities (tour operators and vehicle rental providers).

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `business_name` | `VARCHAR(255)` | No | - | Official registered company name |
| `brand_name` | `VARCHAR(255)` | Yes | `NULL` | Commercial brand name shown to tourists |
| `pic_name` | `VARCHAR(255)` | No | - | Person in Charge / Owner full name |
| `pic_phone` | `VARCHAR(30)` | No | - | Primary WhatsApp phone for SLA alerts |
| `email` | `VARCHAR(255)` | No | - | Unique business contact email |
| `address` | `TEXT` | Yes | `NULL` | Physical business office address |
| `city` | `VARCHAR(100)` | No | `'Samosir'` | Operating base city/regency |
| `status` | `VARCHAR(50)` | No | `'PENDING'` | `PENDING`, `APPROVED`, `SUSPENDED`, `REJECTED` |
| `bank_name` | `VARCHAR(100)` | Yes | `NULL` | Payout destination bank (e.g. BCA, BRI, Mandiri) |
| `bank_account_number`| `VARCHAR(100)`| Yes | `NULL` | Payout bank account number |
| `bank_account_holder`| `VARCHAR(255)`| Yes | `NULL` | Beneficiary name matching bank record |
| `average_rating` | `NUMERIC(3,2)` | Yes | `NULL` | Aggregated star rating (1.00 to 5.00) |
| `total_reviews_count` | `INTEGER` | No | `0` | Cumulative count of customer reviews |
| `completed_trips_count` | `INTEGER` | No | `0` | Cumulative count of successfully completed trips |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.3 `destinations` & `destination_media`
Catalogs official tourism destinations across North Sumatra and their multimedia gallery.

| Table | Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- | :--- |
| `destinations` | `id` | `UUID` | No | Primary Key |
| `destinations` | `category_id` | `UUID` | No | FK references `destination_categories(id)` |
| `destinations` | `name` | `JSONB` | No | Bilingual object: `{"id": "...", "en": "..."}` |
| `destinations` | `slug` | `VARCHAR(255)` | No | URL safe slug |
| `destinations` | `cluster` | `VARCHAR(50)` | No | Cluster enum (`PARAPAT`, `SAMOSIR_EAST`, `SAMOSIR_WEST`, `MEREK`, `MEDAN`) |
| `destinations` | `description` | `JSONB` | Yes | Bilingual object: `{"id": "...", "en": "..."}` |
| `destinations` | `latitude` | `DECIMAL(10,8)`| Yes | Geographic coordinate latitude |
| `destinations` | `longitude` | `DECIMAL(11,8)`| Yes | Geographic coordinate longitude |
| `destinations` | `ticket_price_domestic` | `NUMERIC(14,2)` | Yes | Entrance ticket price for domestic visitors |
| `destinations` | `ticket_price_foreign` | `NUMERIC(14,2)` | Yes | Entrance ticket price for international visitors |
| `destinations` | `operating_hours` | `VARCHAR(100)` | Yes | Operating schedule (e.g. "08:00 - 18:00 WIB") |
| `destinations` | `is_featured` | `BOOLEAN` | No | Highlighted destination flag |
| `destination_media` | `id` | `UUID` | No | Primary Key |
| `destination_media` | `destination_id` | `UUID` | No | FK references `destinations(id)` |
| `destination_media` | `media_type` | `VARCHAR(50)` | No | `IMAGE`, `VIDEO` |
| `destination_media` | `media_url` | `VARCHAR(255)` | No | Storage or CDN URL |
| `destination_media` | `caption` | `VARCHAR(255)` | Yes | Optional caption |
| `destination_media` | `is_primary` | `BOOLEAN` | No | Primary thumbnail flag |
| `destination_media` | `sort_order` | `INTEGER` | No | Gallery sort sequence |

---

### 1.4 `products`
Root polymorphic catalog table for Tour Packages and Rental Vehicles.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `partner_id` | `UUID` | Yes | `NULL` | FK references `partners(id)`. Null for platform-owned items |
| `ownership_type` | `VARCHAR(50)` | No | `'PARTNER'` | `INTERNAL` (Platform official) or `PARTNER` (Vendor supplied) |
| `product_type` | `VARCHAR(50)` | No | - | `TOUR`, `VEHICLE`, `TRANSFER` |
| `name` | `JSONB` | No | - | Bilingual object: `{"id": "...", "en": "..."}` |
| `slug` | `VARCHAR(255)` | No | - | Unique product slug |
| `short_description`| `JSONB` | Yes | `NULL` | Bilingual short pitch |
| `full_description` | `JSONB` | Yes | `NULL` | Bilingual detailed overview |
| `base_price` | `NUMERIC(14,2)`| No | - | Published base selling price in IDR |
| `pricing_type` | `VARCHAR(50)` | No | `'PER_PERSON'` | `PER_PERSON`, `PER_VEHICLE`, `PER_GROUP` |
| `dp_percentage` | `NUMERIC(5,2)` | No | `30.00` | Mandatory DP percentage (Default 30.00%) |
| `status` | `VARCHAR(50)` | No | `'DRAFT'` | `DRAFT`, `PUBLISHED`, `ARCHIVED`, `SUSPENDED` |
| `approval_status` | `VARCHAR(50)` | No | `'DRAFT'` | `DRAFT`, `PENDING_REVIEW`, `APPROVED`, `REJECTED` |
| `min_lead_time_hours` | `INTEGER` | No | `24` | Cut-off advance booking window in hours |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Update timestamp |

---

### 1.5 `tour_itinerary_stops`
Detailed itinerary stops for tour packages with foreign key binding to destinations.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `tour_itinerary_day_id` | `UUID` | No | - | FK references `tour_itinerary_days(id)` ON DELETE CASCADE |
| `destination_id` | `UUID` | Yes | `NULL` | FK references `destinations(id)` ON DELETE SET NULL |
| `stop_order` | `INTEGER` | No | `1` | Order of stop within the itinerary day |
| `activity_title` | `VARCHAR(255)` | No | - | Name of activity or stop |
| `time_schedule` | `VARCHAR(50)` | Yes | `NULL` | Estimated schedule (e.g. `08:30 - 13:00`) |
| `description` | `TEXT` | Yes | `NULL` | Detailed description of the activity |

---

### 1.6 `bookings`
Core transactional table storing customer reservations and immutability snapshots.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `booking_code` | `VARCHAR(50)` | No | - | Unique public reference code (e.g. `BK-202611-0001`) |
| `user_id` | `UUID` | No | - | FK references `users(id)` (Customer) |
| `product_id` | `UUID` | No | - | FK references `products(id)` |
| `partner_id` | `UUID` | Yes | `NULL` | FK references `partners(id)` |
| `booking_status` | `VARCHAR(50)` | No | `'PENDING_APPROVAL'`| Contractual state machine |
| `payment_status` | `VARCHAR(50)` | No | `'UNPAID'` | Independent payment state machine |
| `inventory_status` | `VARCHAR(50)` | No | `'NONE'` | Independent inventory state machine |
| `service_start_date`| `DATE` | No | - | Trip or rental start date |
| `service_end_date` | `DATE` | No | - | Trip or rental end date |
| `service_start_time`| `TIME` | Yes | `NULL` | Pickup/departure time |
| `total_price` | `NUMERIC(14,2)`| No | - | Total agreed order price (IDR) |
| `dp_amount` | `NUMERIC(14,2)`| No | - | Mandatory DP required (e.g. 30% of total) |
| `total_paid` | `NUMERIC(14,2)`| No | `0.00` | Sum of all verified customer payments |
| `commission_percentage`| `NUMERIC(5,2)`| No | `10.00`| Platform commission rate snapshot (Default 10.00%) |
| `platform_commission_amount`| `NUMERIC(14,2)`| No | `0.00`| Platform commission earned |
| `partner_payout_amount`| `NUMERIC(14,2)`| No | `0.00`| Net payable to partner |
| `contact_unmasked` | `BOOLEAN` | No | `FALSE` | Toggle opening partner/driver contact to customer (H-12 hours) |
| `assigned_driver_name` | `VARCHAR(255)` | Yes | `NULL` | Assigned driver name for service fulfillment |
| `assigned_vehicle_plate`| `VARCHAR(50)` | Yes | `NULL` | Assigned licensed vehicle plate number |
| `product_snapshot` | `JSONB` | No | - | Complete snapshot of product, inclusions & terms |
| `pricing_breakdown`| `JSONB` | No | - | Itemized participants, unit price, and add-ons |
| `cancellation_policy_snapshot`| `JSONB` | No | - | Active cancellation refund tier schedule |
| `vendor_sla_deadline`| `TIMESTAMPTZ`| No | - | `NOW() + 24 HOURS` for vendor response |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.7 `inventory_holds`
Manages 24-hour temporary capacity holds post-vendor approval.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `booking_id` | `UUID` | No | - | FK references `bookings(id)` ON DELETE CASCADE |
| `product_id` | `UUID` | No | - | FK references `products(id)` |
| `tour_departure_id`| `UUID` | Yes | `NULL` | FK references `tour_departures(id)` |
| `vehicle_id` | `UUID` | Yes | `NULL` | FK references `vehicles(id)` |
| `held_quantity` | `INTEGER` | No | `1` | Number of seats or vehicle units |
| `held_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Timestamp when hold initiated |
| `expires_at` | `TIMESTAMPTZ` | No | - | Expiration timestamp (`held_at + 24 HOURS`) |
| `status` | `VARCHAR(50)` | No | `'HELD'` | `HELD`, `ALLOCATED`, `RELEASED` |

---

### 1.8 `payments` & `payment_proofs`
Tracks payment receipts, manual transfer proofs, and administrative verification.

| Table | Column | Type | Nullable | Description |
| :--- | :--- | :--- | :--- | :--- |
| `payments` | `id` | `UUID` | No | Primary Key |
| `payments` | `booking_id` | `UUID` | No | FK references `bookings(id)` |
| `payments` | `payment_code` | `VARCHAR(50)` | No | Unique payment ref (`PAY-202611-0001`) |
| `payments` | `payment_type` | `VARCHAR(50)` | No | `DOWN_PAYMENT`, `BALANCE_SETTLEMENT`, `FULL_PAYMENT` |
| `payments` | `amount` | `NUMERIC(14,2)`| No | Billed amount in IDR |
| `payments` | `status` | `VARCHAR(50)` | No | `AWAITING_PAYMENT`, `PAYMENT_REVIEW`, `VERIFIED`, `REJECTED` |
| `payment_proofs`| `id` | `UUID` | No | Primary Key |
| `payment_proofs`| `payment_id` | `UUID` | No | FK references `payments(id)` |
| `payment_proofs`| `file_path` | `VARCHAR(255)` | No | Storage path of receipt image/PDF |
| `payment_proofs`| `bank_sender_name` | `VARCHAR(100)` | Yes | Customer originating bank |
| `payment_proofs`| `verified_by` | `UUID` | Yes | FK references `users(id)` (Admin reviewer) |
| `payment_proofs`| `verified_at` | `TIMESTAMPTZ` | Yes | Verification timestamp |

---

### 1.9 `vendor_settlements`
Financial ledger for post-service disbursement to vendors.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `partner_id` | `UUID` | No | - | FK references `partners(id)` |
| `booking_id` | `UUID` | No | - | FK references `bookings(id)` |
| `gross_amount` | `NUMERIC(14,2)`| No | - | Total verified customer payment |
| `commission_amount`| `NUMERIC(14,2)`| No | - | Platform commission retained (baseline 10%, configurable by terms) |
| `net_payout_amount` | `NUMERIC(14,2)`| No | - | Net payable to partner |
| `status` | `VARCHAR(50)` | No | `'PENDING'` | `PENDING`, `ELIGIBLE`, `PROCESSING`, `PAID`, `ON_HOLD` |
| `bank_reference` | `VARCHAR(100)` | Yes | `NULL` | Bank transfer reference code |
| `eligible_at` | `TIMESTAMPTZ` | Yes | `NULL` | Set when booking reaches `COMPLETED` |
| `paid_at` | `TIMESTAMPTZ` | Yes | `NULL` | Set when Admin executes payout |

---

### 1.10 `reviews`
Stores traveler ratings and feedback comments post-trip completion.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `booking_id` | `UUID` | No | - | Unique FK references `bookings(id)` ON DELETE CASCADE |
| `user_id` | `UUID` | No | - | FK references `users(id)` (Customer reviewer) |
| `partner_id` | `UUID` | No | - | FK references `partners(id)` (Fulfilling operator) |
| `product_id` | `UUID` | No | - | FK references `products(id)` (Tour or vehicle) |
| `rating` | `INTEGER` | No | - | Overall score: 1 to 5 Stars |
| `driver_rating` | `INTEGER` | Yes | `NULL` | Driver punctuality & hospitality: 1 to 5 Stars |
| `vehicle_rating`| `INTEGER` | Yes | `NULL` | Vehicle cleanliness & comfort: 1 to 5 Stars |
| `comment` | `TEXT` | Yes | `NULL` | Traveler feedback comment |
| `is_published` | `BOOLEAN` | No | `TRUE` | Public visibility toggle |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

## 2. High-Performance Index Registry

| Table | Index Name | Columns | Index Type | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `partners` | `partners_status_rating_idx` | `(status, average_rating)` | B-Tree | Fast lookup for top-rated active partners |
| `partners` | `partners_completed_trips_idx` | `(completed_trips_count)` | B-Tree | Partner reliability sorting |
| `tour_itinerary_stops` | `itinerary_stops_dest_id_idx` | `(destination_id)` | B-Tree | Destination gallery photo aggregation |
| `tour_itinerary_stops` | `itinerary_stops_day_order_idx` | `(tour_itinerary_day_id, stop_order)` | B-Tree | Chronological day itinerary display |
| `destination_media` | `dest_media_lookup_idx` | `(destination_id, is_primary, sort_order)` | B-Tree | Fast destination thumbnail & gallery retrieval |
| `destinations` | `destinations_cluster_featured_idx` | `(cluster, is_featured)` | B-Tree | Regional cluster landing page filtering |
| `products` | `products_ownership_lookup_idx` | `(ownership_type, status, approval_status)` | B-Tree | Internal platform tours vs partner products filter |
| `bookings` | `bookings_completion_cron_idx` | `(booking_status, service_end_date)` | B-Tree | Daily midnight trip completion cron execution |
| `bookings` | `bookings_unmasking_cron_idx` | `(booking_status, service_start_date, contact_unmasked)` | B-Tree | Hourly driver contact unmasking cron execution |
| `inventory_holds` | `holds_vehicle_concurrency_idx` | `(vehicle_id, status, expires_at)` | B-Tree | Fleet availability locking & expiration cron |
| `inventory_holds` | `holds_tour_concurrency_idx` | `(tour_departure_id, status, expires_at)` | B-Tree | Departure seat quota locking & expiration cron |
| `destinations` | `destinations_name_translations_gin` | `name` | GIN | Bilingual JSONB name searching (`id`/`en`) |
| `products` | `products_name_translations_gin` | `name` | GIN | Bilingual JSONB product searching (`id`/`en`) |
