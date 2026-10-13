<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FacilitySeeder::class);

        $admin = User::updateOrCreate(['email' => 'admin@kosku.test'], [
            'name' => 'Admin KosKu',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_verified' => true,
            'is_active' => true,
            'phone' => '628000000001',
        ]);

        $owner = User::updateOrCreate(['email' => 'pemilik@kosku.test'], [
            'name' => 'Mbak Sri Rahayu',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OWNER,
            'is_verified' => true,
            'is_active' => true,
            'phone' => '628111111111',
        ]);

        $ownerBaru = User::updateOrCreate(['email' => 'pemilik.baru@kosku.test'], [
            'name' => 'Pak Budi (baru daftar)',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OWNER,
            'is_verified' => false,
            'is_active' => true,
            'phone' => '628222222222',
        ]);

        $mahasiswa = User::updateOrCreate(['email' => 'mahasiswa@test.com'], [
            'name' => 'Rani Mahasiswa',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'is_verified' => true,
            'is_active' => true,
            'phone' => '628333333333',
        ]);

        $mahasiswaLain = User::factory()->count(3)->create([
            'phone' => normalize_whatsapp(fake()->numerify('08##########')),
        ]);

        $files = glob(database_path('seeders/photos/*.jpg')) ?: [];

        $buatKos = fn (string $nama, array $overrides = []) => Kos::create([
            'owner_id' => $owner->id,
            'name' => $nama,
            'slug' => Str::slug($nama),
            'type' => 'putri',
            'description' => 'Kos nyaman dekat kampus, lingkungan aman dan strategis. Kamar berukuran 3x4 meter dengan jendela menghadap taman. Dekat dengan warung makan, minimarket, dan halte yang membuat aktivitas kuliah tetap praktis setiap hari.',
            'price_min' => 1200000,
            'price_max' => 1800000,
            'address' => fake()->streetAddress(),
            'district' => 'Seturan',
            'latitude' => -7.7265 + (fake()->boolean() ? 0.004 : -0.004),
            'longitude' => 110.4096 + (fake()->boolean() ? 0.004 : -0.004),
            'whatsapp' => '628111111111',
            'total_rooms' => 8,
            'available_rooms' => 3,
            'rules' => 'Jam malam pukul 22.00. Dilarang merokok di dalam kamar. Tamu lawan jenis hanya di ruang tamu.',
            'status' => Kos::STATUS_ACTIVE,
            ...$overrides,
        ]);

        $daftarKos = [
            $buatKos('Kos Melati Asri Seturan'),
            $buatKos('Kos Putri Anggrek Babarsari', ['latitude' => -7.7725, 'longitude' => 110.4172, 'district' => 'Babarsari', 'price_min' => 900000, 'available_rooms' => 5, 'type' => 'putri']),
            $buatKos('Kos Putra Gondomanan Sleman', ['latitude' => -7.7099, 'longitude' => 110.3383, 'district' => 'Sleman', 'price_min' => 850000, 'type' => 'putra', 'total_rooms' => 12, 'available_rooms' => 4]),
            $buatKos('Kos Campur Mlati Jalan Kaliurang', ['latitude' => -7.7542, 'longitude' => 110.3745, 'district' => 'Mlati', 'type' => 'campur', 'price_min' => 1300000, 'price_max' => 1500000]),
            $buatKos('Kos Putri Dewi Sartika Ngaglik', ['latitude' => -7.7445, 'longitude' => 110.3832, 'district' => 'Ngaglik', 'price_min' => 1500000, 'available_rooms' => 0, 'total_rooms' => 6]),
            $buatKos('Kos Condongcatur Depok Raya', ['latitude' => -7.7573, 'longitude' => 110.4033, 'district' => 'Depok', 'type' => 'putra', 'price_min' => 1000000, 'total_rooms' => 10, 'available_rooms' => 2]),
            $buatKos('Kos Menunggu Verifikasi UTY', ['status' => Kos::STATUS_PENDING, 'latitude' => -7.7470, 'longitude' => 110.3544, 'district' => 'Depok']),
        ];

        $fasilitas = Facility::pluck('id')->all();

        foreach ($daftarKos as $i => $kos) {
            shuffle($fasilitas);
            $kos->facilities()->sync(array_slice($fasilitas, 0, fake()->numberBetween(4, 8)));

            $fotoKos = fake()->randomElements($files, min(4, count($files)));
            foreach ($fotoKos as $j => $path) {
                $stored = "kos/{$kos->id}/".basename($path);

                Storage::disk('public')->put($stored, file_get_contents($path));

                $kos->photos()->create([
                    'path' => $stored,
                    'is_primary' => $j === 0,
                    'sort_order' => $j,
                ]);
            }
        }

        foreach ($daftarKos as $kos) {
            foreach ([$mahasiswa, ...$mahasiswaLain] as $user) {
                if (fake()->boolean(65)) {
                    Review::create([
                        'kos_id' => $kos->id,
                        'user_id' => $user->id,
                        'rating' => fake()->numberBetween(3, 5),
                        'comment' => fake()->randomElement([
                            'Kamar bersih, pemilik kos ramah. Worth it buat mahasiswa.',
                            'Lokasi dekat kampus, jalan kaki 5 menit. Toilet dalam.',
                            'Alhamdulillah nyaman, air lancar. Paling dekat ke warung makan.',
                            'Harga sebanding dengan fasilitas. Recommended.',
                        ]),
                        'is_hidden' => false,
                    ]);
                }
            }
        }

        $kosPendingBaru = Kos::create([
            'owner_id' => $ownerBaru->id,
            'name' => 'Kos Pak Budi Ringroad Utara',
            'slug' => Str::slug('Kos Pak Budi Ringroad Utara'),
            'type' => 'campur',
            'description' => 'Kos keluarga 6 kamar, cocok untuk mahasiswa yang ingin suasana tenang. Tersedia musala dan dapur bersama.',
            'price_min' => 1100000,
            'address' => 'Ringroad Utara No. 12',
            'district' => 'Condongcatur',
            'latitude' => -7.7548,
            'longitude' => 110.3987,
            'whatsapp' => '628222222222',
            'total_rooms' => 6,
            'available_rooms' => 6,
            'rules' => 'Jam malam 23.00.',
            'status' => Kos::STATUS_PENDING,
        ]);

        shuffle($fasilitas);
        $kosPendingBaru->facilities()->sync(array_slice($fasilitas, 0, 5));

        if (count($files)) {
            $path = $files[0];
            $stored = "kos/{$kosPendingBaru->id}/".basename($path);
            Storage::disk('public')->put($stored, file_get_contents($path));
            $kosPendingBaru->photos()->create(['path' => $stored, 'is_primary' => true, 'sort_order' => 0]);
        }

        $reviewUji = Review::updateOrCreate(
            ['kos_id' => $daftarKos[0]->id, 'user_id' => $mahasiswa->id],
            [
                'rating' => 5,
                'comment' => 'Sudah 1 semester di sini, aman dan nyaman. Pemilik cepat tanggap.',
                'is_hidden' => false,
            ],
        );

        $reviewTersembunyi = Review::updateOrCreate(
            ['kos_id' => $daftarKos[1]->id, 'user_id' => $mahasiswaLain[0]->id],
            [
                'rating' => 1,
                'comment' => 'Ulasan promosi tidak relevan (contoh untuk moderasi admin).',
                'is_hidden' => true,
            ],
        );

        $mahasiswa->favorites()->syncWithoutDetaching([$daftarKos[0]->id, $daftarKos[2]->id]);
    }
}
