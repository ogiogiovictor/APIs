<?php

namespace App\Models\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DimensionCustomer extends Model
{
    use HasFactory;

    protected $table = "customers";
    protected $primaryKey = "CustomerSK";

    public $timestamps = false;
}
