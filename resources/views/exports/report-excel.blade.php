<table>
    <thead>
    <tr>
        <th>Nama Murid</th>
        <th>Email</th>
        <th>Status Modul</th>
        @foreach($module['learning_units'] as $unit)
            <th>Formatif KB {{ $unit['order'] }}</th>
        @endforeach
        <th>Nilai Sumatif</th>
        <th>Skor Proyek</th>
        <th>Skor Forum (Rata-rata)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['email'] }}</td>
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
