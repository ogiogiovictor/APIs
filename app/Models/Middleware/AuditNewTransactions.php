<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditNewTransactions extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Audit_Transaction2";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'transref';
}
