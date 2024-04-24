<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professor extends Model implements Contracts\HasAccountContract
{
    use Concerns\HasAccount, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => EmployeeStatus::class,
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
