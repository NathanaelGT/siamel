<?php

namespace App\Observers;

use App\Models\Building;

class BuildingObserver
{
    public function creating(Building $building): void
    {
        $building->forceFill([
            'abbreviation' => abbreviation($building->name),
        ]);
    }

    public function updating(Building $building): void
    {
        if ($building->isDirty('name')) {
            $building->forceFill([
                'abbreviation' => abbreviation($building->name),
            ]);
        }
    }
}
