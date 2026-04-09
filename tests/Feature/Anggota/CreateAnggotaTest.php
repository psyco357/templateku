<?php

use App\Models\Koperasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

test('creating anggota assigns current koperasi id to the new user', function () {
    $koperasi = Koperasi::create([
        'kode_koperasi' => 'KOP-001',
        'nama_koperasi' => 'Koperasi Uji',
        'status' => 'aktif',
    ]);

    $founder = User::create([
        'koperasi_id' => $koperasi->id,
        'username' => 'founder-anggota',
        'email' => 'founder-anggota@example.com',
        'password' => 'Password123!',
        'role' => User::ROLE_FOUNDER,
        'is_active' => true,
    ]);

    actingAs($founder);

    $response = post(route('anggota.store'), [
        'nama_lengkap' => 'Anggota Baru',
        'username' => 'anggota-baru',
        'email' => 'anggota-baru@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'no_anggota' => 'AGT-00999',
        'auto_generate_no_anggota' => '0',
        'jabatan' => 'anggota',
        'status_keanggotaan' => 'aktif',
    ]);

    $response->assertRedirect(route('anggota.index'));

    assertDatabaseHas('users', [
        'username' => 'anggota-baru',
        'email' => 'anggota-baru@example.com',
        'koperasi_id' => $koperasi->id,
        'role' => User::ROLE_ANGGOTA,
    ]);
});
