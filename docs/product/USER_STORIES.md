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

### US-C05: Submit Post-Trip Rating & Review
- **As a** traveler who has completed a trip,
- **I want to** give a star rating and leave a review regarding the vehicle condition and driver hospitality,
- **So that** other travelers can benefit from my feedback and top partners are rewarded.
- **Acceptance Criteria:**
  - *Given* a booking with status `COMPLETED`,
  - *When* the customer accesses their dashboard or booking detail page,
  - *Then* an interactive review form is presented with overall rating (1-5 stars), optional vehicle and driver sub-scores, and comment box.
  - *Given* the review is submitted,
  - *Then* the partner's lifetime `completed_trips_count` and `average_rating` are updated in real-time, and the customer cannot submit duplicate reviews for the same booking.

### US-C06: Emergency Vehicle Replacement Guarantee
- **As a** traveler exploring remote caldera / mountain destinations,
- **I want to** be guaranteed an emergency replacement vehicle within 1–2 hours if our transport suffers mechanical failure,
- **So that** our vacation remains safe, on schedule, and protected without unexpected out-of-pocket expenses.
- **Acceptance Criteria:**
  - *Given* an active trip where a vehicle breakdown occurs in North Sumatra corridors,
  - *When* the incident is reported to the platform operations desk,
  - *Then* the platform dispatches a replacement car and driver from the nearest partner network within a 1–2 hour SLA.

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

### US-P04: Fleet Management & Driver Assignment
- **As a** Partner Owner,
- **I want to** register our vehicle fleet (Avanza, Innova Reborn, HiAce) with specs and assign qualified drivers,
- **So that** our units are ready to fulfill traveler requests without needing to write complex tour marketing content.
- **Acceptance Criteria:**
  - *Given* a verified Partner,
  - *When* they add a vehicle in the partner portal,
  - *Then* they specify passenger capacity, luggage capacity, transmission, manufacturing year, and with-driver toggle.
  - *Given* a dispatched trip is accepted,
  - *When* the partner assigns a specific vehicle and driver,
  - *Then* the driver can view the traveler manifest and pickup schedule in the portal.

### US-P05: View Trip History & Reputation Badges
- **As a** Partner,
- **I want to** see our total completed trip count and customer review ratings on our dashboard,
- **So that** we can track our business growth, customer satisfaction, and receive more dispatch opportunities.
- **Acceptance Criteria:**
  - *Given* an authenticated Partner,
  - *When* they open their dashboard,
  - *Then* they see their `completed_trips_count`, `average_rating` (e.g. 4.9 / 5.0), and customer review comments.

### US-P06: Priority Dispatching & Cancellation Protection
- **As a** top-rated Partner Driver / Fleet Owner,
- **I want to** receive priority dispatching on high-margin tour bookings and be protected with guaranteed cancellation compensation,
- **So that** operating through the platform provides consistent, secure earnings without offline risks.
- **Acceptance Criteria:**
  - *Given* a new platform-curated tour booking,
  - *When* the dispatch queue evaluates available partners,
  - *Then* partners with high ratings (★ 4.8+) and higher completed trip counts appear at the top of the Admin selection queue.
  - *Given* a customer cancels within < 3 days of departure,
  - *When* the booking is cancelled,
  - *Then* the partner receives the verified Down Payment compensation for the allocated working day.

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
- **I want to** review completed trips and disburse partner earnings minus the agreed platform commission,
- **So that** partners receive their payouts accurately according to bilateral agreements.
- **Acceptance Criteria:**
  - *Given* a booking reaches `COMPLETED` and has no active customer disputes,
  - *When* Admin initiates payout,
  - *Then* the system calculates payout amount based on the snapshotted commission (default baseline 10% or agreed terms), logs the bank reference, and updates settlement status to `PAID`.

### US-A05: Create Platform-Curated Tour Packages
- **As a** Super Administrator,
- **I want to** create and publish high-quality master tour packages directly from the Admin Console with multi-day itineraries and North Sumatra preset inclusions,
- **So that** the platform offers standardized, reliable tours across North Sumatra corridors.
- **Acceptance Criteria:**
  - *Given* an Admin accesses the Tour Package Creator in Admin Console,
  - *When* they define package details, Day 1 to Day N itinerary stops, base price, DP percentage, and preset inclusions (BBM, Driver/Guide, Makan & Rokok Supir, Foto/Drone, Tiket Ferry),
  - *Then* the package is saved with `ownership_type = INTERNAL` and published immediately to the public marketplace.

### US-A06: Dispatch Bookings to Verified Local Partners
- **As a** Super Administrator,
- **I want to** view incoming bookings for platform-curated packages and dispatch them to the best-suited local partner,
- **So that** traveler orders are fulfilled by top-rated, available local transporters.
- **Acceptance Criteria:**
  - *Given* an incoming booking for a curated tour,
  - *When* Admin opens the Dispatch Interface,
  - *Then* the system displays eligible partners sorted by rating and completed trip count, and allows 1-click dispatch to the selected partner with a 24-hour response SLA.

### US-A07: Timed Contact Release at H-1 & Privacy Masking
- **As a** Platform Operations Manager,
- **I want to** mask direct customer and driver contact numbers until H-1 before departure,
- **So that** customer privacy is maintained, off-platform leakage is prevented, and contact is delivered right when needed for airport gate coordination.
- **Acceptance Criteria:**
  - *Given* a confirmed booking,
  - *When* the scheduled departure time is greater than 24 hours away (H > 24h),
  - *Then* driver and customer phone numbers remain masked in the UI and voucher.
  - *Given* the system scheduler detects departure time is within 24 hours (H-1),
  - *When* the cron executes,
  - *Then* the system unmasks contacts and automatically dispatches the digital voucher with driver name/phone to the customer, and sends customer flight arrival details to the driver via WhatsApp.


