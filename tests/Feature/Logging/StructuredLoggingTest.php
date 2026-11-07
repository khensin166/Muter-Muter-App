<?php

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\User;
use App\Services\AppLogger;
use App\Logging\StructuredJsonFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Monolog\Level;
use Monolog\LogRecord;

uses(RefreshDatabase::class);

test('http request receives and returns X-Trace-Id header', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertHeader('X-Trace-Id');
    expect($response->headers->get('X-Trace-Id'))->toStartWith('req-');
});

test('http request preserves client provided trace id', function () {
    $customTraceId = 'req-custom-upstream-id-12345';

    $response = $this->withHeader('X-Trace-Id', $customTraceId)->get('/');

    $response->assertSuccessful();
    $response->assertHeader('X-Trace-Id', $customTraceId);
});

test('structured json formatter produces canonical envelope with all required fields', function () {
    $formatter = new StructuredJsonFormatter('muter-muter-test');

    $record = new LogRecord(
        datetime: new \DateTimeImmutable('2026-11-07 10:00:00.123'),
        channel: 'booking',
        level: Level::Info,
        message: 'Status booking berubah',
        context: [
            'channel' => 'booking',
            'event' => 'booking.status_changed',
            'trace_id' => 'req-test-uuid-999',
            'booking_code' => 'BK-TEST-001',
            'amount' => 500000,
        ]
    );

    $formattedJson = $formatter->format($record);
    $data = json_decode($formattedJson, true);

    expect($data)->toBeArray()
        ->and($data['service'])->toBe('muter-muter-test')
        ->and($data['channel'])->toBe('booking')
        ->and($data['event'])->toBe('booking.status_changed')
        ->and($data['level'])->toBe('INFO')
        ->and($data['trace_id'])->toBe('req-test-uuid-999')
        ->and($data['message'])->toBe('Status booking berubah')
        ->and($data['context']['booking_code'])->toBe('BK-TEST-001')
        ->and($data['context']['amount'])->toBe(500000);
});

test('sensitive data is properly masked and redacted in log context', function () {
    $formatter = new StructuredJsonFormatter('muter-muter-test');

    $record = new LogRecord(
        datetime: new \DateTimeImmutable(),
        channel: 'auth',
        level: Level::Info,
        message: 'Pemeriksaan keamanan data',
        context: [
            'password' => 'secret_password_123',
            'api_token' => 'bearer_token_xyz',
            'user_phone' => '081234567890',
            'nik_ktp' => '1271012304950001',
            'bank_account_number' => '1070019283741',
        ]
    );

    $formattedJson = $formatter->format($record);
    $data = json_decode($formattedJson, true);

    expect($data['context']['password'])->toBe('[REDACTED]')
        ->and($data['context']['api_token'])->toBe('[REDACTED]')
        ->and($data['context']['user_phone'])->toBe('0812****890')
        ->and($data['context']['nik_ktp'])->toBe('1271********0001')
        ->and($data['context']['bank_account_number'])->toBe('107******41');
});

test('app logger audit method creates database record and emits structured event', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $booking = Booking::factory()->make();
    $booking->id = (string) \Illuminate\Support\Str::uuid();

    $auditLog = AppLogger::audit(
        action: 'STATUS_FORCE_APPROVED',
        model: $booking,
        oldValues: ['status' => 'PENDING_APPROVAL'],
        newValues: ['status' => 'AWAITING_PAYMENT'],
        message: 'Admin menyetujui booking manual'
    );

    expect($auditLog)->toBeInstanceOf(AuditLog::class)
        ->and($auditLog->action)->toBe('STATUS_FORCE_APPROVED')
        ->and($auditLog->entity_type)->toBe(Booking::class)
        ->and($auditLog->old_values['status'])->toBe('PENDING_APPROVAL')
        ->and($auditLog->new_values['status'])->toBe('AWAITING_PAYMENT')
        ->and($auditLog->user_id)->toBe($user->id);

    $this->assertDatabaseHas('audit_logs', [
        'id' => $auditLog->id,
        'action' => 'STATUS_FORCE_APPROVED',
    ]);
});
