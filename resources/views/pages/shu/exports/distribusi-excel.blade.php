<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Distribusi SHU Anggota</title>
</head>

<body>
    <h2>Distribusi SHU Anggota - {{ $koperasi->nama_koperasi }}</h2>
    <p>Tahun Buku: {{ $year }}</p>
    <p>Dicetak: {{ $printedAt->format('d-m-Y H:i') }}</p>
    <p>Dasar SHU: Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</p>
    <p>Pool Jasa Modal: Rp {{ number_format($allocation['jasa_modal'], 0, ',', '.') }}</p>
    <p>Pool Jasa Usaha: Rp {{ number_format($allocation['jasa_usaha'], 0, ',', '.') }}</p>
    <p>Total Distribusi: Rp {{ number_format($distributedTotals['total_shu'], 0, ',', '.') }}</p>

    <table border="1">
        <thead>
            <tr>
                <th>No. Anggota</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Total Simpanan</th>
                <th>Total Jasa Usaha</th>
                <th>Bagian Modal</th>
                <th>Bagian Usaha</th>
                <th>Penyesuaian</th>
                <th>Total SHU</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($memberRows as $item)
            <tr>
                <td>{{ $item['no_anggota'] ?: '-' }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ ucfirst($item['status']) }}</td>
                <td>{{ number_format($item['total_simpanan'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['total_jasa_usaha'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['bagian_modal'], 0, ',', '.') }}</td>
                <td>{{ number_format($item['bagian_usaha'], 0, ',', '.') }}</td>
                <td>{{ $item['penyesuaian_pembulatan'] > 0 ? '+' . number_format($item['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</td>
                <td>{{ number_format($item['total_shu'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9">Belum ada data anggota untuk distribusi SHU.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th>{{ number_format($distributedTotals['bagian_modal'], 0, ',', '.') }}</th>
                <th>{{ number_format($distributedTotals['bagian_usaha'], 0, ',', '.') }}</th>
                <th>{{ $distributedTotals['penyesuaian_pembulatan'] > 0 ? '+' . number_format($distributedTotals['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</th>
                <th>{{ number_format($distributedTotals['total_shu'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>