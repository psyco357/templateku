<?php

namespace App\Http\Controllers;

use App\Models\AkunKeuangan;
use App\Models\AkunKeuanganMapping;
use App\Models\Koperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KeuanganMappingController extends Controller
{
    public function index(): View
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $templates = collect(AkunKeuanganMapping::templates())->groupBy('module');
        $mappings = AkunKeuanganMapping::query()
            ->with('akun')
            ->where('koperasi_id', $koperasi->id)
            ->get()
            ->keyBy('mapping_key');

        $akunOptions = AkunKeuangan::query()
            ->where('koperasi_id', $koperasi->id)
            ->where('status', AkunKeuangan::STATUS_AKTIF)
            ->where('can_post', true)
            ->orderBy('kode_akun')
            ->get();

        return view('pages.keuangan.mapping.index', [
            'koperasi' => $koperasi,
            'templates' => $templates,
            'mappings' => $mappings,
            'akunOptions' => $akunOptions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $koperasi = $this->getActiveKoperasi();
        abort_unless($koperasi, 404);

        $templateKeys = collect(AkunKeuanganMapping::templates())->pluck('key')->all();

        $validated = $request->validate([
            'mappings' => ['required', 'array'],
            'mappings.*' => ['nullable', Rule::exists('akun_keuangan', 'id')->where(fn($query) => $query
                ->where('koperasi_id', $koperasi->id)
                ->where('status', AkunKeuangan::STATUS_AKTIF)
                ->where('can_post', true))],
        ], [
            'required' => ':attribute wajib diisi.',
            'array' => ':attribute harus berupa daftar.',
            'exists' => ':attribute yang dipilih tidak valid.',
        ], [
            'mappings' => 'mapping akun',
            'mappings.*' => 'akun mapping',
        ]);

        foreach ($templateKeys as $mappingKey) {
            AkunKeuanganMapping::query()->updateOrCreate(
                [
                    'koperasi_id' => $koperasi->id,
                    'mapping_key' => $mappingKey,
                ],
                [
                    'akun_keuangan_id' => $validated['mappings'][$mappingKey] ?? null,
                ]
            );
        }

        return redirect()->route('keuangan.mapping.index')->with([
            'status' => 'Mapping akun default berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    protected function getActiveKoperasi(): ?Koperasi
    {
        return Auth::user()?->koperasi ?? Koperasi::query()->first();
    }
}
