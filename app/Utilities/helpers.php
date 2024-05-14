<?php

use Illuminate\Support\Str;

if (! function_exists('normalize_phone_number')) {
    function normalize_phone_number(string $phoneNumber): string
    {
        return (string) str($phoneNumber)
            ->replace('(+62)', '0')
            ->replace([' ', '-'], '');
    }
}

if (! function_exists('abbreviation')) {
    function abbreviation(string $words): string
    {
        return Str::squish(
            str($words)
                ->explode(' ')
                ->map(function (string $word) {
                    if (is_numeric($word)) {
                        return " $word ";
                    } elseif (strtolower($word) === 'dan') {
                        return '';
                    } elseif (ctype_alpha($firstChar = $word[0])) {
                        return strtoupper($firstChar);
                    }

                    return '';
                })
                ->join('')
        );
    }
}
