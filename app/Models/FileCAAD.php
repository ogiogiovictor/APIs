<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileCAAD extends Model
{
    use HasFactory;

    protected $table = "file_caad";

     protected $fillable = [
        'process_caad_id', 'file_name', 'file_size', 'file_type', 'file_link'

     ];
}
