<?php

return [
    'global' => [
        'columns' => [
            'no'            => 'No',
            'registered_at' => 'Bergabung pada',
            'published_at'  => 'Diunggah pada',
            'submitted_at'  => 'Dikumpul pada',
            'scored_at'     => 'Dinilai pada',
            'created_at'    => 'Terdaftar pada',
            'updated_at'    => 'Diperbarui pada',
        ],

        'aggregate' => [
            'count' => 'Jumlah :column',
            'min'   => ':Column terkecil',
            'max'   => ':Column terbesar',
            'avg'   => 'Rata-rata :column',
            'sum'   => 'Jumlah :column',
        ],
    ],

    \App\Models\Assignment::class => [
        'name'    => 'Tugas',
        'columns' => [
            'type'     => 'Jenis',
            'category' => 'Kategori',
            'deadline' => 'Batas waktu',
        ],
    ],

    \App\Models\Attendance::class => [
        'name'    => 'Presensi',
        'columns' => [
            'status' => 'Status',
            'date'   => 'Tanggal',
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

    \App\Models\Post::class => [
        'name'    => 'Unggahan',
        'columns' => [
            'title'       => 'Judul',
            'content'     => 'Isi',
            'type'        => 'Jenis',
            'attachments' => 'Berkas',
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
            'name'      => 'Ruangan',
            'full_name' => 'Ruangan',
            'capacity'  => 'Kapasitas',
        ],
    ],

    \App\Models\SubjectSchedule::class => [
        'name'    => 'Pertemuan',
        'columns' => [
            'meeting_no' => 'Pertemuan ke',
            'date'       => 'Tanggal',
            'start_time' => 'Jam mulai',
            'end_time'   => 'Jam selesai',
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

    \App\Models\StudentSubject::class => [
        'name'    => 'Mahasiswa',
        'columns' => [
            //
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

    \App\Models\SubjectGroup::class => [
        'name'    => 'Kelompok',
        'columns' => [
            'name' => 'Kelompok',
        ],
    ],

    \App\Models\SubjectGroupMember::class => [
        'name'    => 'Anggota kelompok',
        'columns' => [
            //
        ],
    ],

    \App\Models\Submission::class => [
        'name'    => 'Pengumpulan',
        'columns' => [
            'note'       => 'Catatan',
            'score'      => 'Nilai',
            'updated_at' => 'Terakhir diubah',
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
