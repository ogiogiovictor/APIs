<?php

namespace App\Models\Middleware;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APIRequest extends Model
{
    use HasFactory;

    protected $table = "ECMI.dbo.APIRequest";

    protected $connection = 'ecmi_prod';

    public $timestamps = false;

    protected $primaryKey = 'end_index';
}
