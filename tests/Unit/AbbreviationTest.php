<?php

describe('abbreviation', function () {
    it('takes the first letter of each word', function () {
        expect(abbreviation('Sistem Informasi Akademik Mahasiswa'))
            ->toBe('SIAM');
    });

    it('does not abbreviate number', function () {
        expect(abbreviation('Mahasiswa Angkatan 2023'))
            ->toBe('MA 2023');

        expect(abbreviation('tahun 2023 sampai 2024'))
            ->toBe('T 2023 S 2024');
    });

    it('ignores "dan"', function () {
        expect(abbreviation('Sistem Informasi Mahasiswa dan Dosen'))
            ->toBe('SIMD');
    });

    it('removes non alphanumeric abbreviation', function () {
        expect(abbreviation('Sistem Informasi Mahasiswa 2023'))
            ->toBe('SIM 2023');

        expect(abbreviation('Sistem Informasi Mahasiswa - 2023'))
            ->toBe('SIM 2023');

        expect(abbreviation('Sistem Informasi Mahasiswa / 2023'))
            ->toBe('SIM 2023');

        expect(abbreviation('Mahasiswa (2023)'))
            ->toBe('M');
    });
});
