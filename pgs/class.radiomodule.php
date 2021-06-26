<?php
require_once("class.configfile.php");

class RadioModule
{
    private $_conf;

    public function __construct($path)
    {
        $this->_conf = new ConfigFile($path);
    }

    public function init()
    {
        return $this->_conf->init();
    }

    public function getCallsign($long = false)
    {
        $callsign = $this->_conf->getConfigItem("General", "Callsign");
        $module = $this->_conf->getConfigItem("D-Star", "Module");

        return $long? (str_pad($callsign, 7, " ") . $module) : ($callsign . "-" . $module);
    }

    public function getLogFileName($wildcard = false)
    {
        return $this->_conf->getConfigItem("Log", "FileRoot") . ($wildcard ? "*.log" : "-" . gmdate("Y-m-d") . ".log");
    }
}
?>