<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPListing extends Model
{
    use HasFactory;

    protected $table = "ip_listings";

    protected $fillable = [
        'domain_name',
        'ip_address',
        'route',
    ];
}
