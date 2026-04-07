<?php

namespace App\Http\Controllers;

use App\Models\Koperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use IntlCalendar;
use RuntimeException;

class KoperasiController extends Controller
{
    public function show(): View
    {
        $koperasi = $this->getPrimaryKoperasi();

        return view('pages.koperasi.show', [
            'koperasi' => $koperasi,
            'periodeBuku' => $koperasi->periodeBuku()->latest('tahun_buku')->get(),
            'hijriMonths' => $this->getHijriMonths(),
            'defaultHijriYearRange' => [
                'start' => $this->getCurrentHijriYear(),
                'end' => $this->getCurrentHijriYear() + 2,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $koperasi = $this->getPrimaryKoperasi();

        $validated = $request->validate([
            'kode_koperasi' => ['required', 'string', 'max:255', Rule::unique('koperasi', 'kode_koperasi')->ignore($koperasi->id)],
            'nama_koperasi' => ['required', 'string', 'max:255'],
            'nomor_badan_hukum' => ['nullable', 'string', 'max:255'],
            'tanggal_berdiri' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'ketua' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['aktif', 'nonaktif'])],
            'siklus_tutup_buku' => ['required', 'string', Rule::in(['tahunan'])],
            'tutup_buku_bulan_hijriah' => ['required', 'integer', 'between:1,12'],
            'tutup_buku_hari_hijriah' => ['required', 'integer', 'between:1,30'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'integer' => ':attribute harus berupa angka.',
            'between' => ':attribute di luar rentang yang diizinkan.',
            'unique' => ':attribute sudah digunakan.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'kode_koperasi' => 'kode koperasi',
            'nama_koperasi' => 'nama koperasi',
            'nomor_badan_hukum' => 'nomor badan hukum',
            'tanggal_berdiri' => 'tanggal berdiri',
            'email' => 'email',
            'telepon' => 'telepon',
            'alamat' => 'alamat',
            'ketua' => 'ketua',
            'status' => 'status',
            'siklus_tutup_buku' => 'siklus tutup buku',
            'tutup_buku_bulan_hijriah' => 'bulan tutup buku hijriah',
            'tutup_buku_hari_hijriah' => 'hari tutup buku hijriah',
        ]);

        $koperasi->update($validated);

        return redirect()->route('koperasi.show')->with([
            'status' => 'Master data koperasi berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function generatePeriodeBuku(Request $request): RedirectResponse
    {
        $koperasi = $this->getPrimaryKoperasi();

        $validated = $request->validate([
            'start_hijri_year' => ['required', 'integer', 'min:1300'],
            'end_hijri_year' => ['required', 'integer', 'gte:start_hijri_year', 'max:2000'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute terlalu kecil.',
            'max' => ':attribute terlalu besar.',
            'gte' => ':attribute harus lebih besar atau sama dengan tahun awal.',
        ], [
            'start_hijri_year' => 'tahun Hijriah awal',
            'end_hijri_year' => 'tahun Hijriah akhir',
        ]);

        $generatedCount = 0;

        for ($year = (int) $validated['start_hijri_year']; $year <= (int) $validated['end_hijri_year']; $year++) {
            $closeDate = $this->convertHijriToGregorian(
                $year,
                (int) $koperasi->tutup_buku_bulan_hijriah,
                (int) $koperasi->tutup_buku_hari_hijriah
            );
            $previousCloseDate = $this->convertHijriToGregorian(
                $year - 1,
                (int) $koperasi->tutup_buku_bulan_hijriah,
                (int) $koperasi->tutup_buku_hari_hijriah
            );
            $startDate = $previousCloseDate->copy()->addDay();

            $koperasi->periodeBuku()->updateOrCreate(
                ['tahun_buku' => $year],
                [
                    'tanggal_mulai' => $startDate->toDateString(),
                    'tanggal_selesai' => $closeDate->toDateString(),
                    'tanggal_tutup_buku' => $closeDate->toDateString(),
                    'tutup_buku_bulan_hijriah' => $koperasi->tutup_buku_bulan_hijriah,
                    'tutup_buku_hari_hijriah' => $koperasi->tutup_buku_hari_hijriah,
                    'status' => 'draft',
                    'catatan' => sprintf(
                        'Digenerate otomatis berdasarkan tutup buku %d %s %d H.',
                        $koperasi->tutup_buku_hari_hijriah,
                        $this->getHijriMonths()[$koperasi->tutup_buku_bulan_hijriah],
                        $year
                    ),
                ]
            );

            $generatedCount++;
        }

        return redirect()->route('koperasi.show')->with([
            'status' => "Periode buku berhasil digenerate untuk {$generatedCount} tahun Hijriah.",
            'status_type' => 'success',
        ]);
    }

    protected function getPrimaryKoperasi(): Koperasi
    {
        return Koperasi::query()->firstOrFail();
    }

    protected function getCurrentHijriYear(): int
    {
        $calendar = IntlCalendar::createInstance('UTC', 'id_ID@calendar=islamic-umalqura');

        if (! $calendar) {
            throw new RuntimeException('IntlCalendar kalender Hijriah tidak tersedia.');
        }

        return (int) $calendar->get(IntlCalendar::FIELD_YEAR);
    }

    protected function convertHijriToGregorian(int $hijriYear, int $hijriMonth, int $hijriDay): Carbon
    {
        $calendar = IntlCalendar::createInstance('UTC', 'id_ID@calendar=islamic-umalqura');

        if (! $calendar) {
            throw new RuntimeException('IntlCalendar kalender Hijriah tidak tersedia.');
        }

        $calendar->clear();
        $calendar->set($hijriYear, $hijriMonth - 1, $hijriDay, 0, 0, 0);

        return Carbon::createFromTimestampUTC((int) ($calendar->getTime() / 1000))->startOfDay();
    }

    protected function getHijriMonths(): array
    {
        return [
            1 => 'Muharram',
            2 => 'Safar',
            3 => 'Rabiul Awal',
            4 => 'Rabiul Akhir',
            5 => 'Jumadil Awal',
            6 => 'Jumadil Akhir',
            7 => 'Rajab',
            8 => 'Syaban',
            9 => 'Ramadan',
            10 => 'Syawal',
            11 => 'Zulkaidah',
            12 => 'Zulhijah',
        ];
    }
}
