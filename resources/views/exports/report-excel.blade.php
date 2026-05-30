<table>
    <thead>
    <tr>
        <th>Nama Murid</th>
        <th>Email</th>
        <th>Status Modul</th>
        @foreach($module->learningUnits as $unit)
            <th>Formatif KB {{ $unit->order }}</th>
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
            <td>{{ Str::headline($row['status']) }}</td>
            @foreach($module->learningUnits as $unit)
                <td>{{ $row['formative_scores'][$unit->id] ?? 0 }}</td>
            @endforeach
            <td>{{ $row['final_score'] }}</td>
            <td>{{ $row['project_score'] }}</td>
            <td>{{ $row['forum_score'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
