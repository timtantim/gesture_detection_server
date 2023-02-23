<?php

namespace App\Helpers;

use Log;
use Exception;
use App\Models\Logs;
class Logger
{
    public static function info($msg, $file, $line)
    {
        Log::info(sprintf("%s - line %d - %s", $file, $line, $msg));
    }

    public static function db_log($user_account, $file, $line, $payload, $status, $response, $note=null)
    {
        try {
            $status = Logs::create([
                'user_account' => $user_account,
                'file' => $file,
                'line' => $line,
                'payload' => json_encode($payload),
                'status' => $status,
                'response' => json_encode($response),
                'note'=>$note
            ]);

        } catch (Exception $e) {
            throw new Exception("DB Log Error: " . $e->getMessage());
        }
    }
}
