<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class CustomerAuthModel extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = "customer_login";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Authorization',
        'accountno',
        'expires_at'
    ];


    /**
     * Get the authenticated user associated with the customer.
     *
     * @return \App\Models\User|null
     */
    public function getUserAttribute()
    {
        return Auth::user();
    }

}
