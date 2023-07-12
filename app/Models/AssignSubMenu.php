<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignSubMenu extends Model
{
    use HasFactory;

    protected $table = "assign_menu_role";

    protected $fillable = [
        'menu_id',
        'sub_menu_id',
        'role_id',
    ];
}
