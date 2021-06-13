<?php
class DateUtils
{
    static public function makeDateLocal($dateTimeString)
    {
        $utc_tz =  new DateTimeZone('UTC');
        $local_tz = new DateTimeZone(date_default_timezone_get());
        $timestamp = new DateTime($dateTimeString, $utc_tz);
        $timestamp->setTimeZone($local_tz);
        return $timestamp->format('d/m/Y H:i:s');
    }
}
?>