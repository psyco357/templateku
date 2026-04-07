<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Transaksi Simpanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            margin: 24px;
        }

        .sheet {
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }

        .muted {
            color: #475569;
            font-size: 14px;
            margin: 4px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 16px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .value {
            margin-top: 8px;
            font-size: 15px;
            font-weight: 600;
        }

        .audit {
            margin-top: 22px;
        }

        .audit h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .audit-item {
            border-top: 1px solid #e2e8f0;
            padding: 10px 0;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="sheet">
        <div class="header">
            <div>
                <h1 class="title">Bukti Transaksi Simpanan</h1>
                <p class="muted">{{ $koperasi->nama_koperasi }}</p>
                <p class="muted">Dicetak: {{ $printedAt->format('d-m-Y H:i') }}</p>
            </div>
            <div>
                <p class="muted">No. Bukti</p>
                <p class="title" style="font-size: 20px;">{{ $simpanan->no_bukti ?? '-' }}</p>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <div class="label">Anggota</div>
                <div class="value">{{ $simpanan->anggota?->profile?->nama_lengkap ?? '-' }}</div>
                <div class="muted">{{ $simpanan->anggota?->no_anggota ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Jenis Simpanan</div>
                <div class="value">{{ $simpanan->jenisSimpanan?->nama_jenis ?? '-' }}</div>
                <div class="muted">{{ $simpanan->jenisSimpanan?->kode_jenis ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Tanggal Transaksi</div>
                <div class="value">{{ $simpanan->tanggal_transaksi?->translatedFormat('d F Y') }}</div>
            </div>
            <div class="card">
                <div class="label">Nominal</div>
                <div class="value">{{ $simpanan->jumlah < 0 ? '-' : '+' }}Rp {{ number_format(abs((float) $simpanan->jumlah), 0, ',', '.') }}</div>
                <div class="muted">{{ $simpanan->jumlah < 0 ? 'Penarikan' : 'Setoran' }}</div>
            </div>
            <div class="card">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($simpanan->status) }}</div>
            </div>
            <div class="card">
                <div class="label">Periode Buku</div>
                <div class="value">{{ $simpanan->periodeBuku?->tahun_buku ? $simpanan->periodeBuku->tahun_buku . ' H' : '-' }}</div>
            </div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <div class="label">Keterangan</div>
            <div class="value">{{ $simpanan->keterangan ?: '-' }}</div>
        </div>

        <div class="audit">
            <h2>Riwayat Audit</h2>
            @forelse ($simpanan->audits as $audit)
            <div class="audit-item">
                <div><strong>{{ ucfirst($audit->action) }}</strong> - {{ $audit->description ?: '-' }}</div>
                <div class="muted">{{ $audit->user?->profile?->nama_lengkap ?? $audit->user?->username ?? 'Sistem' }} • {{ $audit->created_at?->format('d-m-Y H:i') }}</div>
            </div>
            @empty
            <div class="audit-item">Belum ada riwayat audit.</div>
            @endforelse
        </div>
    </div>
</body>

</html>