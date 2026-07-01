# Registrasi Online — Nusanara TV Event Management System

Sistem registrasi dan manajemen peserta acara berbasis web yang dikembangkan khusus untuk **NTV** sebagai pengelola acara. Aplikasi ini membantu tim NTV mengelola seluruh siklus peserta — mulai dari pendaftaran online, persetujuan, pengiriman tiket digital, hingga check-in di lokasi acara.

---

## Tentang Project

**Registrasi Online** adalah platform internal NTV untuk mengelola berbagai jenis acara (seminar, konferensi, gathering, dan kegiatan serupa) dalam satu sistem terpusat. Setiap acara dapat memiliki formulir pendaftaran yang berbeda, branding sendiri, dan alur operasional yang disesuaikan kebutuhan tim.

Aplikasi terdiri dari dua sisi utama:

| Sisi | URL | Keterangan |
|------|-----|------------|
| **Publik** | `/`, `/e/{slug}/register` | Halaman acara dan formulir pendaftaran peserta |
| **Admin** | `/admin` | Panel operasional untuk tim NTV (Filament) |

---

## Tujuan Project

Project ini dibuat untuk menjawab kebutuhan operasional NTV dalam mengelola acara secara efisien, terstruktur, dan dapat diukur. Tujuan utamanya meliputi:

### 1. Digitalisasi Pendaftaran Peserta
- Menyediakan formulir pendaftaran online yang dapat diakses peserta kapan saja melalui link atau QR code.
- Menggantikan proses pendaftaran manual (Google Form, spreadsheet, atau WhatsApp) dengan sistem terpusat yang terintegrasi.

### 2. Manajemen Acara Multi-Event
- Tim NTV dapat mengelola banyak acara sekaligus dalam satu dashboard.
- Setiap acara memiliki slug unik, jadwal, lokasi, branding (logo & warna), dan pengaturan sendiri.
- Fitur **event switcher** memudahkan operator fokus pada acara yang sedang aktif.

### 3. Formulir Pendaftaran Dinamis
- Field formulir (nama, nomor WhatsApp, email, dan field kustom) dapat dikonfigurasi per acara.
- Mendukung berbagai tipe field, validasi wajib/opsional, serta pemetaan peran field (nomor WA, nama lengkap, email).

### 4. Alur Persetujuan Registrasi
- Peserta mendaftar dengan status **pending**, lalu tim NTV melakukan review dan **approve/reject**.
- Setelah disetujui, sistem otomatis menghasilkan **kode tiket unik** dan **QR code** untuk setiap peserta.

### 5. Notifikasi WhatsApp Otomatis
- Pengiriman pesan konfirmasi tiket ke peserta via WhatsApp setelah registrasi disetujui.
- Template pesan dapat dikustomisasi per acara dengan placeholder `{name}`, `{event}`, `{code}`, `{location}`, `{qr_url}`.
- Fitur **blast reminder** untuk mengirim pengingat massal ke peserta yang sudah disetujui.

### 6. Check-in & Scan QR Code
- Petugas dapat melakukan check-in peserta dengan memindai QR code tiket.
- Riwayat scan tercatat dan dapat dipantau melalui dashboard admin.
- Informasi peserta (termasuk field kustom) ditampilkan saat scan untuk verifikasi cepat.

### 7. Manajemen Tempat Duduk (Seating)
- Pengaturan layout meja dan kursi per acara.
- Penugasan kursi ke peserta yang sudah disetujui, baik melalui admin panel maupun saat proses scan.

### 8. Pelaporan & Ekspor Data
- Ekspor data registrasi ke **Excel (.xlsx)** lengkap dengan QR code, status, kursi, dan jawaban formulir.
- Dashboard statistik: jumlah registrasi, approved, pending, dan scan hari ini.

### 9. Keamanan & Kontrol Akses
- Panel admin dilindungi autentikasi dengan manajemen user dan role (Filament Shield).
- Hanya operator berwenang yang dapat approve registrasi, scan peserta, dan mengelola data acara.

---

## Fitur Utama

### Sisi Publik
- Daftar acara yang dipublikasikan
- Formulir pendaftaran dinamis per acara
- Halaman konfirmasi setelah pendaftaran
- Tiket digital dengan QR code (`/t/{code}`)
- QR code untuk link pendaftaran acara
- Daftar peserta publik (opsional per acara)

### Panel Admin (`/admin`)
- **Dashboard** — statistik registrasi, scan, dan acara aktif
- **Events** — CRUD acara, branding, template WA, short link
- **Form Fields** — konfigurasi field formulir per acara
- **Registrations** — review, approve/reject bulk, kirim ulang WA, ekspor Excel
- **Scan** — riwayat check-in per acara
- **Seats & Seat Tables** — manajemen layout dan penugasan kursi
- **Users** — manajemen operator dan hak akses
- **Manage Seating** — visual seat picker
- **Participant Scan** — halaman scan langsung dari admin

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.2, Laravel 11 |
| Admin Panel | Filament 4 |
| Role & Permission | Filament Shield |
| Frontend Publik | Blade, Tailwind CSS, Vite |
| Database | MySQL 8 (production) / SQLite (development) |
| QR Code | simplesoftwareio/simple-qrcode |
| Export | maatwebsite/excel (PhpSpreadsheet) |
| Queue | Laravel Queue (untuk pengiriman WA) |
| Container | Docker + Nginx + MySQL |

---

## Alur Kerja Singkat

```
Peserta mendaftar online
        ↓
Status: Pending
        ↓
Tim NTV review di panel admin
        ↓
Approve → Generate kode tiket + QR code
        ↓
Kirim notifikasi WhatsApp ke peserta
        ↓
Hari H: Scan QR code → Check-in
        ↓
(Optional) Assign tempat duduk
```

---

## Struktur Direktori Penting

```
app/
├── Exports/              # Ekspor Excel registrasi
├── Filament/             # Panel admin (Resources, Pages, Widgets)
├── Http/Controllers/     # Controller publik (Event, Register, Scan, Ticket)
├── Jobs/                 # Background job (SendWaMessageJob)
├── Models/               # Event, Registration, FormField, Seat, Scan, dll.
├── Services/             # Business logic (Approval, Seat, QR, ShortLink, WA Blast)
└── Repositories/         # Data access layer

resources/views/
├── public/               # Halaman publik (register, scan, events)
└── filament/             # Custom view admin panel

docker/                   # Konfigurasi Nginx & entrypoint
database/migrations/      # Skema database
```

---

## Lisensi

Project ini menggunakan [Laravel Framework](https://laravel.com) yang dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---

## Kontak & Dukungan

Untuk pertanyaan teknis atau permintaan fitur terkait sistem registrasi NTV, hubungi tim pengembang internal NTV.
