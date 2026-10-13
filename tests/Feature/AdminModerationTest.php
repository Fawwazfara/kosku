<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Kos;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_student_cannot_access_admin_area(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_admin_can_verify_owner(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => false]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/owners/{$owner->id}/verify")
            ->assertOk();

        $this->assertTrue($owner->fresh()->is_verified);
    }

    public function test_admin_can_approve_pending_kos(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => true]);
        $kos = Kos::factory()->pending()->create(['owner_id' => $owner->id]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/kos/{$kos->id}/approve")
            ->assertOk();

        $this->assertSame(Kos::STATUS_ACTIVE, $kos->fresh()->status);
    }

    public function test_admin_can_hide_and_unhide_review(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => true]);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $review = Review::create([
            'kos_id' => $kos->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 3,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/reviews/{$review->id}/hide")
            ->assertOk();
        $this->assertTrue($review->fresh()->is_hidden);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/reviews/{$review->id}/unhide")
            ->assertOk();
        $this->assertFalse($review->fresh()->is_hidden);
    }

    public function test_admin_can_manage_facilities(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/facilities', ['name' => 'Dapur', 'icon' => 'kitchen'])
            ->assertCreated();

        $facility = Facility::findOrFail($response->json('facility.id'));

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/facilities/{$facility->id}", ['name' => 'Dapur Umum', 'icon' => 'kitchen'])
            ->assertOk();
        $this->assertSame('Dapur Umum', $facility->fresh()->name);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/facilities/{$facility->id}")
            ->assertOk();
        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
    }

    public function test_admin_can_toggle_user_active(): void
    {
        $admin = $this->admin();
        $student = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/users/{$student->id}/toggle-active")
            ->assertOk();

        $this->assertFalse($student->fresh()->is_active);
    }
}
