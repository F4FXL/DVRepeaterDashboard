<div class="row justify-content-md-center">
    <?php
        require_once("class.radiomodule.php");
        $i = 0;
        foreach ($RadioModules as $radioModule) {
            $i++;
            $radioModuleConf = new RadioModule($radioModule["mmdvm.ini"]);
            $radioModuleConf->init(); ?>
    <div class="table-responsive col-md" id="radioMopdule- <?php echo $i ?>">
        <table class="table table-hover table-sm table-responsive-md table-striped" id="links-<?php echo $i ?>"
            data-url="/ajax/radio-module-links.php?id=<?php echo $i; ?>"
            data-toggle="table"
            data-show-refresh="false"
            data-show-auto-refresh="false"
            data-auto-refresh-interval="2"
            data-auto-refresh="true"
            data-row-style="lastHeardRowStyle">
            <thead class="thead-dark">
                <tr>
                    <th colspan="4" scope="col"><h4><?php echo $radioModuleConf->getCallsign(true); ?></h4></th>
                </tr>
                <tr>
                    <th colspan="4" scope="col">Links</th>
                </tr>
                <tr>
                    <th scope="col" data-field="_time">Time</th>
                    <th scope="col" data-field="_protocol">Protocol</th>
                    <th scope="col" data-field="_peer" data-formatter="callsignFormatter">Peer</th>
                    <th scope="col" data-field="_dir">Direction</th>
                </tr>
            </thead>
        </table>
        <table class="table table-hover table-sm table-responsive-md table-striped" id="radio-module-activity-" <?php echo $i ?>
            data-url="/ajax/radio-module-activity.php?id=<?php echo $i; ?>"
            data-toggle="table"
            data-show-refresh="false"
            data-show-auto-refresh="false"
            data-auto-refresh-interval="2"
            data-auto-refresh="true"
            data-row-style="rowStyle">
            <thead class="thead-dark">
                <tr>
                    <?php if($radioModule["showRSSI"]) { ?>
                        <th colspan="8" scope="col">Last Heard</th>
                    <?php } else {?>
                        <th colspan="7" scope="col">Last Heard</th>
                    <?php } ?>    
                </tr>
                <tr>
                    <th scope="col" data-field="_time">Time</th>
                    <th scopt="col" data-field="_duration"  data-formatter="durationFormatter" class="col-md-2">Duration</th>
                    <!-- TODO Hide this column if only one mode is active <th scope="col" data-field="_mode">Mode</th> -->
                    <th scope="col" data-field="_callsign" data-formatter="callsignFormatter" class="col-md-1">Call</th>
                    <th scope="col" data-field="_dprscallsign" data-formatter="dprsCallsignFormatter" class="col-md-1">DPRS</th>
                    <th scope="col" data-field="_target"   data-formatter="callsignFormatter">Target</th>
                    <th scope="col" data-field="_source">Source</th>
                    <th scope="col" data-field="_berorloss" data-formatter="percentFormatter">BER/Loss</th>
                    <?php if($radioModule["showRSSI"]) { ?>
                        <th scope="col" data-field="_rssi" data-formatter="signalFormatter">Signal</th>
                    <?php } ?>
                </tr>
            </thead>
        </table>
    </div>
    <?php
        }
    ?>
    <script>
    function rowStyle(row, index) {
        if (row._istxing)
            return {
                classes: 'rowTXing'
            }

        if (row._timedout || row._transmissionlost)
            return {
                classes: 'rowWarning'
            }

        return {
            css: {}
        };
    }

    function callsignFormatter(value, row, index, field) {
        if (value != null) {
            return value.replaceAll(" ", "&nbsp;");
        }

        return "";
    }

    function dprsCallsignFormatter(value, row, index, field) {

        var img = "<img title=\"" + (value != null ? "DPRS Available" : "No DPRS") + "\" src=\"./img/sat.png\" class=\""+ (value != null ? "filterGreen" : "filterRed") + "\"/>"
        if(value == null)
            return img;

        return "<a href=\"https://aprs.fi/" + value + "\" target=_blank>" + img + "</a>";
    }

    function percentFormatter(value, row, index, field) {
        if (value != null) {
            return value + "%";
        }

        return "-";
    }

    function durationFormatter(value, row, index, field) {
        if(value != null) {
            if (row._timedout)
                return value + "s (Time Out)";

            if (row._transmissionlost)
                return value + "s (Transmission Lost)";

            if (row._istxing)
                return value + "s (Transmitting)";

            return value + "s";
        }

        return "&nbsp;"
    }

    // https://hamwaves.com/decibel/en/
    function signalFormatter(value, row, index, field) {
        if(value != null) {
            var signal = 0;
            var rssi = Number(value);
            var r = -141;

            signal = Math.floor((rssi + 141) / 6);
            if(signal < 0) signal = 0;
            if(signal > 8) signal = 8;

            var dbOverS = rssi + 141 - signal * 6;
            signal ++;

            return "S" + signal + "+" + pad(dbOverS, 2) + "dB";
        }

        return "&nbsp;"
    }

    function pad(num, size) {
        var s = "000000000" + num;
        return s.substr(s.length-size);
    }
    </script>
</div>