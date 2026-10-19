<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function stateful(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost',
            'Referer' => 'http://localhost/',
        ]);
    }

    public function test_reset_link_is_sent_for_registered_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.test']);

        $this->stateful()
            ->postJson('/api/forgot-password', ['email' => 'reset@example.test'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_link_requires_registered_email(): void
    {
        $this->stateful()
            ->postJson('/api/forgot-password', ['email' => 'missing@example.test'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test']);
        $token = Password::broker()->createToken($user);

        $this->stateful()
            ->postJson('/api/reset-password', [
                'token' => $token,
                'email' => 'reset@example.test',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertOk();

        $this->stateful()
            ->postJson('/api/login', [
                'email' => 'reset@example.test',
                'password' => 'PasswordBaru123',
            ])
            ->assertOk()
            ->assertJsonPath('user.email', 'reset@example.test');
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.test']);

        $this->stateful()
            ->postJson('/api/reset-password', [
                'token' => 'token-palsu',
                'email' => 'reset@example.test',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
