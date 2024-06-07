<?php

namespace App\Service\SubjectGroup;

use App\Models\Subject;

abstract class Name
{
    public static function generate(Subject $subject): string
    {
        $groupNames = $subject->groups()->pluck('name');

        foreach ($groupNames as $index => $groupName) {
            preg_match('/kelompok ([0-9]+).*/i', $groupName, $matches);

            $no = $index + 1;
            if (isset($matches[1]) && $matches[1] != $no) {
                return 'Kelompok ' . $no;
            }
        }

        return 'Kelompok ' . ($groupNames->count() + 1);
    }
}
