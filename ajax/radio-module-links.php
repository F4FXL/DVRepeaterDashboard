<?php
    require_once("../pgs/class.linkitem.php");
    require_once("../pgs/class.radiomodule.php");
    require_once("../pgs/class.dstargateway.php");
    require_once("../pgs/class.linkslog.php");
    require_once("../config.php");

    header("Content-Type: application/json");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    $links = array();
    $moduleId = $_GET["id"];

    if (isset($moduleId) && array_key_exists($moduleId, $RadioModules)) {
        $radioModule = new RadioModule($RadioModules[$moduleId]["mmdvm.ini"]);
        $radioModule->init();

        $linksLog = new LinksLog($DStarGateway["linkslog"]);
        $links = $linksLog->getLinks($radioModule->getCallsign(true));
    }

    if(!count($links))
        $links[] = new LinkItem();

    echo json_encode($links);
?>