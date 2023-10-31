<?php
namespace Bitfumes\Setup\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Ecmsdb extends Model
{
    use HasFactory;

    protected $table = "msdb.dbo.TRANSACTION_TRIGGER_ECMI";

    protected $connection = 'msdb_ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'Token_before';
}