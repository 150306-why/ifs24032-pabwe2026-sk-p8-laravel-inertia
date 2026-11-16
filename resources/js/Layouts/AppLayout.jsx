import { FileText, Globe, User } from 'lucide-react';
import SidebarLayout from '@/Components/SidebarLayout';

const nav = [
    { href: '/dashboard/posts', label: 'Artikel Saya', icon: FileText },
    { href: '/profile', label: 'Profil', icon: User },
    { href: '/', label: 'Lihat Blog', icon: Globe },
];

export default function AppLayout({ children }) {
    return <SidebarLayout roleLabel="Dashboard Penulis" nav={nav}>{children}</SidebarLayout>;
}
