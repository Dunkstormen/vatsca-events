export default function Textarea({ 
    className = '', 
    error,
    rows = 4,
    ...props 
}) {
    return (
        <div>
            <textarea
                rows={rows}
                className={`block w-full border-2 px-3 py-2 focus:border-primary focus:outline-none sm:text-sm ${
                    error ? 'border-danger' : 'border-grey-300'
                } ${className}`}
                {...props}
            />
            {error && (
                <p className="mt-1 text-sm text-danger">{error}</p>
            )}
        </div>
    );
}
