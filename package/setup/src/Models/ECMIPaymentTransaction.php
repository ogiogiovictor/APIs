<?php
namespace Bitfumes\Setup\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIPaymentTransaction extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.PaymentTransaction";

    protected $connection = 'ecmi_prod';

    protected $primaryKey = 'transref';

    public $timestamps = false;
}