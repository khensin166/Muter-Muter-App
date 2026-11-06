# User Stories & Acceptance Criteria — Lake Toba Travel Marketplace

- **Document Version:** 1.0
- **Status:** Approved Post-Grooming

---

## 1. Customer User Stories

### US-C01: Browse Destinations and Travel Catalogs
- **As a** prospective traveler,
- **I want to** browse curated destinations, tour packages, and vehicle rentals without logging in,
- **So that** I can explore options and plan my trip to Lake Toba friction-free.
- **Acceptance Criteria:**
  - *Given* an unauthenticated user visits the homepage,
  - *When* they filter by destination cluster (e.g. Parapat, Samosir, Merek) or product type,
  - *Then* the catalog updates dynamically displaying thumbnail, title, pricing, and key highlights.
  - *Given* any product card is clicked,
  - *When* the detail page opens,
  - *Then* the full itinerary, inclusions/exclusions, minimum lead time, and cancellation policy snapshot are clearly visible.

### US-C02: Request a Booking
- **As a** customer,
- **I want to** select my travel dates, participant count, or rental duration and submit a booking request,
- **So that** the vendor can verify availability.
- **Acceptance Criteria:**
  - *Given* a customer selects a Tour Package with departure time within lead time (< 24 hours),
  - *When* they attempt to submit,
  - *Then* the system displays a validation error indicating minimum lead time is H-1.
  - *Given* valid dates and guest counts are selected and customer is not logged in,
  - *When* they click "Lanjutkan Pemesanan",
  - *Then* they are prompted to login or register, after which the booking is saved with status `PENDING_APPROVAL`.

### US-C03: Receive Vendor Approval & Pay Down Payment (DP)
- **As a** customer,
- **I want to** be notified via WhatsApp and on the web portal when my booking is approved,
- **So that** I can make the required DP within the 24-hour hold window.
- **Acceptance Criteria:**
  - *Given* a booking moves to `AWAITING_PAYMENT`,
  - *When* the 24-hour hold is activated,
  - *Then* an automated WhatsApp message is sent to the customer containing booking reference, DP amount (default 30%), bank transfer instructions, and expiration deadline.
  - *Given* the customer opens the booking page,
  - *When* they transfer the funds and upload their proof of payment image,
  - *Then* the booking transitions to `PAYMENT_REVIEW` and the hold remains active while Admin verifies.

### US-C04: Request Booking Cancellation
- **As a** customer with a confirmed booking,
- **I want to** request a cancellation and see my eligible refund amount,
- **So that** I can cancel plans if necessary in accordance with platform policies.
- **Acceptance Criteria:**
  - *Given* a customer initiates cancellation > 7 days prior to departure,
  - *When* they confirm cancellation,
  - *Then* the system calculates 100% refund of the amount actually paid and sets status to `REFUND_PENDING`.
  - *Given* cancellation occurs < 3 days prior,
  - *When* they confirm cancellation,
  - *Then* the system indicates 0% refund, releases inventory, and sets status to `CANCELLED`.

---

## 2. Partner User Stories

### US-P01: Partner Registration & KYC Submission
- **As a** local tour operator or vehicle owner,
- **I want to** register my business and upload required legal documents,
- **So that** my business can be verified by platform administrators.
- **Acceptance Criteria:**
  - *Given* a new partner fills the registration form,
  - *When* they upload Owner KTP, NIB/Business license, and vehicle photos,
  - *Then* their account status becomes `PENDING_VERIFICATION` and product publishing is disabled until Admin approval.

### US-P02: Invite and Manage Multi-Staff Users
- **As a** Partner Owner,
- **I want to** invite operational staff to my partner account,
- **So that** they can respond to booking requests without having access to bank settings.
- **Acceptance Criteria:**
  - *Given* an authenticated Partner Owner,
  - *When* they invite a user as `Partner Staff`,
  - *Then* the invited user can view incoming bookings and click Approve/Reject, but cannot edit payout bank accounts or delete products.

### US-P03: Availability Verification (SLA 24 Hours)
- **As a** Partner Staff or Owner,
- **I want to** view pending booking requests and approve or reject availability within 24 hours,
- **So that** the customer can proceed to payment or find alternatives.
- **Acceptance Criteria:**
  - *Given* a booking is in `PENDING_APPROVAL`,
  - *When* the partner clicks "Setujui Ketersediaan",
  - *Then* the booking status updates to `AWAITING_PAYMENT`, inventory is held for 24h, and customer is alerted.
  - *Given* a partner fails to respond within 24 hours,
  - *When* the timer expires,
  - *Then* the system automatically transitions the booking to `MANUAL_REVIEW` and flags it for Admin intervention.

---

## 3. Admin User Stories

### US-A01: Partner KYC Verification
- **As a** Platform Administrator,
- **I want to** review submitted partner documents,
- **So that** only legitimate, high-quality operators can sell on the marketplace.
- **Acceptance Criteria:**
  - *Given* a partner with status `PENDING_VERIFICATION`,
  - *When* Admin inspects KTP, NIB, and fleet photos and clicks "Approve",
  - *Then* the partner status becomes `APPROVED` and they can publish approved products.

### US-A02: Payment Proof Verification
- **As a** Platform Administrator,
- **I want to** review uploaded bank transfer proofs against the company bank ledger,
- **So that** legitimate payments are confirmed and fraudulent attempts are rejected.
- **Acceptance Criteria:**
  - *Given* a booking with payment status `PAYMENT_REVIEW`,
  - *When* Admin inspects the transfer receipt and clicks "Verifikasi Pembayaran",
  - *Then* booking status transitions to `CONFIRMED`, payment status becomes `PARTIALLY_PAID` (if DP) or `PAID` (if 100%), and inventory status becomes `ALLOCATED`.
  - *Given* the receipt is unreadable or forged,
  - *When* Admin clicks "Tolak Bukti" with reason,
  - *Then* booking returns to `AWAITING_PAYMENT` and the customer is notified via WhatsApp.

### US-A03: Operational SLA Monitoring & Manual Intervention
- **As a** Platform Administrator,
- **I want to** view an urgent queue of bookings marked `MANUAL_REVIEW`,
- **So that** I can call the vendor directly or reassign to an alternate unit before the customer experiences delays.
- **Acceptance Criteria:**
  - *Given* bookings in `MANUAL_REVIEW`,
  - *When* Admin opens the Operations Dashboard,
  - *Then* these bookings are highlighted with vendor contact details and reassignment options.

### US-A04: Partner Settlement Disbursement
- **As a** Platform Administrator,
- **I want to** review completed trips and disburse partner earnings minus the 10% platform commission,
- **So that** partners receive their payouts accurately.
- **Acceptance Criteria:**
  - *Given* a booking reaches `COMPLETED` and has no active customer disputes,
  - *When* Admin initiates payout,
  - *Then* the system calculates payout amount = `Total Price - 10% Platform Commission`, logs the bank reference, and updates settlement status to `PAID`.

