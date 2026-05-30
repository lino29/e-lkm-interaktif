<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Pengguna</flux:heading>
        <flux:modal.trigger name="import-users-modal">
            <flux:button variant="subtle" icon="document-arrow-up">Import CSV</flux:button>
        </flux:modal.trigger>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif
    
    @if (session('error'))
        <flux:callout variant="danger">{{ session('error') }}</flux:callout>
    @endif

    <flux:modal name="import-users-modal" class="md:w-96">
        <form wire:submit="importCsv" class="space-y-6">
            <div>
                <flux:heading size="lg">Import Pengguna dari CSV</flux:heading>
                <flux:subheading>
                    Pastikan file CSV memiliki header baris pertama dengan nama kolom: 
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">name, email, password, role</span>.
                    Kolom dipisahkan dengan koma.
                </flux:subheading>
            </div>

            <flux:field>
                <flux:label>File CSV</flux:label>
                <input type="file" wire:model="csvFile" accept=".csv,.txt" class="block w-full text-sm text-gray-500
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-md file:border-0
                  file:text-sm file:font-semibold
                  file:bg-zinc-100 file:text-zinc-700
                  hover:file:bg-zinc-200
                  dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700
                "/>
                <flux:error name="csvFile" />
            </flux:field>

            <div class="flex space-x-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Upload & Import</flux:button>
            </div>
        </form>
    </flux:modal>

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
