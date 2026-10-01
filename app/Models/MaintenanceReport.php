<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MaintenanceReport extends Model
{
    public const STATUSES = [
        'pending'     => 'Pending',
        'in_progress' => 'In progress',
        'resolved'    => 'Resolved',
    ];

    public const PRIORITIES = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
        'urgent' => 'Urgent',
    ];

    public const CATEGORIES = [
        'Electrical',
        'Plumbing',
        'Furniture',
        'Classroom equipment',
        'Cleanliness',
        'Safety and security',
        'Grounds',
        'Other',
    ];

    public const ROLES = ['Student', 'Teacher', 'Staff', 'Parent'];

    public const BUILDINGS = [
        'Administration Building',
        'CET Building',
        'CICS Building',
        'SBA Building',
        'CTE Building',
        'CAHSS Building',
        'Innovation Building',
        'Gymnasium',
        'Track Oval',
        'Medical Building',
        'IGP Commercial Building',
        'Dormitory',
    ];

    protected $fillable = [
        'user_id',
        'reporter_name',
        'reporter_role',
        'reporter_email',
        'building',
        'room',
        'category',
        'title',
        'description',
        'photo',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /* ---------- Accessors ---------- */

    public function getReferenceAttribute(): string
    {
        return 'CMR-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst($this->priority);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /* ---------- Query scopes ---------- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('title', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhere('building', 'like', $like)
              ->orWhere('room', 'like', $like)
              ->orWhere('reporter_name', 'like', $like);
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn (Builder $q) => $q->where('category', $category));
    }
}