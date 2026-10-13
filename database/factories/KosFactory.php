<?php

namespace Database\Factories;

use App\Models\Kos;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kos>
 */
class KosFactory extends Factory
{
    protected $model = Kos::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Kos '.fake()->randomElement(['Mawar', 'Melati', 'Anggrek', 'Sri Rahayu', 'Bu Ratna', 'Cempaka', 'Kenanga', 'Flamboyan']).' '.fake()->numberBetween(1, 40);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'type' => fake()->randomElement(Kos::TYPES),
            'description' => fake()->paragraphs(3, true),
            'price_min' => fake()->numberBetween(8, 40) * 100_000,
            'price_max' => null,
            'address' => fake()->streetAddress(),
            'district' => fake()->randomElement(['Depok', 'Sleman', 'Gondokusuman', 'Ngaglik', 'Seturan', 'Mlati', 'Babarsari']),
            'latitude' => fake()->latitude(-7.72, -7.73),
            'longitude' => fake()->longitude(110.36, 110.42),
            'whatsapp' => normalize_whatsapp(fake()->numerify('08##########')),
            'total_rooms' => fake()->numberBetween(3, 20),
            'available_rooms' => fn (array $attrs) => $attrs['total_rooms'] > 0 ? fake()->numberBetween(0, $attrs['total_rooms']) : 0,
            'rules' => fake()->randomElement([
                'Jam malam pukul 22.00. Tidak boleh membawa lawan jenis ke kamar.',
                'Dilarang merokok di dalam kamar.',
                null,
            ]),
            'status' => Kos::STATUS_ACTIVE,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => Kos::STATUS_PENDING]);
    }

    public function full(): static
    {
        return $this->state(fn (array $attrs) => ['available_rooms' => 0]);
    }
}
