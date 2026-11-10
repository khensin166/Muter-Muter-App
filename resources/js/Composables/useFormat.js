/**
 * Shared formatting helpers (single source of truth).
 * Use: const { formatRupiah, getInitials, formatDate } = useFormat();
 */

export const formatRupiah = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0);

export const getInitials = (name, max = 2, fallback = 'BT') => {
  if (!name) return fallback;
  const initials = String(name)
    .split(' ')
    .filter(Boolean)
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, max);
  return initials || fallback;
};

export const formatDate = (value, options = { day: 'numeric', month: 'long', year: 'numeric' }, locale = 'id-ID') => {
  if (!value) return '-';
  const date = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return new Intl.DateTimeFormat(locale, options).format(date);
};

export const formatDuration = (minutes) => {
  const m = Number(minutes) || 0;
  if (m < 60) return `${m} mnt`;
  const h = Math.floor(m / 60);
  const rest = m % 60;
  return rest ? `${h} jam ${rest} mnt` : `${h} jam`;
};

export function useFormat() {
  return { formatRupiah, getInitials, formatDate, formatDuration };
}

