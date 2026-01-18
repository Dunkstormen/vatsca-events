export default function Button({ 
    type = 'button', 
    className = '', 
    variant = 'primary',
    size = 'md',
    children, 
    ...props 
}) {
    const baseClasses = 'inline-flex items-center border-2 font-semibold transition-colors duration-150 ease-in-out focus:outline-none';
    
    const sizes = {
        sm: 'px-3 py-1 text-xs',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };
    
    const variants = {
        primary: 'border-primary text-white bg-primary hover:bg-primary-600 disabled:bg-grey-400 disabled:border-grey-400 disabled:cursor-not-allowed',
        secondary: 'border-secondary text-white bg-secondary hover:bg-secondary-600 disabled:bg-grey-400 disabled:border-grey-400 disabled:cursor-not-allowed',
        success: 'border-success text-white bg-success hover:bg-success-600 disabled:bg-grey-400 disabled:border-grey-400 disabled:cursor-not-allowed',
        danger: 'border-danger text-white bg-danger hover:bg-danger-600 disabled:bg-grey-400 disabled:border-grey-400 disabled:cursor-not-allowed',
        warning: 'border-warning text-white bg-warning hover:bg-warning-600 disabled:bg-grey-400 disabled:border-grey-400 disabled:cursor-not-allowed',
        outline: 'border-primary text-primary bg-transparent hover:bg-primary hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-primary': 'border-primary text-primary bg-transparent hover:bg-primary hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-secondary': 'border-secondary text-secondary bg-transparent hover:bg-secondary hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-success': 'border-success text-success bg-transparent hover:bg-success hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-danger': 'border-danger text-danger bg-transparent hover:bg-danger hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-warning': 'border-warning text-warning bg-transparent hover:bg-warning hover:text-white disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
        'outline-light': 'border-white text-white bg-transparent hover:bg-white hover:text-secondary disabled:border-grey-400 disabled:text-grey-400 disabled:cursor-not-allowed',
    };

    return (
        <button
            type={type}
            className={`${baseClasses} ${sizes[size]} ${variants[variant]} ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}
