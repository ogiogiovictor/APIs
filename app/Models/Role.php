<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    use HasFactory;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id')
            ->where('model_type', User::class);
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public static function withUsersCount(): \Illuminate\Database\Eloquent\Collection
    {
       /* return self::leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->select('roles.id', 'roles.name', 'roles.created_at', DB::raw('COUNT(model_has_roles.model_id) as users_count'))
            ->groupBy('roles.id', 'roles.name', 'roles.created_at')
            ->get();
        */

        return self::leftJoin('model_has_roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', User::class);
            })
                ->select('roles.id', 'roles.name', 'roles.created_at', DB::raw('COUNT(model_has_roles.model_id) as users_count'))
                ->groupBy('roles.id', 'roles.name', 'roles.created_at')
                ->get();
    }

}
