import Spinner from './Spinner';

const variants = {
    primary: 'bg-indigo-600 text-white hover:bg-indigo-700',
    secondary: 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    ghost: 'text-slate-600 hover:bg-slate-100',
};

export default function Button({ variant = 'primary', loading = false, disabled = false, className = '', type = 'button', children, ...props }) {
    return (
        <button
            type={type}
            disabled={disabled || loading}
            className={`inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
            {...props}
        >
            {loading && <Spinner />}
            {children}
        </button>
    );
}

export function buttonClass(variant = 'secondary', extra = '') {
    return `inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition ${variants[variant]} ${extra}`;
}
