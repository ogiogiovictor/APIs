<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = "audit_logs";

    protected $fillable = [
        'user_id',
        'route',
        'message',
        'action',
        'ip_address',
        'browser',
        'device',
    ];
}
