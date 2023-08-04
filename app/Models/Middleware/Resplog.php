<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resplog extends Model
{
    use HasFactory;

    protected $connection = 'middleware';
    protected $table = "resplog";
    public $timestamps = false;
}
