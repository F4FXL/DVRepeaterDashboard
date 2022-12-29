<?php
require_once("class.configfile.php");

class DStarGateway
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

    public function getLogFileName($wildcard = false)
    {
        return $this->_conf->getConfigItem("Log", "fileRoot", "dstargateway") . ($wildcard ? "*.log" : "-" . gmdate("Y-m-d") . ".log");
    }

    public function getGatewayName()
    {
        return $this->_conf->getConfigItem("Gateway", "callsign");
    }
}

?>