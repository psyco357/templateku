<?php

namespace App\Services;

use App\Models\AkunKeuangan;
use App\Models\AkunKeuanganMapping;
use App\Models\Angsuran;
use App\Models\AsetKoperasi;
use App\Models\JurnalUmum;
use App\Models\Koperasi;
use App\Models\PeriodeBuku;
use App\Models\Pinjaman;
use App\Models\ShuSkema;
use App\Models\ShuPayment;
use App\Models\Simpanan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JournalPostingService
{
    public function syncSimpanan(Simpanan $simpanan, ?int $userId = null): void
    {
        $sourceReference = 'simpanan:' . $simpanan->id;

        if ($simpanan->status !== Simpanan::STATUS_POSTED) {
            $this->deleteAutomaticJournal((int) $simpanan->koperasi_id, $sourceReference);

            return;
        }

        $amount = round(abs((float) $simpanan->jumlah), 2);

        if ($amount <= 0) {
            $this->deleteAutomaticJournal((int) $simpanan->koperasi_id, $sourceReference);

            return;
        }

        $isSetoran = (float) $simpanan->jumlah >= 0;
        $mappingKeys = ['simpanan_setoran_debit', 'simpanan_setoran_kredit'];
        $accounts = $this->resolveMappedAccounts((int) $simpanan->koperasi_id, $mappingKeys);

        $debitAccount = $isSetoran ? $accounts['simpanan_setoran_debit'] : $accounts['simpanan_setoran_kredit'];
        $creditAccount = $isSetoran ? $accounts['simpanan_setoran_kredit'] : $accounts['simpanan_setoran_debit'];
        $anggotaName = $simpanan->anggota?->profile?->nama_lengkap ?? 'anggota';
        $jenisName = $simpanan->jenisSimpanan?->nama_jenis ?? 'simpanan';
        $verb = $isSetoran ? 'Setoran' : 'Penarikan';

        $this->upsertAutomaticJournal(
            koperasiId: (int) $simpanan->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $simpanan->tanggal_transaksi,
            keterangan: sprintf('%s %s oleh %s (%s).', $verb, $jenisName, $anggotaName, $simpanan->no_bukti),
            lines: [
                [
                    'akun_keuangan_id' => $debitAccount->id,
                    'debit' => $amount,
                    'kredit' => 0,
                    'uraian' => sprintf('%s %s', $verb, $jenisName),
                ],
                [
                    'akun_keuangan_id' => $creditAccount->id,
                    'debit' => 0,
                    'kredit' => $amount,
                    'uraian' => sprintf('%s %s', $verb, $jenisName),
                ],
            ],
            userId: $userId,
            periodeBukuId: $simpanan->periode_buku_id,
        );
    }

    public function syncPinjamanPencairan(Pinjaman $pinjaman, ?int $userId = null): void
    {
        $sourceReference = 'pinjaman-pencairan:' . $pinjaman->id;

        if ($pinjaman->status !== Pinjaman::STATUS_AKTIF) {
            $this->deleteAutomaticJournal((int) $pinjaman->koperasi_id, $sourceReference);

            return;
        }

        $amount = round((float) $pinjaman->jumlah_pinjaman, 2);
        $accounts = $this->resolveMappedAccounts((int) $pinjaman->koperasi_id, ['pinjaman_pencairan_debit', 'pinjaman_pencairan_kredit']);
        $anggotaName = $pinjaman->anggota?->profile?->nama_lengkap ?? 'anggota';

        $this->upsertAutomaticJournal(
            koperasiId: (int) $pinjaman->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $pinjaman->tanggal_pinjaman,
            keterangan: sprintf('Pencairan pinjaman %s untuk %s.', $pinjaman->no_pinjaman, $anggotaName),
            lines: [
                [
                    'akun_keuangan_id' => $accounts['pinjaman_pencairan_debit']->id,
                    'debit' => $amount,
                    'kredit' => 0,
                    'uraian' => 'Piutang pinjaman dicatat.',
                ],
                [
                    'akun_keuangan_id' => $accounts['pinjaman_pencairan_kredit']->id,
                    'debit' => 0,
                    'kredit' => $amount,
                    'uraian' => 'Kas keluar untuk pencairan pinjaman.',
                ],
            ],
            userId: $userId,
            periodeBukuId: $pinjaman->periode_buku_id,
        );
    }

    public function syncAngsuran(Pinjaman $pinjaman, Angsuran $angsuran, ?int $userId = null): void
    {
        $sourceReference = 'pinjaman-angsuran:' . $angsuran->id;

        if ($angsuran->status !== Angsuran::STATUS_DIBAYAR) {
            $this->deleteAutomaticJournal((int) $angsuran->koperasi_id, $sourceReference);

            return;
        }

        $pokok = round((float) $angsuran->pokok, 2);
        $jasa = round((float) $angsuran->bunga + (float) $angsuran->denda, 2);
        $total = round((float) $angsuran->jumlah_bayar, 2);
        $accounts = $this->resolveMappedAccounts(
            (int) $angsuran->koperasi_id,
            ['pinjaman_angsuran_debit', 'pinjaman_angsuran_pokok_kredit', 'pinjaman_angsuran_jasa_kredit']
        );

        $lines = [
            [
                'akun_keuangan_id' => $accounts['pinjaman_angsuran_debit']->id,
                'debit' => $total,
                'kredit' => 0,
                'uraian' => 'Kas masuk pembayaran angsuran.',
            ],
            [
                'akun_keuangan_id' => $accounts['pinjaman_angsuran_pokok_kredit']->id,
                'debit' => 0,
                'kredit' => $pokok,
                'uraian' => 'Pelunasan pokok pinjaman.',
            ],
        ];

        if ($jasa > 0) {
            $lines[] = [
                'akun_keuangan_id' => $accounts['pinjaman_angsuran_jasa_kredit']->id,
                'debit' => 0,
                'kredit' => $jasa,
                'uraian' => 'Pendapatan jasa pinjaman.',
            ];
        }

        $this->upsertAutomaticJournal(
            koperasiId: (int) $angsuran->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $angsuran->tanggal_bayar,
            keterangan: sprintf('Pembayaran angsuran ke-%d untuk pinjaman %s.', $angsuran->angsuran_ke, $pinjaman->no_pinjaman),
            lines: $lines,
            userId: $userId,
            periodeBukuId: $angsuran->periode_buku_id,
        );
    }

    public function syncShuDistribution(ShuSkema $scheme, float $distributionAmount, ?int $userId = null): void
    {
        $sourceReference = 'shu-distribusi:' . $scheme->id;
        $amount = round($distributionAmount, 2);

        if ($amount <= 0) {
            $this->deleteAutomaticJournal((int) $scheme->koperasi_id, $sourceReference);

            return;
        }

        $accounts = $this->resolveMappedAccounts((int) $scheme->koperasi_id, ['shu_distribusi_debit', 'shu_distribusi_kredit']);
        $tanggalJurnal = Carbon::create((int) $scheme->tahun, 12, 31)->endOfDay();

        $this->upsertAutomaticJournal(
            koperasiId: (int) $scheme->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $tanggalJurnal,
            keterangan: sprintf('Posting distribusi SHU tahun %d untuk anggota.', $scheme->tahun),
            lines: [
                [
                    'akun_keuangan_id' => $accounts['shu_distribusi_debit']->id,
                    'debit' => $amount,
                    'kredit' => 0,
                    'uraian' => 'Alokasi SHU anggota.',
                ],
                [
                    'akun_keuangan_id' => $accounts['shu_distribusi_kredit']->id,
                    'debit' => 0,
                    'kredit' => $amount,
                    'uraian' => 'Kewajiban SHU anggota.',
                ],
            ],
            userId: $userId,
            periodeBukuId: $this->resolvePeriodeBukuId((int) $scheme->koperasi_id, $tanggalJurnal),
        );
    }

    public function syncAsetPerolehan(AsetKoperasi $aset, ?int $userId = null): void
    {
        $sourceReference = 'aset-perolehan:' . $aset->id;
        $amount = round((float) $aset->nilai_perolehan, 2);

        if ($amount <= 0) {
            $this->deleteAutomaticJournal((int) $aset->koperasi_id, $sourceReference);

            return;
        }

        $accounts = $this->resolveMappedAccounts((int) $aset->koperasi_id, ['aset_perolehan_debit', 'aset_perolehan_kredit']);

        $this->upsertAutomaticJournal(
            koperasiId: (int) $aset->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $aset->tanggal_perolehan,
            keterangan: sprintf('Perolehan aset %s (%s).', $aset->nama_aset, $aset->kode_aset),
            lines: [
                [
                    'akun_keuangan_id' => $accounts['aset_perolehan_debit']->id,
                    'debit' => $amount,
                    'kredit' => 0,
                    'uraian' => 'Aset koperasi bertambah.',
                ],
                [
                    'akun_keuangan_id' => $accounts['aset_perolehan_kredit']->id,
                    'debit' => 0,
                    'kredit' => $amount,
                    'uraian' => 'Kas atau bank berkurang untuk pembelian aset.',
                ],
            ],
            userId: $userId,
            periodeBukuId: $aset->periode_buku_id,
        );
    }

    public function syncAsetDisposal(AsetKoperasi $aset, ?int $userId = null): void
    {
        $sourceReference = 'aset-pelepasan:' . $aset->id;

        if ($aset->status !== AsetKoperasi::STATUS_NONAKTIF || ! $aset->tanggal_nonaktif) {
            $this->deleteAutomaticJournal((int) $aset->koperasi_id, $sourceReference);

            return;
        }

        $bookValue = round((float) $aset->nilai_perolehan, 2);
        $accounts = $this->resolveMappedAccounts(
            (int) $aset->koperasi_id,
            ['aset_pelepasan_kas_debit', 'aset_pelepasan_aset_kredit', 'aset_pelepasan_laba_kredit', 'aset_pelepasan_rugi_debit']
        );

        if ($aset->tipe_nonaktif === AsetKoperasi::TIPE_NONAKTIF_JUAL) {
            $proceeds = round((float) ($aset->nilai_pelepasan ?? $bookValue), 2);
            $difference = round($proceeds - $bookValue, 2);
            $lines = [
                [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_kas_debit']->id,
                    'debit' => $proceeds,
                    'kredit' => 0,
                    'uraian' => 'Kas masuk dari penjualan aset.',
                ],
                [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_aset_kredit']->id,
                    'debit' => 0,
                    'kredit' => $bookValue,
                    'uraian' => 'Nilai buku aset dilepas.',
                ],
            ];

            if ($difference > 0) {
                $lines[] = [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_laba_kredit']->id,
                    'debit' => 0,
                    'kredit' => $difference,
                    'uraian' => 'Laba pelepasan aset.',
                ];
            } elseif ($difference < 0) {
                $lines[] = [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_rugi_debit']->id,
                    'debit' => abs($difference),
                    'kredit' => 0,
                    'uraian' => 'Rugi pelepasan aset.',
                ];
            }

            $keterangan = sprintf('Pelepasan aset %s (%s) dengan nilai buku Rp %s dan hasil penjualan Rp %s.', $aset->nama_aset, $aset->kode_aset, number_format($bookValue, 0, ',', '.'), number_format($proceeds, 0, ',', '.'));
        } else {
            $lines = [
                [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_rugi_debit']->id,
                    'debit' => $bookValue,
                    'kredit' => 0,
                    'uraian' => 'Beban penghapusan aset.',
                ],
                [
                    'akun_keuangan_id' => $accounts['aset_pelepasan_aset_kredit']->id,
                    'debit' => 0,
                    'kredit' => $bookValue,
                    'uraian' => 'Nilai buku aset dihapus.',
                ],
            ];
            $keterangan = sprintf('Penghapusan aset %s (%s) tanpa penjualan dengan nilai buku Rp %s.', $aset->nama_aset, $aset->kode_aset, number_format($bookValue, 0, ',', '.'));
        }

        $this->upsertAutomaticJournal(
            koperasiId: (int) $aset->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $aset->tanggal_nonaktif,
            keterangan: $keterangan,
            lines: $lines,
            userId: $userId,
            periodeBukuId: $aset->periode_buku_id,
        );
    }

    public function syncShuPayment(ShuPayment $payment, ?int $userId = null): void
    {
        $sourceReference = 'shu-pembayaran:' . $payment->id;

        if ($payment->status !== ShuPayment::STATUS_DIBAYAR) {
            $this->deleteAutomaticJournal((int) $payment->koperasi_id, $sourceReference);

            return;
        }

        $amount = round((float) $payment->jumlah_bayar, 2);
        $accounts = $this->resolveMappedAccounts((int) $payment->koperasi_id, ['shu_pembayaran_debit', 'shu_pembayaran_kredit']);
        $anggotaName = $payment->anggota?->profile?->nama_lengkap ?? 'anggota';

        $this->upsertAutomaticJournal(
            koperasiId: (int) $payment->koperasi_id,
            sourceReference: $sourceReference,
            tanggalJurnal: $payment->tanggal_bayar,
            keterangan: sprintf('Pembayaran SHU tahun %d kepada %s (%s).', $payment->tahun, $anggotaName, $payment->no_bukti),
            lines: [
                [
                    'akun_keuangan_id' => $accounts['shu_pembayaran_debit']->id,
                    'debit' => $amount,
                    'kredit' => 0,
                    'uraian' => 'Pengurangan kewajiban SHU anggota.',
                ],
                [
                    'akun_keuangan_id' => $accounts['shu_pembayaran_kredit']->id,
                    'debit' => 0,
                    'kredit' => $amount,
                    'uraian' => 'Kas keluar untuk pembayaran SHU.',
                ],
            ],
            userId: $userId,
            periodeBukuId: $payment->periode_buku_id,
        );
    }

    public function getTemplateMetadata(string $mappingKey): ?array
    {
        return collect(AkunKeuanganMapping::templates())->firstWhere('key', $mappingKey);
    }

    protected function resolveMappedAccounts(int $koperasiId, array $mappingKeys): array
    {
        $this->ensureDefaultMappings($koperasiId, $mappingKeys);

        $mappings = AkunKeuanganMapping::query()
            ->with('akun')
            ->where('koperasi_id', $koperasiId)
            ->whereIn('mapping_key', $mappingKeys)
            ->get()
            ->keyBy('mapping_key');

        $accounts = [];
        $missing = [];

        foreach ($mappingKeys as $mappingKey) {
            $account = $mappings->get($mappingKey)?->akun;

            if (! $account || $account->status !== AkunKeuangan::STATUS_AKTIF || ! $account->can_post) {
                $missing[] = $this->getTemplateMetadata($mappingKey)['label'] ?? $mappingKey;
                continue;
            }

            $accounts[$mappingKey] = $account;
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'mapping' => 'Mapping akun berikut belum lengkap atau belum aktif: ' . implode(', ', $missing) . '.',
            ]);
        }

        return $accounts;
    }

    protected function ensureDefaultMappings(int $koperasiId, array $mappingKeys): void
    {
        foreach ($mappingKeys as $mappingKey) {
            $template = $this->getTemplateMetadata($mappingKey);

            if (! $template || empty($template['default_code'])) {
                continue;
            }

            $defaultAccount = $this->ensureDefaultAccountExists($koperasiId, (string) $template['default_code']);

            if (! $defaultAccount || $defaultAccount->status !== AkunKeuangan::STATUS_AKTIF || ! $defaultAccount->can_post) {
                continue;
            }

            AkunKeuanganMapping::query()->updateOrCreate(
                [
                    'koperasi_id' => $koperasiId,
                    'mapping_key' => $mappingKey,
                ],
                [
                    'akun_keuangan_id' => $defaultAccount->id,
                ]
            );
        }
    }

    protected function ensureDefaultAccountExists(int $koperasiId, string $accountCode): ?AkunKeuangan
    {
        $existingAccount = AkunKeuangan::query()
            ->where('koperasi_id', $koperasiId)
            ->where('kode_akun', $accountCode)
            ->first();

        if ($existingAccount) {
            return $existingAccount;
        }

        $template = collect(AkunKeuangan::defaultTemplates())->firstWhere('kode_akun', $accountCode);

        if (! $template) {
            return null;
        }

        $parentId = null;
        $level = 1;

        if (! empty($template['parent_kode'])) {
            $parent = $this->ensureDefaultAccountExists($koperasiId, (string) $template['parent_kode']);
            $parentId = $parent?->id;
            $level = $parent ? min(((int) $parent->level) + 1, 9) : 1;
        }

        return AkunKeuangan::query()->updateOrCreate(
            [
                'koperasi_id' => $koperasiId,
                'kode_akun' => $template['kode_akun'],
            ],
            [
                'parent_id' => $parentId,
                'nama_akun' => $template['nama_akun'],
                'tipe_akun' => $template['tipe_akun'],
                'level' => $level,
                'is_header' => (bool) $template['is_header'],
                'can_post' => ! (bool) $template['is_header'],
                'status' => $template['status'],
                'keterangan' => $template['keterangan'],
            ]
        );
    }

    protected function upsertAutomaticJournal(
        int $koperasiId,
        string $sourceReference,
        Carbon|string $tanggalJurnal,
        string $keterangan,
        array $lines,
        ?int $userId = null,
        ?int $periodeBukuId = null,
    ): JurnalUmum {
        $journalDate = $tanggalJurnal instanceof Carbon ? $tanggalJurnal->copy() : Carbon::parse($tanggalJurnal);
        $normalizedLines = collect($lines)
            ->map(fn(array $line) => [
                'akun_keuangan_id' => $line['akun_keuangan_id'],
                'debit' => round((float) ($line['debit'] ?? 0), 2),
                'kredit' => round((float) ($line['kredit'] ?? 0), 2),
                'uraian' => $line['uraian'] ?? null,
            ])
            ->filter(fn(array $line) => ($line['debit'] > 0 || $line['kredit'] > 0))
            ->values();

        if ($normalizedLines->count() < 2) {
            throw ValidationException::withMessages([
                'jurnal' => 'Jurnal otomatis membutuhkan minimal dua baris akun.',
            ]);
        }

        if ($normalizedLines->contains(fn(array $line) => $line['debit'] > 0 && $line['kredit'] > 0)) {
            throw ValidationException::withMessages([
                'jurnal' => 'Setiap baris jurnal otomatis hanya boleh berisi debit atau kredit.',
            ]);
        }

        $totalDebit = round((float) $normalizedLines->sum('debit'), 2);
        $totalKredit = round((float) $normalizedLines->sum('kredit'), 2);

        if ($totalDebit <= 0 || $totalKredit <= 0 || abs($totalDebit - $totalKredit) > 0.0001) {
            throw ValidationException::withMessages([
                'jurnal' => 'Jurnal otomatis tidak seimbang antara debit dan kredit.',
            ]);
        }

        return DB::transaction(function () use ($koperasiId, $sourceReference, $journalDate, $keterangan, $normalizedLines, $userId, $periodeBukuId, $totalDebit, $totalKredit) {
            $journal = JurnalUmum::query()->firstOrNew([
                'koperasi_id' => $koperasiId,
                'sumber_referensi' => $sourceReference,
            ]);

            $journal->fill([
                'periode_buku_id' => $periodeBukuId ?? $this->resolvePeriodeBukuId($koperasiId, $journalDate),
                'no_jurnal' => $journal->exists ? $journal->no_jurnal : $this->generateJournalNumber($koperasiId, $journalDate),
                'tanggal_jurnal' => $journalDate->toDateString(),
                'jenis_jurnal' => JurnalUmum::JENIS_OTOMATIS,
                'status' => JurnalUmum::STATUS_POSTED,
                'keterangan' => $keterangan,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);
            $journal->save();

            $journal->details()->delete();

            foreach ($normalizedLines as $index => $line) {
                $journal->details()->create([
                    'akun_keuangan_id' => $line['akun_keuangan_id'],
                    'urutan' => $index + 1,
                    'uraian' => $line['uraian'],
                    'debit' => $line['debit'],
                    'kredit' => $line['kredit'],
                ]);
            }

            return $journal;
        });
    }

    protected function deleteAutomaticJournal(int $koperasiId, string $sourceReference): void
    {
        JurnalUmum::query()
            ->where('koperasi_id', $koperasiId)
            ->where('sumber_referensi', $sourceReference)
            ->where('jenis_jurnal', JurnalUmum::JENIS_OTOMATIS)
            ->delete();
    }

    protected function resolvePeriodeBukuId(int $koperasiId, Carbon $tanggal): ?int
    {
        return PeriodeBuku::query()
            ->where('koperasi_id', $koperasiId)
            ->whereDate('tanggal_mulai', '<=', $tanggal->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggal->toDateString())
            ->value('id');
    }

    protected function generateJournalNumber(int $koperasiId, Carbon $tanggalJurnal): string
    {
        $datePart = $tanggalJurnal->format('Ymd');
        $prefix = sprintf('JRN-%d-%s-', $koperasiId, $datePart);

        $lastNumber = JurnalUmum::query()
            ->where('koperasi_id', $koperasiId)
            ->where('no_jurnal', 'like', $prefix . '%')
            ->orderByDesc('no_jurnal')
            ->value('no_jurnal');

        $nextSequence = $lastNumber ? ((int) Str::afterLast($lastNumber, '-')) + 1 : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
