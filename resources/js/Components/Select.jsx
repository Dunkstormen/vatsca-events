export default function Select({ 
    className = '', 
    error,
    children,
    ...props 
}) {
    return (
        <div>
            <select
                className={`block w-full border-2 px-3 py-2 focus:border-primary focus:outline-none sm:text-sm ${
                    error ? 'border-danger' : 'border-grey-300'
                } ${className}`}
                {...props}
            >
                {children}
            </select>
            {error && (
                <p className="mt-1 text-sm text-danger">{error}</p>
            )}
        </div>
    );
}
