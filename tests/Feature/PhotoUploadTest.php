<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
    }

    private function fakePhoto(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 120, 'image/jpeg');
    }

    public function test_owner_can_upload_photos_up_to_the_limit(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        $photos = collect(range(1, Kos::MAX_PHOTOS))
            ->map(fn (int $i) => $this->fakePhoto("foto-{$i}.jpg"))
            ->all();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/owner/kos/{$kos->id}/photos", ['photos' => $photos])
            ->assertCreated();

        $this->assertSame(Kos::MAX_PHOTOS, $kos->photos()->count());
        $this->assertTrue($kos->photos()->where('is_primary', true)->exists());
    }

    public function test_owner_cannot_upload_beyond_the_limit(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        foreach (range(1, Kos::MAX_PHOTOS) as $i) {
            $kos->photos()->create([
                'path' => "kos/{$kos->id}/foto-{$i}.jpg",
                'is_primary' => $i === 1,
                'sort_order' => $i,
            ]);
        }

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/owner/kos/{$kos->id}/photos", [
                'photos' => [$this->fakePhoto('extra.jpg')],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('photos');

        $this->assertSame(Kos::MAX_PHOTOS, $kos->photos()->count());
    }

    public function test_upload_is_capped_at_remaining_slots(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        foreach (range(1, Kos::MAX_PHOTOS - 1) as $i) {
            $kos->photos()->create([
                'path' => "kos/{$kos->id}/foto-{$i}.jpg",
                'is_primary' => $i === 1,
                'sort_order' => $i,
            ]);
        }

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/owner/kos/{$kos->id}/photos", [
                'photos' => [$this->fakePhoto('a.jpg'), $this->fakePhoto('b.jpg')],
            ])
            ->assertStatus(422);

        $this->assertSame(Kos::MAX_PHOTOS - 1, $kos->photos()->count());
    }
}
