<?php
class ConfigFile
{
    private $_path;
    private $_conf;

    public function __construct($path)
    {
        $this->_path = $path;
    }

    public function init()
    {
        $success = false;
        $this->_conf = array();
        if ($fileHandler = fopen($this->_path, 'r'))
        {
            while($configLine = fgets($fileHandler))
            {
                array_push($this->_conf, trim($configLine, " \t\n\r\0\x0B"));
            }
            fclose($fileHandler);
            $success = true;
        }

        return $success;
    }

    public function getConfigItem($section, $key, $defaultvalue =  "")
    {
        // retrieves the corresponding config-entry within a [section]
        $sectionpos = array_search("[" . $section . "]", $this->_conf);
        if ($sectionpos !== FALSE) {
            $sectionpos++;
            $len = count($this->_conf);
            while(($sectionpos < $len) && ($this->startsWith($this->_conf[$sectionpos], $key . "=") === false))
            {
                if ($this->startsWith($this->_conf[$sectionpos],"["))
                {
                    return null;
                }
                $sectionpos++;
            }
            if ($sectionpos < $len)
            {
                $config = substr($this->_conf[$sectionpos], strlen($key) + 1);
                $commentidx = strpos($config, "#");
                if($commentidx !== false) {
                    $config = substr($config, 0, $commentidx);
                }
                $config = trim($config, " \t\n\r\0\x0B");
                if($config == "") $config = $defaultvalue;
                return $config;
            }
        }
        return $defaultvalue;
     }

    //https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    private function startsWith($haystack, $needle)
    {
        $length = strlen( $needle );
        return substr($haystack, 0, $length) === $needle;
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if(!$length)
        {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

}
?>