<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECMIHoldMode extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.HoldModeTransactions";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;
}
