<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingEffiency extends Model
{
    use HasFactory;
    
    protected $table = "billing_efficency";

    public $timestamps = false;
}
