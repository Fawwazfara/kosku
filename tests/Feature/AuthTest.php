<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function stateful(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
        ]);
    }

    public function test_student_can_register_and_is_auto_verified(): void
    {
        $response = $this->stateful()->postJson('/api/register', [
            'name' => 'Mhs Baru',
            'email' => 'mhs@example.test',
            'phone' => '081234567890',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
        ]);

        $response->assertCreated()->assertJsonPath('user.role', 'student');

        $user = User::where('email', 'mhs@example.test')->firstOrFail();
        $this->assertTrue($user->is_verified);
        $this->assertTrue($user->isStudent());
        $this->assertSame('6281234567890', $user->phone);
    }

    public function test_owner_registration_is_unverified(): void
    {
        $response = $this->stateful()->postJson('/api/register', [
            'name' => 'Pemilik Baru',
            'email' => 'pemilik@example.test',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'owner',
        ]);

        $response->assertCreated();
        $this->assertFalse($response->json('user.is_verified'));
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'login@example.test']);
        $this->stateful();

        $this->postJson('/api/login', [
            'email' => 'login@example.test',
            'password' => 'password',
        ])->assertOk()->assertJsonPath('user.email', 'login@example.test');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/logout')
            ->assertOk();
    }

    public function test_login_with_wrong_credentials_fails(): void
    {
        User::factory()->create(['email' => 'login@example.test']);
        $this->stateful();

        $this->postJson('/api/login', [
            'email' => 'login@example.test',
            'password' => 'salah',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_guest_is_unauthorized_as_json(): void
    {
        $this->getJson('/api/user')
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_authenticated_user_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }
}
