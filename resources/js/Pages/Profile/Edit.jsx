import { Head, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Field, { inputClass } from '@/Components/Field';
import AdminLayout from '@/Layouts/AdminLayout';
import AppLayout from '@/Layouts/AppLayout';

export default function Edit({ profile }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        name: profile.name,
        username: profile.username,
        email: profile.email,
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        put('/profile', { preserveScroll: true, onSuccess: () => reset('current_password', 'password', 'password_confirmation') });
    };

    const bind = (key, type = 'text') => ({ id: `profile-${key}`, type, value: data[key], onChange: (e) => setData(key, e.target.value), className: inputClass });

    return (
        <>
            <Head title="Profil" />
            <h1 className="font-serif text-3xl font-bold text-slate-900">Profil</h1>
            <p className="mt-1 text-sm text-slate-600">Peran akun: <span className="font-semibold">{profile.role === 'admin' ? 'Administrator' : 'Penulis'}</span></p>

            <form onSubmit={submit} className="mt-6 max-w-xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
                <Field label="Nama lengkap" htmlFor="profile-name" error={errors.name}><input {...bind('name')} /></Field>
                <Field label="Username" htmlFor="profile-username" error={errors.username}><input {...bind('username')} /></Field>
                <Field label="Email" htmlFor="profile-email" error={errors.email}><input {...bind('email', 'email')} /></Field>

                <div className="border-t border-slate-200 pt-5">
                    <p className="mb-4 text-sm font-semibold text-slate-900">Ubah password (opsional)</p>
                    <div className="space-y-5">
                        <Field label="Password saat ini" htmlFor="profile-current_password" error={errors.current_password}><input {...bind('current_password', 'password')} autoComplete="current-password" /></Field>
                        <Field label="Password baru" htmlFor="profile-password" error={errors.password}><input {...bind('password', 'password')} autoComplete="new-password" /></Field>
                        <Field label="Konfirmasi password baru" htmlFor="profile-password_confirmation"><input {...bind('password_confirmation', 'password')} autoComplete="new-password" /></Field>
                    </div>
                </div>

                <Button type="submit" loading={processing}>Simpan perubahan</Button>
            </form>
        </>
    );
}

// Inertia v3 memanggil resolver layout dua kali: pertama dengan objek props halaman,
// lalu dengan elemen React halaman. Dukung keduanya agar tidak crash (layar kosong).
Edit.layout = (page) => {
    const props = page?.props ?? page;
    const isAdmin = props?.auth?.user?.role === 'admin';

    return isAdmin ? <AdminLayout>{page}</AdminLayout> : <AppLayout>{page}</AppLayout>;
};
