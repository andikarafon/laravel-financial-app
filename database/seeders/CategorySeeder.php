<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Data kategori untuk Income (Pemasukan)
         $incomeCategories = [
            'Gaji Pokok',
            'Gaji Lembur',
            'Freelance',
            'Bonus Kinerja',
            'THR',
            'Investasi',
            'Dividen Saham',
            'Bunga Bank',
            'Bisnis Sampingan',
            'Jual Barang',
            'Hadiah',
            'Cashback',
            'Refund',
            'Lainnya'
        ];

        // Data kategori untuk Expense (Pengeluaran)
        $expenseCategories = [
            'Makanan & Minuman',
            'Transportasi',
            'Bensin/Solar',
            'Belanja Kebutuhan',
            'Shopping',
            'Tagihan Listrik',
            'Tagihan Air',
            'Tagihan Internet',
            'Tagihan Telepon',
            'Kesehatan',
            'Obat-obatan',
            'Pendidikan',
            'Hiburan',
            'Bioskop',
            'Travelling',
            'Olahraga',
            'Gym/Fitness',
            'Kecantikan',
            'Salon/Barbershop',
            'Investasi',
            'Asuransi',
            'Pajak',
            'Amal/Donasi',
            'Hadiah untuk Orang',
            'Perbaikan Rumah',
            'Servis Kendaraan',
            'Lainnya'
        ];

        foreach ($incomeCategories as $categoryName) {
            Category::create([
                'type' => 'income',
                'name' => $categoryName,
                'is_active' => true
            ]);
        }

        foreach ($expenseCategories as $categoryName) {
            Category::create([
                'type' => 'expense',
                'name' => $categoryName,
                'is_active' => true
            ]);
        }

    }
}
