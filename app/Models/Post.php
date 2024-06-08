<?php

namespace App\Models;

use App\Enums\PostType;
use App\Exceptions\InvalidPostTypeException;
use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

#[ObservedBy(PostObserver::class)]
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

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class, 'id');
    }

    public function submissions(): HasManyThrough
    {
        if ($this->exists && $this->type !== PostType::Assignment) {
            throw new InvalidPostTypeException(expect: PostType::Assignment, actual: $this->type);
        }

        return $this->hasManyThrough(Submission::class, Assignment::class, firstKey: 'id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    protected function formattedTitle(): Attribute
    {
        return Attribute::get(function (): string {
            $prefix = $this->type->value;

            $title = str($this->title)->lower()->startsWith(strtolower($prefix))
                ? Str::ucfirst($this->title)
                : $this->type->value . ' ' . $this->title;

            if ($this->type === PostType::Assignment) {
                return "$title ({$this->assignment->type->value})";
            }

            return $title;
        });
    }
}
