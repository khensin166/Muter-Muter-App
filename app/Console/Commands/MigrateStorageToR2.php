<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateStorageToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-r2 {--test-only : Only test connection without migrating files} {--list-buckets : List all buckets in the Cloudflare R2 account} {--create-bucket : Attempt to create the bucket if not exists} {--force : Overwrite files that already exist on R2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Cloudflare R2 connectivity and migrate existing local media assets to Cloudflare R2 bucket';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  Cloudflare R2 Object Storage Diagnostic & Migrator');
        $this->info('====================================================');

        $account = config('filesystems.disks.r2.endpoint');
        $bucket = config('filesystems.disks.r2.bucket');
        $keyId = config('filesystems.disks.r2.key');

        $this->line("Endpoint : {$account}");
        $this->line("Bucket   : {$bucket}");
        $this->line("Key ID   : " . substr((string) $keyId, 0, 6) . '...' . substr((string) $keyId, -4));
        $this->newLine();

        if ($this->option('list-buckets')) {
            $this->comment('Listing buckets via AWS S3 client...');
            try {
                $client = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region' => 'auto',
                    'endpoint' => $account,
                    'credentials' => [
                        'key' => (string) $keyId,
                        'secret' => (string) config('filesystems.disks.r2.secret'),
                    ],
                    'http' => ['verify' => false],
                ]);
                $result = $client->listBuckets();
                $buckets = $result['Buckets'] ?? [];
                if (empty($buckets)) {
                    $this->warn('Belum ada bucket yang dibuat di akun Cloudflare R2 ini.');
                } else {
                    $this->info('Daftar bucket yang ditemukan di Cloudflare R2:');
                    foreach ($buckets as $b) {
                        $this->line(' - ' . $b['Name'] . ' (Created: ' . $b['CreationDate'] . ')');
                    }
                }
            } catch (Throwable $e) {
                $this->error('Gagal mengambil daftar bucket: ' . $e->getMessage());
            }
            return self::SUCCESS;
        }

        if ($this->option('create-bucket')) {
            $this->comment("Mencoba membuat bucket [{$bucket}] di Cloudflare R2...");
            try {
                $client = new \Aws\S3\S3Client([
                    'version' => 'latest',
                    'region' => 'auto',
                    'endpoint' => $account,
                    'credentials' => [
                        'key' => (string) $keyId,
                        'secret' => (string) config('filesystems.disks.r2.secret'),
                    ],
                    'http' => ['verify' => false],
                ]);
                $client->createBucket(['Bucket' => $bucket]);
                $this->info("✓ Bucket [{$bucket}] berhasil dibuat di Cloudflare R2!");
            } catch (Throwable $e) {
                $this->error("Gagal membuat bucket [{$bucket}]: " . $e->getMessage());
                $this->warn("Jika API Token dibatasi hanya pada operasi objek (Object Read & Write), silakan buat bucket [{$bucket}] secara manual di Cloudflare Dashboard menu R2 > Create bucket.");
            }
            return self::SUCCESS;
        }

        // 1. Connectivity test
        $this->comment('1. Testing connectivity to Cloudflare R2...');
        $testFile = 'diagnostics/health-check-' . time() . '.txt';
        $testContent = 'Muter-Muter App R2 Storage Health Check - ' . now()->toIso8601String();

        try {
            $r2 = Storage::disk('r2');
            $r2->put($testFile, $testContent);
            $readBack = $r2->get($testFile);

            if (trim((string) $readBack) !== trim($testContent)) {
                $this->error('Failed: Written content does not match read content on R2.');
                $this->line('Written: ' . $testContent);
                $this->line('Read   : ' . var_export($readBack, true));
                return self::FAILURE;
            }

            $r2->delete($testFile);
            $this->info('✓ Connectivity test passed: Successfully wrote, read, and deleted test object on Cloudflare R2!');
        } catch (Throwable $e) {
            $prevMsg = $e->getPrevious() ? ' (' . $e->getPrevious()->getMessage() . ')' : '';
            $this->error('✗ Connection to Cloudflare R2 failed: ' . $e->getMessage() . $prevMsg);
            $this->warn('Pastikan bucket [' . $bucket . '] sudah dibuat di Cloudflare Dashboard dan Access Key memiliki izin Read & Write (Object Read & Write).');
            return self::FAILURE;
        }

        if ($this->option('test-only')) {
            $this->newLine();
            $this->info('Test completed successfully. No files migrated.');
            return self::SUCCESS;
        }

        // 2. Migration of local files to R2
        $this->newLine();
        $this->comment('2. Scanning local public storage (storage/app/public)...');

        $local = Storage::disk('public');
        $allFiles = $local->allFiles();

        // Filter out system files or hidden files
        $filesToMigrate = array_filter($allFiles, function ($path) {
            return ! str_starts_with(basename($path), '.') && ! str_contains($path, '.gitignore');
        });

        $total = count($filesToMigrate);
        $this->line("Found {$total} files in local public storage.");

        if ($total === 0) {
            $this->info('No local files found to migrate.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $skipped = 0;
        $failed = 0;
        $overwrite = $this->option('force');

        foreach ($filesToMigrate as $file) {
            try {
                if (! $overwrite && $r2->exists($file)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $stream = $local->readStream($file);
                if ($stream) {
                    $r2->put($file, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $migrated++;
                } else {
                    $failed++;
                }
            } catch (Throwable $e) {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration Summary:");
        $this->line("  ✓ Successfully uploaded : {$migrated}");
        $this->line("  - Skipped (already exists): {$skipped}");
        if ($failed > 0) {
            $this->error("  ✗ Failed to upload       : {$failed}");
        }

        $this->newLine();
        $this->info('All files are synchronized with Cloudflare R2!');
        return self::SUCCESS;
    }
}
