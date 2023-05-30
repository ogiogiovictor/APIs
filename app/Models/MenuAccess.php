<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAccess extends Model
{
    use HasFactory;

    protected $table = "menu";

    protected $fillable = [
        'menu_name',
        'menu_url',
        'menu_status',
    ];
}
