<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    use HasFactory;

    protected $table = "menu_role";

    protected $fillable = [
        'menu_id',
        'role_id',
        'permission_id',
    ];
}
