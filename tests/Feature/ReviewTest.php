<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function publicKos(?User $owner = null): Kos
    {
        $owner ??= User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);

        return Kos::factory()->create(['owner_id' => $owner->id, 'status' => Kos::STATUS_ACTIVE]);
    }

    public function test_guest_cannot_review(): void
    {
        $kos = $this->publicKos();

        $this->postJson("/api/kos/{$kos->slug}/reviews", ['rating' => 5])
            ->assertStatus(401);
    }

    public function test_student_can_create_review_and_rating_updates(): void
    {
        $kos = $this->publicKos();
        $student = User::factory()->create();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/reviews", ['rating' => 4, 'comment' => 'Bagus'])
            ->assertCreated()
            ->assertJsonPath('data.rating', 4);

        $detail = $this->getJson("/api/kos/{$kos->slug}")->assertOk();
        $this->assertEquals(4.0, (float) $detail->json('data.rating_avg'));
        $this->assertSame(1, $detail->json('data.reviews_count'));
    }

    public function test_duplicate_review_is_rejected(): void
    {
        $kos = $this->publicKos();
        $student = User::factory()->create();

        Review::create(['kos_id' => $kos->id, 'user_id' => $student->id, 'rating' => 5]);

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/reviews", ['rating' => 4])
            ->assertStatus(422)
            ->assertJsonValidationErrors('kos_id');
    }

    public function test_owner_cannot_review_own_kos(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
        $kos = $this->publicKos($owner);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/reviews", ['rating' => 5])
            ->assertStatus(403);
    }

    public function test_student_cannot_review_pending_kos(): void
    {
        $owner = User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
        $kos = Kos::factory()->pending()->create(['owner_id' => $owner->id]);
        $student = User::factory()->create();

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/kos/{$kos->slug}/reviews", ['rating' => 5])
            ->assertStatus(403);
    }

    public function test_hidden_reviews_are_excluded_from_rating(): void
    {
        $kos = $this->publicKos();
        $a = User::factory()->create();
        $b = User::factory()->create();

        Review::create(['kos_id' => $kos->id, 'user_id' => $a->id, 'rating' => 5]);
        Review::create(['kos_id' => $kos->id, 'user_id' => $b->id, 'rating' => 1, 'is_hidden' => true]);

        $detail = $this->getJson("/api/kos/{$kos->slug}")->assertOk();
        $this->assertEquals(5.0, (float) $detail->json('data.rating_avg'));
        $this->assertSame(1, $detail->json('data.reviews_count'));
    }
}
