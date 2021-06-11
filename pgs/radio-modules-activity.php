
<div class="row justify-content-md-center">
    <?php
        require_once("class.radiomodule.php");
        $i = 0;
        foreach ($RadioModules as $radioModule) {
            $tableid = "radio-module-activity-" . ++$i;
            $radioModuleConf = new RadioModule($radioModule["mmdvm.ini"]);
            $radioModuleConf->init(); ?>
        <div class="table-responsive col-md">
            <table class="table table-hover table-sm table-responsive-md table-striped" id="<?php echo $tableid ?>"
                    data-url="/ajax/radio-module-activity.php?id=<?php echo $i; ?>"
                    data-toggle="table"
                    data-show-refresh="false"
                    data-show-auto-refresh="false"
                    data-auto-refresh-interval="2"
                    data-auto-refresh="true"
                    data-row-style="rowStyle">
                <thead class="thead-dark">
                    <tr>
                        <th colspan="6" scope="col"><?php echo $radioModuleConf->getCallsign(); ?></th>
                    </tr>
                
                    <tr>
                        <th scope="col" data-field="_time">Time</th>
                        <th scopt="col" data-field="_duration">Duration</th>
                        <!-- TODO Hide this column if only one mode is active <th scope="col" data-field="_mode">Mode</th> -->
                        <th scope="col" data-field="_callsign">Call</th>
                        <th scope="col" data-field="_target">Target</th>
                        <th scope="col" data-field="_source">Source</th>
                        <th scope="col" data-field="_berorloss">BER/Loss</th>
                    </tr>
                </thead>
            </table>
        </div>
    <?php
        }
    ?>
    <script>
    function rowStyle(row, index)
    {
        if(row._istxing)
            return {classes: 'rowTXing'}
            //return {css:{color: 'red'}}

        return {css:{ }};
    }
    </script>
</div>