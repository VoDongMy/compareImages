<?php

namespace App\Helpers;

/**
 * @author Khiem Le <khiem.lv@neo-lab.vn>
 * Class DataLog
 * @package App\Helpers
 */
class DataLog
{
    /**
     * @author Khiem Le <khiem.lv@neo-lab.vn>
     * @param $filename
     * @param $data
     * @param $append
     */
    static function set($filename, $data, $append = true)
    {
        $logData = "=============== " . date('Y-m-d H:i:s') . " ===============\n";
        $logData .= print_r($data, true) . "\n";

        if($append){
            file_put_contents(storage_path() . "/logs/" . $filename, $logData, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents(storage_path() . "/logs/" . $filename, $logData);
        }
    }

    /**
     * @author Dong My <my.vd@neo-lab.vn>
     * @param $filename
     * @param $data
     * @param bool $append
     */
    static function logPublic($filename, $data, $append = true)
    {
        $logData = "-----------------" . date('Y-m-d H:i:s') . "-----------------\n";
        $logData .= print_r($data, true) . "\n";

        if($append) {
            file_put_contents(public_path() . "/tmp/logs/" . $filename, $logData, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents(public_path() . "/tmp/logs/" . $filename, $logData);
        }
    }
}