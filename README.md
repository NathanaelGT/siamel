# [SIAMEL](https://github.com/NathanaelGT/siamel)
<p>Sistem Informasi Akademik Mahasiswa dan E-Learning</p>

# Requirement
- [PHP 8.3](https://php.net)
    - Ctype PHP Extension
    - cURL PHP Extension
    - DOM PHP Extension
    - Fileinfo PHP Extension
    - Filter PHP Extension
    - Hash PHP Extension
    - Mbstring PHP Extension
    - OPcache PHP Extension
    - OpenSSL PHP Extension
    - PCRE PHP Extension
    - PDO PHP Extension
    - Session PHP Extension
    - Tokenizer PHP Extension
    - XML PHP Extension
    - ZIP PHP Extension
- [MySQL 8](https://www.mysql.com)
- [Composer](https://getcomposer.org)

# Framework / Library
- [Laravel 11](https://laravel.com)
- [Livewire 3.4](https://livewire.laravel.com)
- [Filament 3.2](https://filamentphp.com)
- [Tailwind 3.4](https://tailwindcss.com)

# Installation
```shell
$ git clone https://github.com/nathanaelGT/siamel
$ cd siamel
$ php -r "copy('.env.example', '.env');"
$ composer install
$ php artisan key:generate
$ php artisan migrate --seed
```

# How to Run
```shell
$ php artisan serve
```

# Default Account
| Jenis Akun       | Email                      | Password |
|------------------|----------------------------|----------|
| Admin Global     | admin@siamel.test          | password |
| Admin Fasilkom   | admin.fasilkom@siamel.test | password |
| Staff Global     | staff@siamel.test          | password |
| Staff Fasilkom   | staff.fasilkom@siamel.test | password |
| Akun Mahasiswa   | [npm]@student.siamel.test  | password |
| Akun Dosen/Staff | [nip]@siamel.test          | password |

# Side Note
- Untuk melihat daftar NPM dan NIP, dapat login menggunakan akun Admin Global kemudian masuk ke menu
  Staff/Dosen/Mahasiswa
- Saat staff mendaftarkan akun baru, jika Mailing belum diatur pada file `.env`, maka preview e-mail kan dikirim ke
  file `storage/logs/laravel.log`
- Terdapat beberapa fitur yang hanya dapat dipakai tergantung waktu, misalnya mahasiswa mengatur KRS. Untuk mengecek
  semua fitur tanpa terikat oleh waktu, ubah nilai dari variabel `SIAMEL_BYPASS_PERIOD` pada file `.env` menjadi `true`
