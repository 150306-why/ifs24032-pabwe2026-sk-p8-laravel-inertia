# BlogVerse Pro

Platform blog multi-tier (Public, Member/Penulis, Admin) — Laravel 13, Inertia.js, React, PostgreSQL, Tailwind CSS,
PhpSpreadsheet, OpenAI API, TOAST UI Editor/Viewer. Arsitektur Feature-First (`app/Features/*`).

## Prasyarat
PHP ≥ 8.3 (ext: pgsql, pdo_pgsql, gd, zip, mbstring, xml; `pcov`/`xdebug` untuk coverage), Composer, Node.js ≥ 20, PostgreSQL.

## Instalasi
```bash
createdb ifs24032_pabwe2026_p8           # sesuaikan DB_DATABASE di .env
php artisan migrate --seed
php artisan storage:link
npm run build                            # atau: npm run dev
php artisan serve                        # lalu: composer run dev bila tersedia
```
Isi `OPENAI_API_KEY` di `.env` untuk fitur AI (kunci hanya dibaca di server).

Akun seeder (password `password`): `admin` (Admin), `penulis1`, `penulis2` (Member).

## Struktur
- `app/Features/{Auth,Post,Admin,Dashboard,Profile,Shared}` — Controllers, Requests, Actions, Services, Models, Exports/Imports.
- `resources/js/{Layouts,Components,Pages}` — PublicLayout, AppLayout, AdminLayout, halaman Inertia.
- Excel: header `Judul | Kategori | Konten | Status` (impor all-or-nothing, maks. 200 baris).

## Pengujian & kualitas
```bash
php artisan test                                   # semua Unit + Feature test
XDEBUG_MODE=coverage php artisan test --coverage --min=100   # atau dengan pcov
composer audit
```
Test memakai SQLite in-memory (ubah `DB_CONNECTION`/`DB_DATABASE` di `phpunit.xml` untuk PostgreSQL test DB).

## Catatan `.gitignore` storage
Setiap subfolder `storage/**` berisi `.gitignore` dengan `*` dan `!.gitignore`. Folder induk (`storage/app`,
`storage/framework`, `storage/framework/cache`) ditambah `!*/` agar subfolder (mis. `framework/views`) tetap terlacak Git.
