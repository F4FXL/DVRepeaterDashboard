<?php

class DStarGatewayLog
{
    public $_logFilePath;
    public function __construct($path)
    {
        $this->_logFilePath = $path;
    }


    // [2022-12-28 08:56:03] [INFO   ] DPRS	F4FXL   /ID51	F4FXL-5	F4FXL-5>API51,DSTAR:!1234.51N/12345.42E[/A=000886QRV DStar
    // https://regex101.com/r/wtQMb5/1
    public function getDPRSLog()
    {
        $dprs = array();
        $cmdline = "bash -c 'cat " . $this->_logFilePath. " | egrep $\"\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\] \[INFO   ] DPRS\"'";
        $output = shell_exec($cmdline);
        $logLines = explode("\n", $output);
        $regex = "/\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\] \[INFO   ] DPRS\t(([0-9]|[A-Z]|\s){8}\/([0-9]|[A-Z]|\s){0,4})\t(([0-9]|[A-Z]){1,6}-([0-9]|[A-Z]){0,2})/m";

        foreach($logLines as $logLine) {
            preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

            if(count($matches)) {
                $dprs[$matches[1][0]] = $matches[4][0];
            }
        }

        return $dprs;
    }
}

?>