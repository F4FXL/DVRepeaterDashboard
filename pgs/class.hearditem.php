<?php
class HeardItem
{
    public $_time;
    public $_duration;
    public $_mode;
    public $_callsign;
    public $_target;
    public $_source;
    public $_berorloss;
    public $_timedout;
    public $_istxing;

    public function __construct()
    {
        $this->_timedout = false;
        $this->_istxing = false;
    }
}
?>