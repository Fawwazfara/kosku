<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerAreaTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
    }

    public function test_student_cannot_access_owner_area(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/owner/dashboard')
            ->assertStatus(403);
    }

    public function test_owner_can_access_dashboard(): void
    {
        $this->actingAs($this->owner(), 'sanctum')
            ->getJson('/api/owner/dashboard')
            ->assertOk()
            ->assertJsonStructure(['summary']);
    }

    public function test_owner_can_create_kos_with_normalized_whatsapp(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner, 'sanctum')
            ->postJson('/api/owner/kos', [
                'name' => 'Kos Baru Uji',
                'type' => 'campur',
                'description' => 'Deskripsi kos uji.',
                'price_min' => 900_000,
                'address' => 'Jl. Uji No. 1',
                'district' => 'Depok',
                'latitude' => -7.75,
                'longitude' => 110.4,
                'whatsapp' => '081234567890',
                'total_rooms' => 5,
                'available_rooms' => 5,
            ])
            ->assertCreated()
            ->assertJsonPath('kos.status', Kos::STATUS_PENDING);

        $kos = Kos::findOrFail($response->json('kos.id'));
        $this->assertSame('6281234567890', $kos->whatsapp);
        $this->assertSame($owner->id, $kos->owner_id);
    }

    public function test_owner_cannot_manage_another_owners_kos(): void
    {
        $ownerA = $this->owner();
        $ownerB = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $ownerA->id]);

        $this->actingAs($ownerB, 'sanctum')
            ->getJson("/api/owner/kos/{$kos->id}")
            ->assertStatus(403);

        $this->actingAs($ownerB, 'sanctum')
            ->deleteJson("/api/owner/kos/{$kos->id}")
            ->assertStatus(403);
    }

    public function test_stock_update_respects_total_rooms(): void
    {
        $owner = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'total_rooms' => 5, 'available_rooms' => 5]);

        $this->actingAs($owner, 'sanctum')
            ->putJson("/api/owner/kos/{$kos->id}/stock", ['available_rooms' => 3])
            ->assertOk();

        $this->assertSame(3, $kos->fresh()->available_rooms);

        $this->actingAs($owner, 'sanctum')
            ->putJson("/api/owner/kos/{$kos->id}/stock", ['available_rooms' => 99])
            ->assertStatus(422)
            ->assertJsonValidationErrors('available_rooms');
    }

    public function test_owner_can_delete_own_kos(): void
    {
        $owner = $this->owner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/owner/kos/{$kos->id}")
            ->assertOk();

        $this->assertDatabaseMissing('kos', ['id' => $kos->id]);
    }
}
