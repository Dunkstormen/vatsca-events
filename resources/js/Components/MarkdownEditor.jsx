import { useMemo } from 'react';
import SimpleMDE from 'react-simplemde-editor';
import 'easymde/dist/easymde.min.css';

/**
 * Markdown editor component using SimpleMDE
 * 
 * @param {string} value - The markdown content
 * @param {Function} onChange - Callback when content changes
 * @param {string} error - Error message to display
 * @param {string} placeholder - Placeholder text
 */
export default function MarkdownEditor({ value, onChange, error, placeholder = 'Enter description...' }) {
    const options = useMemo(() => {
        return {
            spellChecker: false,
            placeholder: placeholder,
            status: false,
            toolbar: [
                'bold',
                'italic',
                'heading',
                '|',
                'unordered-list',
                'ordered-list',
                '|',
                'link',
                'quote',
                'code',
                '|',
                'preview',
                'side-by-side',
                'fullscreen',
                '|',
                'guide'
            ],
            minHeight: '300px',
        };
    }, [placeholder]);

    return (
        <div>
            <SimpleMDE
                value={value}
                onChange={onChange}
                options={options}
            />
            {error && (
                <p className="mt-1 text-sm text-danger">{error}</p>
            )}
        </div>
    );
}
