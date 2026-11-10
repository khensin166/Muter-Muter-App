/**
 * Reusable Status Badge Composable for Booking, Payment, and Inventory lifecycles.
 * Adheres strictly to the 3-pillar explicit state separation (BUSINESS_RULES.md)
 * and matches App\Enums\BookingStatus & App\Enums\PaymentStatus 1:1.
 */

export const bookingStatusMap = {
  PENDING_APPROVAL: { label: 'Menunggu Persetujuan Mitra', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  MANUAL_REVIEW: { label: 'Intervensi Manual Admin', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  AWAITING_PAYMENT: { label: 'Menunggu Pembayaran DP', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  PAYMENT_REVIEW: { label: 'Verifikasi Pembayaran', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  CONFIRMED: { label: 'Terkonfirmasi', badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
  IN_PROGRESS: { label: 'Sedang Berlangsung', badgeClass: 'bg-sky-100 text-sky-800 border-sky-200' },
  COMPLETED: { label: 'Selesai', badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
  CANCELLED: { label: 'Dibatalkan', badgeClass: 'bg-rose-100 text-rose-800 border-rose-200' },
  EXPIRED: { label: 'Kedaluwarsa', badgeClass: 'bg-stone-100 text-stone-700 border-stone-200' },
  REFUND_PENDING: { label: 'Menunggu Pengembalian Dana', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  REFUNDED: { label: 'Dana Dikembalikan', badgeClass: 'bg-stone-100 text-stone-700 border-stone-200' },
  REJECTED: { label: 'Ditolak', badgeClass: 'bg-rose-100 text-rose-800 border-rose-200' },
};

export const paymentStatusMap = {
  UNPAID: { label: 'Belum Dibayar', badgeClass: 'bg-rose-100 text-rose-800 border-rose-200' },
  PARTIALLY_PAID: { label: 'DP Terverifikasi (Belum Lunas)', badgeClass: 'bg-sky-100 text-sky-800 border-sky-200' },
  PAYMENT_REVIEW: { label: 'Menunggu Verifikasi Admin', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  PAID: { label: 'Lunas', badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
  OVERDUE: { label: 'Terlambat Bayar', badgeClass: 'bg-rose-100 text-rose-800 border-rose-200' },
  FAILED: { label: 'Pembayaran Gagal', badgeClass: 'bg-rose-100 text-rose-800 border-rose-200' },
  REFUND_PENDING: { label: 'Proses Pengembalian Dana', badgeClass: 'bg-amber-100 text-amber-800 border-amber-200' },
  REFUNDED: { label: 'Dana Dikembalikan', badgeClass: 'bg-stone-100 text-stone-700 border-stone-200' },
};

export const getBookingStatusInfo = (status) => {
  return bookingStatusMap[status] || {
    label: status || 'Unknown',
    badgeClass: 'bg-stone-100 text-stone-700 border-stone-200',
  };
};

export const getPaymentStatusInfo = (status) => {
  return paymentStatusMap[status] || {
    label: status || 'Unknown',
    badgeClass: 'bg-stone-100 text-stone-700 border-stone-200',
  };
};

export function useStatusBadge() {
  return {
    bookingStatusMap,
    paymentStatusMap,
    getBookingStatusInfo,
    getPaymentStatusInfo,
  };
}
