<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('user can login using username', function () {
    $user = User::create([
        'username' => 'anggota01',
        'email' => 'anggota01@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = post(route('login.authenticate'), [
        'login' => $user->username,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('dashboard'));
    assertAuthenticatedAs($user);
});

test('user can login using email', function () {
    $user = User::create([
        'username' => 'anggota02',
        'email' => 'anggota02@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = post(route('login.authenticate'), [
        'login' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect(route('dashboard'));
    assertAuthenticatedAs($user);
});

test('login fails with invalid credentials', function () {
    User::create([
        'username' => 'anggota03',
        'email' => 'anggota03@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = from(route('login'))->post(route('login.authenticate'), [
        'login' => 'anggota03',
        'password' => 'salah-password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('login');
    assertGuest();
});

test('guest is redirected to login when opening dashboard', function () {
    $response = get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can open dashboard', function () {
    $user = User::create([
        'username' => 'anggota04',
        'email' => 'anggota04@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    $response = get(route('dashboard'));

    $response->assertOk();
});
