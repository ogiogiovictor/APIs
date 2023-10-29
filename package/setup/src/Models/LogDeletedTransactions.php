<?php
namespace Bitfumes\Setup\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LogDeletedTransactions extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.log_deletedtransactions";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

   // protected $primaryKey = 'Token';
}
