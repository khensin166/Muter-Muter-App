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
        DB->>WA: Dispatch confirmation & Voucher
        WA-->>C: Send WA: "Payment Verified! Booking CONFIRMED. Voucher attached."
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
    Note over DB: Entitlement = Total Price - 10% Platform Commission

    A->>W: Admin reviews Weekly Settlement Ledger
    A->>V: Transfer net payout to Partner verified bank account
    A->>W: Input Bank Reference & set Settlement Status -> PAID
    W->>DB: Settlement Status: PAID
```

