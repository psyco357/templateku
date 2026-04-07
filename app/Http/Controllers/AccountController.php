<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('profile')
            ->when($request->string('search')->toString() !== '', function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('nama_lengkap', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->string('role')->toString());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.accounts.index', [
            'users' => $users,
            'availableRoles' => User::roles(),
            'filters' => [
                'search' => $request->string('search')->toString(),
                'role' => $request->string('role')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function show(User $user): View
    {
        $user->load('profile');

        return view('pages.accounts.show', [
            'user' => $user,
            'availableRoles' => User::roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(User::roles())],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah digunakan.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'nama_lengkap' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'role' => 'level user',
        ]);

        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User && $authenticatedUser->is($user) && $validated['role'] !== $user->role) {
            return back()->with([
                'status' => 'Role akun yang sedang dipakai tidak bisa diubah dari halaman ini.',
                'status_type' => 'info',
            ]);
        }

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        $user->profile()->updateOrCreate([], [
            'nama_lengkap' => $validated['nama_lengkap'],
        ]);

        return redirect()->route('accounts.show', $user)->with([
            'status' => 'Detail akun berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User && $authenticatedUser->is($user)) {
            return redirect()->route('accounts.show', $user)->with([
                'status' => 'Status akun yang sedang dipakai tidak bisa diubah.',
                'status_type' => 'info',
            ]);
        }

        $user->update([
            'is_active' => ! (bool) $user->is_active,
        ]);

        return redirect()->route('accounts.show', $user)->with([
            'status' => $user->is_active
                ? 'Akun berhasil diaktifkan.'
                : 'Akun berhasil dinonaktifkan.',
            'status_type' => 'success',
        ]);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()->route('accounts.show', $user)->with([
                'status' => __($status),
                'status_type' => 'info',
            ]);
        }

        return redirect()->route('accounts.show', $user)->with([
            'status' => "Link reset password berhasil dikirim ke email {$user->email}.",
            'status_type' => 'success',
        ]);
    }
}
