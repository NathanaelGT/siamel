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
                ->map(fn(string $word) => match (true) {
                    is_numeric($word)           => " $word ",
                    strtolower($word) === 'dan' => '',
                    ctype_alpha($word[0])       => strtoupper($word[0]),
                    default                     => '',
                })
                ->join('')
        );
    }
}
