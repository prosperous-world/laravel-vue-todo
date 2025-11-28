<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'completed',
        'due_date',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!$status || $status === 'all') {
            return $query;
        }

        if ($status === 'completed') {
            return $query->where('completed', true);
        }

        if ($status === 'pending') {
            return $query->where('completed', false);
        }

        return $query;
    }

    public function scopeDueBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('due_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('due_date', '<=', $to);
        }

        return $query;
    }

    public function scopeSortByParam($query, ?string $sort)
    {
        return match ($sort) {
            'due_date_desc' => $query->orderBy('due_date', 'desc'),
            'created_at_desc' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc'),
        };
    }

    public function scopeByTag($query, ?string $tagSlug)
    {
        if (!$tagSlug) {
            return $query;
        }

        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }
}

