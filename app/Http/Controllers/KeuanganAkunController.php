<?php

namespace App\Http\Controllers;

use App\Models\AkunKeuangan;
use App\Models\Koperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KeuanganAkunController extends Controller
{
    public function index(Request $request): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $filters = [
            'tipe_akun' => $request->string('tipe_akun')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $akun = AkunKeuangan::query()
            ->with('parent')
            ->where('koperasi_id', $koperasi->id)
            ->when($filters['tipe_akun'] !== '', fn($query) => $query->where('tipe_akun', $filters['tipe_akun']))
            ->when($filters['status'] !== '', fn($query) => $query->where('status', $filters['status']))
            ->orderBy('kode_akun')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = AkunKeuangan::query()->where('koperasi_id', $koperasi->id);
        $editAccountId = $request->integer('edit');
        $editAccount = $editAccountId
            ? AkunKeuangan::query()->where('koperasi_id', $koperasi->id)->find($editAccountId)
            : null;
        $parentOptions = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->orderBy('kode_akun')
            ->get()
            ->when($editAccount !== null, fn($collection) => $collection->where('id', '!=', $editAccount->id)->values());

        return view('pages.keuangan.akun.index', [
            'koperasi' => $koperasi,
            'akun' => $akun,
            'editAccount' => $editAccount,
            'filters' => $filters,
            'parentOptions' => $parentOptions,
            'tipeOptions' => AkunKeuangan::tipeOptions(),
            'statusOptions' => AkunKeuangan::statusOptions(),
            'summary' => [
                'total_akun' => $summaryQuery->count(),
                'akun_posting' => (clone $summaryQuery)->where('can_post', true)->count(),
                'akun_header' => (clone $summaryQuery)->where('is_header', true)->count(),
                'akun_aktif' => (clone $summaryQuery)->where('status', AkunKeuangan::STATUS_AKTIF)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $validated = $this->validateAkunRequest($request, $koperasi);
        $parent = $this->resolveParentAccount($koperasi, $validated['parent_id'] ?? null);
        $isHeader = $request->boolean('is_header');

        AkunKeuangan::query()->create([
            'koperasi_id' => $koperasi->id,
            'parent_id' => $parent?->id,
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'tipe_akun' => $validated['tipe_akun'],
            'level' => $parent ? min($parent->level + 1, 9) : 1,
            'is_header' => $isHeader,
            'can_post' => !$isHeader,
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('keuangan.akun.index')->with([
            'status' => 'Akun keuangan berhasil ditambahkan.',
            'status_type' => 'success',
        ]);
    }

    public function update(Request $request, AkunKeuangan $akun): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        abort_unless((int) $akun->koperasi_id === (int) $koperasi->id, 404);

        $validated = $this->validateAkunRequest($request, $koperasi, $akun);
        $parent = $this->resolveParentAccount($koperasi, $validated['parent_id'] ?? null, $akun);
        $isHeader = $request->boolean('is_header');

        $akun->update([
            'parent_id' => $parent?->id,
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'tipe_akun' => $validated['tipe_akun'],
            'level' => $parent ? min($parent->level + 1, 9) : 1,
            'is_header' => $isHeader,
            'can_post' => !$isHeader,
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('keuangan.akun.index')->with([
            'status' => 'Akun keuangan berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function deactivate(AkunKeuangan $akun): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);
        abort_unless((int) $akun->koperasi_id === (int) $koperasi->id, 404);

        if ($akun->status === AkunKeuangan::STATUS_NONAKTIF) {
            return redirect()->route('keuangan.akun.index')->with([
                'status' => 'Akun tersebut sudah nonaktif.',
                'status_type' => 'warning',
            ]);
        }

        if ($akun->children()->where('status', AkunKeuangan::STATUS_AKTIF)->exists()) {
            return redirect()->route('keuangan.akun.index')->with([
                'status' => 'Akun tidak bisa dinonaktifkan karena masih memiliki akun turunan yang aktif.',
                'status_type' => 'warning',
            ]);
        }

        $akun->update([
            'status' => AkunKeuangan::STATUS_NONAKTIF,
        ]);

        return redirect()->route('keuangan.akun.index')->with([
            'status' => 'Akun keuangan berhasil dinonaktifkan.',
            'status_type' => 'success',
        ]);
    }

    protected function validateAkunRequest(Request $request, Koperasi $koperasi, ?AkunKeuangan $akun = null): array
    {
        return $request->validate([
            'kode_akun' => [
                'required',
                'string',
                'max:30',
                Rule::unique('akun_keuangan', 'kode_akun')
                    ->where(fn($query) => $query->where('koperasi_id', $koperasi->id))
                    ->ignore($akun?->id),
            ],
            'nama_akun' => ['required', 'string', 'max:255'],
            'tipe_akun' => ['required', Rule::in(AkunKeuangan::tipeOptions())],
            'parent_id' => ['nullable', Rule::exists('akun_keuangan', 'id')->where(fn($query) => $query->where('koperasi_id', $koperasi->id))],
            'status' => ['required', Rule::in(AkunKeuangan::statusOptions())],
            'is_header' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute terlalu panjang.',
            'unique' => ':attribute sudah digunakan pada koperasi ini.',
            'in' => ':attribute yang dipilih tidak valid.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'boolean' => ':attribute harus bernilai benar atau salah.',
        ], [
            'kode_akun' => 'kode akun',
            'nama_akun' => 'nama akun',
            'tipe_akun' => 'tipe akun',
            'parent_id' => 'akun induk',
            'status' => 'status',
            'is_header' => 'header akun',
            'keterangan' => 'keterangan',
        ]);
    }

    protected function resolveParentAccount(Koperasi $koperasi, mixed $parentId, ?AkunKeuangan $akun = null): ?AkunKeuangan
    {
        if (empty($parentId)) {
            return null;
        }

        $parent = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->findOrFail($parentId);

        abort_if($akun && (int) $parent->id === (int) $akun->id, 422, 'Akun induk tidak boleh sama dengan akun yang sedang diubah.');

        return $parent;
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }
}
