# SIAMEL
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

# Framework / Library
- [Laravel 11](https://laravel.com)
- [Livewire 3.4](https://livewire.laravel.com)
- [Filament 3.2](https://filamentphp.com)
- [Tailwind 3.4](https://tailwindcss.com)

# Installation
```shell
$ git clone https://github.com/nathanaelGT/siamel
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
