<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogInsertSubAccountDeductions extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.logs_InsertSubAccountDeductions";

    protected $connection = 'ecmi_prod';

    protected $primaryKey = 'transactionno';


    public $timestamps = false;
}
