import { Globe, Newspaper, User, Users } from 'lucide-react';
import SidebarLayout from '@/Components/SidebarLayout';

const nav = [
    { href: '/admin/users', label: 'Pengguna', icon: Users },
    { href: '/admin/posts', label: 'Semua Artikel', icon: Newspaper },
    { href: '/profile', label: 'Profil', icon: User },
    { href: '/', label: 'Lihat Blog', icon: Globe },
];

export default function AdminLayout({ children }) {
    return <SidebarLayout roleLabel="Panel Administrator" nav={nav} dark>{children}</SidebarLayout>;
}
