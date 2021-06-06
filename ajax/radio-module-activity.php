<?php
    require_once("../pgs/class.hearditem.php");
    require_once("../pgs/class.radiomodule.php");
    require_once("../pgs/class.mmdvmlog.php");
    require_once("../config.php");

    // header("Content-Type: application/json");

    $heardList = array();
    $moduleId = $_GET["id"];

    if(isset($moduleId) && array_key_exists($moduleId, $RadioModules))
    {
        $radioModule = new RadioModule($RadioModules[$moduleId]["mmdvm.ini"]);
        $radioModule->init();
        $logPath = $RadioModules[$moduleId]["logpath"] . "/" . $radioModule->getLogFileName();
        $logFile = new MMDVMLog($logPath);
        $heardList = $logFile->getHeardList();
    }

    echo json_encode($heardList)
?>