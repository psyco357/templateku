<?php

namespace App\Http\Controllers;

use App\Models\AnggotaModel;
use App\Models\Koperasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->whereHas('anggota')
            ->with(['profile', 'anggota'])
            ->when($request->string('search')->toString() !== '', function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('nama_lengkap', 'like', "%{$search}%");
                        })
                        ->orWhereHas('anggota', function ($anggotaQuery) use ($search) {
                            $anggotaQuery
                                ->where('no_anggota', 'like', "%{$search}%")
                                ->orWhere('jabatan', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->whereHas('anggota', function ($anggotaQuery) use ($request) {
                    $anggotaQuery->where('status', $request->string('status')->toString());
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->string('role')->toString());
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.anggota.index', [
            'users' => $users,
            'availableRoles' => User::roles(),
            'availableStatuses' => AnggotaModel::statuses(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'role' => $request->string('role')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('pages.anggota.create', [
            'suggestedMemberNumber' => $this->generateMemberNumber(),
            'availableStatuses' => AnggotaModel::statuses(),
        ]);
    }

    public function show(User $user): View
    {
        $user->load(['profile', 'anggota']);

        abort_if($user->anggota === null, 404);

        return view('pages.anggota.show', [
            'user' => $user,
            'availableStatuses' => AnggotaModel::statuses(),
        ]);
    }

    public function generateMemberNumberResponse(): JsonResponse
    {
        return response()->json([
            'no_anggota' => $this->generateMemberNumber(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->resolveCurrentKoperasi();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'no_anggota' => ['nullable', 'string', 'max:255'],
            'auto_generate_no_anggota' => ['nullable', 'boolean'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'status_keanggotaan' => ['required', 'string', Rule::in(AnggotaModel::statuses())],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'bio' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'unique' => ':attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'nama_lengkap' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'password' => 'password',
            'no_anggota' => 'nomor anggota',
            'jabatan' => 'jabatan',
            'status_keanggotaan' => 'status keanggotaan',
            'no_hp' => 'nomor HP',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'bio' => 'bio',
        ]);

        $validated['no_anggota'] = $this->resolveMemberNumber(
            $validated['no_anggota'] ?? null,
            $request->boolean('auto_generate_no_anggota')
        );

        DB::transaction(function () use ($validated, $koperasi) {
            $user = User::create([
                'koperasi_id' => $koperasi->id,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_ANGGOTA,
                'is_active' => true,
            ]);

            $profile = $user->profile()->create([
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_hp' => $validated['no_hp'] ?? null,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            AnggotaModel::create([
                'profile_id' => $profile->id,
                'no_anggota' => $validated['no_anggota'],
                'jabatan' => $validated['jabatan'] ?? null,
                'status' => $validated['status_keanggotaan'],
            ]);
        });

        return redirect()->route('anggota.index')->with([
            'status' => 'Anggota baru berhasil ditambahkan.',
            'status_type' => 'success',
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $user->load(['profile', 'anggota']);

        abort_if($user->anggota === null, 404);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_anggota' => ['nullable', 'string', 'max:255'],
            'auto_generate_no_anggota' => ['nullable', 'boolean'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'status_keanggotaan' => ['required', 'string', Rule::in(AnggotaModel::statuses())],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'bio' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'unique' => ':attribute sudah digunakan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'nama_lengkap' => 'nama lengkap',
            'no_anggota' => 'nomor anggota',
            'jabatan' => 'jabatan',
            'status_keanggotaan' => 'status keanggotaan',
            'no_hp' => 'nomor HP',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'bio' => 'bio',
        ]);

        $validated['no_anggota'] = $this->resolveMemberNumber(
            $validated['no_anggota'] ?? null,
            $request->boolean('auto_generate_no_anggota'),
            $user->anggota
        );

        DB::transaction(function () use ($user, $validated) {
            $user->profile()->updateOrCreate([], [
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_hp' => $validated['no_hp'] ?? null,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            $user->anggota->update([
                'no_anggota' => $validated['no_anggota'],
                'jabatan' => $validated['jabatan'] ?? null,
                'status' => $validated['status_keanggotaan'],
            ]);
        });

        return redirect()->route('anggota.show', $user)->with([
            'status' => 'Detail anggota berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(User::roles())],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'role' => 'level user',
        ]);

        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User && $authenticatedUser->is($user)) {
            return back()->with([
                'status' => 'Role akun yang sedang dipakai tidak bisa diubah dari halaman ini.',
                'status_type' => 'info',
            ]);
        }

        if ($user->role === $validated['role']) {
            return back()->with([
                'status' => 'Role user sudah sesuai, tidak ada perubahan.',
                'status_type' => 'info',
            ]);
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        return back()->with([
            'status' => 'Role user berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    protected function resolveMemberNumber(?string $memberNumber, bool $autoGenerate, ?AnggotaModel $ignoreAnggota = null): string
    {
        $normalizedMemberNumber = $memberNumber !== null ? trim($memberNumber) : null;

        if ($normalizedMemberNumber === '' || $autoGenerate) {
            return $this->generateMemberNumber($ignoreAnggota);
        }

        $existingMemberQuery = AnggotaModel::query()->where('no_anggota', $normalizedMemberNumber);

        if ($ignoreAnggota instanceof AnggotaModel) {
            $existingMemberQuery->whereKeyNot($ignoreAnggota->getKey());
        }

        if ($existingMemberQuery->exists()) {
            throw ValidationException::withMessages([
                'no_anggota' => 'Nomor anggota sudah digunakan.',
            ]);
        }

        return $normalizedMemberNumber;
    }

    protected function generateMemberNumber(?AnggotaModel $ignoreAnggota = null): string
    {
        $lastMemberNumber = AnggotaModel::query()
            ->latest('id')
            ->value('no_anggota');

        $nextNumber = 1;

        if (is_string($lastMemberNumber) && preg_match('/(\d+)$/', $lastMemberNumber, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $generatedNumber = 'AGT-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            $nextNumber++;

            $memberNumberQuery = AnggotaModel::query()->where('no_anggota', $generatedNumber);

            if ($ignoreAnggota instanceof AnggotaModel) {
                $memberNumberQuery->whereKeyNot($ignoreAnggota->getKey());
            }
        } while ($memberNumberQuery->exists());

        return $generatedNumber;
    }

    protected function resolveCurrentKoperasi(): Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->firstOrFail();
    }
}
