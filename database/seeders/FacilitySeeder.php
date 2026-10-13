<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $fasilitas = [
            'AC' => 'ac',
            'WiFi' => 'wifi',
            'Kamar mandi dalam' => 'bathroom',
            'Kasur' => 'bed',
            'Lemari pakaian' => 'wardrobe',
            'Meja belajar' => 'desk',
            'Kursi' => 'chair',
            'Kipas angin' => 'fan',
            'Air panas' => 'shower',
            'Dapur bersama' => 'kitchen',
            'Kulkas bersama' => 'fridge',
            'Tempat parkir motor' => 'parking',
            'Parkir mobil' => 'car',
            'Jemuran' => 'laundry',
            'CCTV' => 'cctv',
            'Ruang tamu' => 'sofa',
            'Penjaga kos' => 'security',
            'Laundry kiloan' => 'washing',
        ];

        foreach ($fasilitas as $nama => $icon) {
            Facility::updateOrCreate(['name' => $nama], ['icon' => $icon]);
        }
    }
}
