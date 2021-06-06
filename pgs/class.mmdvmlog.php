<?php
require_once("class.hearditem.php");

class MMDVMLog
{
    public $_logFilePath;
    public function __construct($path)
    {
        $this->_logFilePath = $path;
    }

    private function getShortMMDVMLog()
    {
        // Open Logfile and copy loglines into LogLines-Array()
        $logLines = explode("\n", `egrep -h "from|end|watchdog|lost|Alias|0000" $this->_logFilePath | grep -v "data header" | tail -20`);
        $logLines = array_reverse($logLines);
        return $logLines;
    }

    public function getHeardList()
    {
        $logLines = $this->getShortMMDVMLog();
        $heardList = array();
        $i = 0;

        foreach ($logLines as $logLine) {
            if (!$this->isValidLine($logLine)) {
                continue;
            }

            $heardItem = null;

            if (strpos($logLine, "RF header")  && $i == 0) {
                //TODO: Someone is currently TXing, handle this later
            }

            if (strpos($logLine, "end of")) {
                if (strpos($logLine, "D-Star")) {
                    $heardItem = $this->parseDStar($logLine);
                }
            }

            if (isset($heardItem)) {
                $heardList[] = $heardItem;
            }
        }

        return $heardList;
    }

    // M: 2021-06-06 10:18:29.533 D-Star, received RF end of transmission from F5JFA   /705  to CQCQCQ  , 2.4 seconds, BER: 0.0%, RSSI: -95/-94/-94 dBm
    // M: 2021-06-06 10:18:24.444 D-Star, received RF end of transmission from F4FXL   /ID51 to CQCQCQ  , 38.1 seconds, BER: 0.0%
    // M: 2021-06-06 11:18:25.362 D-Star, received network end of transmission from F4FXL   /ID51 to CQCQCQ  , 0.5 seconds, 0% packet loss, BER: 0.0%
    public function parseDStar($logLine)
    {
        $regex = '/M: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}) D-Star, received (RF|network) end of transmission from ([A-Z\d ]{8}\/[A-Z\d ]{4}) to ([A-Z\d ]{8}), (\d{1,5}.\d) seconds, (([0-9]{1,5}%) packet loss, ){0,1}BER: ([0-9]{1,5}.[0-9])%(, RSSI: (-\d{0,3})\/(-\d{0,3})\/(-\d{0,3}) dBm){0,1}/m';

        preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

        if (count($matches)) {
            $heardItem = new HeardItem();

            $isRF = $matches[2][0] == "RF";
            $heardItem->_time = $matches[1][0];
            $heardItem->_duration = $matches[5][0];
            $heardItem->_mode = "D-Star";
            $heardItem->_callsign = $matches[3][0];
            $heardItem->_target = $matches[4][0];
            $heardItem->_source = $isRF? "RF" : "Net";
            $heardItem->_berorloss = $isRF? $matches[8][0] : $matches[7][0];
            
            return $heardItem;
        }

        return null;
    }

    // 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
    // 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
    // M: 2017-02-13 15:53:30.991 0000:  04 00 5E 49 57 38 44 59 94                         *..^IW8DY.*
    // M: 2017-02-13 15:53:31.253 0000:  05 00 20 47 69 6F 76 61 DC                         *.. Giova.*
    public function decodeAlias($logLine)
    {
        if (substr($logLine, 34, 2) !=="04") {
            $tok1 = encode(substr($logLine, 40, 2));
        } else {
            $tok1 = "";
        }
        $tok2 = encode(substr($logLine, 43, 2));
        $tok3 = encode(substr($logLine, 46, 2));
        $tok4 = encode(substr($logLine, 49, 2));
        $tok5 = encode(substr($logLine, 52, 2));
        $tok6 = encode(substr($logLine, 55, 2));
        $tok7 = encode(substr($logLine, 58, 2));
        return $tok1.$tok2.$tok3.$tok4.$tok5.$tok6.$tok7;
    }

    private function isValidLine($logLine)
    {
        if (strpos($logLine, "BS_Dwn_Act")) {
            return false;
        } elseif (strpos($logLine, "invalid access")) {
            return false;
        } elseif (strpos($logLine, "received RF header for wrong repeater")) {
            return false;
        } elseif (strpos($logLine, "unable to decode the network CSBK")) {
            return false;
        } elseif (strpos($logLine, "overflow in the DMR slot RF queue")) {
            return false;
        } elseif (strpos($logLine, "non repeater RF header received")) {
            return false;
        } elseif (strpos($logLine, "Embedded Talker Alias")) {
            return false;
        } elseif (strpos($logLine, "DMR Talker Alias")) {
            return false;
        } elseif (strpos($logLine, "CSBK Preamble")) {
            return false;
        } elseif (strpos($logLine, "Preamble CSBK")) {
            return false;
        }
        return true;
    }
}
