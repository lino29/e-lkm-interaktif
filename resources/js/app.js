import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Heading,
    Link,
    AutoLink,
    List,
    BlockQuote,
    Table,
    TableToolbar,
    MediaEmbed,
    Undo,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

window.CKEditorClassic = ClassicEditor;
window.CKEditorConfig = {
    licenseKey: 'GPL',
    plugins: [
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Heading,
        Link,
        AutoLink,
        List,
        BlockQuote,
        Table,
        TableToolbar,
        MediaEmbed,
        Undo,
    ],
    toolbar: [
        'heading',
        '|',
        'bold',
        'italic',
        'link',
        'bulletedList',
        'numberedList',
        'blockQuote',
        'insertTable',
        'mediaEmbed',
        '|',
        'undo',
        'redo',
    ],
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
};

window.dispatchEvent(new CustomEvent('ckeditor:ready'));
