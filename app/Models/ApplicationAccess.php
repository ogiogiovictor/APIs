<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationAccess extends Model
{
    use HasFactory;

    protected $table = "application_access";

    protected $fillable = [
        'domain_name',
        'ip_address',
        'app-secret',
        'app-token',
        'status'
    ];

}
