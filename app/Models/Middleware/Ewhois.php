<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ewhois extends Model
{
    use HasFactory;

    protected $table = "msdb.dbo.WhoIsActive";

    protected $connection = 'msdb_ecmi_prod';

    public $timestamps = false;
}
