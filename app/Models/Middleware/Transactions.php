<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $connection = 'middleware';
   // protected $table = "transactions";
    protected $table = "transactions_stagging";
    public $timestamps = false;
}
