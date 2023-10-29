<?php
namespace Bitfumes\Setup\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class AuditPaymentTransaction extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.Audit_PaymentTransaction";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'transref';
}
