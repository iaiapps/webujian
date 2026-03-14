<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreditPackageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Paket 1 Kredit',
                'credit_amount' => 1,
                'bonus_credits' => 0,
                'price' => 5000,
                'description' => 'Paket uji coba untuk mencoba fitur kredit',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Paket 5 Kredit',
                'credit_amount' => 5,
                'bonus_credits' => 1,
                'price' => 25000,
                'description' => 'Paket dasar untuk pemula',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Paket 10 Kredit',
                'credit_amount' => 10,
                'bonus_credits' => 2,
                'price' => 50000,
                'description' => 'Paket populer dengan bonus 2 kredit',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Paket 25 Kredit',
                'credit_amount' => 25,
                'bonus_credits' => 5,
                'price' => 125000,
                'description' => 'Paket hemat dengan bonus 5 kredit',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Paket 50 Kredit',
                'credit_amount' => 50,
                'bonus_credits' => 10,
                'price' => 250000,
                'description' => 'Paket besar dengan bonus 10 kredit',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($packages as $package) {
            CreditPackage::create($package);
        }
    }
}
