<?php
    require_once("../pgs/class.hearditem.php");
    require_once("../pgs/class.radiomodule.php");
    require_once("../pgs/class.mmdvmlog.php");
    require_once("../pgs/class.dstargateway.php");
    require_once("../pgs/class.dstargatewaylog.php");
    require_once("../config.php");

    header("Content-Type: application/json");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    $heardList = array();
    $moduleId = $_GET["id"];

    if (isset($moduleId) && array_key_exists($moduleId, $RadioModules)) {
        $dstarGateway = new DStarGateway($DStarGateway["configurationfile"]);
        $dstarGateway->init();

        $radioModule = new RadioModule($RadioModules[$moduleId]["mmdvm.ini"]);
        $radioModule->init();

        $logPath = $RadioModules[$moduleId]["logpath"] . "/" . $radioModule->getLogFileName(false);
        $mmdvmLogFile = new MMDVMLog($logPath);

        $logPath = $DStarGateway["logpath"] . "/" . $dstarGateway->getLogFileName(false);
        $dstarGatewayLogFile = new DStarGatewayLog($logPath);

        $heardList = $mmdvmLogFile->getHeardList();
        $dprs = $dstarGatewayLogFile->getDPRSLog();
        
        foreach($heardList as $heardItem) {
             if(array_key_exists($heardItem->_callsign, $dprs)) {
                 $heardItem->_dprscallsign = $dprs[$heardItem->_callsign];
             }
        }
    }

    echo json_encode($heardList);

?>