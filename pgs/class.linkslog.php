<?php
require_once("class.linkitem.php");
require_once("class.dateutils.php");

class LinksLog
{
    private $_path;

    public function __construct($path)
    {
        $this->_path = $path;
    }

    public function getLinks($repeaterCall)
    {
        $links = array();
        $handle = fopen($this->_path, "r");

        while ($logLine = fgets($handle)) {
            $linkItem = new LinkItem();
            if ($this->parseLine($logLine, $linkItem) && $repeaterCall == $linkItem->_repeater) {
                $links[] = $linkItem;
            }
        }

        return $links;
    }

    /*
    2021-06-10 13:30:55: DCS link - Type: Repeater Rptr: F5ZEE  B Refl: DCS208 C Dir: Outgoing
    2021-06-07 09:04:53: DCS link - Type: Repeater Rptr: F5ZEE  C Refl: DCS208 C Dir: Outgoing
    2021-06-12 05:20:33: DPlus link - Type: Repeater Rptr: F5ZEE  B Refl: XRF208 C Dir: Outgoing
    2021-06-12 05:22:17: DPlus link - Type: Dongle User: F4FXL Dir: Incoming
    2021-06-12 13:36:16: DExtra link - Type: Repeater Rptr: F5ZEE  B Refl: F4FXL  B Dir: Incoming
    */
    private function parseLine($logLine, $linkItem)
    {
        //TODO Handle incoming DPlus
        $regex = '/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}): (DCS|DPlus|DExtra) link - Type: (Repeater|Dongle) Rptr: ([A-Z0-9 ]{8}) Refl: ([A-Z0-9 ]{8}) Dir: (Outgoing|Incoming)/m';
        
        preg_match($regex, $logLine, $matches, PREG_OFFSET_CAPTURE, 0);

        if (count($matches)) {
            $linkItem->_time        = DateUtils::makeDateLocal($matches[1][0]);
            $linkItem->_protocol    = $matches[2][0];
            $linkItem->_type        = $matches[3][0];
            $linkItem->_repeater    = $matches[4][0];
            $linkItem->_peer        = $matches[5][0];
            $linkItem->_dir         = $matches[6][0];

            return true;
        }

        return false;
    }
}
