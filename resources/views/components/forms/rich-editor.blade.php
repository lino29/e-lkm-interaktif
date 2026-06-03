@props([
    'id' => 'rich-editor-'.\Illuminate\Support\Str::random(8),
    'placeholder' => '',
    'height' => '260px',
    'disabled' => false,
    'uploadUrl' => route('guru.uploads.editor-image'),
])

@php($model = $attributes->wire('model')->value())

<div wire:key="rich-editor-wrapper-{{ $id }}" class="ck ck-theme-light">
    <div
        wire:ignore
        data-rich-editor
        x-on:rich-editor:sync.window="syncEditor()"
        x-data="{
            value: $wire.$get(@js($model)) || '',
            editorInstance() {
                return this.$refs.editor._ckeditor || null;
            },
            setEditorInstance(editor) {
                this.$refs.editor._ckeditor = editor;
            },
            async waitForEditor() {
                if (window.CKEditorClassic) {
                    return;
                }

                await new Promise(resolve => window.addEventListener('ckeditor:ready', resolve, { once: true }));
            },
            async initEditor() {
                await this.waitForEditor();

                const editor = await window.CKEditorClassic.create(this.$refs.editor, {
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
                this.setEditorInstance(editor);

                editor.setData(this.value || '');
                editor.editing.view.change(writer => writer.setStyle('min-height', @js($height), editor.editing.view.document.getRoot()));
                editor.model.document.on('change:data', () => {
                    const editorData = editor.getData();

                    if (editorData !== (this.value || '')) {
                        this.value = editorData;
                    }
                });

                @if ($disabled)
                    editor.enableReadOnlyMode(@js($id));
                @endif
            },
            syncEditor() {
                if (this.editorInstance()) {
                    this.value = this.editorInstance().getData();
                    $wire.$set(@js($model), this.value, false);
                }
            },
            destroy() {
                const editor = this.editorInstance();

                if (! editor) {
                    return;
                }

                this.setEditorInstance(null);
                editor.destroy().catch(() => {});
            }
        }"
        x-init="initEditor()"
        x-on:blur.capture="syncEditor()"
    >
        <textarea id="{{ $id }}" x-ref="editor"></textarea>
    </div>
</div>
