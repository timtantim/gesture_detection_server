<?php

namespace App\Helpers;
use Log;

class Logger{
    public static function info($msg,$file,$line){
        Log::info(sprintf("%s - line %d - %s",$file,$line,$msg));
    }
}