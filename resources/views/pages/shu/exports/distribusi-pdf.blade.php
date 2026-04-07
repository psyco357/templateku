<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Distribusi SHU Anggota</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .meta {
            margin-bottom: 16px;
            color: #475569;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary td {
            border: 1px solid #cbd5e1;
            padding: 8px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }

        table.data th {
            background: #f8fafc;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #64748b;
        }
    </style>
</head>

<body>
    <h1>Distribusi SHU Anggota</h1>
    <div class="meta">
        <div>{{ $koperasi->nama_koperasi }}</div>
        <div>Tahun Buku {{ $year }}</div>
        <div>Dicetak {{ $printedAt->format('d-m-Y H:i') }}</div>
    </div>

    <table class="summary">
        <tr>
            <td>Dasar SHU</td>
            <td class="right">Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</td>
            <td>Pool Jasa Modal</td>
            <td class="right">Rp {{ number_format($allocation['jasa_modal'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pool Jasa Usaha</td>
            <td class="right">Rp {{ number_format($allocation['jasa_usaha'], 0, ',', '.') }}</td>
            <td>Total Distribusi</td>
            <td class="right">Rp {{ number_format($distributedTotals['total_shu'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No. Anggota</th>
                <th>Nama</th>
                <th>Status</th>
                <th class="right">Simpanan</th>
                <th class="right">Jasa Usaha</th>
                <th class="right">Bagian Modal</th>
                <th class="right">Bagian Usaha</th>
                <th class="right">Penyesuaian</th>
                <th class="right">Total SHU</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($memberRows as $item)
            <tr>
                <td>{{ $item['no_anggota'] ?: '-' }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ ucfirst($item['status']) }}</td>
                <td class="right">{{ number_format($item['total_simpanan'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item['total_jasa_usaha'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item['bagian_modal'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item['bagian_usaha'], 0, ',', '.') }}</td>
                <td class="right">{{ $item['penyesuaian_pembulatan'] > 0 ? '+' . number_format($item['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</td>
                <td class="right">{{ number_format($item['total_shu'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="muted">Belum ada data anggota untuk distribusi SHU.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total</th>
                <th class="right">{{ number_format($distributedTotals['bagian_modal'], 0, ',', '.') }}</th>
                <th class="right">{{ number_format($distributedTotals['bagian_usaha'], 0, ',', '.') }}</th>
                <th class="right">{{ $distributedTotals['penyesuaian_pembulatan'] > 0 ? '+' . number_format($distributedTotals['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</th>
                <th class="right">{{ number_format($distributedTotals['total_shu'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>