<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosListingTest extends TestCase
{
    use RefreshDatabase;

    protected function activeOwner(): User
    {
        return User::factory()->owner()->create(['is_verified' => true, 'is_active' => true]);
    }

    public function test_only_public_kos_are_listed(): void
    {
        $owner = $this->activeOwner();
        $visible = Kos::factory()->create([
            'owner_id' => $owner->id,
            'status' => Kos::STATUS_ACTIVE,
            'name' => 'Kos Tampil',
        ]);

        Kos::factory()->pending()->create(['owner_id' => $owner->id, 'name' => 'Kos Pending']);

        $unverified = User::factory()->owner()->create(['is_verified' => false]);
        Kos::factory()->create(['owner_id' => $unverified->id, 'name' => 'Kos Owner Belum Verifikasi']);

        $response = $this->getJson('/api/kos')->assertOk();

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains($visible->name));
        $this->assertFalse($names->contains('Kos Pending'));
        $this->assertFalse($names->contains('Kos Owner Belum Verifikasi'));
    }

    public function test_filter_by_type_and_price(): void
    {
        $owner = $this->activeOwner();
        Kos::factory()->create(['owner_id' => $owner->id, 'type' => Kos::TYPE_PUTRI, 'price_min' => 800_000]);
        Kos::factory()->create(['owner_id' => $owner->id, 'type' => Kos::TYPE_PUTRA, 'price_min' => 2_500_000]);

        $this->getJson('/api/kos?type=putri')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', Kos::TYPE_PUTRI);

        $this->getJson('/api/kos?price_max=1000000')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_facilities_and_availability(): void
    {
        $owner = $this->activeOwner();
        $wifi = Facility::create(['name' => 'WiFi', 'icon' => 'wifi']);
        $ac = Facility::create(['name' => 'AC', 'icon' => 'ac']);

        $withBoth = Kos::factory()->create(['owner_id' => $owner->id, 'available_rooms' => 2]);
        $withBoth->facilities()->sync([$wifi->id, $ac->id]);

        $full = Kos::factory()->full()->create(['owner_id' => $owner->id]);
        $full->facilities()->sync([$wifi->id]);

        $this->getJson("/api/kos?facilities[]={$wifi->id}&facilities[]={$ac->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $withBoth->id);

        $ids = collect($this->getJson('/api/kos?available=1')->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($withBoth->id));
        $this->assertFalse($ids->contains($full->id));
    }

    public function test_detail_returns_whatsapp_and_maps_links(): void
    {
        $owner = $this->activeOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'whatsapp' => '6281234567890']);

        $this->getJson("/api/kos/{$kos->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $kos->id)
            ->assertJsonPath('data.owner.name', $owner->name)
            ->assertJsonStructure(['data' => ['whatsapp_link', 'maps_link', 'photos', 'facilities']]);
    }

    public function test_non_public_detail_is_not_found(): void
    {
        $owner = $this->activeOwner();
        $pending = Kos::factory()->pending()->create(['owner_id' => $owner->id]);

        $this->getJson("/api/kos/{$pending->slug}")->assertNotFound();
    }

    public function test_wa_click_increments_counter(): void
    {
        $owner = $this->activeOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'wa_clicks' => 0]);

        $this->postJson("/api/kos/{$kos->slug}/wa-click")
            ->assertOk()
            ->assertJsonStructure(['whatsapp_link']);

        $this->assertSame(1, $kos->fresh()->wa_clicks);
    }
}
