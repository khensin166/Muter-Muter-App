<?php

use App\Services\FileSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('public');
});

test('valid jpeg image is accepted, re-encoded cleanly, and stored with uuid v4 filename', function () {
    $file = UploadedFile::fake()->image('my_original_vacation_photo.jpg', 800, 600);

    $storedPath = FileSecurityService::validateAndStore(
        file: $file,
        directory: 'products/tours',
        fieldName: 'photo',
        allowPdf: false,
        disk: 'public'
    );

    expect($storedPath)
        ->toStartWith('products/tours/')
        ->toEndWith('.jpg')
        ->not->toContain('my_original_vacation_photo');

    Storage::disk('public')->assertExists($storedPath);
});

test('spoofed php script renamed to jpg is strictly rejected', function () {
    // Construct tag dynamically so host AV does not quarantine the test file itself
    $openTag = '<' . '?php';
    $maliciousPhp = $openTag . ' phpinfo(); ?>';
    $file = UploadedFile::fake()->createWithContent('avatar.jpg', $maliciousPhp);

    expect(fn () => FileSecurityService::validateAndStore(
        file: $file,
        directory: 'payments/proofs',
        fieldName: 'proof_file',
        allowPdf: true,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

test('double extension filename with php is strictly rejected even if image content is valid', function () {
    $file = UploadedFile::fake()->image('exploit.php.jpg', 400, 400);

    expect(fn () => FileSecurityService::validateAndStore(
        file: $file,
        directory: 'products/tours',
        fieldName: 'photo',
        allowPdf: false,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

test('polyglot jpeg image containing embedded php tag in binary is caught and rejected', function () {
    $validJpeg = UploadedFile::fake()->image('clean.jpg', 200, 200);
    $rawBytes = file_get_contents($validJpeg->getRealPath());

    // Inject PHP tag into the binary stream (polyglot attack)
    $openTag = '<' . '?php';
    $polyglotBytes = $rawBytes . "\n/* comment */ " . $openTag . ' echo 1; ?>';
    $polyglotFile = UploadedFile::fake()->createWithContent('innocent_photo.jpg', $polyglotBytes);

    expect(fn () => FileSecurityService::validateAndStore(
        file: $polyglotFile,
        directory: 'payments/proofs',
        fieldName: 'proof_file',
        allowPdf: true,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

test('svg file with xss script is strictly rejected even if renamed to png', function () {
    $svgXss = '<svg xmlns="http://www.w3.org/2000/svg"><' . 'script>alert(1)</' . 'script></svg>';
    $file = UploadedFile::fake()->createWithContent('icon.png', $svgXss);

    expect(fn () => FileSecurityService::validateAndStore(
        file: $file,
        directory: 'products/vehicles',
        fieldName: 'photo',
        allowPdf: false,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

test('valid clean pdf is accepted when allowPdf is true but rejected when allowPdf is false', function () {
    $cleanPdfBytes = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n%%EOF";
    $pdfFile = UploadedFile::fake()->createWithContent('ktp_document.pdf', $cleanPdfBytes);

    // Allowed for KTP / Payment Proof
    $path = FileSecurityService::validateAndStore(
        file: $pdfFile,
        directory: 'partner_documents/ktp',
        fieldName: 'ktp_file',
        allowPdf: true,
        disk: 'public'
    );

    expect($path)->toStartWith('partner_documents/ktp/')->toEndWith('.pdf');
    Storage::disk('public')->assertExists($path);

    // Disallowed for Tour Photos
    expect(fn () => FileSecurityService::validateAndStore(
        file: $pdfFile,
        directory: 'products/tours',
        fieldName: 'photo',
        allowPdf: false,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

test('pdf containing embedded javascript action is strictly rejected', function () {
    $maliciousPdfBytes = "%PDF-1.4\n1 0 obj\n<< /Type /Action /S /" . "JavaScript /JS (1) >>\nendobj\n%%EOF";
    $pdfFile = UploadedFile::fake()->createWithContent('invoice.pdf', $maliciousPdfBytes);

    expect(fn () => FileSecurityService::validateAndStore(
        file: $pdfFile,
        directory: 'payments/proofs',
        fieldName: 'proof_file',
        allowPdf: true,
        disk: 'public'
    ))->toThrow(ValidationException::class);
});

