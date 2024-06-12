<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public static function calculateLeaveDays ($date_from, $date_to) 
    {
        $time_from = strtotime($date_from);
        $time_to = strtotime($date_to);
                
        $days = round(($time_to - $time_from)  / (60 * 60 * 24));
        return $days;
    }
}
