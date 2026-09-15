<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeVisibleToStaff(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || ($user->isAdmin() && $user->faculty_id === null)) {
            return $query;
        }

        return $query->whereKey($user->faculty_id);
    }
}
