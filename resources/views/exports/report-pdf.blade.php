<!DOCTYPE html>
<html>
<head>
    <title>Laporan E-LKM - {{ $module->title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        p { margin: 0; color: #4b5563; }
    </style>
</head>
<body>
    <h1>Laporan Kelas: {{ $module['module_title'] }}</h1>
    <p>Tanggal Export: {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
        <tr>
            <th>Nama Murid</th>
            <th>Status</th>
            @foreach($module['learning_units'] as $unit)
                <th>KB{{ $unit['order'] }}</th>
            @endforeach
            <th>Sumatif</th>
            <th>Proyek</th>
            <th>Forum</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ Str::headline($row['module_status']) }}</td>
                @foreach($module['learning_units'] as $unit)
                    <td>{{ $row['formative_scores'][$unit['id']]['score'] ?? 0 }}</td>
                @endforeach
                <td>{{ $row['final_assessment']['score'] ?? 0 }}</td>
                <td>{{ $row['project']['score'] ?? 0 }}</td>
                <td>{{ $row['forum']['average_participation_score'] ?? 0 }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
