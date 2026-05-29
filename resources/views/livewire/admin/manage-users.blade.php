<div class="space-y-6">
    <flux:heading size="xl">Kelola Pengguna</flux:heading>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Nama</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>
        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input type="email" wire:model="email" />
            <flux:error name="email" />
        </flux:field>
        <flux:field>
            <flux:label>Password awal</flux:label>
            <flux:input type="text" wire:model="password" />
            <flux:error name="password" />
        </flux:field>
        <flux:field>
            <flux:label>Role</flux:label>
            <flux:select wire:model.live="role">
                @foreach ($roles as $roleName)
                    <flux:select.option value="{{ $roleName }}">{{ \Illuminate\Support\Str::headline($roleName) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="role" />
        </flux:field>
        <flux:field>
            <flux:label>Kelas murid</flux:label>
            <flux:select wire:model="class_room_id">
                <flux:select.option value="">Tanpa kelas</flux:select.option>
                @foreach ($classes as $class)
                    <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="class_room_id" />
        </flux:field>
        <div class="flex items-end">
            <flux:button type="submit" variant="primary">Tambah Pengguna</flux:button>
        </div>
    </form>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Kelas</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $user->classRoom?->name ?? '-' }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
