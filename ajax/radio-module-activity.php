<?php
    require_once("class.transmisiondto.php");

    header("Content-Type: application/json");

    $transmissions = array();
    for($i=1; $i < 11; $i++)
    {
        $t = new TransmissionDTO();
        $t->_time = date("Y-m-d H:i:s");
        $t->_mode = $i;
        $t->_callsign = $i;
        $t->_target = $i;
        $t->_source = $i;

        array_push($transmissions, $t);
    }

    echo json_encode($transmissions);
?>