<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAADCommentApproval extends Model
{
    use HasFactory;

    protected $table = "caad_comment_approval";

    protected $fillable = [
        'process_caad_id', 'approval_type', 'batch_id', 'approval_by', 'rejected_by', 'comments'
    ];

    public function myCaadcomments(){
        return $this->belongsTo(ProcessCAAD::class, 'id');
    }


    public function mybatchcaad(){
        return $this->hasOne(BulkCAAD::class, 'id');
    }
}
