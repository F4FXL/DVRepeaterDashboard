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
            data-row-style="lastHeardRowStyle">
            <thead class="thead-dark">
                <tr>
                    <th colspan="6" scope="col">Last Heard</th>
                </tr>
                <tr>
                    <th scope="col" data-field="_time">Time</th>
                    <th scopt="col" data-field="_duration"  data-formatter="durationFormatter">Duration</th>
                    <!-- TODO Hide this column if only one mode is active <th scope="col" data-field="_mode">Mode</th> -->
                    <th scope="col" data-field="_callsign" data-formatter="callsignFormatter">Call</th>
                    <th scope="col" data-field="_target"   data-formatter="callsignFormatter">Target</th>
                    <th scope="col" data-field="_source">Source</th>
                    <th scope="col" data-field="_berorloss" data-formatter="percentFormatter">BER/Loss</th>
                </tr>
            </thead>
        </table>
    </div>
    <?php
        }
    ?>
    <script>
    function lastHeardRowStyle(row, index) {
        if (row._istxing)
            return {
                classes: 'rowTXing'
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

    function percentFormatter(value, row, index, field) {
        if (value != null) {
            return value + "%";
        }

        return "-";
    }

    function durationFormatter(value, row, index, field) {
        if(value != null) {
            return value + "s"+ (row._timedout?" (Time Out)" : "");
        }

        return "&nbsp;"
    }
    </script>
</div>