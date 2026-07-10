<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ApiLog extends Model { protected $fillable=['service','endpoint','method','status_code','response_time_ms','success','message','requested_at']; protected $casts=['success'=>'boolean','requested_at'=>'datetime']; }
