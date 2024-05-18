<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::get(fn(): string => $this->building->abbreviation . ' ' . $this->name);
    }
}
