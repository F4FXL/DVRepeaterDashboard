<?php
require_once("class.hearditem.php");
require_once("class.dateutils.php");

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
        $logLines = explode("\n", `egrep -h "from|end|watchdog|lost|Alias|0000" $this->_logFilePath | grep -v "data header | tail -50"`);
        return $logLines;
    }

    public function getHeardList()
    {
        $logLines = $this->getShortMMDVMLog();
        $heardList = array();
        $endOfTransmitReceivedList = array();
        $heardItem =  new HeardItem();
        $tempItem = new HeardItem();

        foreach ($logLines as $logLine) {
            if (!$this->isValidLine($logLine)) {
                continue;
            }

            $parseOk = false;

            if (strpos($logLine, "RF header") || strpos($logLine, "network header")) {
                if (strpos($logLine, "D-Star")) {
                    $parseOk = $this->parseDStarSOT($logLine, $heardItem);
                }
            } elseif (strpos($logLine, "end of")) {
                if (strpos($logLine, "D-Star")) {
                    $parseOk = $this->parseDStarEOT($logLine, $heardItem);
                }
            } elseif (strpos($logLine, "watchdog has expired")) {
                if (strpos($logLine, "D-Star")) {
                    $parseOk = $this->parseDStarTO($logLine, $heardItem);
                }
            }

            if ($parseOk) {
                if(!$heardItem->_istxing && array_key_exists($heardItem->_callsign, $heardList)) { //keep time of start of transmission
                    $heardItem->_time = $heardList[$heardItem->_callsign]->_time;
                    $heardItem->_sortabletime = $heardList[$heardItem->_callsign]->_sortabletime;
                }
                $heardList[$heardItem->_callsign] = $heardItem;
                $heardItem = new HeardItem();
            }
        }

        $heardList = array_values($heardList);
        usort($heardList, function($a, $b) { return strcmp($b->_sortabletime, $a->_sortabletime);});
        return $heardList;
    }

    // M: 2021-06-06 09:24:48.507 D-Star, network watchdog has expired, 18.8 seconds, 10% packet loss, BER: 0.0%
    // M: 2021-06-06 09:22:22.619 D-Star, network watchdog has expired, 33.2 seconds, 3% packet loss, BER: 0.0%
    // M: 2021-06-06 09:21:29.025 D-Star, network watchdog has expired, 3.6 seconds, 0% packet loss, BER: 0.0%
    // M: 2021-06-06 09:21:24.917 D-Star, network watchdog has expired, 2.2 seconds, 0% packet loss, BER: 0.0%
    // M: 2021-06-06 09:21:21.437 D-Star, network watchdog has expired, 47.8 seconds, 3% packet loss, BER: 0.0%
    // M: 2021-06-06 09:19:01.073 D-Star, network watchdog has expired, 1.2 seconds, 95% packet loss, BER: 0.0%
    // M: 2021-06-06 09:18:30.186 D-Star, network watchdog has expired, 4.7 seconds, 0% packet loss, BER: 0.0%
    private function parseDStarTO($logLine, $heardItem)
    {
        $regex = '/M: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).\d{3} D-Star, (RF|network) watchdog has expired, (\d{1,5}.\d) seconds,  (\d{0,2})% packet loss, BER: ([0-9]{1,5}.[0-9])%/m';

        preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

        if (count($matches)) {
            $isRF = $matches[2][0] == "RF";
            $heardItem->_time = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_sortabletime = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_duration = $matches[3][0];
            $heardItem->_mode = "D-Star";
            $heardItem->_source = $isRF? "RF" : "Net";
            $heardItem->_berorloss = ($isRF? $matches[5][0] : $matches[4][0]);
            $heardItem->_timedout = true;
            $heardItem->_istxing = false;

            return true;
        }

        return false;
    }

    // M: 2021-06-06 15:39:57.666 D-Star, received network header from F4FXL   /IC92 to CQCQCQ   via DCS208 C
    // M: 2021-06-06 15:39:37.660 D-Star, received RF header from F4FXL   /IC92 to CQCQCQ
    // M: 2021-06-06 13:26:24.212 D-Star, received RF header from DL3CM   /9700 to /DM0HMBA
    private function parseDStarSOT($logLine, $heardItem)
    {
        $regex = '/M: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).\d{3} D-Star, received (RF|network) header from ([A-Z\d ]{8}\/[A-Z\d ]{4}) to (\/{0,1}[A-Z\d ]{7,8})/m';

        preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

        if (count($matches)) {
            $isRF = $matches[2][0] == "RF";
            $heardItem->_time = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_sortabletime = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_source = $isRF? "RF" : "Net";
            $heardItem->_mode = "D-Star";
            $heardItem->_callsign = $matches[3][0];
            $heardItem->_target = $matches[4][0];
            if ($heardItem->_istxing) {
                $heardItem->_duration = $this->getTXDuration($matches[1][0]);
            }

            return true;
        }

        return false;
    }

    // M: 2021-06-06 10:18:29.533 D-Star, received RF end of transmission from F5JFA   /705  to CQCQCQ  , 2.4 seconds, BER: 0.0%, RSSI: -95/-94/-94 dBm
    // M: 2021-06-06 10:18:24.444 D-Star, received RF end of transmission from F4FXL   /ID51 to CQCQCQ  , 38.1 seconds, BER: 0.0%
    // M: 2021-06-06 11:18:25.362 D-Star, received network end of transmission from F4FXL   /ID51 to CQCQCQ  , 0.5 seconds, 0% packet loss, BER: 0.0%
    // M: 2021-06-06 13:26:24.916 D-Star, received RF end of transmission from DL3CM   /9700 to /DM0HMBA, 0.7 seconds, BER: 0.1%
    private function parseDStarEOT($logLine, $heardItem)
    {
        $regex = '/M: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).\d{3} D-Star, received (RF|network) end of transmission from ([A-Z\d ]{8}\/[A-Z\d ]{4}) to (\/{0,1}[A-Z\d ]{7,8}), (\d{1,5}.\d) seconds, (([0-9]{1,5})% packet loss, ){0,1}BER: ([0-9]{1,5}.[0-9])%(, RSSI: (-\d{0,3})\/(-\d{0,3})\/(-\d{0,3}) dBm){0,1}/m';

        preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

        if (count($matches)) {
            $isRF = $matches[2][0] == "RF";
            $heardItem->_time = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_sortabletime = DateUtils::makeDateLocal($matches[1][0]);
            $heardItem->_duration = $matches[5][0];
            $heardItem->_mode = "D-Star";
            $heardItem->_callsign = $matches[3][0];
            $heardItem->_target = $matches[4][0];
            $heardItem->_source = $isRF? "RF" : "Net";
            $heardItem->_berorloss = $isRF? $matches[8][0] : $matches[7][0];
            $heardItem->_istxing = false;
            
            return true;
        }

        return false;
    }

    private function getTXDuration($txtStartDateUTCString)
    {
        $utc_tz =  new DateTimeZone('UTC');
        $timestamp = new DateTime($txtStartDateUTCString, $utc_tz);
        $now = new DateTime("now", $utc_tz);
        $diff = $now->diff($timestamp, true);
        return round($diff->h * 3600.0 + $diff->i * 60.0 + $diff->s + $diff->f, 1);
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
        if (trim($logLine) == "") {
            return false;
        }
        if (strpos($logLine, "BS_Dwn_Act")) {
            return false;
        }
        if (strpos($logLine, "invalid access")) {
            return false;
        }
        if (strpos($logLine, "received RF header for wrong repeater")) {
            return false;
        }
        if (strpos($logLine, "unable to decode the network CSBK")) {
            return false;
        }
        if (strpos($logLine, "overflow in the DMR slot RF queue")) {
            return false;
        }
        if (strpos($logLine, "non repeater RF header received")) {
            return false;
        }
        if (strpos($logLine, "Embedded Talker Alias")) {
            return false;
        }
        if (strpos($logLine, "DMR Talker Alias")) {
            return false;
        }
        if (strpos($logLine, "CSBK Preamble")) {
            return false;
        }
        if (strpos($logLine, "Preamble CSBK")) {
            return false;
        }
        return true;
    }
}
