# End-to-End User Flows — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Approved Post-Grooming

---

## 1. Discovery, Booking & Semi-Automated Payment Flow (Opsi A)

This flow illustrates the end-to-end journey from initial customer search to verified booking confirmation.

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant W as Web Application
    participant DB as Database
    participant V as Partner (Vendor)
    participant WA as Go WA Gateway
    actor A as Platform Admin

    C->>W: Browse destination/product (No login required)
    C->>W: Select product, dates & guests (Lead time validated: H-1 tour / H-12h vehicle)
    C->>W: Proceed to Checkout -> Authenticate (Login/Register)
    W->>DB: Create Booking (Status: PENDING_APPROVAL, Snapshot created)
    DB->>WA: Trigger notification to Partner PIC
    WA-->>V: Send WA alert: "New Booking Request #BOOK-xxx (SLA 24h)"

    alt Vendor responds within 24 Hours
        V->>W: Partner logs in -> Approves Availability
        W->>DB: Status -> AWAITING_PAYMENT, Create Inventory Hold (24h)
        DB->>WA: Dispatch DP instructions to Customer
        WA-->>C: Send WA: "Booking Approved! Pay 30% DP in 24h to BCA/Mandiri"
    else Vendor fails to respond within 24 Hours (SLA Timeout)
        DB->>DB: Cron/Scheduler detects 24h elapsed
        DB->>W: Status -> MANUAL_REVIEW
        W->>A: Add to Admin Urgent Intervention Queue
        A->>V: Admin calls vendor / confirms manual availability
        A->>W: Admin forces approval -> Status: AWAITING_PAYMENT (Hold 24h)
    end

    C->>W: Customer views bank accounts on Web & transfers DP
    C->>W: Customer uploads transfer proof image
    W->>DB: Status -> PAYMENT_REVIEW, hold preserved
    W->>A: New Payment Proof alert on Admin Dashboard

    A->>W: Admin inspects bank mutation & verifies receipt
    alt Payment is Valid
        W->>DB: Booking: CONFIRMED, Payment: PARTIALLY_PAID (or PAID), Inventory: ALLOCATED
        Note over W,DB: Contact details remain MASKED to prevent leakage
        DB->>WA: Dispatch confirmation & Voucher (Masked Contact)
        WA-->>C: Send WA: "Payment Verified! Booking CONFIRMED. Driver contact will unlock at H-1."
    else Payment is Invalid / Fake
        W->>DB: Status -> AWAITING_PAYMENT (Reason recorded)
        DB->>WA: Notify Customer
        WA-->>C: Send WA: "Proof rejected. Please re-upload legitimate transfer proof."
    end
```

---

## 2. 24-Hour Temporary Inventory Hold & Expiration Flow

```mermaid
flowchart TD
    Start["Vendor Approves Availability"] --> HoldCreated["Create inventory_holds record (expires_at = NOW() + 24h)"]
    HoldCreated --> StatusAwait["Booking Status = AWAITING_PAYMENT<br/>Inventory Status = HELD"]
    StatusAwait --> CheckAction{"Customer submits payment proof within 24h?"}
    
    CheckAction -- Yes --> SubmitProof["Status = PAYMENT_REVIEW<br/>Hold stays active during Admin review"]
    SubmitProof --> AdminReview{"Admin approves proof?"}
    AdminReview -- Yes --> Confirmed["Status = CONFIRMED<br/>Inventory Status = ALLOCATED"]
    AdminReview -- No --> RejectProof["Status = AWAITING_PAYMENT<br/>Customer must re-upload within remaining time"]

    CheckAction -- No --> HoldExpires["24h Timer Expires (Scheduler detects)"]
    HoldExpires --> ReleaseInventory["Inventory Status = RELEASED<br/>Booking Status = EXPIRED"]
    ReleaseInventory --> SendAlert["Send WhatsApp alert to Customer: Hold Expired"]
```

---

## 3. Customer Cancellation & Refund Processing Flow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant W as Web Application
    participant DB as Database
    actor A as Admin
    actor V as Partner

    C->>W: Customer requests cancellation on confirmed booking
    W->>W: Evaluate departure timestamp vs NOW()
    
    alt > 7 Days before departure
        W->>DB: Calculate 100% refund on verified paid amount
        W->>DB: Status -> REFUND_PENDING, Inventory -> RELEASED
    else 3 to 7 Days before departure
        W->>DB: Calculate 50% refund on verified paid amount
        W->>DB: Status -> REFUND_PENDING, Inventory -> RELEASED
    else < 3 Days before departure
        W->>DB: Calculate 0% refund (Non-refundable)
        W->>DB: Status -> CANCELLED, Inventory -> RELEASED
    end

    opt If Refund Amount > 0
        A->>W: Admin inspects Refund Queue
        A->>C: Transfer refund to customer account
        A->>W: Mark refund as COMPLETED with transfer ref
        W->>DB: Booking Status -> REFUNDED, Payment Status -> REFUNDED
    end
    
    DB->>V: Notify Partner that slot/unit is released
```

---

## 4. Partner Onboarding & Product Lifecycle Flow

```mermaid
flowchart TD
    PRegister["Partner Registers Profile on Web"] --> UploadDocs["Upload KTP, NIB, & Vehicle/Fleet Photos"]
    UploadDocs --> PendingAdmin["Partner Status = PENDING_VERIFICATION"]
    PendingAdmin --> AdminKYC{"Admin Verifies KYC Documents"}
    
    AdminKYC -- Rejected --> RejectPartner["Partner Status = REJECTED<br/>Reason sent to Partner"]
    AdminKYC -- Approved --> ApprovePartner["Partner Status = APPROVED<br/>Multi-staff invitations unlocked"]
    
    ApprovePartner --> CreateProduct["Partner Owner creates Product (Tour / Vehicle)"]
    CreateProduct --> ProductReview["Product Status = PENDING_REVIEW"]
    ProductReview --> AdminProductReview{"Admin Reviews Product & Pricing"}
    
    AdminProductReview -- Rejected --> EditProduct["Revise Product according to feedback"]
    EditProduct --> ProductReview
    AdminProductReview -- Approved --> PublishProduct["Product Status = PUBLISHED<br/>Live in public marketplace"]
```

---

## 5. Trip Fulfillment & Partner Settlement Flow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    actor V as Partner
    participant W as Web Application
    participant DB as Database
    actor A as Platform Admin

    Note over C,V: Trip date arrives. Booking is CONFIRMED.
    W->>DB: Transition Booking -> IN_PROGRESS on departure date
    V->>C: Provide tour / vehicle service in Lake Toba
    C->>W: Service completed safely
    V->>W: Mark Service Fulfillments as Done
    W->>DB: Booking Status -> COMPLETED
    W->>DB: Create vendor_settlement (Status: ELIGIBLE)
    Note over DB: Entitlement = Total Price - Platform Commission (default baseline 10%, configurable by terms)

    A->>W: Admin reviews Weekly Settlement Ledger
    A->>V: Transfer net payout to Partner verified bank account
    A->>W: Input Bank Reference & set Settlement Status -> PAID
    W->>DB: Settlement Status: PAID
```

---

## 6. Admin Platform-Curated Tour Package Creation Flow

```mermaid
flowchart TD
    Admin["Super Admin Opens Admin Console"] --> CreateTour["Navigate to: Admin > Tour Packages > Create Master Package"]
    CreateTour --> MetaInfo["Input Title, Summary, Cluster, Pricing Type & Base Rate"]
    MetaInfo --> PresetInclusions["Select North Sumatra Inclusions:<br/>• BBM Pulang-Pergi<br/>• Driver & Guide Lokal<br/>• Makan & Uang Rokok Supir<br/>• Dokumentasi Foto/Video/Drone<br/>• Tiket Ferry Danau Toba PP"]
    PresetInclusions --> DayStructure["Define Multi-Day Itinerary (Day 1 to Day N):<br/>• Day Title & Description<br/>• Itinerary Stops from Master Destinations<br/>• Estimated visit duration (minutes)"]
    DayStructure --> AutoTranslate["Click: Auto-Translate (LibreTranslate ID ↔ EN)"]
    AutoTranslate --> PublishMaster["Save & Publish Package (Ownership: INTERNAL, Status: PUBLISHED)"]
    PublishMaster --> LivePublic["Instantly Available in Public Marketplace"]
```

---

## 7. Booking Dispatching & Partner Assignment Flow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant W as Web Application
    participant DB as Database
    actor A as Super Admin
    actor V as Partner (Transporter / Fleet Owner)
    actor D as Driver (Partner Staff)

    C->>W: Books Platform-Curated Package (e.g. 6D5N Famtrip Sumut)
    W->>DB: Create Booking (Ownership: INTERNAL, Status: PENDING_APPROVAL)
    W->>A: Alert on Admin Console: New Curated Booking Needs Dispatch
    A->>W: Admin views Dispatch Interface (Filtered by cluster, capacity & rating)
    Note over A,W: Admin selects e.g. "CV Horas Samosir (Rating ★ 4.9, 34 Trips)"
    A->>W: Admin dispatches booking to Partner
    W->>DB: Update booking.partner_id & starts 24h Partner SLA
    DB->>V: Dispatch WA & in-portal alert to Partner Owner

    alt Partner accepts within 24h
        V->>W: Partner Owner accepts dispatch
        V->>W: Partner assigns Vehicle (Innova BK-1234) & Driver (Ucok Siregar)
        W->>DB: Booking -> AWAITING_PAYMENT, Hold 24h active
        W->>C: Notify Customer: Booking Confirmed by Partner! Pay DP 30%
        W->>D: Driver sees passenger manifest & pickup time on Partner Portal
    else Partner rejects or SLA expires (24h)
        W->>DB: Status -> MANUAL_REVIEW
        W->>A: Alert Admin: Partner did not accept dispatch
        A->>W: Admin re-dispatches to alternate qualified partner
    end
```

---

## 8. Customer Review, Rating & Partner Trip Counter Flow

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    participant W as Web Application
    participant DB as Database
    actor V as Partner
    actor A as Super Admin

    Note over C,V: Trip finishes safely. Booking status transitions to COMPLETED.
    DB->>DB: Increment partner.completed_trips_count by +1
    W->>C: Display prompt on Dashboard / My Bookings: "Beri Ulasan Perjalanan Anda"
    C->>W: Customer rates trip (1-5 Stars overall, vehicle condition, driver hospitality) + Review comment
    W->>DB: Insert into `reviews` (Unique per booking)
    DB->>DB: Recalculate partner.average_rating & total_reviews_count
    
    W->>V: Review published to Partner Dashboard (Reputation boost)
    W->>A: Review visible in Admin Console partner profile
    W->>W: Rating & completed trip badges visible on Public Marketplace & Checkout
```

---

## 9. Emergency Vehicle Replacement & Breakdown Guarantee Flow

This flow illustrates the platform's value guarantee in the event of mechanical breakdown in remote mountain/caldera terrain (e.g. Tele, Sibea-bea, Tongging, Bukit Lawang). Solo offline drivers cannot provide this network guarantee.

```mermaid
sequenceDiagram
    autonumber
    actor C as Customer
    actor D1 as Assigned Driver (Partner A)
    participant W as Web Application / Operations
    actor A as Operations Super Admin
    actor D2 as Replacement Driver (Partner B)

    Note over C,D1: Trip is IN_PROGRESS on steep caldera route (e.g., Menara Pandang Tele)
    D1->>D1: Vehicle encounters mechanical failure / flat tire / radiator overheat
    D1->>W: Driver flags "EMERGENCY_BREAKDOWN" via Partner Portal / WA Hotlink
    W->>A: Immediate High-Priority Emergency Alert triggered on Admin Console
    
    A->>W: Admin views active partner fleet within 20km radius of incident GPS
    Note over A,W: Platform SLA: Replacement unit arrives within 1–2 Hours
    A->>W: Admin dispatches Emergency Backup Request to nearest qualified Partner B
    W->>D2: Alert to Replacement Driver with exact GPS pin & passenger count
    D2->>W: Accepts emergency backup assignment
    
    D2->>C: Arrives at breakdown location within SLA window (< 2 hours)
    D2->>C: Transshipment of passengers & luggage to fresh vehicle
    Note over C,D2: Trip continues smoothly according to itinerary
    
    A->>W: System records Incident Log & splits/adjusts settlement fairly
    W->>C: WA message: "Kami pastikan perjalanan Anda aman. Garansi pergantian unit Muter-Muter sukses."
```

---

## 10. Timed Contact Release & Airport Pickup Sync Flow (H-1 Departure)

To eliminate off-platform leakage while guaranteeing seamless coordination upon arrival at Kualanamu (KNO) or Silangit (DTB) Airport, direct contacts are released precisely 24 hours prior to scheduled departure.

```mermaid
sequenceDiagram
    autonumber
    participant S as Laravel Scheduler (Cron)
    participant DB as Database
    participant WA as Go WA Gateway
    actor C as Customer
    actor D as Assigned Driver

    Note over S,DB: Cron runs every hour checking confirmed bookings
    S->>DB: Query bookings WHERE status = 'CONFIRMED' AND departure_time <= NOW() + 24 HOURS AND contact_unmasked = FALSE
    
    loop For each booking reaching H-1
        DB->>DB: Set contact_unmasked = TRUE
        DB->>WA: Queue automated WhatsApp Voucher to Customer
        WA-->>C: Send WA: "Halo! Trip Anda besok. Driver Anda: Bpk Ucok (0812-xxxx-xxxx), Mobil Innova BK-1234-XX. Siap jemput di Gate Kedatangan KNO."
        
        DB->>WA: Queue Passenger Manifest to Driver
        WA-->>D: Send WA: "Tugas Besok! Tamu: Bpk/Ibu Budi (0813-xxxx-xxxx), Pax: 4 orang, Flight: Citilink QG-123 Tiba 14.30 WIB di KNO."
    end

    Note over C,D: Customer & Driver coordinate directly on H-1 for flight gate sync
    C->>D: "Pak, kami landing di Gate 2 KNO"
    D->>C: "Baik Pak, mobil sudah stand by di pick-up zone"
```


