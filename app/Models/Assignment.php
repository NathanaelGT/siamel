<?php

namespace App\Models;

use App\Enums\AssignmentCategory;
use App\Enums\AssignmentMime;
use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'category',
        'mimes',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'type'     => AssignmentType::class,
            'category' => AssignmentCategory::class,
            'mimes'    => AsEnumCollection::of(AssignmentMime::class),
            'deadline' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
