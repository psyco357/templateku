<?php

use App\Models\User;
use App\Notifications\QueuedResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

test('founder reset password action queues reset notification', function () {
    Notification::fake();

    $founder = User::create([
        'username' => 'founder01',
        'email' => 'founder01@example.com',
        'password' => 'Password123!',
        'role' => User::ROLE_FOUNDER,
        'is_active' => true,
    ]);

    $user = User::create([
        'username' => 'anggota01',
        'email' => 'anggota01@example.com',
        'password' => 'Password123!',
        'role' => User::ROLE_ANGGOTA,
        'is_active' => true,
    ]);

    actingAs($founder);

    $response = patch(route('accounts.reset-password', $user));

    $response->assertRedirect(route('accounts.show', $user));
    $response->assertSessionHas('status_type', 'success');

    assertDatabaseHas('password_reset_tokens', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, QueuedResetPasswordNotification::class);
    expect(new QueuedResetPasswordNotification('token'))->toBeInstanceOf(ShouldQueue::class);
});
