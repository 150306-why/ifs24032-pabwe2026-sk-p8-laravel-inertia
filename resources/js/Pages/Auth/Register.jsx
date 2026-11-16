import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Field, { inputClass } from '@/Components/Field';
import PublicLayout from '@/Layouts/PublicLayout';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({ name: '', username: '', email: '', password: '', password_confirmation: '' });

    const submit = (event) => {
        event.preventDefault();
        post('/register');
    };

    const bind = (key) => ({ id: `register-${key}`, name: key, value: data[key], onChange: (e) => setData(key, e.target.value), className: inputClass });

    return (
        <>
            <Head title="Daftar" />
            <section className="mx-auto max-w-md px-4 py-12">
                <h1 className="font-serif text-3xl font-bold text-slate-900">Buat akun penulis</h1>
                <p className="mt-2 text-sm text-slate-600">Daftar gratis dan mulai menerbitkan artikel Anda.</p>

                <form onSubmit={submit} className="mt-8 space-y-5">
                    <Field label="Nama lengkap" htmlFor="register-name" error={errors.name}><input type="text" autoComplete="name" {...bind('name')} /></Field>
                    <Field label="Username" htmlFor="register-username" error={errors.username} hint="Huruf, angka, strip, dan garis bawah."><input type="text" autoComplete="username" {...bind('username')} /></Field>
                    <Field label="Email" htmlFor="register-email" error={errors.email}><input type="email" autoComplete="email" {...bind('email')} /></Field>
                    <Field label="Password" htmlFor="register-password" error={errors.password} hint="Minimal 8 karakter."><input type="password" autoComplete="new-password" {...bind('password')} /></Field>
                    <Field label="Konfirmasi password" htmlFor="register-password_confirmation"><input type="password" autoComplete="new-password" {...bind('password_confirmation')} /></Field>
                    <Button id="register-submit" type="submit" loading={processing} className="w-full">Daftar</Button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-600">
                    Sudah punya akun? <Link href="/login" className="font-semibold text-indigo-700 hover:underline">Masuk</Link>
                </p>
            </section>
        </>
    );
}

Register.layout = (page) => <PublicLayout>{page}</PublicLayout>;
