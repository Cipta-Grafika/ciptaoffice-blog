<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

DB::statement('CREATE SCHEMA IF NOT EXISTS ciptaoffice_e2e');
Artisan::call('migrate:fresh', ['--force' => true]);

User::create([
    'name' => 'WordPress Migration Tester',
    'email' => 'browser-test@ciptaoffice.test',
    'password' => Hash::make('Browser-test-password'),
    'email_verified_at' => now(),
    'role' => UserRole::Author,
    'is_active' => true,
]);

fwrite(STDOUT, "Isolated browser-test schema prepared.\n");
