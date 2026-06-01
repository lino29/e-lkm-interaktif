@props([
    'id' => 'rich-editor-'.\Illuminate\Support\Str::random(8),
    'placeholder' => '',
    'height' => '260px',
    'disabled' => false,
    'uploadUrl' => route('guru.uploads.editor-image'),
])

@php($model = $attributes->wire('model')->value())

<div
    wire:ignore
    data-rich-editor
    x-data="{
        value: $wire.entangle(@js($model)).live,
        editor: null,
        async waitForEditor() {
            if (window.CKEditorClassic) {
                return;
            }

            await new Promise(resolve => window.addEventListener('ckeditor:ready', resolve, { once: true }));
        },
        async initEditor() {
            await this.waitForEditor();

            this.editor = await window.CKEditorClassic.create(this.$refs.editor, {
                ...window.CKEditorConfig,
                placeholder: @js($placeholder),
                simpleUpload: {
                    uploadUrl: @js($uploadUrl),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || @js(csrf_token()),
                        'Accept': 'application/json',
                    },
                },
            });

            this.editor.setData(this.value || '');
            this.editor.editing.view.change(writer => writer.setStyle('min-height', @js($height), this.editor.editing.view.document.getRoot()));
            this.editor.model.document.on('change:data', () => {
                const editorData = this.editor.getData();

                if (editorData !== (this.value || '')) {
                    this.value = editorData;
                }
            });
            this.$watch('value', value => {
                if (this.editor && this.editor.getData() !== (value || '')) {
                    this.editor.setData(value || '');
                }
            });

            @if ($disabled)
                this.editor.enableReadOnlyMode(@js($id));
            @endif
        }
    }"
    x-init="initEditor()"
>
    <textarea id="{{ $id }}" x-ref="editor"></textarea>
</div>
