<?php

namespace App\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class StructuredJsonFormatter extends NormalizerFormatter
{
    protected string $serviceName;

    public function __construct(string $serviceName = 'muter-muter-app')
    {
        parent::__construct('Y-m-d\TH:i:s.v\Z');
        $this->serviceName = $serviceName;
    }

    /**
     * Format a single log record to canonical JSON structure.
     */
    public function format(LogRecord $record): string
    {
        $context = $record->context;

        // Extract trace_id and channel from context if present
        $traceId = $context['trace_id'] ?? request()?->attributes?->get('trace_id') ?? 'sys-system';
        $channel = $context['channel'] ?? $record->channel ?? 'application';
        $event = $context['event'] ?? ($channel . '.' . strtolower($record->level->name));

        // Resolve actor details
        $user = auth()->user();
        $actor = $context['actor'] ?? [
            'user_id' => $user?->id ?? null,
            'role' => $user?->role?->value ?? ($user ? 'AUTHENTICATED' : 'ANONYMOUS'),
            'ip' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? 'CLI/System',
        ];

        // Clean domain context (remove metadata already extracted to root)
        unset($context['trace_id'], $context['channel'], $context['event'], $context['actor']);

        // Apply PII & Sensitive Data Masking
        $cleanContext = $this->maskSensitiveData($context);

        $payload = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.v\Z'),
            'level' => $record->level->name,
            'service' => $this->serviceName,
            'channel' => $channel,
            'event' => $event,
            'message' => $record->message,
            'trace_id' => $traceId,
            'actor' => $actor,
            'context' => $cleanContext,
        ];

        if (! empty($record->extra)) {
            $payload['extra'] = $this->maskSensitiveData($record->extra);
        }

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    /**
     * Recursively mask sensitive fields.
     */
    public function maskSensitiveData(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $blacklist = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'remember_token',
            'api_token',
            'whatsapp_token',
            'api_key',
            'secret',
        ];

        $masked = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            // Blacklist check: redact completely
            if (in_array($lowerKey, $blacklist, true)) {
                $masked[$key] = '[REDACTED]';
                continue;
            }

            // Recursive array traversal
            if (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
                continue;
            }

            if (! is_string($value)) {
                $masked[$key] = $value;
                continue;
            }

            // KTP / NIK Masking (16 digits)
            if (str_contains($lowerKey, 'ktp') || str_contains($lowerKey, 'nik')) {
                $len = strlen($value);
                $masked[$key] = $len >= 8 ? substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4) : '[MASKED_KTP]';
                continue;
            }

            // Phone number masking
            if (str_contains($lowerKey, 'phone') || str_contains($lowerKey, 'telepon') || str_contains($lowerKey, 'wa')) {
                $len = strlen($value);
                $masked[$key] = $len >= 8 ? substr($value, 0, 4) . str_repeat('*', max(4, $len - 7)) . substr($value, -3) : '[MASKED_PHONE]';
                continue;
            }

            // Bank account number masking
            if (str_contains($lowerKey, 'account_number') || str_contains($lowerKey, 'rekening')) {
                $len = strlen($value);
                $masked[$key] = $len >= 6 ? substr($value, 0, 3) . str_repeat('*', max(3, $len - 5)) . substr($value, -2) : '[MASKED_ACCOUNT]';
                continue;
            }

            $masked[$key] = $value;
        }

        return $masked;
    }
}
