<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Buku Simpanan Anggota</title>
</head>

<body>
    <h2>Buku Simpanan Anggota - {{ $koperasi->nama_koperasi }}</h2>
    <p>Dicetak: {{ $printedAt->format('d-m-Y H:i') }}</p>
    <p>Anggota: {{ $selectedAnggota?->profile?->nama_lengkap ?? '-' }} ({{ $selectedAnggota?->no_anggota ?? '-' }})</p>
    <p>Saldo Awal: Rp {{ number_format($openingBalance, 0, ',', '.') }}</p>
    <p>Saldo Akhir: Rp {{ number_format($closingBalance, 0, ',', '.') }}</p>
    <table border="1">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Bukti</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6"><strong>Saldo awal</strong></td>
                <td><strong>{{ number_format($openingBalance, 0, ',', '.') }}</strong></td>
            </tr>
            @forelse ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->tanggal_transaksi?->format('d-m-Y') }}</td>
                <td>{{ $transaction->no_bukti ?? '-' }}</td>
                <td>{{ $transaction->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                <td>{{ $transaction->keterangan ?: '-' }}</td>
                <td>{{ $transaction->jumlah > 0 ? number_format((float) $transaction->jumlah, 0, ',', '.') : '-' }}</td>
                <td>{{ $transaction->jumlah < 0 ? number_format(abs((float) $transaction->jumlah), 0, ',', '.') : '-' }}</td>
                <td>{{ number_format((float) $transaction->running_balance, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Belum ada mutasi simpanan untuk diexport.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>