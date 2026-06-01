@props([
    'id' => 'rich-editor-'.\Illuminate\Support\Str::random(8),
    'placeholder' => '',
    'height' => '260px',
    'disabled' => false,
])

@php($model = $attributes->wire('model')->value())

<div
    wire:ignore
    x-data="{
        value: $wire.entangle('{{ $model }}').live,
        editor: null,
        async initEditor() {
            if (! window.ClassicEditor) {
                await new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-ckeditor]');

                    if (existing) {
                        existing.addEventListener('load', resolve);
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js';
                    script.dataset.ckeditor = 'true';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            this.editor = await ClassicEditor.create(this.$refs.editor, {
                placeholder: '{{ $placeholder }}',
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'mediaEmbed', '|', 'undo', 'redo'],
                extraPlugins: [
                    editor => {
                        editor.plugins.get('FileRepository').createUploadAdapter = loader => ({
                            upload: async () => {
                                const file = await loader.file;
                                const body = new FormData();
                                body.append('upload', file);

                                const response = await fetch(@js(route('guru.uploads.editor-image')), {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Accept': 'application/json',
                                    },
                                    body,
                                });

                                if (! response.ok) {
                                    throw new Error('Upload gagal.');
                                }

                                const data = await response.json();

                                return { default: data.url };
                            },
                            abort: () => {},
                        });
                    },
                ],
            });

            this.editor.setData(this.value || '');
            this.editor.editing.view.change(writer => writer.setStyle('min-height', '{{ $height }}', this.editor.editing.view.document.getRoot()));
            this.editor.model.document.on('change:data', () => this.value = this.editor.getData());
            this.$watch('value', value => {
                if (this.editor && this.editor.getData() !== (value || '')) {
                    this.editor.setData(value || '');
                }
            });

            @if ($disabled)
                this.editor.enableReadOnlyMode('{{ $id }}');
            @endif
        }
    }"
    x-init="initEditor()"
>
    <textarea id="{{ $id }}" x-ref="editor"></textarea>
</div>
