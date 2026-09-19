# GAK CRM

CRM internal untuk tim sales properti: lead, pipeline, proyek,
aktivitas, laporan, dan aplikasi kunjungan lapangan.

## Stack

- Laravel 12 (PHP 8.3) + Blade + Tailwind + Alpine
- PostgreSQL (Supabase)
- Deploy: Render (Docker)

## Peran pengguna

| Peran    | Akses                                          |
|----------|------------------------------------------------|
| direktur | semua data, kelola user, hapus permanen        |
| manajer  | data timnya sendiri, pulihkan arsip, import    |
| staff    | hanya data miliknya sendiri                    |

## Menjalankan di lokal

```bash
cp .env.example .env     # lalu isi bagian DB_*
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Tes

```bash
php artisan test
```

`RoleAccessTest` menjaga aturan paling penting: sales tidak boleh
melihat atau mengubah data sales lain. Jangan dihapus.

## Catatan

- `routes/web.php` masih memakai closure untuk dashboard, analytics,
  dan reports. Karena itu `php artisan route:cache` TIDAK bisa dipakai.
  Pindahkan ke controller kalau ingin mengaktifkannya.
- Kredensial hanya hidup di `.env` lokal dan di Environment Variables
  milik platform hosting. Jangan pernah masuk ke repo.
