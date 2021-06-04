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

    public function getCallsign()
    {
        $callsign = $this->_conf->getConfigItem("General", "Callsign");
        $module = $this->_conf->getConfigItem("D-Star", "Module");

        return $callsign . "-" . $module;
    }
}
?>