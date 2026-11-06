<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    protected bool $enabled;
    protected string $gatewayUrl;
    protected string $apiToken;
    protected bool $mockMode;
    protected int $timeout;

    public function __construct()
    {
        $this->enabled = config('whatsapp.enabled', false);
        $this->gatewayUrl = config('whatsapp.gateway_url', 'http://127.0.0.1:3000/send-message');
        $this->apiToken = config('whatsapp.api_token', '');
        $this->mockMode = config('whatsapp.mock_mode', true);
        $this->timeout = config('whatsapp.timeout', 5);
    }

    /**
     * Format phone number to standard international format (e.g. 62812...).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            return '62'.substr($clean, 1);
        }

        if (str_starts_with($clean, '62')) {
            return $clean;
        }

        return '62'.$clean;
    }

    /**
     * Send arbitrary text message to recipient.
     */
    public function sendMessage(string $phone, string $message): bool
    {
        $formattedPhone = $this->formatPhoneNumber($phone);

        if ($this->mockMode || ! $this->enabled) {
            Log::info("[WhatsApp Mock Mode] Dispatched to {$formattedPhone}:\n{$message}");

            return true;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->post($this->gatewayUrl, [
                    'phone' => $formattedPhone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("[WhatsApp Gateway] Success sending to {$formattedPhone}");

                return true;
            }

            Log::error("[WhatsApp Gateway Error] HTTP {$response->status()}: {$response->body()}");

            return false;
        } catch (Throwable $e) {
            Log::error('[WhatsApp Gateway Exception] '.$e->getMessage());

            return false;
        }
    }

    /**
     * 1. Alert Partner of a new booking request (triggers 24h SLA).
     */
    public function notifyVendorNewBooking(Booking $booking): bool
    {
        $partnerPhone = $booking->partner?->pic_phone;
        if (! $partnerPhone) {
            return false;
        }

        $formattedDp = 'Rp '.number_format((float) $booking->dp_amount, 0, ',', '.');
        $formattedTotal = 'Rp '.number_format((float) $booking->total_price, 0, ',', '.');
        $productName = $booking->product_snapshot['name'] ?? $booking->product?->name;

        $msg = "📢 *PERMINTAAN BOOKING BARU (SLA 24 JAM)*\n\n".
            "Halo Mitra *{$booking->partner?->business_name}*,\n".
            "Terdapat pesanan masuk baru di marketplace Muter-Muter Danau Toba:\n\n".
            "• *Kode Booking:* {$booking->booking_code}\n".
            "• *Produk:* {$productName}\n".
            "• *Jadwal Keberangkatan:* {$booking->service_start_date->format('d M Y')}\n".
            "• *Pemesan:* {$booking->user?->name} ({$booking->user?->phone})\n".
            "• *Nilai Transaksi:* {$formattedTotal} (Wajib DP: {$formattedDp})\n\n".
            "⏱️ *Batas Waktu Respon:* 24 Jam (Sebelum {$booking->vendor_sla_deadline?->format('d M Y, H:i')} WIB)\n\n".
            "Silakan buka portal mitra untuk menyetujui atau menolak ketersediaan:\n".
            url('/partner/bookings/'.$booking->id)."\n\n".
            "_Pesan otomatis oleh Muter-Muter Danau Toba Travel Marketplace._";

        return $this->sendMessage($partnerPhone, $msg);
    }

    /**
     * 2. Alert Customer that availability is approved and Down Payment is unlocked.
     */
    public function notifyCustomerBookingApproved(Booking $booking): bool
    {
        $customerPhone = $booking->user?->phone;
        if (! $customerPhone) {
            return false;
        }

        $formattedDp = 'Rp '.number_format((float) $booking->dp_amount, 0, ',', '.');
        $productName = $booking->product_snapshot['name'] ?? $booking->product?->name;

        $msg = "🎉 *KETERSEDIAAN JADWAL DISETUJUI MITRA*\n\n".
            "Halo Kak *{$booking->user?->name}*,\n".
            "Kabar gembira! Mitra *{$booking->partner?->business_name}* telah menyetujui ketersediaan pesanan Anda:\n\n".
            "• *Kode Booking:* {$booking->booking_code}\n".
            "• *Produk:* {$productName}\n".
            "• *Jadwal:* {$booking->service_start_date->format('d M Y')}\n\n".
            "🛡️ *Status Inventory:* Unit & jadwal di-hold sementara selama 24 jam.\n\n".
            "💳 *Tagihan Down Payment (DP):* {$formattedDp}\n".
            "• *Bank:* Bank Mandiri\n".
            "• *No. Rekening:* 107-00-1928374-1\n".
            "• *Atas Nama:* PT MUTER MUTER WISATA TOBA\n\n".
            "Silakan transfer DP dan upload bukti transfer melalui link di bawah ini:\n".
            url('/bookings/'.$booking->booking_code)."\n\n".
            "_Terima kasih, selamat merencanakan liburan impian ke Danau Toba!_";

        return $this->sendMessage($customerPhone, $msg);
    }

    /**
     * 3. Alert Customer that payment proof is verified and booking is CONFIRMED.
     */
    public function notifyCustomerPaymentConfirmed(Booking $booking): bool
    {
        $customerPhone = $booking->user?->phone;
        if (! $customerPhone) {
            return false;
        }

        $formattedDp = 'Rp '.number_format((float) $booking->dp_amount, 0, ',', '.');
        $formattedBalance = 'Rp '.number_format((float) ($booking->total_price - $booking->dp_amount), 0, ',', '.');
        $productName = $booking->product_snapshot['name'] ?? $booking->product?->name;

        $msg = "✅ *PEMBAYARAN TERVERIFIKASI & PESANAN TERKONFIRMASI*\n\n".
            "Halo Kak *{$booking->user?->name}*,\n".
            "Bukti transfer Down Payment (DP) senilai *{$formattedDp}* untuk pesanan *{$booking->booking_code}* telah terverifikasi oleh tim keuangan Muter-Muter!\n\n".
            "• *Produk:* {$productName}\n".
            "• *Jadwal:* {$booking->service_start_date->format('d M Y')}\n".
            "• *Status Armada/Tour:* TERALOKASI PASTI (Unit Dijamin)\n".
            "• *Sisa Pelunasan:* {$formattedBalance} (dibayarkan paling lambat saat keberangkatan)\n\n".
            "Kontak Mitra PIC Lapangan:\n".
            "• {$booking->partner?->business_name} ({$booking->partner?->pic_name} - {$booking->partner?->pic_phone})\n\n".
            "Akses e-voucher dan rincian perjalanan Anda di:\n".
            url('/bookings/'.$booking->booking_code)."\n\n".
            "_Horas! Selamat menikmati pesona Danau Toba & Sumatera Utara!_";

        return $this->sendMessage($customerPhone, $msg);
    }
}

