# Classroom Management System

Sistem manajemen kelas online yang memungkinkan guru dan siswa untuk berinteraksi dalam lingkungan pembelajaran virtual.

## Fitur

1. **Autentikasi Pengguna**
   - Registrasi akun untuk guru dan siswa
   - Login dengan username dan password
   - Verifikasi email (dalam pengembangan)

2. **Manajemen Kelas**
   - Guru dapat membuat kelas baru
   - Siswa dapat bergabung dengan kelas menggunakan kode kelas
   - Tampilan daftar kelas untuk guru dan siswa

3. **Tugas**
   - Guru dapat membuat tugas dengan judul, deskripsi, dan batas waktu
   - Siswa dapat mengumpulkan tugas dengan mengupload file
   - Guru dapat melihat dan menilai tugas yang dikumpulkan

4. **Pengumuman**
   - Guru dan siswa dapat membuat pengumuman di kelas
   - Daftar pengumuman terbaru di halaman kelas

## Teknologi yang Digunakan

- PHP Native
- MySQL Database
- Bootstrap 5 untuk UI
- Laragon sebagai local development environment

## Instalasi

1. Pastikan Laragon sudah terinstall di komputer Anda
2. Clone repository ini ke folder `www` Laragon
3. Buka phpMyAdmin dan import file `database.sql`
4. Konfigurasi koneksi database di file `config.php` jika diperlukan
5. Akses aplikasi melalui browser dengan URL: `http://localhost/classroom`

## Struktur Database

1. **users**
   - id (INT, PRIMARY KEY)
   - username (VARCHAR)
   - email (VARCHAR)
   - password (VARCHAR)
   - role (ENUM: 'student', 'teacher')
   - created_at (TIMESTAMP)

2. **classes**
   - id (INT, PRIMARY KEY)
   - teacher_id (INT, FOREIGN KEY)
   - class_name (VARCHAR)
   - class_code (VARCHAR)
   - created_at (TIMESTAMP)

3. **class_members**
   - id (INT, PRIMARY KEY)
   - class_id (INT, FOREIGN KEY)
   - student_id (INT, FOREIGN KEY)
   - joined_at (TIMESTAMP)

4. **assignments**
   - id (INT, PRIMARY KEY)
   - class_id (INT, FOREIGN KEY)
   - title (VARCHAR)
   - description (TEXT)
   - due_date (DATETIME)
   - created_at (TIMESTAMP)

5. **submissions**
   - id (INT, PRIMARY KEY)
   - assignment_id (INT, FOREIGN KEY)
   - student_id (INT, FOREIGN KEY)
   - file_path (VARCHAR)
   - submitted_at (TIMESTAMP)
   - grade (DECIMAL)
   - feedback (TEXT)

6. **announcements**
   - id (INT, PRIMARY KEY)
   - class_id (INT, FOREIGN KEY)
   - user_id (INT, FOREIGN KEY)
   - title (VARCHAR)
   - content (TEXT)
   - created_at (TIMESTAMP)

## Catatan Pengembangan

- Pastikan folder `uploads` memiliki permission yang sesuai untuk menyimpan file yang diupload
- Gunakan password yang aman untuk akun database
- Selalu validasi input pengguna untuk mencegah SQL injection
- Implementasikan CSRF protection untuk form
- Tambahkan fitur verifikasi email
- Tambahkan fitur notifikasi
- Tambahkan fitur chat/diskusi
- Tambahkan fitur upload materi pembelajaran 