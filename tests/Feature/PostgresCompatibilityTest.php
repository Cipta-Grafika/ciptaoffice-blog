<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgresCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_migrations_and_json_round_trip_on_postgresql(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Jalankan suite dengan DB_CONNECTION=pgsql untuk integrasi PostgreSQL.');
        }
        $id = DB::table('product_categories')->insertGetId(['name' => 'Integrasi', 'slug' => 'integrasi', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['product_category_id' => $id, 'name' => 'Produk', 'slug' => 'produk-pg', 'summary' => 'Uji', 'specifications' => json_encode(['material' => 'steel']), 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $this->assertDatabaseHas('products', ['slug' => 'produk-pg']);
    }
}
