<?php
namespace Bitfumes\Setup\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class AuditTokenLog extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Audit_TokenLog";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'Token';
}
