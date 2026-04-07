<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Simpanan Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            margin: 24px;
        }

        .header {
            margin-bottom: 24px;
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .meta {
            color: #475569;
            font-size: 14px;
            margin: 4px 0;
        }

        .summary {
            display: flex;
            gap: 16px;
            margin: 20px 0;
        }

        .card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 16px;
            flex: 1;
        }

        .card-title {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .card-value {
            margin-top: 8px;
            font-size: 18px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h1 class="title">Buku Simpanan Anggota</h1>
        <p class="meta">{{ $koperasi->nama_koperasi }}</p>
        <p class="meta">Dicetak: {{ $printedAt->format('d-m-Y H:i') }}</p>
        @if ($selectedAnggota)
        <p class="meta">Anggota: {{ $selectedAnggota->profile?->nama_lengkap ?? '-' }} ({{ $selectedAnggota->no_anggota }})</p>
        @endif
        @if ($filters['start_date'] || $filters['end_date'])
        <p class="meta">Periode: {{ $filters['start_date'] ?: 'awal' }} s.d. {{ $filters['end_date'] ?: 'sekarang' }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="card">
            <div class="card-title">Saldo Awal</div>
            <div class="card-value">Rp {{ number_format($openingBalance, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-title">Saldo Akhir</div>
            <div class="card-value">Rp {{ number_format($closingBalance, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-title">Jumlah Mutasi</div>
            <div class="card-value">{{ $transactions->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Bukti</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th class="right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5"><strong>Saldo awal</strong></td>
                <td class="right"><strong>Rp {{ number_format($openingBalance, 0, ',', '.') }}</strong></td>
            </tr>
            @forelse ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->tanggal_transaksi?->format('d-m-Y') }}</td>
                <td>{{ $transaction->no_bukti ?? '-' }}</td>
                <td>{{ $transaction->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                <td>{{ $transaction->keterangan ?: '-' }}</td>
                <td>{{ $transaction->jumlah > 0 ? 'Rp ' . number_format((float) $transaction->jumlah, 0, ',', '.') : '-' }}</td>
                <td>{{ $transaction->jumlah < 0 ? 'Rp ' . number_format(abs((float) $transaction->jumlah), 0, ',', '.') : '-' }}</td>
                <td class="right">Rp {{ number_format((float) $transaction->running_balance, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Belum ada mutasi simpanan untuk dicetak.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>