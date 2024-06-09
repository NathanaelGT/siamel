<?php

namespace App\Service\Semester;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class Holiday
{
    /**
     * @returns array{name: string, start_date: string, end_date: string}
     */
    public static function fetch(int | string $year): array
    {
        $ttl = 60 * 60 * 24 * 14;

        return Cache::driver('file')->remember("holidays-$year", $ttl, function () use ($year) {
            $response = (new Client)->request('GET', "https://dayoffapi.vercel.app/api?year=$year")
                ->getBody()
                ->getContents();

            return array_map(fn(array $holiday) => [
                'name' => $year >= 2024
                    ? Str::replace('Isa Al Masih', 'Yesus Kristus', $holiday['keterangan'])
                    : $holiday['keterangan'],
                'date' => Carbon::parse($holiday['tanggal']),
            ], json_decode($response, true));
        });
    }
}
