<div class="space-y-6">
    <flux:heading size="xl">Kelola Kelas</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    <form wire:submit="save" class="grid gap-4 md:grid-cols-3">
        <flux:field><flux:label>Nama kelas</flux:label><flux:input wire:model="name" /><flux:error name="name" /></flux:field>
        <flux:field><flux:label>Kode</flux:label><flux:input wire:model="code" /><flux:error name="code" /></flux:field>
        <flux:field><flux:label>Deskripsi</flux:label><flux:input wire:model="description" /><flux:error name="description" /></flux:field>
        <flux:button type="submit" variant="primary">Simpan Kelas</flux:button>
    </form>
    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($classes as $class)
            <flux:card wire:key="class-{{ $class->id }}">
                <div class="font-semibold">{{ $class->name }}</div>
                <flux:text>{{ $class->code }} · {{ $class->users_count }} murid</flux:text>
            </flux:card>
        @endforeach
    </div>
</div>
