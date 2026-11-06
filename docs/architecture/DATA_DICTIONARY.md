# Data Dictionary — Lake Toba Travel Marketplace

- **Document Version:** 1.0
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
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.3 `partner_members`
Implements multi-staff support per partner organization.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `partner_id` | `UUID` | No | - | FK references `partners(id)` ON DELETE CASCADE |
| `user_id` | `UUID` | No | - | FK references `users(id)` ON DELETE CASCADE |
| `role` | `VARCHAR(50)` | No | `'STAFF'` | `OWNER` (full access) or `STAFF` (operational only) |
| `is_active` | `BOOLEAN` | No | `TRUE` | Staff active status flag |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.4 `products`
Master catalog table unifying Tour Packages, Vehicle Rentals, and Transfers.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `partner_id` | `UUID` | Yes | `NULL` | FK references `partners(id)`. NULL for `INTERNAL` |
| `ownership_type` | `VARCHAR(50)` | No | `'PARTNER'` | `INTERNAL` or `PARTNER` |
| `product_type` | `VARCHAR(50)` | No | - | `TOUR`, `VEHICLE`, `TRANSFER` |
| `name` | `VARCHAR(255)` | No | - | Public listing title |
| `slug` | `VARCHAR(255)` | No | - | URL-friendly unique slug |
| `short_description` | `TEXT` | Yes | `NULL` | Excerpt for search cards |
| `full_description` | `TEXT` | Yes | `NULL` | Detailed description and terms |
| `base_price` | `NUMERIC(14,2)`| No | - | Base price in IDR |
| `pricing_type` | `VARCHAR(50)` | No | `'PER_PERSON'` | `PER_PERSON`, `PER_VEHICLE`, `PER_GROUP` |
| `dp_percentage` | `NUMERIC(5,2)` | No | `30.00` | Down payment percentage (Default 30.00%) |
| `min_lead_time_hours`| `INTEGER` | No | `24` | Lead time in hours (Tour: 24h, Vehicle: 12h) |
| `status` | `VARCHAR(50)` | No | `'DRAFT'` | `DRAFT`, `PUBLISHED`, `ARCHIVED`, `SUSPENDED` |
| `approval_status` | `VARCHAR(50)` | No | `'DRAFT'` | `DRAFT`, `PENDING_REVIEW`, `APPROVED`, `REJECTED` |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Update timestamp |

---

### 1.5 `bookings`
Core transactional table storing customer reservations and immutability snapshots.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `booking_code` | `VARCHAR(50)` | No | - | Unique public reference code (e.g. `BK-202611-0001`) |
| `user_id` | `UUID` | No | - | FK references `users(id)` (Customer) |
| `product_id` | `UUID` | No | - | FK references `products(id)` |
| `partner_id` | `UUID` | Yes | `NULL` | FK references `partners(id)` |
| `booking_status` | `VARCHAR(50)` | No | `'PENDING_APPROVAL'`| Contractual state: `PENDING_APPROVAL`, `MANUAL_REVIEW`, `AWAITING_PAYMENT`, `PAYMENT_REVIEW`, `CONFIRMED`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`, `EXPIRED`, `REFUND_PENDING`, `REFUNDED` |
| `payment_status` | `VARCHAR(50)` | No | `'UNPAID'` | `UNPAID`, `PARTIALLY_PAID`, `PAYMENT_REVIEW`, `PAID`, `OVERDUE`, `REFUND_PENDING`, `REFUNDED` |
| `inventory_status` | `VARCHAR(50)` | No | `'NONE'` | `NONE`, `HELD`, `ALLOCATED`, `RELEASED` |
| `service_start_date`| `DATE` | No | - | Trip or rental start date |
| `service_end_date` | `DATE` | No | - | Trip or rental end date |
| `service_start_time`| `TIME` | Yes | `NULL` | Pickup/departure time |
| `total_price` | `NUMERIC(14,2)`| No | - | Total agreed order price (IDR) |
| `dp_amount` | `NUMERIC(14,2)`| No | - | Mandatory DP required (e.g. 30% of total) |
| `total_paid` | `NUMERIC(14,2)`| No | `0.00` | Sum of all verified customer payments |
| `commission_percentage`| `NUMERIC(5,2)`| No | `10.00`| Platform commission rate snapshot (Default 10.00%) |
| `platform_commission_amount`| `NUMERIC(14,2)`| No | `0.00`| Platform commission earned |
| `partner_payout_amount`| `NUMERIC(14,2)`| No | `0.00`| Net payable to partner |
| `product_snapshot` | `JSONB` | No | - | Complete snapshot of product, inclusions & terms |
| `pricing_breakdown`| `JSONB` | No | - | Itemized participants, unit price, and add-ons |
| `cancellation_policy_snapshot`| `JSONB` | No | - | Active cancellation refund tier schedule |
| `vendor_sla_deadline`| `TIMESTAMPTZ`| No | - | `NOW() + 24 HOURS` for vendor response |
| `created_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record creation timestamp |
| `updated_at` | `TIMESTAMPTZ` | No | `CURRENT_TIMESTAMP` | Record update timestamp |

---

### 1.6 `inventory_holds`
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
| `status` | `VARCHAR(50)` | No | `'ACTIVE'` | `ACTIVE`, `RELEASED`, `CONVERTED` |

---

### 1.7 `payments` & `payment_proofs`
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

### 1.8 `vendor_settlements`
Financial ledger for post-service disbursement to vendors.

| Column | Type | Nullable | Default | Description / Constraints |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `UUID` | No | `gen_random_uuid()` | Primary Key |
| `partner_id` | `UUID` | No | - | FK references `partners(id)` |
| `booking_id` | `UUID` | No | - | FK references `bookings(id)` |
| `gross_amount` | `NUMERIC(14,2)`| No | - | Total verified customer payment |
| `commission_amount`| `NUMERIC(14,2)`| No | - | Platform 10% commission retained |
| `net_payout_amount` | `NUMERIC(14,2)`| No | - | Net payable to partner |
| `status` | `VARCHAR(50)` | No | `'PENDING'` | `PENDING`, `ELIGIBLE`, `PROCESSING`, `PAID`, `ON_HOLD` |
| `bank_reference` | `VARCHAR(100)` | Yes | `NULL` | Bank transfer reference code |
| `eligible_at` | `TIMESTAMPTZ` | Yes | `NULL` | Set when booking reaches `COMPLETED` |
| `paid_at` | `TIMESTAMPTZ` | Yes | `NULL` | Set when Admin executes payout |

