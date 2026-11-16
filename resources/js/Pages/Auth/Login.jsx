import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Field, { inputClass } from '@/Components/Field';
import PublicLayout from '@/Layouts/PublicLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '', remember: false });

    const submit = (event) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Login" />
            <section className="mx-auto max-w-md px-4 py-12">
                <h1 className="font-serif text-3xl font-bold text-slate-900">Masuk ke BlogVerse Pro</h1>
                <p className="mt-2 text-sm text-slate-600">Kelola artikel Anda atau pantau seluruh konten sebagai administrator.</p>

                <form onSubmit={submit} className="mt-8 space-y-5">
                    <Field label="Username" htmlFor="login-username" error={errors.username}>
                        <input id="login-username" name="username" type="text" autoComplete="username" className={inputClass} value={data.username} onChange={(e) => setData('username', e.target.value)} />
                    </Field>
                    <Field label="Password" htmlFor="login-password" error={errors.password}>
                        <input id="login-password" name="password" type="password" autoComplete="current-password" className={inputClass} value={data.password} onChange={(e) => setData('password', e.target.value)} />
                    </Field>
                    <label className="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-indigo-600" />
                        Ingat saya
                    </label>
                    <Button id="login-submit" type="submit" loading={processing} className="w-full">Masuk</Button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-600">
                    Belum punya akun? <Link href="/register" className="font-semibold text-indigo-700 hover:underline">Daftar sebagai penulis</Link>
                </p>
            </section>
        </>
    );
}

Login.layout = (page) => <PublicLayout>{page}</PublicLayout>;
