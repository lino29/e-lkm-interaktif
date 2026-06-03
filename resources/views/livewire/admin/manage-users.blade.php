<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Pengguna</flux:heading>
        <div class="flex gap-2">
            <flux:modal.trigger name="import-teachers-modal">
                <flux:button variant="subtle" icon="document-arrow-up">Import CSV Guru</flux:button>
            </flux:modal.trigger>
            <flux:modal.trigger name="import-students-modal">
                <flux:button variant="subtle" icon="document-arrow-up">Import CSV Murid</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif
    
    @if (session('error'))
        <flux:callout variant="danger">
            <div class="space-y-2">
                <div>{{ session('error') }}</div>
                @if ($importErrors !== [])
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        @foreach ($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </flux:callout>
    @endif

    <flux:modal name="import-teachers-modal" class="md:w-96">
        <form wire:submit="importTeacherCsv" class="space-y-6">
            <div>
                <flux:heading size="lg">Import Guru dari CSV</flux:heading>
                <flux:subheading>
                    Header CSV guru wajib:
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">name, email, password</span>.
                    Kolom dipisahkan dengan koma.
                </flux:subheading>
            </div>

            <button
                type="button"
                wire:click="downloadTeacherCsvTemplate"
                class="inline-flex items-center gap-2 rounded-xl border border-elkm-line px-3 py-2 text-sm font-semibold text-elkm-text transition hover:border-elkm-primary hover:text-elkm-primary data-loading:pointer-events-none data-loading:opacity-60"
            >
                <span>Unduh Template Guru</span>
            </button>

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

            <div wire:loading.flex wire:target="csvFile" class="items-center gap-2 rounded-xl border border-elkm-line bg-elkm-surface px-3 py-2 text-sm font-medium text-elkm-muted">
                <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                Mengunggah file CSV...
            </div>

            <div wire:loading.flex wire:target="importTeacherCsv" class="items-center gap-2 rounded-xl border border-elkm-line bg-elkm-surface px-3 py-2 text-sm font-medium text-elkm-muted">
                <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                Memproses import guru...
            </div>

            <div class="flex space-x-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="csvFile,importTeacherCsv">
                    <span wire:loading.remove wire:target="importTeacherCsv">Upload & Import</span>
                    <span wire:loading wire:target="importTeacherCsv">Memproses...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="import-students-modal" class="md:w-96">
        <form wire:submit="importStudentCsv" class="space-y-6">
            <div>
                <flux:heading size="lg">Import Murid dari CSV</flux:heading>
                <flux:subheading>
                    Header CSV murid wajib:
                    <span class="font-mono font-bold text-gray-800 dark:text-gray-200">name, nisn, password, kelas</span>.
                    NISN wajib 10 angka. Kolom kelas cocok dengan nama atau kode kelas.
                </flux:subheading>
            </div>

            <button
                type="button"
                wire:click="downloadStudentCsvTemplate"
                class="inline-flex items-center gap-2 rounded-xl border border-elkm-line px-3 py-2 text-sm font-semibold text-elkm-text transition hover:border-elkm-primary hover:text-elkm-primary data-loading:pointer-events-none data-loading:opacity-60"
            >
                <span>Unduh Template Murid</span>
            </button>

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

            <div wire:loading.flex wire:target="csvFile" class="items-center gap-2 rounded-xl border border-elkm-line bg-elkm-surface px-3 py-2 text-sm font-medium text-elkm-muted">
                <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                Mengunggah file CSV...
            </div>

            <div wire:loading.flex wire:target="importStudentCsv" class="items-center gap-2 rounded-xl border border-elkm-line bg-elkm-surface px-3 py-2 text-sm font-medium text-elkm-muted">
                <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                Memproses import murid...
            </div>

            <div class="flex space-x-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="csvFile,importStudentCsv">
                    <span wire:loading.remove wire:target="importStudentCsv">Upload & Import</span>
                    <span wire:loading wire:target="importStudentCsv">Memproses...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Nama</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>
        @if ($role === 'murid')
            <flux:field>
                <flux:label>NISN murid</flux:label>
                <flux:input type="text" wire:model="nisn" maxlength="10" inputmode="numeric" placeholder="10 angka" />
                <flux:error name="nisn" />
            </flux:field>
        @else
            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" wire:model="email" />
                <flux:error name="email" />
            </flux:field>
        @endif
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
        @if ($role === 'murid')
            <flux:field>
                <flux:label>Kelas murid</flux:label>
                <flux:select wire:model="class_room_id">
                    <flux:select.option value="">Pilih kelas</flux:select.option>
                    @foreach ($classes as $class)
                        <flux:select.option value="{{ $class->id }}">{{ $class->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="class_room_id" />
            </flux:field>
        @endif
        <div class="flex items-end">
            <flux:button type="submit" variant="primary">Tambah Pengguna</flux:button>
        </div>
    </form>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>NISN</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Kelas</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->nisn ?? '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $user->email }}</flux:table.cell>
                    <flux:table.cell>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $user->classRoom?->name ?? '-' }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
