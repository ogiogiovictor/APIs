<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccount extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = "ECMI.dbo.SubAccount";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

}
