<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 *
 * Cara pakai: php artisan db:seed
 * (otomatis terpanggil juga kalau pakai `php artisan migrate --seed`)
 *
 * Akun demo yang dibuat (password semua = "password"):
 *   admin        -> role: admin
 *   seller_demo  -> role: seller (sudah punya store + 3 produk)
 *   buyer_demo   -> role: buyer (wallet Rp 1.000.000, sudah ada alamat)
 *   driver_demo  -> role: driver
 *   multi_demo   -> role: seller + buyer (untuk demo role-selection)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles sudah dibuat otomatis lewat migration create_roles_table,
        // jadi di sini kita cukup ambil referensinya.
        $adminRole = Role::where('name', 'admin')->first();
        $sellerRole = Role::where('name', 'seller')->first();
        $buyerRole = Role::where('name', 'buyer')->first();
        $driverRole = Role::where('name', 'driver')->first();

        // 2. Buat user admin
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@seapedia.test',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($adminRole);

        // 3. Buat user seller + store + produk
        $seller = User::create([
            'username' => 'seller_demo',
            'email' => 'seller@seapedia.test',
            'password' => Hash::make('password'),
            'active_role' => 'seller',
        ]);
        $seller->roles()->attach($sellerRole);
        $seller->wallet()->create(['balance' => 0]);

        $store = $seller->store()->create([
            'name' => 'Toko Elektronik Jaya',
            'description' => 'Menjual berbagai perangkat elektronik dan aksesori.',
        ]);

        $store->products()->createMany([
            ['name' => 'Headphone Wireless X100', 'description' => 'Headphone bluetooth dengan noise cancelling.', 'price' => 350000, 'stock' => 25],
            ['name' => 'Power Bank 20000mAh', 'description' => 'Power bank fast charging 20W.', 'price' => 220000, 'stock' => 40],
            ['name' => 'Kabel USB-C 1.5m', 'description' => 'Kabel data dan charging USB-C ke USB-C.', 'price' => 45000, 'stock' => 100],
        ]);

        // 4. Buat user buyer + wallet + alamat
        $buyer = User::create([
            'username' => 'buyer_demo',
            'email' => 'buyer@seapedia.test',
            'password' => Hash::make('password'),
            'active_role' => 'buyer',
        ]);
        $buyer->roles()->attach($buyerRole);

        $wallet = $buyer->wallet()->create(['balance' => 1000000]);
        $wallet->transactions()->create([
            'type' => 'topup',
            'amount' => 1000000,
            'description' => 'Top-up awal demo akun buyer',
        ]);

        $buyer->addresses()->create([
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'full_address' => 'Jl. Merdeka No. 10, Soreang, Bandung Barat, Jawa Barat 40911',
            'is_default' => true,
        ]);

        // 5. Buat user driver
        $driver = User::create([
            'username' => 'driver_demo',
            'email' => 'driver@seapedia.test',
            'password' => Hash::make('password'),
            'active_role' => 'driver',
        ]);
        $driver->roles()->attach($driverRole);
        $driver->wallet()->create(['balance' => 0]);

        // 6. Buat user multi-role (seller + buyer) untuk demo role-selection
        $multi = User::create([
            'username' => 'multi_demo',
            'email' => 'multi@seapedia.test',
            'password' => Hash::make('password'),
        ]);
        $multi->roles()->attach([$sellerRole->id, $buyerRole->id]);

        $multiWallet = $multi->wallet()->create(['balance' => 500000]);
        $multiWallet->transactions()->create([
            'type' => 'topup',
            'amount' => 500000,
            'description' => 'Top-up awal demo akun multi-role',
        ]);

        // 7. Voucher & Promo demo
        \App\Models\Voucher::create([
            'code' => 'SEAPEDIA10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'max_discount' => 50000,
            'usage_limit' => 100,
            'expires_at' => now()->addDays(30),
        ]);

        \App\Models\Voucher::create([
            'code' => 'HEMAT20K',
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'usage_limit' => 50,
            'expires_at' => now()->addDays(30),
        ]);

        \App\Models\Promo::create([
            'name' => 'Promo Gajian',
            'discount_type' => 'percentage',
            'discount_value' => 5,
            'max_discount' => 25000,
            'expires_at' => now()->addDays(7),
        ]);

        // 8. Review demo
        \App\Models\Review::create([
            'user_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'Aplikasinya enak dipakai, checkout-nya cepat!',
        ]);

        \App\Models\Review::create([
            'guest_name' => 'Pengunjung',
            'rating' => 4,
            'comment' => 'Bagus, tapi loading produk agak lambat di awal.',
        ]);

        $this->command->info('Seeder selesai. Akun demo (password semua = "password"):');
        $this->command->table(
            ['Username', 'Role'],
            [
                ['admin', 'admin'],
                ['seller_demo', 'seller'],
                ['buyer_demo', 'buyer'],
                ['driver_demo', 'driver'],
                ['multi_demo', 'seller + buyer'],
            ]
        );
    }
}
