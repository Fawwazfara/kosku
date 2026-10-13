<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private function publicKos(): Kos
    {
        $owner = User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);

        return Kos::factory()->create(['owner_id' => $owner->id, 'status' => Kos::STATUS_ACTIVE]);
    }

    public function test_student_can_toggle_favorite(): void
    {
        $kos = $this->publicKos();
        $student = User::factory()->create();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/favorite")
            ->assertCreated()
            ->assertJsonPath('is_favorite', true);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/favorites')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($student, 'sanctum')
            ->deleteJson("/api/kos/{$kos->slug}/favorite")
            ->assertOk()
            ->assertJsonPath('is_favorite', false);

        $this->assertSame(0, $student->favorites()->count());
    }

    public function test_cannot_favorite_non_public_kos(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
        $kos = Kos::factory()->pending()->create(['owner_id' => $owner->id]);
        $student = User::factory()->create();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/favorite")
            ->assertNotFound();
    }

    public function test_guest_cannot_access_favorites(): void
    {
        $this->getJson('/api/favorites')->assertStatus(401);
    }
}
