<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\AuthorityEnum;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'authority',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'authority' => AuthorityEnum::class,
    ];


    public function isHQ(): bool
    {
        return $this->authority->value == AuthorityEnum::HEADQUATERS->value;
    }

    public function isRegion(): bool
    {
        return $this->authority->value == AuthorityEnum::REGION->value;
    }

    public function isBhub(): bool
    {
        return $this->authority->value == AuthorityEnum::BUSINESSHUB->value;
    }

    public function isSCenter(): bool
    {
        return $this->authority->value == AuthorityEnum::SERVICECENTER->value;
    }

    public function socialAccounts(){
        return $this->hasMany(SocialAccount::class);
    }
}
