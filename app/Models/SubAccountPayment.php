<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccountPayment extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.SubAccPayment";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

}
