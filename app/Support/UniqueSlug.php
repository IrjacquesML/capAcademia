<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $scope
     */
    public static function make(string $model, string $name, array $scope = [], ?int $ignoreId = null, string $fallback = 'item'): string
    {
        $base = Str::slug($name) ?: $fallback;
        $slug = $base;
        $i = 2;

        while ($model::query()
            ->where($scope)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
