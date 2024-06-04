<?php

namespace App\Models;

use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'user_id',
        'title',
        'content',
        'type',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type'         => PostType::class,
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function assignment(): MorphOne
    {
        $instance = $this->newRelatedInstance(Assignment::class);
        $localKey = $this->getKeyName();
        $table = $instance->getTable();

        return $this->newMorphOne($instance->newQuery(), $this, "$table.type", "$table.$localKey", $localKey);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }
}
