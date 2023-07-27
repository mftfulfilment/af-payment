<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallAgent extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone_number','client_name','admin_id','status','session_id',
    ];
}
