<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'message', 'email', 'subject', 'accountType', 'unique_code', 'status', 'phone'
    ];
}
