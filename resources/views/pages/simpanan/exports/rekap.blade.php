<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Rekap Saldo Simpanan</title>
</head>

<body>
    <h2>Rekap Saldo Simpanan - {{ $koperasi->nama_koperasi }}</h2>
    <p>Total Saldo: Rp {{ number_format((float) $summary['total_saldo'], 0, ',', '.') }}</p>
    <p>Total Anggota: {{ $summary['total_anggota'] }}</p>
    <p>Total Jenis: {{ $summary['total_jenis'] }}</p>
    <table border="1">
        <thead>
            <tr>
                <th>No. Anggota</th>
                <th>Nama Anggota</th>
                <th>Kode Jenis</th>
                <th>Jenis Simpanan</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $item)
            <tr>
                <td>{{ $item->no_anggota }}</td>
                <td>{{ $item->nama_lengkap }}</td>
                <td>{{ $item->kode_jenis }}</td>
                <td>{{ $item->nama_jenis }}</td>
                <td>{{ number_format((float) $item->saldo, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Belum ada data saldo simpanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>