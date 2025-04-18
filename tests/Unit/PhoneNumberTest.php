<?php

describe('normalize_phone_number', function () {
    it('removes space', function () {
        expect(normalize_phone_number('0812 3456 7890'))
            ->toBe('081234567890');
    });

    it('removes dash', function () {
        expect(normalize_phone_number('0812-3456-7890'))
            ->toBe('081234567890');
    });

    it('normalize indonesian phone number', function () {
        expect(normalize_phone_number('(+62)81234567890'))
            ->toBe('081234567890');

        expect(normalize_phone_number('(+62) 812 3456 7890'))
            ->toBe('081234567890');

        expect(normalize_phone_number('(+62) 812-3456-7890'))
            ->toBe('081234567890');

        expect(normalize_phone_number('(+62) 812 3456-7890'))
            ->toBe('081234567890');
    });
});
