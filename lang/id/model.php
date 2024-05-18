<?php

return [
    'global' => [
        'columns' => [
            'created_at' => 'Terdaftar pada',
            'updated_at' => 'Diperbarui pada',
        ],

        'aggregate' => [
            'count' => 'Jumlah :column',
            'min'   => ':Column terkecil',
            'max'   => ':Column terbesar',
            'avg'   => 'Rata-rata :column',
            'sum'   => 'Jumlah :column',
        ],
    ],

    \App\Models\Building::class => [
        'name'    => 'Gedung',
        'columns' => [
            'name' => 'Gedung',
        ],
    ],

    \App\Models\Course::class => [
        'name'    => 'Mata kuliah',
        'columns' => [
            'name'              => 'Mata kuliah',
            'semester_required' => 'Semester minimal',
            'semester_parity'   => 'Semester',
            'credits'           => 'SKS',
            'is_elective'       => 'Mata kuliah pilihan',
        ],
    ],

    \App\Models\Faculty::class => [
        'name'    => 'Fakultas',
        'columns' => [
            'name'          => 'Fakultas',
            'accreditation' => 'Akreditasi',
        ],
    ],

    \App\Models\Professor::class => [
        'name'    => 'Dosen',
        'columns' => [
            'id'     => 'NIP',
            'status' => 'Status',
        ],
    ],

    \App\Models\Room::class => [
        'name'    => 'Ruangan',
        'columns' => [
            'name'     => 'Ruangan',
            'capacity' => 'Kapasitas',
        ],
    ],

    \App\Models\Semester::class => [
        'name'    => 'Semester',
        'columns' => [
            'parity'        => 'Paritas',
            'year'          => 'Tahun',
            'academic_year' => 'Tahun ajaran',
        ],
    ],

    \App\Models\Staff::class => [
        'name'    => 'Staff',
        'columns' => [
            'id'     => 'NIP',
            'status' => 'Status',
        ],
    ],

    \App\Models\Student::class => [
        'name'    => 'Mahasiswa',
        'columns' => [
            'id'              => 'NPM',
            'hometown'        => 'Kota asal',
            'enrollment_type' => 'Jalur masuk',
            'status'          => 'Status',
        ],
    ],

    \App\Models\StudyProgram::class => [
        'name'    => 'Program studi',
        'columns' => [
            'name'  => 'Program studi',
            'level' => 'Jenjang',
        ],
    ],

    \App\Models\Subject::class => [
        'name'    => 'Kelas',
        'columns' => [
            'title'      => 'Kelas',
            'capacity'   => 'Kapasitas',
            'parallel'   => 'Paralel',
            'code'       => 'Kode',
            'note'       => 'Catatan',
            'day'        => 'Hari',
            'start_time' => 'Jam mulai',
            'end_time'   => 'Jam selesai',
            'time'       => 'Jam',
            'year'       => 'Tahun',
        ],
    ],

    \App\Models\User::class => [
        'name'    => 'Akun',
        'columns' => [
            'name'         => 'Nama',
            'email'        => 'Email',
            'phone_number' => 'Nomor telepon',
            'gender'       => 'Jenis kelamin',
            'avatar_url'   => 'Profil',
        ],
    ],
];
