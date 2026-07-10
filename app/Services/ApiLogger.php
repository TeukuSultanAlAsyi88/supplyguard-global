<?php
namespace App\Services;
use App\Models\ApiLog;
class ApiLogger
{
 public static function record(string $service,string $endpoint,int $status,bool $success,float $start,?string $message=null):void
 { try { ApiLog::create(['service'=>$service,'endpoint'=>$endpoint,'method'=>'GET','status_code'=>$status,'response_time_ms'=>(int)((microtime(true)-$start)*1000),'success'=>$success,'message'=>$message,'requested_at'=>now()]); } catch (\Throwable $e) {} }
}
