<div class="space-y-4">
    <div>
        <flux:heading size="lg">Pokok-Pokok Materi</flux:heading>
        <flux:text>Isi ringkasan materi dalam empat kategori agar murid mudah memetakan konsep penting.</flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach (['konsep' => 'Konsep', 'fakta' => 'Fakta', 'prosedur' => 'Prosedur', 'metakognitif' => 'Metakognitif'] as $key => $label)
            <flux:field>
                <flux:label>{{ $label }}</flux:label>
                <flux:textarea rows="4" wire:model="form.content_json.{{ $key }}" />
            </flux:field>
        @endforeach
    </div>
</div>
