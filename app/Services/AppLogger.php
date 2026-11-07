<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AppLogger
{
    /**
     * Dispatch structured log entry.
     */
    public static function log(string $level, string $channel, string $event, string $message, array $context = []): void
    {
        $logPayload = array_merge($context, [
            'channel' => $channel,
            'event' => $event,
        ]);

        Log::log($level, $message, $logPayload);
    }

    public static function info(string $channel, string $event, string $message, array $context = []): void
    {
        self::log('info', $channel, $event, $message, $context);
    }

    public static function warning(string $channel, string $event, string $message, array $context = []): void
    {
        self::log('warning', $channel, $event, $message, $context);
    }

    public static function error(string $channel, string $event, string $message, array $context = []): void
    {
        self::log('error', $channel, $event, $message, $context);
    }

    public static function auth(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'auth', $event, $message, $context);
    }

    public static function booking(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'booking', $event, $message, $context);
    }

    public static function payment(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'payment', $event, $message, $context);
    }

    public static function notification(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'notification', $event, $message, $context);
    }

    public static function settlement(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'settlement', $event, $message, $context);
    }

    public static function application(string $event, string $message, array $context = [], string $level = 'info'): void
    {
        self::log($level, 'application', $event, $message, $context);
    }

    /**
     * Record a critical change to both the PostgreSQL audit_logs table and the structured log stream.
     */
    public static function audit(
        string $action,
        Model $model,
        array $oldValues = [],
        array $newValues = [],
        ?string $message = null
    ): AuditLog {
        $user = auth()->user();

        // 1. Persist to PostgreSQL audit_logs table
        $auditLog = AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => ! empty($oldValues) ? $oldValues : null,
            'new_values' => ! empty($newValues) ? $newValues : null,
            'ip_address' => request()?->ip(),
        ]);

        // 2. Emit structured log entry to the unified log stream
        $logMessage = $message ?? "Audit action {$action} on ".class_basename($model)." [{$model->getKey()}]";
        self::info('audit', 'audit.entry_created', $logMessage, [
            'audit_id' => $auditLog->id,
            'action' => $action,
            'entity_type' => class_basename($model),
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);

        return $auditLog;
    }
}

